<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestReview;
use App\Models\Room;
use App\Models\Tour;
use App\Models\BoatTour;
use Illuminate\Http\Request;

class AdminGuestReviewController extends Controller
{
    /**
     * List all reviews with filters
     */
    public function index(Request $request)
    {
        $query = GuestReview::with('approvedBy')
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            if ($request->type === 'room') {
                $query->forRooms();
            } elseif ($request->type === 'tour') {
                $query->forTours();
            } elseif ($request->type === 'boat-tour') {
                $query->forBoatTours();
            }
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $reviews = $query->paginate($request->integer('per_page', 15));

        // Transform to include reviewable name
        $reviews->getCollection()->transform(function ($review) {
            $review->reviewable_name = $this->getReviewableName($review);
            return $review;
        });

        return response()->json($reviews);
    }

    /**
     * Get review statistics
     */
    public function stats()
    {
        return response()->json([
            'pending' => GuestReview::pending()->count(),
            'approved' => GuestReview::approved()->count(),
            'rejected' => GuestReview::where('status', 'rejected')->count(),
            'total' => GuestReview::count(),
            'room_reviews' => GuestReview::forRooms()->count(),
            'tour_reviews' => GuestReview::forTours()->count(),
            'boat_tour_reviews' => GuestReview::forBoatTours()->count(),
        ]);
    }

    /**
     * Show single review
     */
    public function show(GuestReview $guestReview)
    {
        $guestReview->load('approvedBy');
        $guestReview->reviewable_name = $this->getReviewableName($guestReview);

        return response()->json($guestReview);
    }

    /**
     * Approve a review
     */
    public function approve(Request $request, GuestReview $guestReview)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $guestReview->approve($request->user(), $request->admin_note);

        return response()->json([
            'message' => 'Đánh giá đã được duyệt thành công.',
            'review' => $guestReview->fresh(),
        ]);
    }

    /**
     * Reject a review
     */
    public function reject(Request $request, GuestReview $guestReview)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $guestReview->reject($request->admin_note);

        return response()->json([
            'message' => 'Đánh giá đã bị từ chối.',
            'review' => $guestReview->fresh(),
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(GuestReview $guestReview)
    {
        // Delete associated images
        if ($guestReview->images) {
            foreach ($guestReview->images as $imageUrl) {
                $path = str_replace(url('storage/'), '', $imageUrl);
                \Storage::disk('public')->delete($path);
            }
        }

        $guestReview->delete();

        return response()->json([
            'message' => 'Đánh giá đã được xóa.',
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:guest_reviews,id',
            'action' => 'required|in:approve,reject,delete',
        ]);

        $reviews = GuestReview::whereIn('id', $request->ids)->get();
        $count = $reviews->count();

        foreach ($reviews as $review) {
            switch ($request->action) {
                case 'approve':
                    $review->approve($request->user());
                    break;
                case 'reject':
                    $review->reject();
                    break;
                case 'delete':
                    if ($review->images) {
                        foreach ($review->images as $imageUrl) {
                            $path = str_replace(url('storage/'), '', $imageUrl);
                            \Storage::disk('public')->delete($path);
                        }
                    }
                    $review->delete();
                    break;
            }
        }

        return response()->json([
            'message' => "Đã thực hiện {$request->action} cho {$count} đánh giá.",
        ]);
    }

    /**
     * Get name of the reviewable item
     */
    protected function getReviewableName(GuestReview $review): string
    {
        if ($review->reviewable_type === Room::class) {
            $room = Room::find($review->reviewable_id);
            return $room ? $room->name : 'Phòng đã xóa';
        }

        if ($review->reviewable_type === Tour::class) {
            $tour = Tour::find($review->reviewable_id);
            return $tour ? $tour->name : 'Tour đã xóa';
        }

        if ($review->reviewable_type === BoatTour::class) {
            $boatTour = BoatTour::find($review->reviewable_id);
            return $boatTour ? $boatTour->name : 'Boat Tour đã xóa';
        }

        return 'Không xác định';
    }
}
