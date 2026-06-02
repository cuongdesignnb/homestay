<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $this->applyFilters($request)
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        $bookings->getCollection()->transform(fn (Booking $booking) => $this->transformBooking($booking));

        return response()->json($bookings);
    }

    public function show($id)
    {
        $booking = Booking::with(['room', 'user', 'payment'])->findOrFail($id);

        return response()->json($this->transformBooking($booking));
    }

    public function updateStatus(Request $request, $id)
    {
        $data = $request->validate([
            'status' => 'nullable|in:pending,confirmed,cancelled,checked_in,checked_out,completed',
            'payment_status' => 'nullable|in:unpaid,paid,pending,failed,refunded',
        ]);

        $booking = Booking::findOrFail($id);
        $booking->fill(array_filter($data, fn ($value) => !is_null($value)));
        $booking->save();
        $booking->load(['room', 'user', 'payment']);

        return response()->json([
            'message' => 'Booking updated successfully',
            'booking' => $this->transformBooking($booking),
        ]);
    }

    public function export(Request $request)
    {
        $filename = 'room-bookings-' . now()->format('Ymd_His') . '.csv';

        $query = $this->applyFilters($request)
            ->orderByDesc('created_at');

        $handle = fn () => $this->streamCsv($query);

        return Response::streamDownload($handle, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function streamCsv($query): void
    {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, [
            'Booking Number',
            'Guest Name',
            'Room',
            'Check-in',
            'Check-out',
            'Total Nights',
            'Final Amount',
            'Status',
            'Payment Status',
        ]);

        $query->chunk(200, function ($rows) use ($handle) {
            foreach ($rows as $booking) {
                fputcsv($handle, [
                    $booking->booking_number,
                    $booking->guest_name ?? optional($booking->user)->name,
                    optional($booking->room)->name,
                    optional($booking->check_in)?->format('Y-m-d'),
                    optional($booking->check_out)?->format('Y-m-d'),
                    $booking->total_nights,
                    $booking->final_amount,
                    $booking->status,
                    $booking->payment_status,
                ]);
            }
        });

        fclose($handle);
    }

    protected function applyFilters(Request $request)
    {
        return Booking::query()
            ->with(['room', 'user', 'payment'])
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->payment_status, fn ($query, $paymentStatus) => $query->where('payment_status', $paymentStatus))
            ->when($request->payment_method, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($request->booking_number, fn ($query, $number) => $query->where('booking_number', 'like', "%{$number}%"))
            ->when($request->from_date, fn ($query, $from) => $query->whereDate('check_in', '>=', $from))
            ->when($request->to_date, fn ($query, $to) => $query->whereDate('check_out', '<=', $to));
    }

    protected function transformBooking(Booking $booking): Booking
    {
        $booking->setAttribute('type', 'room');
        $booking->setAttribute('guest_display', $booking->guest_name ?? optional($booking->user)->name);
        $booking->setAttribute('payment_display', optional($booking->payment)->status ?? $booking->payment_status);
        $booking->setAttribute('amount_paid', optional($booking->payment)->amount);
        $booking->setAttribute('room_name', optional($booking->room)->name);

        return $booking;
    }
}
