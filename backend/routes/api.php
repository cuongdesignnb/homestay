<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\RoomCategoryController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\TourCategoryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\GuestReviewController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\Admin\AdminRoomController;
use App\Http\Controllers\Api\Admin\AdminRoomCategoryController;
use App\Http\Controllers\Api\Admin\AdminBookingController;
use App\Http\Controllers\Api\Admin\AdminTourController;
use App\Http\Controllers\Api\Admin\AdminTourCategoryController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminBlogController;
use App\Http\Controllers\Api\Admin\AdminGuestReviewController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\MediaController;
use App\Http\Controllers\Api\Admin\MediaAlbumController;
use App\Http\Controllers\Api\Admin\MenuCategoryController;
use App\Http\Controllers\Api\Admin\MenuItemController;
use App\Http\Controllers\Api\Admin\RestaurantSettingController;
use App\Http\Controllers\Api\Admin\AdminCarRentalController;
use App\Http\Controllers\Api\Admin\AdminCarRentalCategoryController;
use App\Http\Controllers\Api\SePayWebhookController;
use App\Http\Controllers\Api\CarRentalController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\Admin\AdminEquipmentController;
use App\Http\Controllers\Api\Admin\AdminEquipmentCategoryController;
use App\Http\Controllers\Api\Admin\AdminEquipmentOrderController;
use App\Http\Controllers\Api\Admin\AdminNavMenuController;
use App\Http\Controllers\Api\NavMenuController;

// Debug SePay (XÓA SAU KHI DEBUG XONG)
Route::get('/debug-sepay', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now()->toDateTimeString(),
        'config' => [
            'webhook_token_set' => !empty(config('sepay.webhook_token')),
            'webhook_token_preview' => config('sepay.webhook_token') ? substr(config('sepay.webhook_token'), 0, 15) . '...' : 'NOT SET',
            'pattern' => config('sepay.pattern'),
            'bank_account' => config('sepay.bank.account_number'),
        ],
        'webhook_url' => url('/api/sepay/webhook'),
        'sepay_transactions_table' => \Schema::hasTable('sepay_transactions'),
    ]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Navigation Menus (public)
