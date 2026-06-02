<?php
/**
 * Social Crawler Meta Tags Handler
 * Detects Facebook, Zalo, Twitter crawlers and serves proper OG meta tags
 * for Vue.js SPA pages. Falls through to the normal SPA for regular visitors.
 * 
 * Deploy: place this at backend/public/social-meta.php
 * Nginx: add `location /` handler to check crawler user-agent
 */

// Detect social media crawlers
function isSocialCrawler(): bool
{
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $crawlers = [
        'facebookexternalhit',
        'Facebot',
        'Twitterbot',
        'LinkedInBot',
        'WhatsApp',
        'TelegramBot',
        'Slackbot',
        'Discordbot',
        'Pinterest',
        'Zalobot',
        'ZaloSharePreview',
        'Zalo',
        'SkypeUriPreview',
        'Viber',
        'Line',
    ];

    foreach ($crawlers as $crawler) {
        if (stripos($userAgent, $crawler) !== false) {
            return true;
        }
    }
    return false;
}

if (!isSocialCrawler()) {
    // Not a crawler - serve the normal SPA index.html
    $indexPath = __DIR__ . '/index.html';
    if (file_exists($indexPath)) {
        echo file_get_contents($indexPath);
    } else {
        http_response_code(404);
        echo 'Not found';
    }
    exit;
}

// ============================================================
// Social Crawler: Build dynamic meta tags from DB settings & URL
// ============================================================

// Minimal Laravel bootstrap to access DB
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the application
$request = Illuminate\Http\Request::capture();
$app->instance('request', $request);
$kernel->bootstrap();

use App\Models\Tour;
use App\Models\Room;
use App\Models\Setting;

// Parse the current URL path
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

// Detect locale from path or Accept-Language
$locale = 'vi'; // default
if (preg_match('#^/(en|vi)/#', $path, $m)) {
    $locale = $m[1];
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && str_starts_with(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']), 'en')) {
    $locale = 'en';
}

// Get site settings using the Setting model's static helper
$siteName = Setting::get('site_name', $locale, 'Happy Island Tour');
$defaultTitle = Setting::get('meta_title', $locale) ?: $siteName;
$defaultDescription = Setting::get('meta_description', $locale) ?: "Discover amazing tours, rooms, and experiences. Book your adventure today!";
$defaultImage = Setting::get('og_image', $locale) ?: '';
$siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'condaoislandtour.com');

// Page-specific meta
$title = $defaultTitle;
$description = $defaultDescription;
$image = $defaultImage;
$type = 'website';

// Helper to get localized value
function getLocalized($item, string $field, string $locale, string $fallback = ''): string
{
    if (!$item) return $fallback;
    $localizedField = "{$field}_{$locale}";
    return $item->$localizedField ?? $item->$field ?? $fallback;
}

try {
    // Tour detail page: /tours/{id} or /tours/{id}/{slug}
    if (preg_match('#^/tours/(\d+)#', $path, $matches)) {
        $tour = Tour::find($matches[1]);
        if ($tour) {
            $title = getLocalized($tour, 'name', $locale, $defaultTitle) . ' - ' . $siteName;
            $desc = getLocalized($tour, 'description', $locale, '');
            $description = $desc ? mb_substr(strip_tags($desc), 0, 200) . '...' : $defaultDescription;
            $image = $tour->cover_image ?: ($tour->images[0] ?? $defaultImage);
            $type = 'article';
        }
    }
    // Room detail page: /rooms/{id}
    elseif (preg_match('#^/rooms/(\d+)#', $path, $matches)) {
        $room = Room::find($matches[1]);
        if ($room) {
            $title = getLocalized($room, 'name', $locale, $defaultTitle) . ' - ' . $siteName;
            $desc = getLocalized($room, 'description', $locale, '');
            $description = $desc ? mb_substr(strip_tags($desc), 0, 200) . '...' : $defaultDescription;
            $image = $room->cover_image ?: ($room->images[0] ?? $defaultImage);
            $type = 'article';
        }
    }
    // Tours list page
    elseif ($path === '/tours' || $path === '/tours/') {
        $title = ($locale === 'vi' ? 'Danh sách Tour' : 'Tours') . ' - ' . $siteName;
    }
    // Rooms list page
    elseif ($path === '/rooms' || $path === '/rooms/') {
        $title = ($locale === 'vi' ? 'Danh sách Phòng' : 'Rooms') . ' - ' . $siteName;
    }
} catch (\Exception $e) {
    // Use defaults on any error
}

// Ensure absolute URL for images
if ($image && !str_starts_with($image, 'http')) {
    $image = $siteUrl . '/' . ltrim($image, '/');
}

$canonicalUrl = $siteUrl . $path;

// Output minimal HTML with proper OG tags
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= $locale ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    
    <!-- Open Graph / Facebook / Zalo -->
    <meta property="og:type" content="<?= $type ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:site_name" content="<?= htmlspecialchars($siteName) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <?php if ($image): ?>
    <meta property="og:image" content="<?= htmlspecialchars($image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    <meta property="og:locale" content="<?= $locale === 'vi' ? 'vi_VN' : 'en_US' ?>">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <?php if ($image): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($image) ?>">
    <?php endif; ?>
</head>
<body>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($description) ?></p>
    <?php if ($image): ?>
    <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>">
    <?php endif; ?>
</body>
</html>
