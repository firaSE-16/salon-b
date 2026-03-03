<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\StaffStoreRequest;
use App\Http\Requests\Catalog\StaffUpdateRequest;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                return response()->json(['error' => 'Salon not found'], 404);
            }

            $q = Staff::where('salon_id', $salon->id);

            if ($request->has('active')) {
                $q->where('is_active', (bool)$request->boolean('active'));
            }

            return response()->json([
                'data' => $q->orderBy('sort_order')->orderBy('name')->get()
            ]);
        } catch (\Exception $e) {
            \Log::error('StaffController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch staff',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);
        return response()->json(['data' => $row]);
    }

    public function store(StaffStoreRequest $request)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                \Log::error('StaffController@store: Salon not found');
                return response()->json(['error' => 'Salon not found'], 404);
            }

            $data = $request->validated();
            \Log::info('StaffController@store: Creating staff', [
                'salon_id' => $salon->id,
                'data' => $data
            ]);

            $row = Staff::create([
                'salon_id' => $salon->id,
                'name' => $data['name'],
                'title' => $data['title'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'avatar_url' => $data['avatar_url'] ?? null,
            ]);

            \Log::info('StaffController@store: Staff created successfully', ['id' => $row->id]);
            
            // Refresh the model to ensure all attributes are loaded
            $row->refresh();
            
            return response()->json(['data' => $row], 201);
        } catch (\Exception $e) {
            \Log::error('StaffController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'error' => 'Failed to create staff',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(StaffUpdateRequest $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);
        $row->update($data);

        return response()->json(['data' => $row]);
    }

    public function destroy(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Staff::where('salon_id', $salon->id)->findOrFail($id);

        // Safe delete: deactivate
        $row->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }
}
