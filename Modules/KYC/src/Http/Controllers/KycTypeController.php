<?php

namespace Modules\KYC\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\KYC\Services\KycTypeService;
use Modules\KYC\Http\Requests\KycTypeRequest;
use Modules\KYC\Http\Requests\KycFieldRequest;
use Modules\KYC\DataView\KycTypeGrid;
use Modules\KYC\Traits\HandlesKycErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Traits\SanitizesInput;
use App\Rules\NoScripts;

class KycTypeController extends Controller
{
    use SanitizesInput;
    use HandlesKycErrors;
    protected $kycTypeService;

    public function __construct(KycTypeService $kycTypeService)
    {
        $this->kycTypeService = $kycTypeService;
    }

    /**
     * Display listing of KYC types
     */
    public function index()
    {
        $lists = fn_datagrid(KycTypeGrid::class)->process();

        return view('kyc::admin.types.index', compact('lists'));
    }

    /**
     * Show form for creating new KYC type
     */
    public function create()
    {
        return view('kyc::admin.types.create');
    }

    

    public function store(KycTypeRequest $request)
    {
        try {
            $adminId = Auth::guard('admin')->id();
            if (!$adminId) {
                return back()->withInput()
                    ->with('error', 'Authentication required');
            }

            $validated = $request->validated();
            $validated['name'] = $this->sanitizeStringFields(['name' => $validated['name']])['name'];
            $validated['description'] = isset($validated['description'])
                ? $this->sanitizeStringFields(['description' => $validated['description']])['description']
                : null;

            if (!empty($validated['fields'])) {
                foreach ($validated['fields'] as $index => $field) {
                    $validated['fields'][$index]['field_name'] = $this->sanitizeStringFields(
                        ['field_name' => $field['field_name']]
                    )['field_name'];
                }
            }

            $kycType = $this->kycTypeService->createType($validated, $adminId);

            return redirect()->route('admin.kyc.types.index')
                ->with('success', 'KYC Type created successfully with ' . $kycType->fields->count() . ' field(s)');
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create KYC Type: ' . $e->getMessage());
        }
    }


    /**
     * Show form for editing KYC type
     */
    public function edit($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return redirect()->route('admin.kyc.types.index')
                    ->with('error', 'Invalid KYC Type ID');
            }

            $kycType = $this->kycTypeService->getType((int)$id);

            return view('kyc::admin.types.edit', compact('kycType'));
        } catch (Exception $e) {
            return redirect()->route('admin.kyc.types.index')
                ->with('error', 'Failed to load KYC Type: ' . $e->getMessage());
        }
    }

   

    public function update(KycTypeRequest $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return back()->withInput()
                    ->with('error', 'Invalid KYC Type ID');
            }

            // Sanitize before storing
            $validated = $request->validated();
            $validated['name'] = $this->sanitizeStringFields(['name' => $validated['name']])['name'];
            $validated['description'] = isset($validated['description'])
                ? $this->sanitizeStringFields(['description' => $validated['description']])['description']
                : null;

            if (!empty($validated['fields'])) {
                foreach ($validated['fields'] as $index => $field) {
                    $validated['fields'][$index]['field_name'] = $this->sanitizeStringFields(
                        ['field_name' => $field['field_name']]
                    )['field_name'];
                }
            }

            $kycType = $this->kycTypeService->updateType((int)$id, $validated);

            return redirect()->route('admin.kyc.types.index')
                ->with('success', 'KYC Type updated successfully');
        } catch (Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to update KYC Type: ' . $e->getMessage());
        }
    }


    /**
     * Delete KYC type
     */
    public function destroy($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid KYC Type ID'
                ], 400);
            }

            $this->kycTypeService->deleteType((int)$id);

            return $this->handleKycSuccess(null, 'KYC Type deleted successfully');
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Delete KYC Type');
        }
    }

    /**
     * Bulk delete KYC types
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:kyc_types,id',
        ]);

        try {
            \DB::transaction(function () use ($validated) {
                $kycTypes = \Modules\KYC\Models\KycType::whereIn('id', $validated['ids'])->get();

                foreach ($kycTypes as $kycType) {
                    if ($kycType->isInUse()) {
                        throw new Exception('Cannot delete KYC type that is currently in use');
                    }
                }

                foreach ($kycTypes as $kycType) {
                    $kycType->fields()->delete();
                    $kycType->delete();
                }
            });

            return redirect()->route('admin.kyc.types.index')
                ->with('success', 'Selected KYC Types deleted successfully');
        } catch (Exception $e) {
            return redirect()->route('admin.kyc.types.index')
                ->with('error', 'Failed to delete selected KYC Types: ' . $e->getMessage());
        }
    }

    /**
     * Show fields for a KYC type
     */
    public function fields($id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return redirect()->route('admin.kyc.types.index')
                    ->with('error', 'Invalid KYC Type ID');
            }

            $kycType = $this->kycTypeService->getType((int)$id);

            return view('kyc::admin.types.fields', compact('kycType'));
        } catch (Exception $e) {
            return redirect()->route('admin.kyc.types.index')
                ->with('error', 'Failed to load KYC Type fields: ' . $e->getMessage());
        }
    }

    /**
     * Add field to KYC type
     */
    public function addField(KycFieldRequest $request, $id)
    {
        try {
            if (!is_numeric($id) || $id <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid KYC Type ID'
                ], 400);
            }

            $field = $this->kycTypeService->addField((int)$id, $request->validated());

            return $this->handleKycSuccess($field, 'Field added successfully');
        } catch (Exception $e) {
            return $this->handleKycException($e, 'Add Field');
        }
    }

    /**
     * Update field
     */
    public function updateField(KycFieldRequest $request, $typeId, $fieldId)
    {
        try {
            $field = $this->kycTypeService->updateField($fieldId, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully',
                'field' => $field
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Delete field
     */
    public function deleteField($typeId, $fieldId)
    {
        try {
            $this->kycTypeService->deleteField($fieldId);

            return response()->json([
                'success' => true,
                'message' => 'Field deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Reorder fields
     */
    public function reorderFields(Request $request, $id)
    {
        try {
            $this->kycTypeService->reorderFields($id, $request->input('field_ids', []));

            return response()->json([
                'success' => true,
                'message' => 'Fields reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
