<?php

namespace App\Http\Controllers\Api\Booking;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\AppointmentCreateRequest;
use App\Http\Requests\Booking\AppointmentRescheduleRequest;
use App\Http\Requests\Booking\AppointmentStatusRequest;
use App\Models\Appointment;
use App\Services\Booking\AppointmentService;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                return response()->json(['error' => 'Salon not found'], 404);
            }

            $q = Appointment::with(['service', 'staff'])
                ->where('salon_id',$salon->id);

            if ($request->filled('status')) $q->where('status', $request->query('status'));
            if ($request->filled('staff_id')) $q->where('staff_id', (int)$request->query('staff_id'));
            if ($request->filled('from')) $q->where('start_at','>=', $request->query('from'));
            if ($request->filled('to')) $q->where('start_at','<=', $request->query('to'));

            $result = $q->orderBy('start_at', 'asc')->paginate(50);
            
            // Return data in a format that frontend expects
            return response()->json(['data' => $result->items()]);
        } catch (\Exception $e) {
            \Log::error('AppointmentController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to fetch appointments',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(AppointmentCreateRequest $request, AppointmentService $svc)
    {
        try {
            $salon = $request->attributes->get('currentSalon');
            if (!$salon) {
                \Log::error('AppointmentController@store: Salon not found');
                return response()->json(['error' => 'Salon not found'], 404);
            }

            $data = $request->validated();
            \Log::info('AppointmentController@store: Creating appointment', [
                'salon_id' => $salon->id,
                'data' => $data
            ]);

            $timezone = $data['timezone'] ?? ($salon->timezone ?? 'Europe/Sarajevo');

            $appt = $svc->create($salon->id, $data, $timezone);

            \Log::info('AppointmentController@store: Appointment created successfully', ['id' => $appt->id]);

            // Refresh the model to ensure all attributes are loaded
            $appt->refresh();

            return response()->json(['data'=>$appt], 201);
        } catch (\Exception $e) {
            \Log::error('AppointmentController@store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            return response()->json([
                'error' => 'Failed to create appointment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

/**
 * PATCH /appointments/{id}
 * Alias for reschedule (keeps backward compatibility with POST /appointments/{id}/reschedule)
 */
public function update(AppointmentRescheduleRequest $request, AppointmentService $svc, $id)
{
    return $this->reschedule($request, $svc, $id);
}


    public function reschedule(AppointmentRescheduleRequest $request, AppointmentService $svc, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $appt = Appointment::where('salon_id',$salon->id)->findOrFail($id);

        $data = $request->validated();
        $timezone = $data['timezone'] ?? ($salon->timezone ?? 'Europe/Sarajevo');

        $appt = $svc->reschedule($appt, $data, $timezone);

        return response()->json(['data'=>$appt]);
    }

    public function setStatus(AppointmentStatusRequest $request, AppointmentService $svc, $id)
    {
        $salon = $request->attributes->get('currentSalon');
        $appt = Appointment::where('salon_id',$salon->id)->findOrFail($id);

        $appt = $svc->setStatus($appt, $request->validated()['status']);

        return response()->json(['data'=>$appt]);
    }
}
