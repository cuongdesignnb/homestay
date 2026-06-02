<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoatTour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BoatTourController extends Controller
{
    /**
     * Display a listing of boat tours for admin
     */
    public function index(Request $request)
    {
        $query = BoatTour::withTrashed();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('departure_location', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $boatTours = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $boatTours
        ]);
    }

    /**
     * Store a newly created boat tour
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|string|max:100',
            'departure_location' => 'required|string|max:255',
            'departure_time' => 'required|string|max:100',
            'max_participants' => 'required|integer|min:1',
            'contact_whatsapp' => 'required|string|max:20',
            'included_services' => 'array',
            'excluded_services' => 'array',
            'itinerary' => 'string|nullable',
            'image_gallery' => 'array',
            'status' => 'in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $boatTour = BoatTour::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Boat tour created successfully',
            'data' => $boatTour
        ], 201);
    }

    /**
     * Display the specified boat tour
     */
    public function show($id)
    {
        $boatTour = BoatTour::withTrashed()->with('reviews')->find($id);
        
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
     * Update the specified boat tour
     */
    public function update(Request $request, $id)
    {
        $boatTour = BoatTour::withTrashed()->find($id);
        
        if (!$boatTour) {
            return response()->json([
                'success' => false,
                'message' => 'Boat tour not found'
            ], 404);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'string',
            'short_description' => 'string|max:500',
            'price' => 'numeric|min:0',
            'duration' => 'string|max:100',
            'departure_location' => 'string|max:255',
            'departure_time' => 'string|max:100',
            'max_participants' => 'integer|min:1',
            'contact_whatsapp' => 'string|max:20',
            'included_services' => 'array',
            'excluded_services' => 'array',
            'itinerary' => 'string|nullable',
            'image_gallery' => 'array',
            'status' => 'in:active,inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $boatTour->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Boat tour updated successfully',
            'data' => $boatTour->fresh()
        ]);
    }

    /**
     * Remove the specified boat tour
     */
    public function destroy($id)
    {
        $boatTour = BoatTour::find($id);
        
        if (!$boatTour) {
            return response()->json([
                'success' => false,
                'message' => 'Boat tour not found'
            ], 404);
        }
        
        $boatTour->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Boat tour deleted successfully'
        ]);
    }

    /**
     * Restore a soft deleted boat tour
     */
    public function restore($id)
    {
        $boatTour = BoatTour::onlyTrashed()->find($id);
        
        if (!$boatTour) {
            return response()->json([
                'success' => false,
                'message' => 'Boat tour not found'
            ], 404);
        }
        
        $boatTour->restore();
        
        return response()->json([
            'success' => true,
            'message' => 'Boat tour restored successfully'
        ]);
    }
}
