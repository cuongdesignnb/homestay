<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Tour;
use App\Models\TourBooking;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return response()->json([
            'metrics' => [
                'rooms' => Room::count(),
                'bookings' => Booking::count(),
                'tour_bookings' => TourBooking::count(),
            ],
        ]);
    }

    public function stats()
    {
        $now = Carbon::now();
        $from30Days = $now->copy()->subDays(30);
        $from7Days = $now->copy()->subDays(7);
        $prevFrom30Days = $now->copy()->subDays(60);
        $prevFrom7Days = $now->copy()->subDays(14);

        // Total counts
        $totalBookings = Booking::count();
        $totalRooms = Room::count();
        $totalTours = Tour::count();
        $totalUsers = User::where('role', 'user')->count();

        // 7-day comparisons for KPI delta
        $bookings7Days = Booking::where('created_at', '>=', $from7Days)->count();
        $bookingsPrev7Days = Booking::whereBetween('created_at', [$prevFrom7Days, $from7Days])->count();
        $bookingsDelta = $bookingsPrev7Days > 0 ? round((($bookings7Days - $bookingsPrev7Days) / $bookingsPrev7Days) * 100) : 0;

        $users7Days = User::where('role', 'user')->where('created_at', '>=', $from7Days)->count();
        $usersPrev7Days = User::where('role', 'user')->whereBetween('created_at', [$prevFrom7Days, $from7Days])->count();
        $usersDelta = $usersPrev7Days > 0 ? round((($users7Days - $usersPrev7Days) / $usersPrev7Days) * 100) : 0;

        // Revenue calculations
        $totalRevenue = Payment::where('status', 'completed')->sum('amount');
        $revenue30Days = Payment::where('status', 'completed')
            ->where('created_at', '>=', $from30Days)
            ->sum('amount');
        $revenuePrev30Days = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$prevFrom30Days, $from30Days])
            ->sum('amount');
        $revenueDelta = $revenuePrev30Days > 0 ? round((($revenue30Days - $revenuePrev30Days) / $revenuePrev30Days) * 100) : 0;

        // Weekly revenue trend (last 4 weeks)
        $revenueTrend = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = $now->copy()->subWeeks($i + 1);
            $weekEnd = $now->copy()->subWeeks($i);
            $weekRevenue = Payment::where('status', 'completed')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('amount');
            $revenueTrend[] = $weekRevenue;
        }
        // Normalize to percentages for chart
        $maxRevenue = max($revenueTrend) ?: 1;
        $revenueTrendPercent = array_map(fn($v) => round(($v / $maxRevenue) * 100), $revenueTrend);

        // Recent bookings
        $recentBookings = Booking::with(['room', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'guest' => $booking->guest_name,
                    'room' => $booking->room->name ?? 'N/A',
                    'nights' => $booking->total_nights,
                    'date' => $this->formatRelativeDate($booking->created_at),
                    'status' => $booking->status,
                    'amount' => $booking->final_amount,
                ];
            });

        // Room occupancy - calculate based on current bookings
        $roomOccupancy = Room::withCount(['bookings' => function ($query) use ($now) {
            $query->where('check_in', '<=', $now)
                  ->where('check_out', '>=', $now)
                  ->whereIn('status', ['confirmed', 'checked_in']);
        }])
        ->limit(5)
        ->get()
        ->map(function ($room) {
            // Calculate occupancy based on booking ratio last 30 days
            $daysBooked = Booking::where('room_id', $room->id)
                ->where('check_out', '>=', Carbon::now()->subDays(30))
                ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                ->sum('total_nights');
            $occupancyRate = min(100, round(($daysBooked / 30) * 100));
            
            return [
                'id' => $room->id,
                'label' => $room->name,
                'value' => $occupancyRate,
                'segment' => $room->room_type ?? 'Phòng tiêu chuẩn',
                'is_occupied' => $room->bookings_count > 0,
            ];
        });

        $averageOccupancy = $roomOccupancy->avg('value') ?: 0;

        // Pending actions / tasks
        $pendingBookings = Booking::where('status', 'pending')->count();
        $pendingPayments = Payment::where('status', 'pending')->count();
        $pendingReviews = DB::table('reviews')->where('status', 'pending')->count();

        // Tour bookings stats
        $tourBookings7Days = TourBooking::where('created_at', '>=', $from7Days)->count();
        $tourBookingsPrev7Days = TourBooking::whereBetween('created_at', [$prevFrom7Days, $from7Days])->count();
        $toursDelta = $tourBookingsPrev7Days > 0 ? round((($tourBookings7Days - $tourBookingsPrev7Days) / $tourBookingsPrev7Days) * 100) : 0;

        // Today's bookings
        $todayBookings = Booking::whereDate('created_at', $now->toDateString())->count();

        // Top rooms by bookings
        $topRooms = Room::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get()
            ->map(function ($room) {
                // Calculate total revenue for this room
                $totalRevenue = Booking::where('room_id', $room->id)
                    ->whereIn('status', ['confirmed', 'checked_in', 'checked_out'])
                    ->sum('final_amount');
                
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'bookings_count' => $room->bookings_count,
                    'total_revenue' => $totalRevenue,
                ];
            });

        return response()->json([
            'range' => [
                'from' => $from30Days->toDateString(),
                'to' => $now->toDateString(),
            ],
            // KPI Cards
            'total_bookings' => $totalBookings,
            'total_rooms' => $totalRooms,
            'total_tours' => $totalTours,
            'total_users' => $totalUsers,
            'bookings_delta' => ($bookingsDelta >= 0 ? '+' : '') . $bookingsDelta . '%',
            'users_delta' => ($usersDelta >= 0 ? '+' : '') . $usersDelta . '%',
            'tours_delta' => ($toursDelta >= 0 ? '+' : '') . $toursDelta . '%',
            'rooms_delta' => '+0%', // Rooms don't change often
            
            // Revenue
            'total_revenue' => $totalRevenue,
            'revenue_30_days' => $revenue30Days,
            'revenue_delta' => ($revenueDelta >= 0 ? '+' : '') . $revenueDelta . '%',
            'revenue_trend' => $revenueTrendPercent,
            'revenue_trend_raw' => $revenueTrend,
            
            // Recent bookings
            'recent_bookings' => $recentBookings,
            
            // Occupancy
            'occupancy' => [
                'average' => round($averageOccupancy),
                'rooms' => $roomOccupancy,
            ],
            
            // Pending tasks
            'pending_tasks' => [
                'bookings' => $pendingBookings,
                'payments' => $pendingPayments,
                'reviews' => $pendingReviews,
            ],
            
            // Counts for period
            'bookings_7_days' => $bookings7Days,
            'new_users_7_days' => $users7Days,
            'today_bookings' => $todayBookings,
            
            // Top rooms
            'top_rooms' => $topRooms,
        ]);
    }

    private function formatRelativeDate($date)
    {
        $carbon = Carbon::parse($date);
        $now = Carbon::now();
        
        if ($carbon->isToday()) {
            return 'Hôm nay · ' . $carbon->format('H:i');
        } elseif ($carbon->isYesterday()) {
            return 'Hôm qua · ' . $carbon->format('H:i');
        } elseif ($carbon->diffInDays($now) < 7) {
            return $carbon->diffInDays($now) . ' ngày trước';
        } else {
            return $carbon->format('d/m/Y');
        }
    }
}
