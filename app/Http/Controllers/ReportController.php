<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the Practice Reports Hub with multi-module reports, date filters, and search (Sec 14, p. 16).
     */
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $reportType = $filters['type'] ?? 'financial';
        $dateFilter = $filters['date_range'] ?? 'this_month';
        $search = $filters['search'] ?? null;

        [$startDate, $endDate, $dateLabel] = $this->resolveDateRange($dateFilter, $filters['from_date'] ?? null, $filters['to_date'] ?? null);

        $currencySymbol = Setting::get('currency_symbol', '₹');
        $clinicName = Setting::get('clinic_name', 'MediFlow Clinic');

        $data = match ($reportType) {
            'appointments' => $this->getAppointmentReportData($startDate, $endDate, $search),
            'patients' => $this->getPatientReportData($startDate, $endDate, $search),
            'prescriptions' => $this->getPrescriptionReportData($startDate, $endDate, $search),
            default => $this->getFinancialReportData($startDate, $endDate, $search),
        };

        return view('reports.index', array_merge($data, [
            'reportType' => $reportType,
            'dateFilter' => $dateFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateLabel' => $dateLabel,
            'search' => $search,
            'currencySymbol' => $currencySymbol,
            'clinicName' => $clinicName,
        ]));
    }

    /**
     * Stream CSV export for the selected report dataset (Sec 14, p. 16).
     */
    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $reportType = $filters['type'] ?? 'financial';
        $dateFilter = $filters['date_range'] ?? 'this_month';
        $search = $filters['search'] ?? null;

        [$startDate, $endDate, $dateLabel] = $this->resolveDateRange($dateFilter, $filters['from_date'] ?? null, $filters['to_date'] ?? null);

        $fileName = "mediflow_{$reportType}_report_".now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($reportType, $startDate, $endDate, $search): void {
            $handle = fopen('php://output', 'w');

            if ($reportType === 'appointments') {
                fputcsv($handle, ['Appointment ID', 'Date', 'Time', 'Patient Name', 'Phone', 'Doctor', 'Type', 'Status', 'Payment Status']);
                $query = Appointment::with(['patient', 'doctor'])
                    ->whereBetween('appointment_date', [$startDate->toDateString(), $endDate->toDateString()]);
                if (! empty($search)) {
                    $query->where(function ($q) use ($search): void {
                        $q->where('appointment_id', 'like', "%{$search}%")
                            ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
                    });
                }
                foreach ($query->orderBy('appointment_date', 'desc')->cursor() as $row) {
                    fputcsv($handle, [
                        $row->appointment_id,
                        $row->appointment_date?->format('Y-m-d'),
                        $row->appointment_time,
                        $row->patient->name ?? 'N/A',
                        $row->patient->phone ?? 'N/A',
                        $row->doctor->name ?? 'N/A',
                        $row->appointment_type,
                        $row->status_label,
                        ucfirst($row->payment_status),
                    ]);
                }
            } elseif ($reportType === 'patients') {
                fputcsv($handle, ['Patient ID', 'Full Name', 'Gender', 'Age', 'Phone', 'Email', 'Blood Group', 'Registered Date']);
                $query = Patient::whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()]);
                if (! empty($search)) {
                    $query->where(function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('patient_id', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
                }
                foreach ($query->orderBy('created_at', 'desc')->cursor() as $row) {
                    fputcsv($handle, [
                        $row->patient_id,
                        $row->name,
                        $row->gender,
                        $row->age,
                        $row->phone,
                        $row->email,
                        $row->blood_group,
                        $row->created_at?->format('Y-m-d H:i'),
                    ]);
                }
            } elseif ($reportType === 'prescriptions') {
                fputcsv($handle, ['Rx Number', 'Date', 'Patient Name', 'Doctor', 'Diagnosis', 'Medicines Count', 'Follow-up Date']);
                $query = Prescription::with(['patient', 'doctor', 'items'])
                    ->whereBetween('prescription_date', [$startDate->toDateString(), $endDate->toDateString()]);
                if (! empty($search)) {
                    $query->where(function ($q) use ($search): void {
                        $q->where('prescription_number', 'like', "%{$search}%")
                            ->orWhere('diagnosis', 'like', "%{$search}%")
                            ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
                    });
                }
                foreach ($query->orderBy('prescription_date', 'desc')->cursor() as $row) {
                    fputcsv($handle, [
                        $row->prescription_number,
                        $row->prescription_date?->format('Y-m-d'),
                        $row->patient->name ?? 'N/A',
                        $row->doctor->name ?? 'N/A',
                        $row->diagnosis,
                        $row->items->count(),
                        $row->follow_up_date?->format('Y-m-d') ?? 'None',
                    ]);
                }
            } else {
                // Financial Report
                fputcsv($handle, ['Invoice Number', 'Date', 'Patient Name', 'Doctor', 'Subtotal', 'Tax', 'Discount', 'Grand Total', 'Paid Amount', 'Balance Due', 'Status']);
                $query = Invoice::with(['patient', 'doctor'])
                    ->whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()]);
                if (! empty($search)) {
                    $query->where(function ($q) use ($search): void {
                        $q->where('invoice_number', 'like', "%{$search}%")
                            ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
                    });
                }
                foreach ($query->orderBy('invoice_date', 'desc')->cursor() as $row) {
                    fputcsv($handle, [
                        $row->invoice_number,
                        $row->invoice_date?->format('Y-m-d'),
                        $row->patient->name ?? 'N/A',
                        $row->doctor->name ?? 'N/A',
                        $row->subtotal,
                        $row->tax_amount,
                        $row->discount_amount,
                        $row->grand_total,
                        $row->paid_amount,
                        $row->balance_due,
                        $row->status,
                    ]);
                }
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Printable view of the report with clinic letterhead and summary (Sec 14, p. 16).
     */
    public function print(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $reportType = $filters['type'] ?? 'financial';
        $dateFilter = $filters['date_range'] ?? 'this_month';
        $search = $filters['search'] ?? null;

        [$startDate, $endDate, $dateLabel] = $this->resolveDateRange($dateFilter, $filters['from_date'] ?? null, $filters['to_date'] ?? null);

        $currencySymbol = Setting::get('currency_symbol', '₹');
        $clinicName = Setting::get('clinic_name', 'MediFlow Clinic');
        $doctorName = Setting::get('doctor_name', 'Dr. Alexander Fleming');

        $data = match ($reportType) {
            'appointments' => $this->getAppointmentReportData($startDate, $endDate, $search, 200),
            'patients' => $this->getPatientReportData($startDate, $endDate, $search, 200),
            'prescriptions' => $this->getPrescriptionReportData($startDate, $endDate, $search, 200),
            default => $this->getFinancialReportData($startDate, $endDate, $search, 200),
        };

        return view('reports.print', array_merge($data, [
            'reportType' => $reportType,
            'dateFilter' => $dateFilter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'dateLabel' => $dateLabel,
            'currencySymbol' => $currencySymbol,
            'clinicName' => $clinicName,
            'doctorName' => $doctorName,
        ]));
    }

    /**
     * @return array{type?: string, date_range?: string, from_date?: string, to_date?: string, search?: string|null}
     */
    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', Rule::in(['financial', 'appointments', 'patients', 'prescriptions'])],
            'date_range' => ['nullable', Rule::in(['today', 'yesterday', 'this_week', 'this_month', 'last_month', 'this_year', 'custom'])],
            'from_date' => ['nullable', 'date_format:Y-m-d', 'required_if:date_range,custom'],
            'to_date' => [
                'nullable',
                'date_format:Y-m-d',
                'required_if:date_range,custom',
                Rule::when($request->filled('from_date'), ['after_or_equal:from_date']),
            ],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
    }

    /**
     * Resolve start and end Carbon dates based on preset filter or custom range.
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    private function resolveDateRange(string $filter, ?string $from = null, ?string $to = null): array
    {
        $now = now();

        return match ($filter) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Today ('.$now->format('d M Y').')'],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay(), 'Yesterday ('.$now->copy()->subDay()->format('d M Y').')'],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'This Week ('.$now->copy()->startOfWeek()->format('d M').' - '.$now->copy()->endOfWeek()->format('d M Y').')'],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
                $now->copy()->subMonth()->format('F Y'),
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
                'Year '.$now->format('Y'),
            ],
            'custom' => [
                $from ? Carbon::parse($from)->startOfDay() : $now->copy()->startOfMonth(),
                $to ? Carbon::parse($to)->endOfDay() : $now->copy()->endOfDay(),
                ($from ? Carbon::parse($from)->format('d M Y') : '').' to '.($to ? Carbon::parse($to)->format('d M Y') : ''),
            ],
            default => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
                $now->format('F Y'),
            ],
        };
    }

    /**
     * Gather Financial Report metrics & itemized records (Sec 14, p. 16).
     *
     * @return array<string, mixed>
     */
    private function getFinancialReportData(Carbon $start, Carbon $end, ?string $search = null, int $perPage = 15): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $totalRevenue = (float) Payment::whereBetween('payment_date', [$startStr, $endStr])->sum('amount');
        $totalInvoiced = (float) Invoice::whereBetween('invoice_date', [$startStr, $endStr])->sum('grand_total');
        $paidInvoicesCount = Invoice::whereBetween('invoice_date', [$startStr, $endStr])
            ->where('status', Invoice::STATUS_PAID)
            ->count();
        $outstandingAmount = (float) Invoice::whereBetween('invoice_date', [$startStr, $endStr])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
            ->sum('balance_due');
        $outstandingCount = Invoice::whereBetween('invoice_date', [$startStr, $endStr])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
            ->where('balance_due', '>', 0)
            ->count();

        // Payment Methods breakdown
        $paymentMethods = Payment::whereBetween('payment_date', [$startStr, $endStr])
            ->selectRaw('payment_method, SUM(amount) as total_amount, COUNT(*) as tx_count')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        // Invoices list query
        $query = Invoice::with(['patient', 'doctor', 'payments'])
            ->whereBetween('invoice_date', [$startStr, $endStr]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->orderBy('invoice_date', 'desc')->paginate($perPage)->withQueryString();

        return [
            'metrics' => [
                'total_revenue' => $totalRevenue,
                'total_invoiced' => $totalInvoiced,
                'paid_invoices_count' => $paidInvoicesCount,
                'outstanding_amount' => $outstandingAmount,
                'outstanding_count' => $outstandingCount,
            ],
            'paymentMethods' => $paymentMethods,
            'records' => $records,
        ];
    }

    /**
     * Gather Appointment Report metrics & records (Sec 14, p. 16).
     *
     * @return array<string, mixed>
     */
    private function getAppointmentReportData(Carbon $start, Carbon $end, ?string $search = null, int $perPage = 15): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $totalAppointments = Appointment::whereBetween('appointment_date', [$startStr, $endStr])->count();
        $completedCount = Appointment::whereBetween('appointment_date', [$startStr, $endStr])
            ->where('status', Appointment::STATUS_COMPLETED)
            ->count();
        $cancelledCount = Appointment::whereBetween('appointment_date', [$startStr, $endStr])
            ->where('status', Appointment::STATUS_CANCELLED)
            ->count();
        $noShowCount = Appointment::whereBetween('appointment_date', [$startStr, $endStr])
            ->where('status', Appointment::STATUS_NO_SHOW)
            ->count();
        $confirmedCount = Appointment::whereBetween('appointment_date', [$startStr, $endStr])
            ->whereIn('status', [Appointment::STATUS_CONFIRMED, Appointment::STATUS_CHECKED_IN, Appointment::STATUS_IN_CONSULTATION])
            ->count();

        $completionRate = $totalAppointments > 0 ? round(($completedCount / $totalAppointments) * 100, 1) : 0;

        $query = Appointment::with(['patient', 'doctor'])
            ->whereBetween('appointment_date', [$startStr, $endStr]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('appointment_id', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $records = $query->orderBy('appointment_date', 'desc')->orderBy('appointment_time', 'asc')->paginate($perPage)->withQueryString();

        return [
            'metrics' => [
                'total_appointments' => $totalAppointments,
                'completed_count' => $completedCount,
                'cancelled_count' => $cancelledCount,
                'no_show_count' => $noShowCount,
                'confirmed_count' => $confirmedCount,
                'completion_rate' => $completionRate,
            ],
            'records' => $records,
        ];
    }

    /**
     * Gather Patient Report metrics & patient logs (Sec 14, p. 16).
     *
     * @return array<string, mixed>
     */
    private function getPatientReportData(Carbon $start, Carbon $end, ?string $search = null, int $perPage = 15): array
    {
        $newPatients = Patient::whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])->count();
        $totalRegistered = Patient::where('created_at', '<=', $end->copy()->endOfDay())->count();

        // Returning patients: had an appointment in this period, but registered before start date
        $returningPatients = Patient::where('created_at', '<', $start->copy()->startOfDay())
            ->whereHas('appointments', function ($q) use ($start, $end): void {
                $q->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()]);
            })->count();

        $query = Patient::withCount(['appointments', 'consultations', 'invoices'])
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('patient_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $records = $query->latest()->paginate($perPage)->withQueryString();

        return [
            'metrics' => [
                'new_patients' => $newPatients,
                'returning_patients' => $returningPatients,
                'total_registered' => $totalRegistered,
            ],
            'records' => $records,
        ];
    }

    /**
     * Gather Prescription Report metrics & items (Sec 14, p. 16).
     *
     * @return array<string, mixed>
     */
    private function getPrescriptionReportData(Carbon $start, Carbon $end, ?string $search = null, int $perPage = 15): array
    {
        $startStr = $start->toDateString();
        $endStr = $end->toDateString();

        $totalRx = Prescription::whereBetween('prescription_date', [$startStr, $endStr])->count();
        $distinctPatients = Prescription::whereBetween('prescription_date', [$startStr, $endStr])
            ->distinct('patient_id')
            ->count('patient_id');

        // Most prescribed medications
        $topMedicines = PrescriptionItem::whereHas('prescription', function ($q) use ($startStr, $endStr): void {
            $q->whereBetween('prescription_date', [$startStr, $endStr]);
        })
            ->selectRaw('medicine_name, COUNT(*) as frequency')
            ->groupBy('medicine_name')
            ->orderByDesc('frequency')
            ->take(5)
            ->get();

        $query = Prescription::with(['patient', 'doctor', 'items'])
            ->whereBetween('prescription_date', [$startStr, $endStr]);

        if (! empty($search)) {
            $query->where(function ($q) use ($search): void {
                $q->where('prescription_number', 'like', "%{$search}%")
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhereHas('patient', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $records = $query->orderBy('prescription_date', 'desc')->paginate($perPage)->withQueryString();

        return [
            'metrics' => [
                'total_prescriptions' => $totalRx,
                'distinct_patients' => $distinctPatients,
            ],
            'topMedicines' => $topMedicines,
            'records' => $records,
        ];
    }
}
