<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\PracticeActivityNotification;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display a listing of appointments with multi-criteria filters or interactive calendar (Sec 4, pp. 3-4).
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'view' => ['nullable', Rule::in(['list', 'calendar'])],
            'cal_type' => ['nullable', Rule::in(['month', 'week', 'day'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'date_preset' => ['nullable', Rule::in(['today', 'tomorrow', 'this_week', 'upcoming', 'past'])],
            'search' => ['nullable', 'string', 'max:100'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('from_date'), ['after_or_equal:from_date'])],
            'doctor_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
                    ->where('status', 'active')),
            ],
            'status' => ['nullable', Rule::in([
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
                Appointment::STATUS_CHECKED_IN,
                Appointment::STATUS_IN_CONSULTATION,
                Appointment::STATUS_COMPLETED,
                Appointment::STATUS_CANCELLED,
                Appointment::STATUS_NO_SHOW,
            ])],
            'payment_status' => ['nullable', Rule::in([
                Appointment::PAYMENT_UNPAID,
                Appointment::PAYMENT_PAID,
                Appointment::PAYMENT_PARTIALLY_PAID,
            ])],
        ]);
        $viewMode = $filters['view'] ?? 'list';

        // Top KPI Counts for Today (Sec 3, pp. 1-2 & Sec 4)
        $todayStr = now()->toDateString();
        $todayStats = [
            'total' => Appointment::whereDate('appointment_date', $todayStr)->count(),
            'confirmed' => Appointment::whereDate('appointment_date', $todayStr)->where('status', Appointment::STATUS_CONFIRMED)->count(),
            'checked_in' => Appointment::whereDate('appointment_date', $todayStr)->where('status', Appointment::STATUS_CHECKED_IN)->count(),
            'in_consultation' => Appointment::whereDate('appointment_date', $todayStr)->where('status', Appointment::STATUS_IN_CONSULTATION)->count(),
            'completed' => Appointment::whereDate('appointment_date', $todayStr)->where('status', Appointment::STATUS_COMPLETED)->count(),
            'cancelled' => Appointment::whereDate('appointment_date', $todayStr)->where('status', Appointment::STATUS_CANCELLED)->count(),
        ];

        // Active Doctors for filter dropdown
        $doctors = User::whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        if ($viewMode === 'calendar') {
            return $this->renderCalendarView($request, $todayStats, $doctors);
        }

        // List View Query with Filters
        $query = Appointment::with(['patient', 'doctor'])->latest('appointment_date')->latest('appointment_time');

        // Search: Appointment ID, Patient Name, Patient Phone
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('appointment_id', 'like', "%{$search}%")
                    ->orWhereHas('patient', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('patient_id', 'like', "%{$search}%");
                    });
            });
        }

        // Filter: Date Presets
        $dateFilter = $filters['date_preset'] ?? null;
        if ($dateFilter === 'today') {
            $query->whereDate('appointment_date', $todayStr);
        } elseif ($dateFilter === 'tomorrow') {
            $query->whereDate('appointment_date', now()->addDay()->toDateString());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('appointment_date', [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()]);
        } elseif ($dateFilter === 'upcoming') {
            $query->whereDate('appointment_date', '>=', $todayStr)
                ->whereNotIn('status', [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED]);
        } elseif ($dateFilter === 'past') {
            $query->whereDate('appointment_date', '<', $todayStr);
        }

        // Custom Date Range
        if (! empty($filters['from_date'])) {
            $query->whereDate('appointment_date', '>=', $filters['from_date']);
        }
        if (! empty($filters['to_date'])) {
            $query->whereDate('appointment_date', '<=', $filters['to_date']);
        }

        // Filter: Doctor
        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        // Filter: Lifecycle Status
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter: Payment Status
        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $appointments = $query->paginate(15)->withQueryString();

        return view('appointments.index', compact('appointments', 'todayStats', 'doctors'));
    }

    /**
     * Interactive Calendar View (Month, Week, Day) (Sec 4, p. 4).
     */
    protected function renderCalendarView(Request $request, array $todayStats, $doctors): View
    {
        $calType = $request->input('cal_type', 'month'); // month, week, day
        $dateStr = $request->input('date', now()->toDateString());
        $currentDate = Carbon::parse($dateStr);

        $calQuery = Appointment::with(['patient', 'doctor']);

        if ($request->filled('doctor_id')) {
            $calQuery->where('doctor_id', $request->input('doctor_id'));
        }

        if ($request->filled('status')) {
            $calQuery->where('status', $request->input('status'));
        }

        if ($calType === 'day') {
            $appointments = $calQuery->whereDate('appointment_date', $currentDate->toDateString())
                ->orderBy('appointment_time')
                ->get();
        } elseif ($calType === 'week') {
            $startOfWeek = $currentDate->copy()->startOfWeek();
            $endOfWeek = $currentDate->copy()->endOfWeek();
            $appointments = $calQuery->whereBetween('appointment_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();
        } else {
            // Month view (default)
            $startOfMonth = $currentDate->copy()->startOfMonth()->startOfWeek();
            $endOfMonth = $currentDate->copy()->endOfMonth()->endOfWeek();
            $appointments = $calQuery->whereBetween('appointment_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();
        }

        // Group appointments by date string for easy calendar cell indexing
        $groupedAppointments = $appointments->groupBy(function ($app) {
            return $app->appointment_date->toDateString();
        });

        return view('appointments.calendar', compact(
            'todayStats',
            'doctors',
            'calType',
            'currentDate',
            'appointments',
            'groupedAppointments'
        ));
    }

    /**
     * Show the appointment creation form (Sec 4, pp. 3-4).
     */
    public function create(Request $request): View
    {
        $selectedDate = $request->validate(['date' => ['nullable', 'date']])['date'] ?? now()->toDateString();
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->input('patient_id'));
        }

        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $timeSlots = $this->getTimeSlots();

        $appointmentTypes = [
            'Consultation',
            'Follow-up',
            'Routine Checkup',
            'Emergency',
            'Vaccination',
            'Procedure',
        ];

        return view('appointments.create', compact(
            'selectedPatient',
            'patients',
            'doctors',
            'timeSlots',
            'appointmentTypes',
            'selectedDate'
        ));
    }

    /**
     * Store a newly created appointment in storage (Sec 4, pp. 3-4).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
                    ->where('status', 'active')),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', Rule::in($this->getTimeSlots())],
            'appointment_type' => ['required', 'string', 'max:100'],
            'reason_for_visit' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'patient_id.required' => 'Please select a patient for this appointment.',
            'patient_id.exists' => 'The selected patient record does not exist.',
            'doctor_id.required' => 'Please select an attending doctor.',
            'doctor_id.exists' => 'The selected doctor is not active or available.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_date.date_format' => 'Appointment date must be in YYYY-MM-DD format.',
            'appointment_date.after_or_equal' => 'Appointment date cannot be in the past. Please select today or a future date.',
            'appointment_time.required' => 'Please choose a scheduled time slot.',
            'appointment_time.in' => 'Please select a valid time slot from practice working hours.',
            'appointment_type.required' => 'Please select an appointment type.',
        ]);

        $this->validateSchedule($validated);

        $appointment = Appointment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'appointment_type' => $validated['appointment_type'],
            'reason_for_visit' => $validated['reason_for_visit'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => Appointment::STATUS_PENDING,
            'payment_status' => Appointment::PAYMENT_UNPAID,
        ]);

        PracticeActivityNotification::sendToActiveStaff([
            'category' => 'appointments',
            'title' => 'Appointment booked',
            'message' => $appointment->patient->name.' has an appointment on '.$appointment->appointment_date->format('d M Y').' at '.date('g:i A', strtotime($appointment->appointment_time)).'.',
            'url' => route('appointments.show', $appointment),
            'permission' => 'appointments.view',
        ], 'notify_new_appointment');

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment {$appointment->appointment_id} has been scheduled successfully.");
    }

    /**
     * Display appointment details & clinical workflow actions (Sec 4, p. 4 & Sec 12, p. 14).
     */
    public function show(Appointment $appointment): View
    {
        $appointment->load(['patient.notes', 'patient.documents', 'doctor']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show edit and rescheduling form.
     */
    public function edit(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor']);

        $patients = Patient::orderBy('name')->get();
        $doctors = User::whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $timeSlots = $this->getTimeSlots();
        $currentTime = substr($appointment->appointment_time, 0, 5);
        if (! in_array($currentTime, $timeSlots, true)) {
            $timeSlots[] = $currentTime;
        }

        $appointmentTypes = [
            'Consultation',
            'Follow-up',
            'Routine Checkup',
            'Emergency',
            'Vaccination',
            'Procedure',
        ];

        return view('appointments.edit', compact(
            'appointment',
            'patients',
            'doctors',
            'timeSlots',
            'appointmentTypes'
        ));
    }

    /**
     * Update appointment record.
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $timeSlots = $this->getTimeSlots();
        $currentTime = substr($appointment->appointment_time, 0, 5);
        if (! in_array($currentTime, $timeSlots, true)) {
            $timeSlots[] = $currentTime;
        }

        $validated = $request->validate([
            'doctor_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn (QueryBuilder $query) => $query
                    ->whereIn('role', [User::ROLE_ADMIN_DOCTOR, User::ROLE_DOCTOR])
                    ->where('status', 'active')),
            ],
            'appointment_date' => ['required', 'date_format:Y-m-d'],
            'appointment_time' => ['required', 'date_format:H:i', Rule::in($timeSlots)],
            'appointment_type' => ['required', 'string', 'max:100'],
            'reason_for_visit' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'string', 'in:pending,confirmed,checked_in,in_consultation,completed,cancelled,no_show'],
            'cancelled_reason' => ['nullable', 'string', 'max:500'],
        ], [
            'doctor_id.required' => 'Please select an attending doctor.',
            'doctor_id.exists' => 'The selected doctor is not active or available.',
            'appointment_date.required' => 'Please select an appointment date.',
            'appointment_time.required' => 'Please choose a scheduled time slot.',
            'appointment_time.in' => 'Please select a valid time slot.',
            'appointment_type.required' => 'Please select an appointment type.',
            'status.required' => 'Please choose a lifecycle status.',
            'status.in' => 'The selected appointment status is invalid.',
        ]);

        $allowedTransitions = match ($appointment->status) {
            Appointment::STATUS_PENDING => [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_CONFIRMED => [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_CHECKED_IN => [Appointment::STATUS_IN_CONSULTATION, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_IN_CONSULTATION => [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED],
            default => [],
        };

        if ($validated['status'] !== $appointment->status && ! in_array($validated['status'], $allowedTransitions, true)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment cannot move to the selected status.',
            ]);
        }

        if ($validated['status'] === Appointment::STATUS_CANCELLED) {
            abort_unless($request->user()->can('appointments.cancel'), 403);
        }

        $this->validateSchedule($validated, $appointment);

        $previousStatus = $appointment->status;
        $appointment->update($validated);

        if ($validated['status'] !== $previousStatus) {
            $settingKey = match ($validated['status']) {
                Appointment::STATUS_CONFIRMED => 'notify_appointment_confirmation',
                Appointment::STATUS_CANCELLED => 'notify_appointment_cancellation',
                default => null,
            };

            if ($settingKey !== null) {
                PracticeActivityNotification::sendToActiveStaff([
                    'category' => 'appointments',
                    'title' => 'Appointment '.$appointment->status_label,
                    'message' => $appointment->patient->name.' has appointment '.$appointment->appointment_id.' scheduled for '.$appointment->appointment_date->format('d M Y').'.',
                    'url' => route('appointments.show', $appointment),
                    'permission' => 'appointments.view',
                ], $settingKey);
            }
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', "Appointment {$appointment->appointment_id} updated successfully.");
    }

    /**
     * Lifecycle Status Transitions (Sec 4, p. 4 & Sec 12, p. 14).
     * Quick actions: Check In, Start Consultation, Complete, Cancel, No-Show.
     */
    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,checked_in,in_consultation,completed,cancelled,no_show'],
            'cancelled_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $permission = $validated['status'] === Appointment::STATUS_CANCELLED
            ? 'appointments.cancel'
            : 'appointments.edit';

        abort_unless($request->user()->can($permission), 403);

        $allowedTransitions = match ($appointment->status) {
            Appointment::STATUS_PENDING => [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_CONFIRMED => [Appointment::STATUS_CHECKED_IN, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_CHECKED_IN => [Appointment::STATUS_IN_CONSULTATION, Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW],
            Appointment::STATUS_IN_CONSULTATION => [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED],
            default => [],
        };

        if (! in_array($validated['status'], $allowedTransitions, true)) {
            throw ValidationException::withMessages([
                'status' => 'This appointment cannot move to the selected status.',
            ]);
        }

        $updateData = ['status' => $validated['status']];
        if ($validated['status'] === Appointment::STATUS_CANCELLED && ! empty($validated['cancelled_reason'])) {
            $updateData['cancelled_reason'] = $validated['cancelled_reason'];
        }

        $appointment->update($updateData);

        $settingKey = match ($validated['status']) {
            Appointment::STATUS_CONFIRMED => 'notify_appointment_confirmation',
            Appointment::STATUS_CANCELLED => 'notify_appointment_cancellation',
            default => null,
        };

        if ($settingKey !== null) {
            PracticeActivityNotification::sendToActiveStaff([
                'category' => 'appointments',
                'title' => 'Appointment '.$appointment->status_label,
                'message' => $appointment->patient->name.' has appointment '.$appointment->appointment_id.' scheduled for '.$appointment->appointment_date->format('d M Y').'.',
                'url' => route('appointments.show', $appointment),
                'permission' => 'appointments.view',
            ], $settingKey);
        }

        $label = $appointment->status_label;

        return back()->with('success', "Appointment {$appointment->appointment_id} status updated to '{$label}'.");
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $id = $appointment->appointment_id;
        $appointment->delete();

        return redirect()->route('appointments.index')
            ->with('success', "Appointment {$id} archived successfully.");
    }

    /**
     * @return array<int, string>
     */
    private function getTimeSlots(): array
    {
        $startTime = Carbon::createFromTimeString(Setting::get('appointment_working_hours_start', '09:00'));
        $endTime = Carbon::createFromTimeString(Setting::get('appointment_working_hours_end', '19:00'));
        $breakStart = Setting::get('appointment_break_time_start', '13:00');
        $breakEnd = Setting::get('appointment_break_time_end', '14:00');
        $duration = max(1, (int) Setting::get('appointment_duration', '15'));
        $timeSlots = [];

        while ($startTime->copy()->addMinutes($duration) <= $endTime) {
            $slot = $startTime->format('H:i');
            $slotEnd = $startTime->copy()->addMinutes($duration)->format('H:i');
            $overlapsBreak = $slot < $breakEnd && $slotEnd > $breakStart;

            if (! $overlapsBreak) {
                $timeSlots[] = $slot;
            }
            $startTime->addMinutes($duration);
        }

        return $timeSlots;
    }

    /**
     * @param  array<string, mixed>  $appointmentData
     */
    private function validateSchedule(array $appointmentData, ?Appointment $appointment = null): void
    {
        $appointmentDate = Carbon::parse($appointmentData['appointment_date']);
        $workingDays = json_decode(Setting::get('appointment_working_days', '[]'), true);
        $timeSlot = $appointmentData['appointment_time'];
        $isUnchangedSlot = $appointment !== null
            && $appointment->appointment_date->toDateString() === $appointmentDate->toDateString()
            && (int) $appointment->doctor_id === (int) $appointmentData['doctor_id']
            && substr($appointment->appointment_time, 0, 5) === $timeSlot;

        if ($appointmentDate->lt(today()) && ! $isUnchangedSlot) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Appointments cannot be scheduled in the past.',
            ]);
        }

        if (is_array($workingDays) && $workingDays !== [] && ! in_array($appointmentDate->format('l'), $workingDays, true) && ! $isUnchangedSlot) {
            throw ValidationException::withMessages([
                'appointment_date' => 'The clinic is closed on the selected day.',
            ]);
        }

        if (! in_array($timeSlot, $this->getTimeSlots(), true) && ! $isUnchangedSlot) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Choose a time during clinic hours, outside the break period.',
            ]);
        }

        $dailyLimit = max(1, (int) Setting::get('appointment_max_per_day', '30'));
        $dailyAppointments = Appointment::query()
            ->whereDate('appointment_date', $appointmentDate->toDateString())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW])
            ->when($appointment, fn ($query) => $query->where('id', '!=', $appointment->id))
            ->count();

        if ($dailyAppointments >= $dailyLimit && (! $appointment || $appointment->appointment_date->toDateString() !== $appointmentDate->toDateString())) {
            throw ValidationException::withMessages([
                'appointment_date' => 'The clinic has reached its appointment limit for that day.',
            ]);
        }

        $duration = max(1, (int) Setting::get('appointment_duration', '15'));
        $candidateStart = Carbon::parse($appointmentDate->toDateString().' '.$appointmentData['appointment_time']);

        if ($candidateStart->isPast() && ! $isUnchangedSlot) {
            throw ValidationException::withMessages([
                'appointment_time' => 'Appointments cannot be scheduled for a time that has already passed.',
            ]);
        }

        $candidateEnd = $candidateStart->copy()->addMinutes($duration);
        $existingAppointments = Appointment::query()
            ->where('doctor_id', $appointmentData['doctor_id'])
            ->whereDate('appointment_date', $appointmentDate->toDateString())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED, Appointment::STATUS_NO_SHOW])
            ->when($appointment, fn ($query) => $query->where('id', '!=', $appointment->id))
            ->get(['appointment_time']);

        foreach ($existingAppointments as $existingAppointment) {
            $existingStart = Carbon::parse($appointmentDate->toDateString().' '.$existingAppointment->appointment_time);
            $existingEnd = $existingStart->copy()->addMinutes($duration);

            if ($candidateStart->lt($existingEnd) && $candidateEnd->gt($existingStart)) {
                throw ValidationException::withMessages([
                    'appointment_time' => 'This doctor already has an appointment during that time.',
                ]);
            }
        }
    }
}
