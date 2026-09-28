<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the centralized practice dashboard with live KPIs, appointments queue,
     * recent patients, revenue summaries, and Chart.js analytics (Sec 3, pp. 1-3).
     */
    public function index(Request $request): View
    {
        $todayStr = now()->toDateString();

        // 1. Dashboard Statistics Cards (Sec 3, pp. 1-2)
        $kpiStats = [
            'total_patients' => Patient::count(),
            'new_patients_week' => Patient::where('created_at', '>=', now()->startOfWeek())->count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $todayStr)->count(),
            'today_morning' => Appointment::whereDate('appointment_date', $todayStr)
                ->whereTime('appointment_time', '<', '12:00:00')
                ->count(),
            'today_afternoon' => Appointment::whereDate('appointment_date', $todayStr)
                ->whereTime('appointment_time', '>=', '12:00:00')
                ->count(),
            'upcoming_appointments' => Appointment::whereDate('appointment_date', '>', $todayStr)
                ->whereNotIn('status', [Appointment::STATUS_COMPLETED, Appointment::STATUS_CANCELLED])
                ->count(),
            'completed_appointments' => Appointment::where('status', Appointment::STATUS_COMPLETED)->count(),
            'pending_appointments' => Appointment::where('status', Appointment::STATUS_PENDING)->count(),
            'cancelled_appointments' => Appointment::where('status', Appointment::STATUS_CANCELLED)->count(),
            'total_invoices' => Invoice::count(),
            'paid_amount' => (float) Payment::sum('amount'),
            'pending_amount' => (float) Invoice::whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
                ->sum('balance_due'),
        ];

        // 2. Today's Appointments Queue with Live Relations (Sec 3, p. 2)
        $todayAppointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $todayStr)
            ->orderBy('appointment_time', 'asc')
            ->get();

        // 3. Recent Patients with Computed Last Visit & Next Appointment (Sec 3, p. 2)
        $recentPatients = Patient::with([
            'appointments' => function ($query): void {
                $query->select('id', 'patient_id', 'appointment_date', 'appointment_time', 'status')
                    ->orderBy('appointment_date', 'desc')
                    ->orderBy('appointment_time', 'desc');
            },
            'consultations' => function ($query): void {
                $query->select('id', 'patient_id', 'consultation_date')->latest('consultation_date');
            },
        ])
            ->latest()
            ->take(6)
            ->get()
            ->map(function (Patient $patient): Patient {
                // Computed Last Visit
                $lastConsult = $patient->consultations->first();
                $lastCompletedApt = $patient->appointments->firstWhere('status', Appointment::STATUS_COMPLETED);

                $lastVisitDate = null;
                if ($lastConsult && $lastConsult->consultation_date) {
                    $lastVisitDate = $lastConsult->consultation_date;
                } elseif ($lastCompletedApt && $lastCompletedApt->appointment_date) {
                    $lastVisitDate = $lastCompletedApt->appointment_date;
                }
                $patient->last_visit_display = $lastVisitDate ? $lastVisitDate->format('d M Y') : 'First Visit';

                // Computed Next Appointment
                $nextApt = $patient->appointments
                    ->filter(function ($apt): bool {
                        return $apt->appointment_date && $apt->appointment_date->toDateString() >= now()->toDateString()
                            && in_array($apt->status, [
                                Appointment::STATUS_CONFIRMED,
                                Appointment::STATUS_CHECKED_IN,
                                Appointment::STATUS_PENDING,
                            ], true);
                    })
                    ->sortBy('appointment_date')
                    ->first();

                if ($nextApt) {
                    $isToday = $nextApt->appointment_date->isToday();
                    $timeStr = $nextApt->appointment_time ? date('h:i A', strtotime($nextApt->appointment_time)) : '';
                    $patient->next_appointment_display = ($isToday ? 'Today' : $nextApt->appointment_date->format('d M Y')).($timeStr ? ', '.$timeStr : '');
                } else {
                    $patient->next_appointment_display = 'None scheduled';
                }

                return $patient;
            });

        // 4. Revenue Overview (Sec 3, p. 2)
        $revenueOverview = [
            'today' => (float) Payment::whereDate('payment_date', $todayStr)->sum('amount'),
            'week' => (float) Payment::whereBetween('payment_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ])->sum('amount'),
            'month' => (float) Payment::whereBetween('payment_date', [
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ])->sum('amount'),
            'outstanding' => (float) Invoice::whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
                ->sum('balance_due'),
            'outstanding_count' => Invoice::whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
                ->where('balance_due', '>', 0)
                ->count(),
        ];

        // 5. Chart.js Monthly Revenue Trend (Last 6 Months) (Sec 3, p. 3)
        $monthlyRevenueChart = [
            'labels' => [],
            'revenue' => [],
            'invoiced' => [],
        ];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = now()->subMonths($i);
            $startOfMonth = $monthDate->copy()->startOfMonth()->toDateString();
            $endOfMonth = $monthDate->copy()->endOfMonth()->toDateString();

            $monthlyRevenueChart['labels'][] = $monthDate->format('M Y');
            $monthlyRevenueChart['revenue'][] = (float) Payment::whereBetween('payment_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');
            $monthlyRevenueChart['invoiced'][] = (float) Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])
                ->sum('grand_total');
        }

        // 6. Chart.js Appointment Lifecycle Distribution
        $appointmentDistribution = [
            'labels' => ['Confirmed', 'Checked In', 'In Consultation', 'Completed', 'Pending', 'Cancelled'],
            'data' => [
                Appointment::where('status', Appointment::STATUS_CONFIRMED)->count(),
                Appointment::where('status', Appointment::STATUS_CHECKED_IN)->count(),
                Appointment::where('status', Appointment::STATUS_IN_CONSULTATION)->count(),
                Appointment::where('status', Appointment::STATUS_COMPLETED)->count(),
                Appointment::where('status', Appointment::STATUS_PENDING)->count(),
                Appointment::where('status', Appointment::STATUS_CANCELLED)->count(),
            ],
        ];

        // 7. Practice Context & Settings
        $clinicName = Setting::get('clinic_name', 'MediFlow Clinic');
        $currencySymbol = Setting::get('currency_symbol', '₹');

        return view('dashboard', compact(
            'kpiStats',
            'todayAppointments',
            'recentPatients',
            'revenueOverview',
            'monthlyRevenueChart',
            'appointmentDistribution',
            'clinicName',
            'currencySymbol'
        ));
    }
}
