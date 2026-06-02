<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Booking;
use App\Models\TourBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required_without:tour_booking_id|exists:bookings,id',
            'tour_booking_id' => 'required_without:booking_id|exists:tour_bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user()->id;

        // Verify booking belongs to user
        if ($request->has('booking_id')) {
            $booking = Booking::where('id', $request->booking_id)
                ->where('user_id', $userId)
                ->where('status', 'checked_out')
                ->firstOrFail();

            // Check if already reviewed
            if (Review::where('booking_id', $booking->id)->exists()) {
                return response()->json(['message' => 'You have already reviewed this booking'], 422);
            }

            $review = Review::create([
                'user_id' => $userId,
                'room_id' => $booking->room_id,
                'booking_id' => $booking->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        } else {
            $tourBooking = TourBooking::where('id', $request->tour_booking_id)
                ->where('user_id', $userId)
                ->where('status', 'completed')
                ->firstOrFail();

            // Check if already reviewed
            if (Review::where('tour_booking_id', $tourBooking->id)->exists()) {
                return response()->json(['message' => 'You have already reviewed this tour'], 422);
            }

            $review = Review::create([
                'user_id' => $userId,
                'tour_id' => $tourBooking->tour_id,
                'tour_booking_id' => $tourBooking->id,
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);
        }

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('user'),
        ], 201);
    }

    public function userReviews(Request $request)
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with(['room', 'tour'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }
}
