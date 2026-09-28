<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SubaccountController;
use Illuminate\Support\Facades\Route;

// --- Guest Authentication Routes ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetCode'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');
    Route::post('/reset-password/resend', [AuthController::class, 'resendPasswordResetCode'])->middleware('throttle:3,1')->name('password.resend');
});

// --- Authenticated CRM Workspace Routes ---
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 1. Dashboard (Sec 3)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view');

    Route::prefix('doctors')->name('doctors.')->middleware('permission:doctors.view')->group(function () {
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('/create', [DoctorController::class, 'create'])->name('create')->middleware('permission:doctors.create');
        Route::post('/', [DoctorController::class, 'store'])->name('store')->middleware('permission:doctors.create');
        Route::get('/{doctor}/edit', [DoctorController::class, 'edit'])->name('edit')->middleware('permission:doctors.edit');
        Route::put('/{doctor}', [DoctorController::class, 'update'])->name('update')->middleware('permission:doctors.edit');
        Route::delete('/{doctor}', [DoctorController::class, 'destroy'])->name('destroy')->middleware('permission:doctors.delete');
    });

    // 2. Appointments (Sec 4)
    Route::prefix('appointments')->name('appointments.')->middleware('permission:appointments.view')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/create', [AppointmentController::class, 'create'])->name('create')->middleware('permission:appointments.create');
        Route::post('/', [AppointmentController::class, 'store'])->name('store')->middleware('permission:appointments.create');
        Route::get('/{appointment}', [AppointmentController::class, 'show'])->name('show');
        Route::get('/{appointment}/edit', [AppointmentController::class, 'edit'])->name('edit')->middleware('permission:appointments.edit');
        Route::put('/{appointment}', [AppointmentController::class, 'update'])->name('update')->middleware('permission:appointments.edit');
        Route::delete('/{appointment}', [AppointmentController::class, 'destroy'])->name('destroy')->middleware('permission:appointments.delete');
        Route::post('/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('update-status');
    });

    // 3. Patients (Sec 5)
    Route::prefix('patients')->name('patients.')->middleware('permission:patients.view')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index');
        Route::get('/create', [PatientController::class, 'create'])->name('create')->middleware('permission:patients.create');
        Route::post('/', [PatientController::class, 'store'])->name('store')->middleware('permission:patients.create');
        Route::get('/{patient}', [PatientController::class, 'show'])->name('show');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit')->middleware('permission:patients.edit');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update')->middleware('permission:patients.edit');
        Route::delete('/{patient}', [PatientController::class, 'destroy'])->name('destroy')->middleware('permission:patients.delete');
        Route::post('/{patient}/notes', [PatientController::class, 'storeNote'])->name('notes.store')->middleware('permission:patients.edit');
        Route::delete('/notes/{note}', [PatientController::class, 'destroyNote'])->name('notes.destroy')->middleware('permission:patients.edit');
        Route::post('/{patient}/documents', [PatientController::class, 'storeDocument'])->name('documents.store')->middleware('permission:patients.edit');
        Route::get('/documents/{document}/download', [PatientController::class, 'downloadDocument'])->name('documents.download');
        Route::delete('/documents/{document}', [PatientController::class, 'destroyDocument'])->name('documents.destroy')->middleware('permission:patients.edit');
    });

    // 4. Prescriptions (Sec 6, pp. 6-7)
    Route::prefix('prescriptions')->name('prescriptions.')->middleware('permission:prescriptions.view')->group(function () {
        Route::get('/', [PrescriptionController::class, 'index'])->name('index');
        Route::get('/create', [PrescriptionController::class, 'create'])->name('create')->middleware('permission:prescriptions.create');
        Route::post('/', [PrescriptionController::class, 'store'])->name('store')->middleware('permission:prescriptions.create');
        Route::get('/{prescription}', [PrescriptionController::class, 'show'])->name('show');
        Route::get('/{prescription}/print', [PrescriptionController::class, 'print'])->name('print')->middleware('permission:prescriptions.print');
        Route::get('/{prescription}/edit', [PrescriptionController::class, 'edit'])->name('edit')->middleware('permission:prescriptions.edit');
        Route::put('/{prescription}', [PrescriptionController::class, 'update'])->name('update')->middleware('permission:prescriptions.edit');
        Route::delete('/{prescription}', [PrescriptionController::class, 'destroy'])->name('destroy')->middleware('permission:prescriptions.delete');
    });

    // Clinical Consultations (Sec 12, pp. 14-15)
    Route::prefix('consultations')->name('consultations.')->middleware('permission:consultations.view')->group(function () {
        Route::get('/create', [ConsultationController::class, 'create'])->name('create')->middleware('permission:consultations.create');
        Route::post('/', [ConsultationController::class, 'store'])->name('store')->middleware('permission:consultations.create');
        Route::get('/{consultation}', [ConsultationController::class, 'show'])->name('show');
        Route::get('/{consultation}/edit', [ConsultationController::class, 'edit'])->name('edit')->middleware('permission:consultations.edit');
        Route::put('/{consultation}', [ConsultationController::class, 'update'])->name('update')->middleware('permission:consultations.edit');
        Route::delete('/{consultation}', [ConsultationController::class, 'destroy'])->name('destroy')->middleware('permission:consultations.delete');
    });

    // 5. Invoices & Billing (Sec 7, pp. 7-9)
    Route::prefix('invoices')->name('invoices.')->middleware('permission:invoices.view')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create')->middleware('permission:invoices.create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store')->middleware('permission:invoices.create');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/print', [InvoiceController::class, 'print'])->name('print');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit')->middleware('permission:invoices.edit');
        Route::put('/{invoice}', [InvoiceController::class, 'update'])->name('update')->middleware('permission:invoices.edit');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy')->middleware('permission:invoices.delete');
    });

    // Multi-Mode Payments & Receipts (Sec 7, pp. 8-9)
    Route::prefix('payments')->name('payments.')->middleware('permission:invoices.payment_management')->group(function () {
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
        Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
    });

    // 6. Reports (Sec 14)
    Route::prefix('reports')->name('reports.')->middleware('permission:reports.view')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'export'])->name('export')->middleware('permission:reports.export');
        Route::get('/print', [ReportController::class, 'print'])->name('print')->middleware('permission:reports.print');
    });

    // 7. Subaccounts / Staff Directory (Sec 9, 10)
    Route::prefix('subaccounts')->name('subaccounts.')->middleware('permission:subaccounts.view')->group(function () {
        Route::get('/', [SubaccountController::class, 'index'])->name('index');
        Route::get('/create', [SubaccountController::class, 'create'])->name('create')->middleware('permission:subaccounts.create');
        Route::post('/', [SubaccountController::class, 'store'])->name('store')->middleware('permission:subaccounts.create');
        Route::get('/{subaccount}/edit', [SubaccountController::class, 'edit'])->name('edit')->middleware('permission:subaccounts.edit');
        Route::put('/{subaccount}', [SubaccountController::class, 'update'])->name('update')->middleware('permission:subaccounts.edit');
        Route::delete('/{subaccount}', [SubaccountController::class, 'destroy'])->name('destroy')->middleware('permission:subaccounts.delete');
        Route::post('/{subaccount}/toggle-status', [SubaccountController::class, 'toggleStatus'])->name('toggle-status')->middleware('permission:subaccounts.edit');
    });

    // 8. Settings (Sec 8)
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.view')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::post('/update/{group}', [SettingController::class, 'update'])->name('update')->middleware('permission:settings.edit');
        Route::post('/password', [SettingController::class, 'updatePassword'])->name('password')->middleware('permission:settings.edit');
        Route::delete('/sessions/{session}', [SettingController::class, 'destroySession'])->name('session.destroy')->middleware('permission:settings.edit');
    });

    // Global Search & Notifications (Sec 11, 13)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/open', [NotificationController::class, 'open'])->name('notifications.open');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/{notification}/unread', [NotificationController::class, 'markUnread'])->name('notifications.unread');
    Route::delete('/notifications/read', [NotificationController::class, 'clearRead'])->name('notifications.clear-read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/search', [SearchController::class, 'index'])->name('search');
});
