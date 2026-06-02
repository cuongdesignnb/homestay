<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Payment;
use App\Services\SePayService;
use App\Services\MailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        // Only authenticated users can list their bookings
        if (!auth()->check()) {
            return response()->json(['message' => 'Authentication required'], 401);
        }
        
        $bookings = $request->user()
            ->bookings()
            ->with(['room'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'guests' => 'required|integer|min:1',
            'guest_name' => 'required|string',
            'guest_email' => 'required|email',
            'guest_phone' => 'required|string',
            'special_requests' => 'nullable|string',
            'payment_method' => 'nullable|string|in:sepay,pay_at_checkin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $room = Room::findOrFail($request->room_id);

        // Check availability
        if (!$room->isAvailable($request->check_in, $request->check_out)) {
            return response()->json(['message' => 'Room is not available for the selected dates'], 422);
        }

        // Check capacity
        if ($request->guests > $room->capacity) {
            return response()->json(['message' => 'Number of guests exceeds room capacity'], 422);
        }

        // Calculate pricing - no tax
        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $totalNights = $checkIn->diffInDays($checkOut);
        
        $roomPrice = $room->discount_price ?? $room->price_per_night;
        $totalAmount = $roomPrice * $totalNights;
        $finalAmount = $totalAmount;

        // Use database transaction to ensure atomicity
        try {
            return DB::transaction(function () use ($request, $room, $totalNights, $roomPrice, $totalAmount, $finalAmount) {
                $booking = new Booking();
                $booking->user_id = $request->user()?->id; // Optional user for guest bookings
                $booking->room_id = $request->room_id;
                $booking->booking_number = $booking->generateBookingNumber();
                $booking->check_in = $request->check_in;
                $booking->check_in_time = $request->check_in_time ?? '14:00';
                $booking->check_out = $request->check_out;
                $booking->check_out_time = $request->check_out_time ?? '12:00';
                $booking->guests = $request->guests;
                $booking->total_nights = $totalNights;
                $booking->room_price = $roomPrice;
                $booking->total_amount = $totalAmount;
                $booking->tax_amount = 0;
                $booking->final_amount = $finalAmount;
                $booking->guest_name = $request->guest_name;
                $booking->guest_email = $request->guest_email;
                $booking->guest_phone = $request->guest_phone;
                $booking->special_requests = $request->special_requests;
                $booking->payment_method = $request->payment_method ?? 'bank_transfer';
                
                // Nếu thanh toán khi nhận phòng, xác nhận giữ chỗ
                if ($request->payment_method === 'pay_at_checkin') {
                    $booking->status = 'confirmed';
                    $booking->payment_status = 'pending';
                }
                
                $booking->save();

                // Send email notifications
                try {
                    MailNotificationService::sendRoomBookingNotification($booking);
                } catch (\Exception $e) {
                    \Log::error('Room Booking Email Notification Error: ' . $e->getMessage());
                }

                $response = [
                    'message' => 'Booking created successfully',
                    'booking' => $booking->load('room'),
                ];

                // Nếu thanh toán online (chuyển khoản qua QR)
                if ($request->payment_method !== 'pay_at_checkin') {
                    $sePayService = new SePayService();
                    
                    // Tạo payment record
                    $payment = $sePayService->createPayment($booking);
                    
                    // Trả về thông tin QR Code để thanh toán
                    $response['payment'] = $sePayService->getPaymentInfo($payment, $booking);
                    $response['payment']['id'] = $payment->id;
                    $response['payment']['transaction_id'] = $payment->transaction_id;
                }

                return response()->json($response, 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create booking. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function guestLookup($bookingNumber, Request $request)
    {
        $request->validate([
            'guest_email' => 'required|email'
        ]);

        $booking = Booking::with(['room', 'payment'])
            ->where('booking_number', $bookingNumber)
            ->where('guest_email', $request->guest_email)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found or email does not match'], 404);
        }

        return response()->json($booking);
    }

    public function guestCancel($bookingNumber, Request $request)
    {
        $request->validate([
            'guest_email' => 'required|email'
        ]);

        $booking = Booking::where('booking_number', $bookingNumber)
            ->where('guest_email', $request->guest_email)
            ->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found or email does not match'], 404);
        }

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 422);
        }

        if (in_array($booking->status, ['checked_in', 'checked_out'])) {
            return response()->json(['message' => 'Cannot cancel booking in current status'], 422);
        }

        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->save();

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }

    public function show($id)
    {
        $query = Booking::with(['room', 'payment']);
        
        // If user is authenticated, filter by user_id
        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            // For guests, allow access by booking_number instead of ID
            $query->where('booking_number', $id);
        }
        
        $booking = $query->firstOrFail();

        return response()->json($booking);
    }

    public function cancel($id)
    {
        // This route is for authenticated users only
        $booking = Booking::where('user_id', auth()->id())->findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Booking is already cancelled'], 422);
        }

        if (in_array($booking->status, ['checked_in', 'checked_out'])) {
            return response()->json(['message' => 'Cannot cancel booking in current status'], 422);
        }

        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->save();

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'booking' => $booking,
        ]);
    }
}
