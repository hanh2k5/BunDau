# 🍜 Bún Đậu Quán — Restaurant Management System

Hệ thống quản lý bán hàng offline cho quán bún đậu.

## 🏗️ Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend API | Laravel | 11.x |
| Authentication | Laravel Sanctum | 4.x |
| Frontend SPA | VueJS 3 | 3.4+ |
| State Management | Pinia | 2.x |
| CSS Framework | Tailwind CSS | 4.x |
| Database | MySQL | 8.x |

## 📁 Project Structure

```
BunDau/
├── backend/            ← Laravel 11 REST API
├── frontend/           ← VueJS 3 SPA
└── .github/workflows/  ← CI/CD
```

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js 20+
- MySQL 8.x

### Backend Setup
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate

# Configure your MySQL database in .env
php artisan migrate:fresh --seed
php artisan serve --port=8000
```

### Frontend Setup
```bash
cd frontend
npm install
npm run dev
```

## 🔑 Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bundau.vn | password |
| Staff | staff@bundau.vn | password |

## 🌐 API Endpoints

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | /api/auth/login | Public | Login |
| POST | /api/auth/logout | Bearer | Logout |
| GET | /api/auth/me | Bearer | Current user |
| GET | /api/products | Bearer | List products |
| POST | /api/products | Admin | Create product |
| PUT | /api/products/{id} | Admin | Update product |
| DELETE | /api/products/{id} | Admin | Delete product |
| GET | /api/orders | Bearer | List orders |
| POST | /api/orders | Bearer | Create order |
| PATCH | /api/orders/{id}/pay | Bearer | Pay order |
| PATCH | /api/orders/{id}/cancel | Bearer | Cancel order |
| GET | /api/revenue/today | Admin | Today's revenue |
| GET | /api/revenue/summary | Admin | Revenue summary |

## 🏛️ Architecture

- **Fat Service, Thin Controller**: Business logic lives in Services
- **Repository Pattern**: All DB queries go through Repositories
- **API Resources**: Consistent JSON responses via Resources
- **Form Requests**: Validation separated into FormRequest classes
- **Pinia Stores**: Centralized state management with persistence

## 📝 License

MIT