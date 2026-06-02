<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use App\Models\Room;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestReviewController extends Controller
{
    /**
     * Get approved reviews for a room
     */
    public function roomReviews(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);

        $reviews = GuestReview::where('reviewable_type', Room::class)
            ->where('reviewable_id', $room->id)
            ->approved()
            ->orderByDesc('approved_at')
            ->paginate($request->integer('per_page', 10));

        return response()->json($reviews);
    }

    /**
     * Get approved reviews for a tour
     */
    public function tourReviews(Request $request, $tourId)
    {
        $tour = Tour::findOrFail($tourId);

        $reviews = GuestReview::where('reviewable_type', Tour::class)
            ->where('reviewable_id', $tour->id)
            ->approved()
            ->orderByDesc('approved_at')
            ->paginate($request->integer('per_page', 10));

        return response()->json($reviews);
    }

    /**
     * Submit a review for a room
     */
    public function storeRoomReview(Request $request, $roomId)
    {
        $room = Room::findOrFail($roomId);

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imageUrls = $this->uploadImages($request);

        $review = GuestReview::create([
            'reviewable_type' => Room::class,
            'reviewable_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'rating' => $validated['rating'],
            'content' => $validated['content'],
            'images' => $imageUrls,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn sẽ được hiển thị sau khi được duyệt.',
            'review' => $review,
        ], 201);
    }

    /**
     * Submit a review for a tour
     */
    public function storeTourReview(Request $request, $tourId)
    {
        $tour = Tour::findOrFail($tourId);

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|min:10|max:2000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $imageUrls = $this->uploadImages($request);

        $review = GuestReview::create([
            'reviewable_type' => Tour::class,
            'reviewable_id' => $tour->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'] ?? null,
            'guest_phone' => $validated['guest_phone'] ?? null,
            'rating' => $validated['rating'],
            'content' => $validated['content'],
            'images' => $imageUrls,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Cảm ơn bạn đã đánh giá! Đánh giá của bạn sẽ được hiển thị sau khi được duyệt.',
            'review' => $review,
        ], 201);
    }

    /**
     * Upload review images
     */
    protected function uploadImages(Request $request): array
    {
        $urls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('reviews/' . date('Y/m'), $filename, 'public');
                $urls[] = url('storage/' . $path);
            }
        }

        return $urls;
    }
}
