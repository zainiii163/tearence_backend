<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    public function index()
    {
        return view('property.index');
    }

    public function search(Request $request)
    {
        return view('property.search');
    }

    public function create()
    {
        return view('property.post');
    }

    public function store(Request $request)
    {
        // This will be handled by the API controller
        return redirect()->route('property.index')->with('success', 'Property posted successfully!');
    }

    public function show($id)
    {
        $property = Property::with(['user', 'favourites', 'analytics', 'enquiries'])
            ->findOrFail($id);
        
        return view('property.show', compact('property'));
    }

    public function edit($id)
    {
        $property = Property::where('user_id', Auth::id())->findOrFail($id);
        return view('property.edit', compact('property'));
    }

    public function update(Request $request, $id)
    {
        // This will be handled by the API controller
        return redirect()->route('property.show', $id)->with('success', 'Property updated successfully!');
    }

    public function destroy($id)
    {
        $property = Property::where('user_id', Auth::id())->findOrFail($id);
        $property->delete();
        
        return redirect()->route('property.my')->with('success', 'Property deleted successfully!');
    }

    public function myProperties()
    {
        return view('property.my');
    }

    public function save(Request $request, $id)
    {
        // This will be handled by the API controller
        return back()->with('success', 'Property saved successfully!');
    }

    public function saved()
    {
        return view('property.saved');
    }

    public function contact(Request $request, $id)
    {
        // This will be handled by the API controller
        return back()->with('success', 'Message sent successfully!');
    }
}
