# E-commerce Order Management System

## Overview

This is an e-commerce order management system built with Laravel and React. The system allows management of products, orders, and users with different roles (admin and supplier).

## Features

- **User Management**
  - Role-based authentication (Admin/Supplier)
  - JWT-based authentication
  - User registration, login & logout

- **Product Management**
  - Create, read, update and delete products
  - Product attributes include:
    - Name
    - Description
    - Price
    - SKU
    - Stock level
    - Status (active/inactive)

- **Order Management**
  - Order creation and tracking
  - Multiple order statuses (pending, processing, shipped, delivered)
  - Order items with quantity and price
  - Delivery time tracking

## Tech Stack

- **Backend**
  - PHP 8.2
  - Laravel 11
  - MySQL Database
  - JWT Authentication
  - Laravel Sanctum

- **Frontend**
  - React 19
  - React Router DOM 7
  - Tailwind CSS

## Prerequisites

- Docker (local devleopment)
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

## Installation 

### 1. Clone the repository
```bash
git clone [repository-url]
```

### 2. Local env init
```bash
cp .env.example .env
docker compose up -d
```

### 3. Install apps
```bash
# Laravel API app
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate

# React web app
docker compose exec app npm install
docker compose exec app npm run dev # For local development with hot reload
docker compose exec app npm run build # For production build
```

### 4. Verify and happy coding
- Visit app: http://localhost:8020
- Local DB: port `3020`, user `root` with password `123456`

