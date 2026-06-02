<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoatTour;
use App\Models\GuestReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoatTourController extends Controller
{
    /**
     * Display a listing of boat tours.
     */
    public function index(Request $request)
    {
        $query = BoatTour::active()->with('reviews');
        
        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('departure_location', 'like', "%{$search}%");
            });
        }
        
        // Sort by price
        if ($request->has('sort_by_price')) {
            $direction = $request->sort_by_price === 'desc' ? 'desc' : 'asc';
            $query->orderBy('price', $direction);
        }
        
        // Default ordering
        $query->orderBy('created_at', 'desc');
        
        $boatTours = $query->paginate(12);
        
        return response()->json([
            'success' => true,
            'data' => $boatTours
        ]);
    }

    /**
     * Display the specified boat tour.
     */
    public function show($id)
    {
        $boatTour = BoatTour::active()->with(['reviews' => function($query) {
            $query->approved()->latest()->take(10);
        }])->find($id);
        
        if (!$boatTour) {
            return response()->json([
                'success' => false,
                'message' => 'Boat tour not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $boatTour
        ]);
    }

    /**
     * Get featured boat tours for homepage
     */
    public function featured()
    {
        $boatTours = BoatTour::active()
            ->orderBy('average_rating', 'desc')
            ->take(6)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $boatTours
        ]);
    }

    /**
     * Store a review for a boat tour
     */
    public function storeReview(Request $request, $id)
    {
        try {
            $boatTour = BoatTour::active()->find($id);
            
            if (!$boatTour) {
                return response()->json([
                    'success' => false,
                    'message' => 'Boat tour not found'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'content' => 'required|string|max:1000',
                'guest_name' => 'required|string|max:100',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'nullable|string|max:20'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $review = GuestReview::create([
                'reviewable_type' => BoatTour::class,
                'reviewable_id' => $boatTour->id,
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'guest_phone' => $request->guest_phone,
                'rating' => $request->rating,
                'content' => $request->content,
                'status' => 'pending' // Require approval
            ]);
            
            // Update boat tour rating
            $boatTour->updateRating();
            
            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully and is pending approval',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            \Log::error('Boat tour review submission failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy(string $id)
    {
        //
    }
}
