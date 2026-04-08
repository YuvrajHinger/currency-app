# Currency Conversion App

## Setup

### Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve

### Frontend
cd frontend
npm install
npm run dev