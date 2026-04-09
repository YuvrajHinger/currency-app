# Project: Currency Converter & Reports Management

A full-stack web application for currency conversion and report management with authentication. Built with Laravel 13 (backend) and Vue 3 (frontend).

## 📋 Project Structure

```
project/
├── backend/          # Laravel 13 API
│   ├── app/
│   │   ├── Http/Controllers/    # API endpoints
│   │   ├── Models/              # Report, ReportData, User
│   │   ├── Jobs/                # GenerateReportJob (queue)
│   │   └── Services/            # CurrencyService
│   ├── routes/api.php           # API routes
│   ├── database/migrations/     # Database schema
│   └── config/                  # Configuration files
│
└── frontend/         # Vue 3 + Vite SPA
    ├── src/
    │   ├── pages/               # Login, Register, CurrencyConverter, Reports
    │   ├── components/          # UI components
    │   ├── services/            # API, Currency, Reports services
    │   ├── stores/              # Pinia auth store
    │   └── router/              # Vue Router config
    └── index.html

```

## ⚙️ Requirements

### Backend
- **PHP**: 8.3 or higher
- **Framework**: Laravel 13
- **Database**: MySQL/PostgreSQL

### Frontend
- **Node.js**: 18+
- **Vue**: 3.5.32
- **Vite**: 8.0.4
- **Package Manager**: npm

## 🚀 Installation & Setup

### Backend Setup

1. **Navigate to backend directory**:
   ```bash
   cd backend
   ```

2. **Configure environment**:
   ```bash
   cp .env.example .env
   update CURRENCY_API_KEY to .env
   update CURRENCY_API_DRY_RUN to .env [true/false]   
   php artisan key:generate
   ```

3. **Setup database**:
   ```bash
   php artisan migrate
   ```

### Frontend Setup

1. **Navigate to frontend directory**:
   ```bash
   cd frontend
   ```

2. **Install dependencies**:
   ```bash
   npm install
   ```

## 🔧 Running the Project

**Backend**:
```bash
cd backend
php artisan serve
```

**Queue Processing** (in another terminal):
```bash
cd backend
php artisan queue:work
```
**Schedule Processing** (in another terminal):
```bash
cd backend
php artisan schedule:run
```

**Frontend** (in another terminal):
```bash
cd frontend
npm run dev
```

**Access**: http://localhost:5173 (frontend) or http://localhost:8000 (backend)

## 🔐 Authentication

- **Method**: Laravel Sanctum (token-based)
- **Endpoints**:
  - `POST /api/register` - Register new user
  - `POST /api/login` - Login and receive access token
- **Protected Routes**: All routes requiring `auth:sanctum` middleware
- **Token Usage**: Send token in `Authorization: Bearer <token>` header

## 📡 API Endpoints

### Authentication
- `POST /api/register` - User registration
- `POST /api/login` - User login

### Currency (requires authentication)
- `GET /api/currencies` - List available currencies
- `POST /api/rates` - Get currency conversion rates

### Reports (requires authentication)
- `POST /api/reports` - Create new report
- `GET /api/reports` - List all reports
- `GET /api/reports/{report}` - Get specific report

## 📝 Key Features

- **User Authentication**: Register/Login with token-based auth
- **Currency Conversion**: Real-time currency rates
- **Report Management**: Create, view, and manage reports
- **Async Processing**: Background job queue for report generation
- **Modern UI**: Vue 3 with Vuetify components and Material Design icons
- **Charts**: Chart.js integration for data visualization

## 📌 Important Notes

- **Environment**: Copy `.env.example` to `.env` before running
- **API Base URL**: Frontend communicates with backend API at `/api`
- **CORS**: Ensure frontend and backend are on compatible URLs

![Demo Video](screen-capture-2026-04-09t170022731_2L35PhEd (1).mp4)
<video width="600" controls>
  <source src="vid.mp4" type="video/mp4">  
</video>