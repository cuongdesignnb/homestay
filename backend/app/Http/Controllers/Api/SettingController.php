<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Get all settings for frontend (public)
     */
    public function index(Request $request)
    {
        $locale = $request->header('Accept-Language', $request->input('lang', 'vi'));
        app()->setLocale($locale);

        $settings = Setting::getAllForFrontend($locale);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get settings by group
     */
    public function getByGroup(Request $request, string $group)
    {
        $locale = $request->header('Accept-Language', $request->input('lang', 'vi'));
        app()->setLocale($locale);

        $settings = Setting::getByGroup($group, $locale);

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Get single setting by key
     */
    public function show(Request $request, string $key)
    {
        $locale = $request->header('Accept-Language', $request->input('lang', 'vi'));
        
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $setting->key,
                'value' => $locale === 'vi' ? $setting->value_vi : ($setting->value_en ?? $setting->value_vi),
                'value_vi' => $setting->value_vi,
                'value_en' => $setting->value_en,
                'type' => $setting->type,
                'group' => $setting->group,
            ],
        ]);
    }

    /**
     * Admin: Get all settings for management
     */
    public function adminIndex()
    {
        $settings = Setting::orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        return response()->json([
            'success' => true,
            'data' => $settings,
        ]);
    }

    /**
     * Admin: Update settings (bulk)
     */
    public function bulkUpdate(Request $request)
    {
        try {
            $settings = $request->input('settings', []);
            
            \Log::info('Settings bulk update request:', ['settings' => $settings]);

            if (empty($settings)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No settings provided',
                ], 400);
            }

            foreach ($settings as $key => $data) {
                \Log::info('Processing setting:', ['key' => $key, 'data' => $data]);
                
                // Handle array values that need to be JSON encoded
                $valueVi = $data['value_vi'] ?? null;
                $valueEn = $data['value_en'] ?? null;
                
                if (is_array($valueVi)) {
                    $valueVi = json_encode($valueVi);
                }
                if (is_array($valueEn)) {
                    $valueEn = json_encode($valueEn);
                }
                
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value_vi' => $valueVi,
                        'value_en' => $valueEn,
                        'group' => $data['group'] ?? 'general',
                        'type' => $data['type'] ?? 'text',
                        'label' => $data['label'] ?? $key,
                        'description' => $data['description'] ?? null,
                        'sort_order' => $data['sort_order'] ?? 0,
                    ]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Settings bulk update error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Admin: Update single setting
     */
    public function update(Request $request, string $key)
    {
        $validator = Validator::make($request->all(), [
            'value_vi' => 'nullable|string',
            'value_en' => 'nullable|string',
            'group' => 'nullable|string',
            'type' => 'nullable|string|in:text,textarea,image,json',
            'label' => 'nullable|string',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = Setting::updateOrCreate(
            ['key' => $key],
            array_filter($request->only([
                'value_vi', 'value_en', 'group', 'type', 'label', 'description', 'sort_order'
            ]), fn($v) => $v !== null)
        );

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting,
        ]);
    }

    /**
     * Admin: Upload image for setting
     */
    public function uploadImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120',
            'key' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('image');
        $filename = 'settings/' . $request->key . '_' . time() . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs('public', $filename);
        $url = Storage::url($path);

        // Update setting with image URL
        Setting::updateOrCreate(
            ['key' => $request->key],
            [
                'value_vi' => $url,
                'value_en' => $url,
                'type' => 'image',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'url' => $url,
        ]);
    }

    /**
     * Admin: Delete setting
     */
    public function destroy(string $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found',
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully',
        ]);
    }

    /**
     * Admin: Initialize default settings
     */
    public function initDefaults()
    {
        $defaults = [
            // General
            ['key' => 'site_name', 'group' => 'general', 'type' => 'text', 'label' => 'Site Name', 'value_vi' => 'Happy Island Tour', 'value_en' => 'Happy Island Tour', 'sort_order' => 1],
            ['key' => 'site_tagline', 'group' => 'general', 'type' => 'text', 'label' => 'Tagline', 'value_vi' => 'Nghỉ dưỡng trong phong cách tinh tế', 'value_en' => 'Retreat in effortless style', 'sort_order' => 2],
            ['key' => 'logo', 'group' => 'general', 'type' => 'image', 'label' => 'Logo', 'value_vi' => '', 'value_en' => '', 'sort_order' => 3],
            ['key' => 'favicon', 'group' => 'general', 'type' => 'image', 'label' => 'Favicon', 'value_vi' => '', 'value_en' => '', 'sort_order' => 4],
            
            // SEO
            ['key' => 'meta_title', 'group' => 'seo', 'type' => 'text', 'label' => 'Meta Title', 'value_vi' => 'Happy Island Tour - Đặt phòng Happy Island Tour trực tuyến', 'value_en' => 'Happy Island Tour - Book Your Perfect Stay', 'sort_order' => 1],
            ['key' => 'meta_description', 'group' => 'seo', 'type' => 'textarea', 'label' => 'Meta Description', 'value_vi' => 'Đặt phòng Happy Island Tour cao cấp với giá tốt nhất. Trải nghiệm kỳ nghỉ tuyệt vời cùng thiên nhiên.', 'value_en' => 'Book premium Happy Island Tour rooms at best prices. Experience wonderful vacation with nature.', 'sort_order' => 2],
            ['key' => 'meta_keywords', 'group' => 'seo', 'type' => 'text', 'label' => 'Meta Keywords', 'value_vi' => 'happy island tour, đặt phòng, du lịch, nghỉ dưỡng', 'value_en' => 'happy island tour, booking, travel, vacation', 'sort_order' => 3],
            ['key' => 'og_image', 'group' => 'seo', 'type' => 'image', 'label' => 'OG Image', 'value_vi' => '', 'value_en' => '', 'sort_order' => 4],
            
            // Contact
            ['key' => 'address', 'group' => 'contact', 'type' => 'textarea', 'label' => 'Address', 'value_vi' => '123 Đường Happy Island Tour, Quận 1, TP.HCM', 'value_en' => '123 Happy Island Tour Street, District 1, HCMC', 'sort_order' => 1],
            ['key' => 'phone', 'group' => 'contact', 'type' => 'text', 'label' => 'Phone', 'value_vi' => '+84 123 456 789', 'value_en' => '+84 123 456 789', 'sort_order' => 2],
            ['key' => 'email', 'group' => 'contact', 'type' => 'text', 'label' => 'Email', 'value_vi' => 'contact@homestay.com', 'value_en' => 'contact@homestay.com', 'sort_order' => 3],
            ['key' => 'working_hours', 'group' => 'contact', 'type' => 'text', 'label' => 'Working Hours', 'value_vi' => '8:00 - 22:00 hàng ngày', 'value_en' => '8:00 AM - 10:00 PM Daily', 'sort_order' => 4],
            
            // Social
            ['key' => 'facebook', 'group' => 'social', 'type' => 'text', 'label' => 'Facebook', 'value_vi' => 'https://facebook.com/homestay', 'value_en' => 'https://facebook.com/homestay', 'sort_order' => 1],
            ['key' => 'instagram', 'group' => 'social', 'type' => 'text', 'label' => 'Instagram', 'value_vi' => 'https://instagram.com/homestay', 'value_en' => 'https://instagram.com/homestay', 'sort_order' => 2],
            ['key' => 'zalo', 'group' => 'social', 'type' => 'text', 'label' => 'Zalo', 'value_vi' => '0123456789', 'value_en' => '0123456789', 'sort_order' => 3],
            ['key' => 'messenger', 'group' => 'social', 'type' => 'text', 'label' => 'Messenger', 'value_vi' => 'https://m.me/homestay', 'value_en' => 'https://m.me/homestay', 'sort_order' => 4],
            ['key' => 'youtube', 'group' => 'social', 'type' => 'text', 'label' => 'YouTube', 'value_vi' => '', 'value_en' => '', 'sort_order' => 5],
            ['key' => 'tiktok', 'group' => 'social', 'type' => 'text', 'label' => 'TikTok', 'value_vi' => '', 'value_en' => '', 'sort_order' => 6],
            
            // About
            ['key' => 'about_title', 'group' => 'about', 'type' => 'text', 'label' => 'About Title', 'value_vi' => 'Về Chúng Tôi', 'value_en' => 'About Us', 'sort_order' => 1],
            ['key' => 'about_subtitle', 'group' => 'about', 'type' => 'text', 'label' => 'About Subtitle', 'value_vi' => 'Nơi nghỉ dưỡng lý tưởng giữa thiên nhiên', 'value_en' => 'Your ideal retreat in nature', 'sort_order' => 2],
            ['key' => 'about_content', 'group' => 'about', 'type' => 'textarea', 'label' => 'About Content', 'value_vi' => '<p>Chào mừng bạn đến với Happy Island Tour - nơi nghỉ dưỡng lý tưởng kết hợp giữa sự thoải mái hiện đại và vẻ đẹp thiên nhiên hoang sơ.</p><p>Với hơn 10 năm kinh nghiệm trong ngành du lịch và lưu trú, chúng tôi tự hào mang đến cho du khách những trải nghiệm nghỉ dưỡng đẳng cấp với giá cả hợp lý.</p><p>Đội ngũ nhân viên chuyên nghiệp, nhiệt tình luôn sẵn sàng phục vụ bạn 24/7.</p>', 'value_en' => '<p>Welcome to Happy Island Tour - the ideal retreat that combines modern comfort with pristine natural beauty.</p><p>With over 10 years of experience in the travel and accommodation industry, we are proud to offer guests world-class vacation experiences at reasonable prices.</p><p>Our professional and enthusiastic staff is always ready to serve you 24/7.</p>', 'sort_order' => 3],
            ['key' => 'about_image', 'group' => 'about', 'type' => 'image', 'label' => 'About Image', 'value_vi' => '', 'value_en' => '', 'sort_order' => 4],
            ['key' => 'about_features', 'group' => 'about', 'type' => 'json', 'label' => 'About Features', 'value_vi' => '[{"icon":"star","title":"Đẳng cấp 5 sao","desc":"Tiêu chuẩn phục vụ cao cấp"},{"icon":"leaf","title":"Thiên nhiên tươi đẹp","desc":"Không gian xanh mát, trong lành"},{"icon":"heart","title":"Tận tâm phục vụ","desc":"Đội ngũ chuyên nghiệp 24/7"},{"icon":"shield","title":"An toàn & Bảo mật","desc":"Thanh toán an toàn, thông tin bảo mật"}]', 'value_en' => '[{"icon":"star","title":"5-Star Quality","desc":"Premium service standards"},{"icon":"leaf","title":"Beautiful Nature","desc":"Fresh and green environment"},{"icon":"heart","title":"Dedicated Service","desc":"Professional team 24/7"},{"icon":"shield","title":"Safe & Secure","desc":"Secure payment, protected info"}]', 'sort_order' => 5],
            
            // Banners
            ['key' => 'banner_1_image', 'group' => 'banner', 'type' => 'image', 'label' => 'Banner 1 Image', 'value_vi' => '', 'value_en' => '', 'sort_order' => 1],
            ['key' => 'banner_1_title', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 1 Title', 'value_vi' => 'Ưu đãi mùa hè', 'value_en' => 'Summer Sale', 'sort_order' => 2],
            ['key' => 'banner_1_subtitle', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 1 Subtitle', 'value_vi' => 'Giảm đến 30% cho đặt phòng sớm', 'value_en' => 'Up to 30% off for early bookings', 'sort_order' => 3],
            ['key' => 'banner_1_link', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 1 Link', 'value_vi' => '/rooms', 'value_en' => '/rooms', 'sort_order' => 4],
            ['key' => 'banner_1_active', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 1 Active', 'value_vi' => '1', 'value_en' => '1', 'sort_order' => 5],
            
            ['key' => 'banner_2_image', 'group' => 'banner', 'type' => 'image', 'label' => 'Banner 2 Image', 'value_vi' => '', 'value_en' => '', 'sort_order' => 6],
            ['key' => 'banner_2_title', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 2 Title', 'value_vi' => 'Tour khám phá', 'value_en' => 'Discovery Tours', 'sort_order' => 7],
            ['key' => 'banner_2_subtitle', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 2 Subtitle', 'value_vi' => 'Trải nghiệm văn hóa địa phương độc đáo', 'value_en' => 'Experience unique local culture', 'sort_order' => 8],
            ['key' => 'banner_2_link', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 2 Link', 'value_vi' => '/tours', 'value_en' => '/tours', 'sort_order' => 9],
            ['key' => 'banner_2_active', 'group' => 'banner', 'type' => 'text', 'label' => 'Banner 2 Active', 'value_vi' => '1', 'value_en' => '1', 'sort_order' => 10],
            
            // Hero
            ['key' => 'hero_title', 'group' => 'hero', 'type' => 'text', 'label' => 'Hero Title', 'value_vi' => 'Chào mừng đến Happy Island Tour', 'value_en' => 'Welcome to Our Happy Island Tour', 'sort_order' => 1],
            ['key' => 'hero_subtitle', 'group' => 'hero', 'type' => 'text', 'label' => 'Hero Subtitle', 'value_vi' => 'Trải nghiệm sự thoải mái và thiên nhiên hòa quyện hoàn hảo', 'value_en' => 'Experience comfort and nature in perfect harmony', 'sort_order' => 2],
            ['key' => 'hero_image', 'group' => 'hero', 'type' => 'image', 'label' => 'Hero Image', 'value_vi' => '', 'value_en' => '', 'sort_order' => 3],
            
            // Footer
            ['key' => 'footer_about', 'group' => 'footer', 'type' => 'textarea', 'label' => 'Footer About', 'value_vi' => 'Happy Island Tour - Nơi nghỉ dưỡng lý tưởng với không gian xanh mát và dịch vụ chuyên nghiệp.', 'value_en' => 'Happy Island Tour - Your ideal retreat with green spaces and professional service.', 'sort_order' => 1],
            ['key' => 'footer_copyright', 'group' => 'footer', 'type' => 'text', 'label' => 'Copyright', 'value_vi' => '© 2024 Happy Island Tour. Tất cả quyền được bảo lưu.', 'value_en' => '© 2024 Happy Island Tour. All rights reserved.', 'sort_order' => 2],
        ];

        foreach ($defaults as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Default settings initialized successfully',
            'count' => count($defaults),
        ]);
    }

    /**
     * Admin: Test SMTP mail configuration
     */
    public function testEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'mail_receive_address' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $encryption = $request->mail_encryption === 'none' ? null : $request->mail_encryption;

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $request->mail_host,
                'mail.mailers.smtp.port' => (int) $request->mail_port,
                'mail.mailers.smtp.username' => $request->mail_username,
                'mail.mailers.smtp.password' => $request->mail_password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => $request->mail_from_address,
                'mail.from.name' => $request->mail_from_name,
            ]);

            \Illuminate\Support\Facades\Mail::purge();

            $receiveAddress = $request->mail_receive_address;
            $subject = '📧 Thử nghiệm cấu hình Email - Happy Island Tour';
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                <div style='background-color: #4f46e5; padding: 20px; text-align: center; border-radius: 6px 6px 0 0;'>
                    <h2 style='color: #ffffff; margin: 0;'>Cấu hình Email thành công!</h2>
                </div>
                <div style='padding: 20px;'>
                    <p>Chào bạn,</p>
                    <p>Đây là thư thử nghiệm được gửi từ hệ thống quản lý Homestay / Happy Island Tour.</p>
                    <p>Nếu bạn nhận được thư này, cấu hình SMTP của bạn đã hoạt động chính xác!</p>
                    <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;' />
                    <p style='font-size: 12px; color: #64748b;'>Thời gian gửi: " . now()->toDateTimeString() . "</p>
                </div>
            </div>";

            \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($receiveAddress, $subject, $body) {
                $message->to($receiveAddress)
                        ->subject($subject)
                        ->html($body);
            });

            return response()->json([
                'success' => true,
                'message' => 'Gửi email thử nghiệm thành công! Hãy kiểm tra hộp thư của bạn.',
            ]);
        } catch (\Exception $e) {
            \Log::error('SMTP Test Mail Failure: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi SMTP: ' . $e->getMessage(),
            ], 500);
        }
    }
}
