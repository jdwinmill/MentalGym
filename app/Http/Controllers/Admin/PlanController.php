<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Capability;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::withCount(['capabilities', 'users'])
            ->ordered()
            ->paginate(20);

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $capabilities = Capability::active()->ordered()->get()->groupBy('category');
        $nextSortOrder = (Plan::max('sort_order') ?? 0) + 1;

        return view('admin.plans.create', compact('capabilities', 'nextSortOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:50|unique:plans,key|alpha_dash',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'tagline' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_interval' => 'required|in:monthly,yearly,lifetime',
            'yearly_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'capabilities' => 'nullable|array',
            'capabilities.*.id' => 'nullable|integer',
            'capabilities.*.value' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? (Plan::max('sort_order') ?? 0) + 1;

        $plan = Plan::create($validated);

        // Sync capabilities - filter out empty IDs (unchecked capabilities)
        $syncData = [];
        if (!empty($validated['capabilities'])) {
            foreach ($validated['capabilities'] as $capData) {
                if (!empty($capData['id']) && Capability::where('id', $capData['id'])->exists()) {
                    $syncData[$capData['id']] = ['value' => $capData['value'] ?? null];
                }
            }
        }
        $plan->capabilities()->sync($syncData);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(Plan $plan)
    {
        $plan->load('capabilities', 'users');

        return view('admin.plans.show', compact('plan'));
    }

    public function edit(Plan $plan)
    {
        $capabilities = Capability::active()->ordered()->get()->groupBy('category');
        $planCapabilities = $plan->capabilities->keyBy('id')->map(fn ($cap) => $cap->pivot->value);

        return view('admin.plans.edit', compact('plan', 'capabilities', 'planCapabilities'));
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('plans', 'key')->ignore($plan->id)],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'tagline' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'billing_interval' => 'required|in:monthly,yearly,lifetime',
            'yearly_price' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'capabilities' => 'nullable|array',
            'capabilities.*.id' => 'nullable|integer',
            'capabilities.*.value' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');

        $plan->update($validated);

        // Sync capabilities - filter out empty IDs (unchecked capabilities)
        $syncData = [];
        if (!empty($validated['capabilities'])) {
            foreach ($validated['capabilities'] as $capData) {
                if (!empty($capData['id']) && Capability::where('id', $capData['id'])->exists()) {
                    $syncData[$capData['id']] = ['value' => $capData['value'] ?? null];
                }
            }
        }
        $plan->capabilities()->sync($syncData);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $userCount = $plan->users()->count();

        if ($userCount > 0) {
            return redirect()->route('admin.plans.index')
                ->with('error', "Cannot delete plan with {$userCount} active subscribers. Please migrate them first.");
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    public function featureMatrix()
    {
        $plans = Plan::active()->ordered()->with('capabilities')->get();
        $capabilities = Capability::active()->ordered()->get()->groupBy('category');

        return view('admin.plans.feature-matrix', compact('plans', 'capabilities'));
    }
}
