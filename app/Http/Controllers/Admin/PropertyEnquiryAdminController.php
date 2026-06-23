<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyEnquiry;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyEnquiryAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of property enquiries
     */
    public function index(Request $request)
    {
        $query = PropertyEnquiry::with(['property', 'user']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        if ($request->filled('is_important')) {
            $query->where('is_important', $request->boolean('is_important'));
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('property', function ($subQuery) use ($search) {
                      $subQuery->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $enquiries = $query->latest()->paginate(20);

        return view('admin.property-enquiries.index', compact('enquiries'));
    }

    /**
     * Display the specified property enquiry
     */
    public function show(PropertyEnquiry $enquiry)
    {
        $enquiry->load(['property', 'user']);
        
        // Mark as read if not already read
        if (!$enquiry->is_read) {
            $enquiry->update(['is_read' => true]);
        }

        return view('admin.property-enquiries.show', compact('enquiry'));
    }

    /**
     * Respond to a property enquiry
     */
    public function respond(Request $request, PropertyEnquiry $enquiry)
    {
        $validated = $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:pending,responded,closed',
        ]);

        $enquiry->update([
            'admin_response' => $validated['response'],
            'status' => $validated['status'],
            'responded_at' => now(),
            'responded_by' => auth()->id(),
        ]);

        // Here you would typically send an email to the enquirer
        // Mail::to($enquiry->email)->send(new PropertyEnquiryResponse($enquiry));

        return back()->with('success', 'Response sent successfully.');
    }

    /**
     * Mark enquiry as read/unread
     */
    public function markRead(Request $request, PropertyEnquiry $enquiry)
    {
        $enquiry->update(['is_read' => $request->boolean('is_read')]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark enquiry as important/unimportant
     */
    public function markImportant(Request $request, PropertyEnquiry $enquiry)
    {
        $enquiry->update(['is_important' => $request->boolean('is_important')]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified property enquiry
     */
    public function destroy(PropertyEnquiry $enquiry)
    {
        $enquiry->delete();

        return redirect()->route('admin.properties.enquiries.index')
            ->with('success', 'Enquiry deleted successfully.');
    }

    /**
     * Get enquiry statistics
     */
    public function statistics()
    {
        $stats = [
            'total_enquiries' => PropertyEnquiry::count(),
            'unread_enquiries' => PropertyEnquiry::where('is_read', false)->count(),
            'important_enquiries' => PropertyEnquiry::where('is_important', true)->count(),
            'pending_enquiries' => PropertyEnquiry::where('status', 'pending')->count(),
            'responded_enquiries' => PropertyEnquiry::where('status', 'responded')->count(),
            'closed_enquiries' => PropertyEnquiry::where('status', 'closed')->count(),
        ];

        $recentEnquiries = PropertyEnquiry::with(['property', 'user'])
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_enquiries' => $recentEnquiries,
        ]);
    }

    /**
     * Bulk mark enquiries as read
     */
    public function bulkMarkRead(Request $request)
    {
        $enquiryIds = $request->input('enquiry_ids', []);
        
        PropertyEnquiry::whereIn('id', $enquiryIds)->update(['is_read' => true]);

        return back()->with('success', 'Enquiries marked as read.');
    }

    /**
     * Bulk mark enquiries as important
     */
    public function bulkMarkImportant(Request $request)
    {
        $enquiryIds = $request->input('enquiry_ids', []);
        
        PropertyEnquiry::whereIn('id', $enquiryIds)->update(['is_important' => true]);

        return back()->with('success', 'Enquiries marked as important.');
    }

    /**
     * Bulk delete enquiries
     */
    public function bulkDelete(Request $request)
    {
        $enquiryIds = $request->input('enquiry_ids', []);
        
        PropertyEnquiry::whereIn('id', $enquiryIds)->delete();

        return back()->with('success', 'Enquiries deleted successfully.');
    }
}
