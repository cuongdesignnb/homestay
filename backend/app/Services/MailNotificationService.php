<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Booking;
use App\Models\TourBooking;
use App\Models\EquipmentOrder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailNotificationService
{
    /**
     * Configure Laravel Mailer dynamically based on database settings
     */
    public static function configureMail()
    {
        $enabled = Setting::getValue('mail_enable_notifications', 'false');
        if ($enabled !== 'true' && $enabled !== true && $enabled !== 1 && $enabled !== '1') {
            return false;
        }

        $host = Setting::getValue('mail_host');
        $port = Setting::getValue('mail_port', 587);
        $username = Setting::getValue('mail_username');
        $password = Setting::getValue('mail_password');
        $encryption = Setting::getValue('mail_encryption', 'tls');
        $fromAddress = Setting::getValue('mail_from_address');
        $fromName = Setting::getValue('mail_from_name', 'Happy Island Tour');

        if (empty($host) || empty($username) || empty($password)) {
            Log::warning('SMTP Mail Configuration is incomplete. Host, username, and password are required.');
            return false;
        }

        $encryptionValue = ($encryption === 'none' || empty($encryption)) ? null : $encryption;

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $encryptionValue,
            'mail.from.address' => $fromAddress ?: $username,
            'mail.from.name' => $fromName,
        ]);

        Mail::purge();
        return true;
    }

    /**
     * Send Room Booking Notification
     */
    public static function sendRoomBookingNotification(Booking $booking)
    {
        if (!self::configureMail()) {
            return;
        }

        $adminEmail = Setting::getValue('mail_receive_address');
        $guestEmail = $booking->guest_email;

        // 1. Send Alert to Admin
        if (!empty($adminEmail)) {
            $adminSubject = '🔔 [Đặt Phòng Mới] Mã đặt chỗ: ' . $booking->booking_number;
            $adminBody = self::compileRoomBookingHtml($booking, true);
            self::sendHtmlMail($adminEmail, $adminSubject, $adminBody);
        }

        // 2. Send Confirmation to Guest
        if (!empty($guestEmail)) {
            $guestSubject = '🌸 Xác nhận đặt phòng thành công - Happy Island Tour';
            $guestBody = self::compileRoomBookingHtml($booking, false);
            self::sendHtmlMail($guestEmail, $guestSubject, $guestBody);
        }
    }

    /**
     * Send Tour Booking Notification
     */
    public static function sendTourBookingNotification(TourBooking $booking)
    {
        if (!self::configureMail()) {
            return;
        }

        $adminEmail = Setting::getValue('mail_receive_address');
        $guestEmail = $booking->contact_email;

        // 1. Send Alert to Admin
        if (!empty($adminEmail)) {
            $adminSubject = '🔔 [Đặt Tour Mới] Mã đặt chỗ: ' . $booking->booking_number;
            $adminBody = self::compileTourBookingHtml($booking, true);
            self::sendHtmlMail($adminEmail, $adminSubject, $adminBody);
        }

        // 2. Send Confirmation to Guest
        if (!empty($guestEmail)) {
            $guestSubject = '🗺️ Xác nhận đăng ký Tour thành công - Happy Island Tour';
            $guestBody = self::compileTourBookingHtml($booking, false);
            self::sendHtmlMail($guestEmail, $guestSubject, $guestBody);
        }
    }

    /**
     * Send Equipment Order Notification
     */
    public static function sendEquipmentOrderNotification(EquipmentOrder $order)
    {
        if (!self::configureMail()) {
            return;
        }

        $adminEmail = Setting::getValue('mail_receive_address');
        $customerEmail = $order->customer_email;

        // 1. Send Alert to Admin
        if (!empty($adminEmail)) {
            $adminSubject = '🔔 [Đơn Hàng Mới] Mã đơn hàng: ' . $order->order_number;
            $adminBody = self::compileEquipmentOrderHtml($order, true);
            self::sendHtmlMail($adminEmail, $adminSubject, $adminBody);
        }

        // 2. Send Confirmation to Customer
        if (!empty($customerEmail)) {
            $customerSubject = '🏊 Xác nhận đơn hàng dụng cụ thành công - Happy Island Tour';
            $customerBody = self::compileEquipmentOrderHtml($order, false);
            self::sendHtmlMail($customerEmail, $customerSubject, $customerBody);
        }
    }

    /**
     * Helper to send HTML mail
     */
    protected static function sendHtmlMail($to, $subject, $body)
    {
        try {
            Mail::send([], [], function ($message) use ($to, $subject, $body) {
                $message->to($to)
                        ->subject($subject)
                        ->html($body);
            });
            return true;
        } catch (\Exception $e) {
            Log::error('MailNotificationService send failure: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compile Room Booking HTML Template
     */
    protected static function compileRoomBookingHtml(Booking $booking, bool $isAdmin)
    {
        $room = $booking->room;
        $roomName = $room ? ($room->name_vi ?: $room->name) : 'Phòng nghỉ';
        $title = $isAdmin ? '🔔 Thông báo Đặt phòng Mới' : '🌸 Xác nhận đặt phòng thành công';
        $sub = $isAdmin ? 'Một khách hàng vừa thực hiện đặt phòng trực tuyến.' : 'Cảm ơn bạn đã lựa chọn Happy Island Tour. Dưới đây là thông tin chi tiết về phòng của bạn.';
        
        $totalAmount = number_format($booking->final_amount) . ' VND';
        $checkIn = $booking->check_in->format('d/m/Y');
        $checkOut = $booking->check_out->format('d/m/Y');

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff;'>
            <div style='background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); padding: 25px; text-align: center; border-radius: 6px 6px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: bold;'>$title</h2>
                <p style='color: #e0e7ff; margin: 5px 0 0; font-size: 14px;'>$sub</p>
            </div>
            
            <div style='padding: 20px;'>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Mã đặt phòng:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b; font-family: monospace; font-weight: bold;'>{$booking->booking_number}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Phòng đặt:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b; font-weight: bold;'>$roomName</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Nhận phòng:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>$checkIn ({$booking->check_in_time})</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Trả phòng:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>$checkOut ({$booking->check_out_time})</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Số đêm nghỉ:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>{$booking->total_nights} đêm</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Số lượng khách:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>{$booking->guests} khách</td>
                    </tr>
                </table>
                
                <div style='border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 15px 0; margin-bottom: 20px;'>
                    <h3 style='margin: 0 0 10px; font-size: 16px; color: #1e293b;'>Thông tin khách hàng:</h3>
                    <p style='margin: 3px 0; color: #475569;'><strong>Họ và tên:</strong> {$booking->guest_name}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Số điện thoại:</strong> {$booking->guest_phone}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Email:</strong> {$booking->guest_email}</p>
                    " . ($booking->special_requests ? "<p style='margin: 3px 0; color: #475569;'><strong>Yêu cầu đặc biệt:</strong> {$booking->special_requests}</p>" : "") . "
                </div>
                
                <div style='background-color: #f8fafc; padding: 15px; border-radius: 6px; text-align: center;'>
                    <span style='font-size: 14px; color: #64748b; display: block;'>Tổng cộng thanh toán</span>
                    <strong style='font-size: 24px; color: #4f46e5;'>$totalAmount</strong>
                    <span style='font-size: 12px; color: #94a3b8; display: block; margin-top: 5px;'>Phương thức: " . ($booking->payment_method === 'pay_at_checkin' ? 'Thanh toán trực tiếp' : 'Chuyển khoản / Quét mã QR') . "</span>
                </div>
            </div>
            
            <div style='text-align: center; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
                <p style='margin: 2px 0;'>Happy Island Tour - Condao Island Adventures</p>
                <p style='margin: 2px 0;'>Email liên hệ: contact@homestay.com | Hotline: 1900 1234</p>
            </div>
        </div>";
    }

    /**
     * Compile Tour Booking HTML Template
     */
    protected static function compileTourBookingHtml(TourBooking $booking, bool $isAdmin)
    {
        $tour = $booking->tour;
        $tourName = $tour ? ($tour->name_vi ?: $tour->name) : 'Hành trình khám phá';
        $title = $isAdmin ? '🔔 Thông báo Đăng ký Tour Mới' : '🗺️ Xác nhận đăng ký Tour thành công';
        $sub = $isAdmin ? 'Một khách hàng vừa đăng ký tour trực tuyến.' : 'Cảm ơn bạn đã lựa chọn Happy Island Tour. Dưới đây là thông tin chi tiết về chuyến đi của bạn.';
        
        $totalAmount = number_format($booking->final_amount) . ' VND';
        $tourDate = date('d/m/Y', strtotime($booking->tour_date));

        // Addon list layout
        $addonsHtml = '';
        if ($booking->addons && $booking->addons->count() > 0) {
            $addonsHtml .= "
            <div style='margin-top: 15px; border-top: 1px dashed #cbd5e1; padding-top: 10px;'>
                <h4 style='margin: 0 0 8px 0; font-size: 14px; color: #1e293b;'>Dịch vụ đi kèm đã chọn:</h4>
                <table style='width: 100%; border-collapse: collapse; font-size: 13px;'>";
            foreach ($booking->addons as $addon) {
                $addonName = $addon->name_vi ?: $addon->name;
                $addonPrice = number_format($addon->price) . ' VND';
                $addonTotal = number_format($addon->total_amount) . ' VND';
                $unit = $addon->pricing_type === 'per_person' ? 'khách' : 'đoàn';
                $addonsHtml .= "
                <tr>
                    <td style='padding: 4px 0; color: #475569;'>+ {$addonName} ({$addonPrice}/{$unit} x {$addon->quantity})</td>
                    <td style='padding: 4px 0; text-align: right; color: #1e293b; font-weight: bold;'>{$addonTotal}</td>
                </tr>";
            }
            $addonsHtml .= "
                </table>
            </div>";
        }

        $variantRow = '';
        if (!empty($booking->tour_variant_name)) {
            $variantRow = "
            <tr>
                <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Biến thể:</td>
                <td style='padding: 8px 0; text-align: right; color: #1e293b; font-weight: bold;'>{$booking->tour_variant_name}</td>
            </tr>";
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff;'>
            <div style='background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); padding: 25px; text-align: center; border-radius: 6px 6px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: bold;'>$title</h2>
                <p style='color: #ccfbf1; margin: 5px 0 0; font-size: 14px;'>$sub</p>
            </div>
            
            <div style='padding: 20px;'>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Mã đặt tour:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b; font-family: monospace; font-weight: bold;'>{$booking->booking_number}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Tên Tour:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b; font-weight: bold;'>$tourName</td>
                    </tr>
                    $variantRow
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Ngày khởi hành:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>$tourDate</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Số người tham gia:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>{$booking->participants} người</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Giá tour cơ bản:</td>
                        <td style='padding: 8px 0; text-align: right; color: #1e293b;'>" . number_format($booking->price_per_person) . " VND " . ($booking->pricing_type === 'flat' ? '(trọn gói)' : '/khách') . "</td>
                    </tr>
                </table>
                
                $addonsHtml
                
                <div style='border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 15px 0; margin-bottom: 20px; margin-top: 15px;'>
                    <h3 style='margin: 0 0 10px; font-size: 16px; color: #1e293b;'>Thông tin khách hàng:</h3>
                    <p style='margin: 3px 0; color: #475569;'><strong>Họ và tên:</strong> {$booking->contact_name}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Số điện thoại:</strong> {$booking->contact_phone}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Email:</strong> {$booking->contact_email}</p>
                    " . ($booking->special_requests ? "<p style='margin: 3px 0; color: #475569;'><strong>Yêu cầu đặc biệt:</strong> {$booking->special_requests}</p>" : "") . "
                </div>
                
                <div style='background-color: #f8fafc; padding: 15px; border-radius: 6px; text-align: center;'>
                    <span style='font-size: 14px; color: #64748b; display: block;'>Tổng cộng thanh toán (gồm dịch vụ)</span>
                    <strong style='font-size: 24px; color: #0d9488;'>$totalAmount</strong>
                    <span style='font-size: 12px; color: #94a3b8; display: block; margin-top: 5px;'>Hình thức: Reserve & Pay Later (Thanh toán khi tham gia tour)</span>
                </div>
            </div>
            
            <div style='text-align: center; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
                <p style='margin: 2px 0;'>Happy Island Tour - Condao Island Adventures</p>
                <p style='margin: 2px 0;'>Email liên hệ: contact@homestay.com | Hotline: 1900 1234</p>
            </div>
        </div>";
    }

    /**
     * Compile Equipment Order HTML Template
     */
    protected static function compileEquipmentOrderHtml(EquipmentOrder $order, bool $isAdmin)
    {
        $title = $isAdmin ? '🔔 Thông báo Đơn hàng Mới' : '🏊 Xác nhận đơn hàng dụng cụ thành công';
        $sub = $isAdmin ? 'Một khách hàng vừa gửi đơn hàng trực tuyến.' : 'Cảm ơn bạn đã lựa chọn Happy Island Tour. Dưới đây là thông tin chi tiết đơn đặt dụng cụ của bạn.';
        
        $totalAmount = number_format($order->total_amount) . ' VND';

        $itemsRows = '';
        foreach ($order->items ?? [] as $item) {
            $itemName = $item['name_vi'] ?: $item['name'];
            $typeLabel = $item['type'] === 'rental' ? 'Thuê' : 'Mua';
            $rentalDaysLabel = $item['type'] === 'rental' ? " ({$item['rental_days']} ngày)" : '';
            $itemsRows .= "
            <tr>
                <td style='padding: 8px 0; border-bottom: 1px solid #f1f5f9; color: #1e293b;'>$itemName <small style='color:#64748b;'>[$typeLabel$rentalDaysLabel]</small></td>
                <td style='padding: 8px 0; border-bottom: 1px solid #f1f5f9; text-align: center; color: #475569;'>{$item['quantity']}</td>
                <td style='padding: 8px 0; border-bottom: 1px solid #f1f5f9; text-align: right; color: #1e293b;'>" . number_format($item['line_total']) . " VND</td>
            </tr>";
        }

        $rentalPeriodRow = '';
        if ($order->order_type === 'rental' && $order->rental_start_date) {
            $start = date('d/m/Y', strtotime($order->rental_start_date));
            $end = $order->rental_end_date ? date('d/m/Y', strtotime($order->rental_end_date)) : '';
            $rentalPeriodRow = "
            <tr>
                <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Thời hạn thuê:</td>
                <td colspan='2' style='padding: 8px 0; text-align: right; color: #1e293b;'>Từ $start đến $end ({$order->rental_days} ngày)</td>
            </tr>";
        }

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px; background-color: #ffffff;'>
            <div style='background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); padding: 25px; text-align: center; border-radius: 6px 6px 0 0;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 22px; font-weight: bold;'>$title</h2>
                <p style='color: #e0f2fe; margin: 5px 0 0; font-size: 14px;'>$sub</p>
            </div>
            
            <div style='padding: 20px;'>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Mã đơn hàng:</td>
                        <td colspan='2' style='padding: 8px 0; text-align: right; color: #1e293b; font-family: monospace; font-weight: bold;'>{$order->order_number}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; font-weight: bold; color: #475569;'>Loại đơn hàng:</td>
                        <td colspan='2' style='padding: 8px 0; text-align: right; color: #1e293b; font-weight: bold;'>" . ($order->order_type === 'rental' ? 'Thuê dụng cụ' : 'Mua dụng cụ') . "</td>
                    </tr>
                    $rentalPeriodRow
                </table>

                <h3 style='margin: 15px 0 10px; font-size: 15px; color: #1e293b; border-bottom: 2px solid #e2e8f0; padding-bottom: 5px;'>Danh sách dụng cụ:</h3>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                    <thead>
                        <tr style='border-bottom: 1px solid #cbd5e1; color: #475569;'>
                            <th style='padding: 6px 0; text-align: left;'>Sản phẩm</th>
                            <th style='padding: 6px 0; text-align: center;'>Số lượng</th>
                            <th style='padding: 6px 0; text-align: right;'>Tổng cộng</th>
                        </tr>
                    </thead>
                    <tbody>
                        $itemsRows
                    </tbody>
                </table>
                
                <div style='border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; padding: 15px 0; margin-bottom: 20px;'>
                    <h3 style='margin: 0 0 10px; font-size: 16px; color: #1e293b;'>Thông tin khách hàng:</h3>
                    <p style='margin: 3px 0; color: #475569;'><strong>Họ và tên:</strong> {$order->customer_name}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Số điện thoại:</strong> {$order->customer_phone}</p>
                    <p style='margin: 3px 0; color: #475569;'><strong>Email:</strong> " . ($order->customer_email ?: 'Không cung cấp') . "</p>
                    " . ($order->notes ? "<p style='margin: 3px 0; color: #475569;'><strong>Ghi chú:</strong> {$order->notes}</p>" : "") . "
                </div>
                
                <div style='background-color: #f8fafc; padding: 15px; border-radius: 6px; text-align: center;'>
                    <span style='font-size: 14px; color: #64748b; display: block;'>Tổng cộng thanh toán</span>
                    <strong style='font-size: 24px; color: #0284c7;'>$totalAmount</strong>
                </div>
            </div>
            
            <div style='text-align: center; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8;'>
                <p style='margin: 2px 0;'>Happy Island Tour - Condao Island Adventures</p>
                <p style='margin: 2px 0;'>Email liên hệ: contact@homestay.com | Hotline: 1900 1234</p>
            </div>
        </div>";
    }
}
