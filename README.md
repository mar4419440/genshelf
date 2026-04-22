# GenShelf POS & Inventory Management System

GenShelf is a robust Retail POS and Inventory Management system built with Laravel. It features a modern design, real-time stock tracking, multi-storage support, and detailed BI reporting.

## Key Features
- **Point of Sale (POS)**: Fast checkout, barcode support, customer loyalty points, and credit sales.
- **Inventory Management**: FIFO stock deduction, automated low-stock alerts, and expiration tracking.
- **Finance**: Expense tracking, cash drawer management, and automated profit/loss calculation.
- **Multi-Storage**: Manage stock across different physical locations or POS stations.
- **BI Reports**: Sales analytics, top-selling products, and debt tracking.
- **Dynamic Offers**: Create fixed, percentage, or BOGO offers with date-based activation.

## Setup Instructions
1. **Clone the repository**
2. **Install dependencies**: `composer install` & `npm install`
3. **Environment Setup**: Copy `.env.example` to `.env` and configure your database.
4. **Generate App Key**: `php artisan key:generate`
5. **Run Migrations**: `php artisan migrate`
6. **Serve Application**: `php artisan serve`

## Default Credentials
- **Admin**: admin@example.com / password (setup via seeder or manual entry)

## Tech Stack
- **Backend**: Laravel 11.x
- **Frontend**: Blade, Vanilla CSS, JavaScript
- **Database**: MySQL / MariaDB
