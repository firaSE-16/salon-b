<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalog\ServiceStoreRequest;
use App\Http\Requests\Catalog\ServiceUpdateRequest;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                return response()->json(['error' => 'Salon not found'], 404);
            }

            $q = Service::where('salon_id', $salon->id);

            if ($request->has('active')) {
                $q->where('is_active', (bool)$request->boolean('active'));
            }

            return response()->json([
                'data' => $q->orderBy('sort_order')->orderBy('name')->get()
            ]);
        } catch (\Exception $e) {
            \Log::error('ServiceController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch services',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Service::where('salon_id', $salon->id)->findOrFail($id);
        return response()->json(['data' => $row]);
    }

    public function store(ServiceStoreRequest $request)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                return response()->json(['error' => 'Salon not found'], 404);
            }
            
            $data = $request->validated();

            $row = Service::create([
                'salon_id' => $salon->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'duration_min' => $data['duration_min'],
                'buffer_min' => $data['buffer_min'] ?? 0,
                'price_cents' => $data['price_cents'],
                'currency' => $data['currency'] ?? ($salon->currency ?? 'EUR'),
                'is_active' => $data['is_active'] ?? true,
                'sort_order' => $data['sort_order'] ?? 0,
                'image_url' => $data['image_url'] ?? null,
            ]);

            return response()->json(['data' => $row], 201);
        } catch (\Exception $e) {
            \Log::error('ServiceController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->validated()
            ]);
            return response()->json([
                'error' => 'Failed to create service',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(ServiceUpdateRequest $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $data = $request->validated();

        $row = Service::where('salon_id', $salon->id)->findOrFail($id);
        $row->update($data);

        return response()->json(['data' => $row]);
    }

    public function destroy(Request $request, $id)
    {
        $salon = $request->attributes->get('currentSalon');

        $row = Service::where('salon_id', $salon->id)->findOrFail($id);

        // Safe delete: deactivate instead of hard delete
        $row->update(['is_active' => false]);

        return response()->json(['ok' => true]);
    }
}