Route::get('/nav-menus/{name}', [NavMenuController::class, 'getByName']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Settings (public)
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/group/{group}', [SettingController::class, 'getByGroup']);
Route::get('/settings/{key}', [SettingController::class, 'show']);

// Public resource routes
Route::get('/rooms', [RoomController::class, 'index']);
Route::get('/rooms/{id}', [RoomController::class, 'show']);
Route::post('/rooms/check-availability', [RoomController::class, 'checkAvailability']);
Route::get('/room-categories', [RoomCategoryController::class, 'index']);

Route::get('/tours', [TourController::class, 'index']);
Route::get('/tours/popular', [TourController::class, 'popular']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::get('/tour-categories', [TourCategoryController::class, 'index']);

// Car Rentals (public)
Route::get('/car-rentals', [CarRentalController::class, 'index']);
Route::get('/car-rentals/featured', [CarRentalController::class, 'featured']);
Route::get('/car-rentals/{id}', [CarRentalController::class, 'show']);

// Equipment Rental & Shop (public)
Route::get('/equipments', [EquipmentController::class, 'index']);
Route::get('/equipments/{id}', [EquipmentController::class, 'show']);
Route::get('/equipment-categories', [EquipmentController::class, 'categories']);
Route::post('/equipment-orders', [EquipmentController::class, 'createOrder']);

Route::get('/blog/posts', [BlogController::class, 'index']);
Route::get('/blog/posts/{slug}', [BlogController::class, 'show']);
Route::get('/blog/categories', [BlogController::class, 'categories']);

// Restaurant Menu (public)
Route::get('/menu', [MenuController::class, 'index']);
Route::get('/menu/categories', [MenuController::class, 'categories']);
Route::get('/menu/categories/{slug}', [MenuController::class, 'category']);
Route::get('/menu/featured', [MenuController::class, 'featured']);
Route::get('/restaurant/info', [MenuController::class, 'restaurantInfo']);

// Guest Reviews (public)
Route::get('/rooms/{roomId}/guest-reviews', [GuestReviewController::class, 'roomReviews']);
Route::post('/rooms/{roomId}/guest-reviews', [GuestReviewController::class, 'storeRoomReview']);
Route::get('/tours/{tourId}/guest-reviews', [GuestReviewController::class, 'tourReviews']);
Route::post('/tours/{tourId}/guest-reviews', [GuestReviewController::class, 'storeTourReview']);

// Protected routes (requires authentication)
// Guest booking (no auth required)
Route::post('/bookings', [BookingController::class, 'store']);
Route::get('/bookings/lookup/{bookingNumber}', [BookingController::class, 'guestLookup']);
Route::put('/bookings/cancel/{bookingNumber}', [BookingController::class, 'guestCancel']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);

    // Authenticated user bookings
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Tour Bookings
    Route::post('/tour-bookings', [TourController::class, 'bookTour']);
    Route::get('/tour-bookings', [TourController::class, 'userBookings']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/my-reviews', [ReviewController::class, 'userReviews']);

    // Payments
    Route::post('/payments/sepay', [PaymentController::class, 'processSePay']);
    Route::post('/payments/paypal', [PaymentController::class, 'processPayPal']);
    Route::get('/payments/{id}/form', [PaymentController::class, 'getPaymentForm']);
    Route::post('/payments/callback', [PaymentController::class, 'callback']);
});

// Payment status check (public - for QR payment page)
Route::get('/payments/{id}/status', [PaymentController::class, 'checkStatus']);

// Payment Callbacks (public - no auth required for IPN/Webhook)
Route::post('/payment/sepay/callback', [PaymentController::class, 'sePayCallback']);
Route::post('/payment/sepay/webhook', [PaymentController::class, 'sePayWebhook']);
Route::post('/payment/paypal/callback', [PaymentController::class, 'paypalCallback']);

// Admin routes (requires authentication and admin role)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Room Management
    Route::apiResource('rooms', AdminRoomController::class);
    Route::post('rooms/{room}/upload-images', [AdminRoomController::class, 'uploadImages']);
    Route::delete('rooms/{room}/images/{imageId}', [AdminRoomController::class, 'deleteImage']);

    // Room Categories
    Route::get('room-categories', [AdminRoomCategoryController::class, 'index']);
    Route::post('room-categories', [AdminRoomCategoryController::class, 'store']);
    Route::put('room-categories/{roomCategory}', [AdminRoomCategoryController::class, 'update']);
    Route::delete('room-categories/{roomCategory}', [AdminRoomCategoryController::class, 'destroy']);

    // Booking Management
    Route::get('bookings', [AdminBookingController::class, 'index']);
    Route::get('bookings/{id}', [AdminBookingController::class, 'show']);
    Route::put('bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
    Route::get('bookings/export', [AdminBookingController::class, 'export']);

    // Tour Management
    Route::apiResource('tours', AdminTourController::class);
    Route::get('tour-bookings', [AdminTourController::class, 'bookings']);
    Route::put('tour-bookings/{id}/status', [AdminTourController::class, 'updateBookingStatus']);
    Route::get('tour-categories', [AdminTourCategoryController::class, 'index']);
    Route::post('tour-categories', [AdminTourCategoryController::class, 'store']);
    Route::put('tour-categories/{tourCategory}', [AdminTourCategoryController::class, 'update']);
    Route::delete('tour-categories/{tourCategory}', [AdminTourCategoryController::class, 'destroy']);

    // Car Rentals Management
    Route::apiResource('car-rentals', AdminCarRentalController::class);
    Route::get('car-rental-categories', [AdminCarRentalCategoryController::class, 'index']);
    Route::post('car-rental-categories', [AdminCarRentalCategoryController::class, 'store']);
    Route::put('car-rental-categories/{carRentalCategory}', [AdminCarRentalCategoryController::class, 'update']);
    Route::delete('car-rental-categories/{carRentalCategory}', [AdminCarRentalCategoryController::class, 'destroy']);

    // Equipment Rental & Shop Management
    Route::apiResource('equipments', AdminEquipmentController::class);
    Route::get('equipment-categories', [AdminEquipmentCategoryController::class, 'index']);
    Route::post('equipment-categories', [AdminEquipmentCategoryController::class, 'store']);
    Route::put('equipment-categories/{equipmentCategory}', [AdminEquipmentCategoryController::class, 'update']);
    Route::delete('equipment-categories/{equipmentCategory}', [AdminEquipmentCategoryController::class, 'destroy']);

    // Equipment Orders Management
    Route::get('equipment-orders', [AdminEquipmentOrderController::class, 'index']);
    Route::get('equipment-orders/{id}', [AdminEquipmentOrderController::class, 'show']);
    Route::put('equipment-orders/{id}/status', [AdminEquipmentOrderController::class, 'updateStatus']);
    Route::delete('equipment-orders/{id}', [AdminEquipmentOrderController::class, 'destroy']);

    // User Management
    Route::get('users', [AdminUserController::class, 'index']);
    Route::get('users/{id}', [AdminUserController::class, 'show']);
    Route::put('users/{id}', [AdminUserController::class, 'update']);
    Route::delete('users/{id}', [AdminUserController::class, 'destroy']);
    Route::put('users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);

    // Blog Management
    Route::apiResource('blog/posts', AdminBlogController::class);
    Route::post('blog/posts/{post}/publish', [AdminBlogController::class, 'publish']);
    Route::post('blog/posts/{post}/unpublish', [AdminBlogController::class, 'unpublish']);
    Route::get('blog/categories', [AdminBlogController::class, 'categories']);
    Route::post('blog/categories', [AdminBlogController::class, 'storeCategory']);

    // Media Library
    Route::get('media', [MediaController::class, 'index']);
    Route::post('media', [MediaController::class, 'store']);
    Route::delete('media/{media}', [MediaController::class, 'destroy']);

    // Media Albums
    Route::apiResource('media-albums', MediaAlbumController::class);

    // Guest Reviews Management
    Route::get('guest-reviews', [AdminGuestReviewController::class, 'index']);
    Route::get('guest-reviews/stats', [AdminGuestReviewController::class, 'stats']);
    Route::get('guest-reviews/{guestReview}', [AdminGuestReviewController::class, 'show']);
    Route::post('guest-reviews/{guestReview}/approve', [AdminGuestReviewController::class, 'approve']);
    Route::post('guest-reviews/{guestReview}/reject', [AdminGuestReviewController::class, 'reject']);
    Route::delete('guest-reviews/{guestReview}', [AdminGuestReviewController::class, 'destroy']);
    Route::post('guest-reviews/bulk', [AdminGuestReviewController::class, 'bulkAction']);

    // Settings Management
    Route::get('settings', [SettingController::class, 'adminIndex']);
    Route::post('settings/bulk', [SettingController::class, 'bulkUpdate']);
    Route::post('settings/test-email', [SettingController::class, 'testEmail']);
    Route::post('settings/init', [SettingController::class, 'initDefaults']);
    Route::put('settings/{key}', [SettingController::class, 'update']);
    Route::post('settings/upload', [SettingController::class, 'uploadImage']);
    Route::delete('settings/{key}', [SettingController::class, 'destroy']);

    // Menu Categories Management
    Route::get('menu-categories', [MenuCategoryController::class, 'index']);
    Route::post('menu-categories', [MenuCategoryController::class, 'store']);
    Route::get('menu-categories/{menuCategory}', [MenuCategoryController::class, 'show']);
    Route::put('menu-categories/{menuCategory}', [MenuCategoryController::class, 'update']);
    Route::delete('menu-categories/{menuCategory}', [MenuCategoryController::class, 'destroy']);
    Route::post('menu-categories/order', [MenuCategoryController::class, 'updateOrder']);

    // Menu Items Management
    Route::get('menu-items', [MenuItemController::class, 'index']);
    Route::post('menu-items', [MenuItemController::class, 'store']);
    Route::get('menu-items/{menuItem}', [MenuItemController::class, 'show']);
    Route::put('menu-items/{menuItem}', [MenuItemController::class, 'update']);
    Route::delete('menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
    Route::post('menu-items/order', [MenuItemController::class, 'updateOrder']);
    Route::post('menu-items/{menuItem}/toggle-availability', [MenuItemController::class, 'toggleAvailability']);
    Route::post('menu-items/{menuItem}/toggle-featured', [MenuItemController::class, 'toggleFeatured']);

    // Restaurant Settings
    Route::get('restaurant-settings', [RestaurantSettingController::class, 'index']);
    Route::put('restaurant-settings', [RestaurantSettingController::class, 'update']);

    // Navigation Menus Management
    Route::get('nav-menus', [AdminNavMenuController::class, 'index']);
    Route::post('nav-menus', [AdminNavMenuController::class, 'store']);
    Route::get('nav-menus/available-routes', [AdminNavMenuController::class, 'availableRoutes']);
    Route::get('nav-menus/{navMenu}', [AdminNavMenuController::class, 'show']);
    Route::put('nav-menus/{navMenu}', [AdminNavMenuController::class, 'update']);
    Route::delete('nav-menus/{navMenu}', [AdminNavMenuController::class, 'destroy']);
    Route::put('nav-menus/{navMenu}/tree', [AdminNavMenuController::class, 'updateTree']);
    Route::post('nav-menus/{navMenu}/items', [AdminNavMenuController::class, 'addItem']);
});
