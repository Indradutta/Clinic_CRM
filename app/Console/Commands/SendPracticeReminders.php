<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Setting;
use App\Notifications\PracticeActivityNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

#[Signature('practice:send-reminders')]
#[Description('Send deduplicated appointment and invoice reminders to active staff.')]
class SendPracticeReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sentAppointments = $this->sendAppointmentReminders();
        $sentInvoices = $this->sendInvoiceReminders();

        $this->info("Sent {$sentAppointments} appointment and {$sentInvoices} invoice reminder(s).");

        return self::SUCCESS;
    }

    private function sendAppointmentReminders(): int
    {
        if (Setting::get('appointment_reminders', '1') !== '1'
            || Setting::get('notify_appointment_reminders', '1') !== '1') {
            return 0;
        }

        $reminderDate = now()->addDay()->toDateString();
        $sent = 0;

        Appointment::query()
            ->with('patient')
            ->whereDate('appointment_date', $reminderDate)
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->orderBy('id')
            ->chunkById(100, function ($appointments) use (&$sent, $reminderDate): void {
                foreach ($appointments as $appointment) {
                    $key = 'appointment:'.$appointment->id.':'.$reminderDate;
                    if ($this->reminderWasSent($key)) {
                        continue;
                    }

                    PracticeActivityNotification::sendToActiveStaff([
                        'category' => 'appointments',
                        'title' => 'Appointment reminder',
                        'message' => $appointment->patient->name.' is scheduled for '.$appointment->appointment_date->format('d M Y').' at '.date('g:i A', strtotime($appointment->appointment_time)).'.',
                        'url' => route('appointments.show', $appointment),
                        'permission' => 'appointments.view',
                        'reminder_key' => $key,
                    ], 'notify_appointment_reminders');

                    $sent++;
                }
            });

        return $sent;
    }

    private function sendInvoiceReminders(): int
    {
        if (Setting::get('notify_invoice_due', '1') !== '1') {
            return 0;
        }

        $today = now()->toDateString();
        $sent = 0;

        Invoice::query()
            ->with('patient')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', $today)
            ->where('balance_due', '>', 0)
            ->where('status', '!=', Invoice::STATUS_CANCELLED)
            ->orderBy('id')
            ->chunkById(100, function ($invoices) use (&$sent): void {
                foreach ($invoices as $invoice) {
                    $key = 'invoice:'.$invoice->id.':due';
                    if ($this->reminderWasSent($key)) {
                        continue;
                    }

                    $dueLabel = $invoice->due_date->lt(today()) ? 'is overdue' : 'is due today';
                    PracticeActivityNotification::sendToActiveStaff([
                        'category' => 'billing',
                        'title' => 'Invoice payment due',
                        'message' => 'Invoice '.$invoice->invoice_number.' for '.$invoice->patient->name.' '.$dueLabel.' with ₹'.number_format((float) $invoice->balance_due, 2).' outstanding.',
                        'url' => route('invoices.show', $invoice),
                        'permission' => 'invoices.view',
                        'reminder_key' => $key,
                    ], 'notify_invoice_due');

                    $sent++;
                }
            });

        return $sent;
    }

    private function reminderWasSent(string $key): bool
    {
        return DatabaseNotification::query()
            ->where('type', PracticeActivityNotification::class)
            ->whereJsonContains('data->reminder_key', $key)
            ->exists();
    }
}
