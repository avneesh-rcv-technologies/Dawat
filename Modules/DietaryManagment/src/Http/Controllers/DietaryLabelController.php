<?php

namespace Modules\DietaryManagment\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\DietaryManagment\DataView\DietaryLabelGrid;
use Modules\DietaryManagment\Models\DietaryLabel;

class DietaryLabelController extends Controller
{
    public function index()
    {
        $lists = fn_datagrid(DietaryLabelGrid::class)->process();

        return view('dietarymanagment::labels.index', compact('lists'));
    }

    public function create()
    {
        return view('dietarymanagment::labels.form', ['label' => new DietaryLabel([
            'is_active' => true, 'is_certification_required' => false, 'display_order' => 0,
        ])]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        DB::transaction(fn () => DietaryLabel::create($data));

        return $this->savedResponse($request, 'Dietary label created successfully.');
    }

    public function edit(string $label)
    {
        return view('dietarymanagment::labels.form', [
            'label' => DietaryLabel::findOrFail($label),
        ]);
    }

    public function update(Request $request, string $label)
    {
        $model = DietaryLabel::findOrFail($label);
        $data = $this->validatedData($request, $model);
        DB::transaction(fn () => $model->update($data));

        return $this->savedResponse($request, 'Dietary label updated successfully.');
    }

    public function destroy(Request $request, string $label)
    {
        DB::transaction(fn () => DietaryLabel::findOrFail($label)->delete());

        return $this->savedResponse($request, 'Dietary label deleted successfully.');
    }

    private function validatedData(Request $request, ?DietaryLabel $label = null): array
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
        ]);

        // A blank slug is generated from the name; explicit slugs are normalized too.
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('name', '')),
            'is_certification_required' => $request->input('is_certification_required', false),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10'],
            'slug' => ['required', 'string', 'max:100', Rule::unique('dietary_labels', 'slug')->ignore($label)],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'backend_value' => ['required', 'string', 'max:50', Rule::unique('dietary_labels', 'backend_value')->ignore($label)],
            'is_active' => ['required', 'boolean'],
            'is_certification_required' => ['required', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);
    }

    private function savedResponse(Request $request, string $message)
    {
        $url = route('admin.dietarymanagment.labels.index');
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message, 'redirect_url' => $url]);
        }

        return redirect($url)->with('success', $message);
    }
}
