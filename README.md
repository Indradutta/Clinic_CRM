# MediFlow — Clinic Management & CRM System 🏥⚡

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38BDF8?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Vite](https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white)](https://vitejs.dev)
[![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)](https://alpinejs.dev)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**MediFlow** is a modern, enterprise-grade Healthcare & Clinic Customer Relationship Management (CRM) and Point-of-Sale (POS) system designed to streamline clinical operations, patient care, staff workflow, prescription generation, and financial billing.

---

## 🌟 Key Features & Modules

### 📊 1. Executive Dashboard & Analytics
- **Live Metrics**: Real-time stats on total patients, daily appointments, monthly revenue, and active consultations.
- **Interactive Visualizations**: Financial trends and patient status breakdowns powered by **Chart.js**.
- **Quick Action Bar**: Fast access to record appointments, create invoices, or add new patients.

### 👨‍⚕️ 2. Doctor Management & Directory
- Complete doctor profile management including specializations, contact details, and availability schedules.
- Departmental categorization and appointment assignment capabilities.

### 📅 3. Appointment Lifecycle Management
- **Full Workflow Tracking**: Scheduled, In-Progress, Completed, and Cancelled statuses.
- Filter and search appointments by date range, assigned practitioner, or status.
- Real-time status update toggles directly from the calendar or table view.

### 🩺 4. Electronic Health Records (EHR) & Patient Portal
- Complete patient demographics, medical history, and emergency contact details.
- **Clinical Notes**: Chronological progress notes per patient visit.
- **Document Vault**: File uploads and downloads for lab reports, medical scans, and insurance cards.

### 📝 5. Consultations & Digital Prescription Generator
- Clinical encounter recording with chief complaints, diagnosis, and treatment plans.
- **Digital Rx Builder**: Add itemized medications, dosage instructions, frequency, and duration.
- **Print & Export**: Generate clean, printable PDF-ready digital prescriptions formatted for clinical output.

### 💳 6. Billing, Invoices & Multi-Mode Payments
- Itemized invoice creation for consultations, procedures, lab tests, and medications.
- **Split & Multi-Mode Payments**: Support for Cash, Credit/Debit Card, UPI/QR, and Online payments.
- Instant printable payment receipts with tax calculations and payment status indicators.

### 👥 7. Role-Based Access Control (RBAC) & Staff Directory
- **Granular Permission Middleware**: Route-level protection for viewing, creating, editing, and deleting records.
- **Subaccount / Staff Delegation**: Role delegation for receptionists, nurses, billing staff, and associate doctors.
- Status toggle to activate/deactivate staff accounts instantly.

### 📈 8. Reports & Financial Insights
- Detailed financial, patient attendance, and consultation volume reports.
- Printable report summaries and CSV/Excel data export functionality.

### 🔔 9. Real-Time Notification Center & Global Search
- Categorized inbox for unread notifications, system alerts, and appointment reminders.
- Global search shortcut bar to quickly lookup patients, invoices, doctors, or appointments.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | [Laravel 13](https://laravel.com) / PHP 8.3 |
| **Frontend Framework** | Blade Templating, [Alpine.js 3](https://alpinejs.dev) |
| **Styling & UI** | [Tailwind CSS v4](https://tailwindcss.com), Custom Glassmorphism Design System |
| **Asset Bundler** | [Vite 6](https://vitejs.dev) |
| **Charts & Graphics** | [Chart.js 4](https://www.chartjs.org) |
| **Database** | SQLite (Development) / MySQL 8.0+ / PostgreSQL (Production) |

---

## 🚀 Getting Started

Follow these step-by-step instructions to set up the project locally on your machine.

### Prerequisites
Make sure you have the following installed on your system:
- **PHP** >= 8.3
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm**
- **Git**

---

### Step 1: Clone the Repository
```bash
git clone https://github.com/YOUR-USERNAME/Clinic_CRM.git
cd Clinic_CRM
```

### Step 2: Install Backend Dependencies
```bash
composer install
```

### Step 3: Install Frontend Dependencies
```bash
npm install
```

### Step 4: Environment Configuration
Copy the example environment file and generate the application encryption key:
```bash
cp .env.example .env
php artisan key:generate
```

> [!NOTE]
> By default, the application is configured to use SQLite database (`database/database.sqlite`). You can configure MySQL credentials in `.env` if desired.

### Step 5: Run Database Migrations & Seeders
Execute database migrations along with sample data seeders:
```bash
php artisan migrate --seed
```

### Step 6: Build Frontend Assets
Compile assets for development or production:
```bash
# Build for production
npm run build

# OR start Vite development server
npm run dev
```

### Step 7: Launch Local Server
Start the Laravel development server:
```bash
php artisan serve
```
Open your browser and navigate to `http://127.0.0.1:8000` to access **MediFlow Clinic CRM**.

---

## 📁 Directory Structure Overview

```text
Clinic_CRM/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # Application Controllers (Auth, Patient, Doctor, Invoice, etc.)
│   │   └── Middleware/      # Custom Permission & Auth Middleware
│   └── Models/              # Eloquent Models (User, Patient, Appointment, Invoice, etc.)
├── config/                  # Framework Configuration files
├── database/
│   ├── factories/           # Model Factories
│   ├── migrations/          # Database Migration files
│   └── seeders/             # Initial Data Seeders
├── public/                  # Entry point & public assets
├── resources/
│   ├── css/                 # Tailwind CSS styles
│   ├── js/                  # Alpine.js & Chart.js scripts
│   └── views/               # Blade Views & Layout Components
├── routes/
│   └── web.php              # CRM Web Routes & RBAC Definitions
└── README.md                # Project Documentation
```

---

## 🧪 Running Tests

Execute PHPUnit / Artisan test suites:
```bash
php artisan test
```

---

## 📝 License

This project is open-source and available under the [MIT License](LICENSE).
