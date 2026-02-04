# HRM Platform - Backend API

The backend engine for the Enterprise HRM Platform, built with Laravel 12.0.

## 🚀 Features

- **Authentication & Security**: 
  - Token-based auth (Laravel Sanctum).
  - Role-Based Access Control (RBAC) with Spatie Roles & Permissions.
- **Employee Management**: Full lifecycle management, document tracking.
- **Attendance system**: GPS-tagged clock-in/out via API.
- **Leave Management**: Configurable approval workflows.
- **Payroll & Documents**:
  - Period-based payroll calculations.
  - **Official Payslip Generation**: High-quality PDF generation with Arabic and RTL support using `dompdf`.
- **Localization**: Backend support for multiple locales (en/ar) in error messages and document generation.
- **RESTful API**: Fully documented using Swagger/OpenAPI.

## 🛠️ Tech Stack

- **Framework**: Laravel 12.0
- **Database**: PostgreSQL
- **Cache**: Redis
- **Documentation**: L5-Swagger (OpenAPI 3.0)
- **PDF**: Barryvdh Laravel DomPDF

## 📁 Installation

### 1. Prerequisites
- PHP 8.2+
- Composer 2.x
- Docker (optional)

### 2. Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

### 3. Running
```bash
php artisan serve
```
API will be available at: `http://localhost:8000/api/v1`

## 📚 API Documentation

Once the server is running, you can access the interactive Swagger documentation at:
`http://localhost:8000/api/documentation`

## 🧪 Testing

We use PHPUnit for both Unit and Feature testing.
```bash
php artisan test
```

---
**Built for Enterprise Excellence**
