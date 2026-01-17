<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Capability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CapabilityController extends Controller
{
    public function index()
    {
        $capabilities = Capability::withCount('plans')
            ->ordered()
            ->paginate(20);

        return view('admin.capabilities.index', compact('capabilities'));
    }

    public function create()
    {
        $valueTypes = Capability::getValueTypes();
        $categories = Capability::getCategories();
        $nextSortOrder = (Capability::max('sort_order') ?? 0) + 1;

        return view('admin.capabilities.create', compact('valueTypes', 'categories', 'nextSortOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:50|unique:capabilities,key|alpha_dash',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'value_type' => 'required|in:boolean,integer,string,json',
            'default_value' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = $validated['sort_order'] ?? (Capability::max('sort_order') ?? 0) + 1;

        Capability::create($validated);

        return redirect()->route('admin.capabilities.index')
            ->with('success', 'Capability created successfully.');
    }

    public function show(Capability $capability)
    {
        $capability->load('plans');

        return view('admin.capabilities.show', compact('capability'));
    }

    public function edit(Capability $capability)
    {
        $valueTypes = Capability::getValueTypes();
        $categories = Capability::getCategories();

        return view('admin.capabilities.edit', compact('capability', 'valueTypes', 'categories'));
    }

    public function update(Request $request, Capability $capability)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('capabilities', 'key')->ignore($capability->id)],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'value_type' => 'required|in:boolean,integer,string,json',
            'default_value' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $capability->update($validated);

        return redirect()->route('admin.capabilities.index')
            ->with('success', 'Capability updated successfully.');
    }

    public function destroy(Capability $capability)
    {
        $planCount = $capability->plans()->count();

        $capability->delete();

        return redirect()->route('admin.capabilities.index')
            ->with('success', "Capability deleted successfully." . ($planCount > 0 ? " It was removed from {$planCount} plan(s)." : ''));
    }
}
