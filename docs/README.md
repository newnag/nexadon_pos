# 📚 Nexadon POS - Documentation

Welcome to the Nexadon POS documentation. This directory contains comprehensive documentation for the project.

## 📁 Directory Structure

```
docs/
├── README.md                          # This file
├── TASKS_COMPLETED.md                 # Project progress tracking
├── ROUTER_MIGRATION.md                # Vue Router → Inertia migration guide
├── ROUTER_MIGRATION_SUMMARY.md        # Migration summary
│
├── api/                               # API Documentation
│   ├── README.md                      # API overview & quick reference
│   ├── API_AUTHENTICATION.md          # Sanctum authentication setup
│   ├── AUTHENTICATION.md              # Authentication endpoints
│   ├── MENU_ITEMS_API.md              # Menu items CRUD API
│   ├── ORDER_API.md                   # Order management API
│   └── PAYMENT_API.md                 # Payment processing API
│
├── testing/                           # Testing Documentation
│   ├── README.md                      # Testing guide & coverage
│   ├── AUTHENTICATION_TEST.md         # Auth test documentation
│   ├── PERFORMANCE_COMPARISON.md      # Performance benchmarks
│   ├── AuthTest-README.md             # Auth test details
│   └── MenuItemTest-README.md         # Menu item test details
│
├── setup/                             # Setup & Configuration
│   ├── KDS_SETUP.md                   # Kitchen Display System setup
│   ├── VUE_SETUP.md                   # Vue.js frontend setup
│   └── SEEDERS_DOCUMENTATION.md       # Database seeders guide
│
└── tasks/                             # Task Documentation
    ├── TASK_11_ORDER_VIEW.md          # Order view implementation
    ├── TASK_11_TESTING.md             # Order view testing
    ├── TASK_12_BILLING_VIEW.md        # Billing view implementation
    ├── TASK_12_TESTING.md             # Billing view testing
    ├── TASK_13_KDS_VIEW.md            # KDS view implementation
    └── TASK_14_MENU_MANAGEMENT.md     # Menu management implementation
```

---

## 🚀 Quick Links

### 📖 Project Documentation
- [Tasks Completed](./TASKS_COMPLETED.md) - Full project progress and history
- [Router Migration Guide](./ROUTER_MIGRATION.md) - Vue Router → Inertia.js migration
- [Router Migration Summary](./ROUTER_MIGRATION_SUMMARY.md) - Quick migration overview

### For Backend Developers

**API Documentation:**
- [API Overview](./api/README.md) - Complete API reference
- [Authentication API](./api/AUTHENTICATION.md) - Login, logout, user profile
- [Menu Items API](./api/MENU_ITEMS_API.md) - CRUD operations for menu items
- [Order API](./api/ORDER_API.md) - Create and manage orders
- [Payment API](./api/PAYMENT_API.md) - Process payments

**Setup Guides:**
- [API Authentication Setup](./api/API_AUTHENTICATION.md) - Configure Sanctum
- [Database Seeders](./setup/SEEDERS_DOCUMENTATION.md) - Seed test data

### For Frontend Developers

**Setup Guides:**
- [Vue.js Setup](./setup/VUE_SETUP.md) - Frontend configuration
- [KDS Setup](./setup/KDS_SETUP.md) - Kitchen Display System
- [Router Migration](./ROUTER_MIGRATION.md) - Inertia.js routing guide

**Feature Implementation:**
- [Order View](./tasks/TASK_11_ORDER_VIEW.md) - Order management UI
- [Billing View](./tasks/TASK_12_BILLING_VIEW.md) - Billing/payment UI
- [KDS View](./tasks/TASK_13_KDS_VIEW.md) - Kitchen display UI
- [Menu Management](./tasks/TASK_14_MENU_MANAGEMENT.md) - Menu CRUD UI

### For QA/Testers

**Testing Documentation:**
- [Testing Guide](./testing/README.md) - Complete testing overview
- [Authentication Tests](./testing/AUTHENTICATION_TEST.md) - Auth test scenarios
- [Performance Comparison](./testing/PERFORMANCE_COMPARISON.md) - API benchmarks
- [Auth Test Details](./testing/AuthTest-README.md) - Comprehensive auth testing
- [Menu Item Tests](./testing/MenuItemTest-README.md) - Menu item test cases

---

## 📊 Testing

### Running Tests

```bash
# Run all tests (SQLite)
vendor/bin/pest

# Run tests with MySQL
vendor/bin/pest --configuration=phpunit.mysql.xml

# Run specific test file
vendor/bin/pest tests/Feature/AuthTest.php

# Run performance tests
vendor/bin/pest tests/Feature/ApiPerformanceTest.php
```

### Test Coverage

- ✅ Authentication (20 tests) - 100% passing
- ✅ Menu Items (26 tests) - 100% passing
- ✅ Orders (23 tests) - 100% passing
- ✅ Payments (14 tests) - 100% passing
- ✅ Broadcasting (13 tests) - 100% passing
- ✅ Performance (13 tests) - 100% passing

**Total: 109 tests, 503 assertions**

---

## 🔧 Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- Redis (optional, for caching)

### Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Configure environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Setup database:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. Start development servers:
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 📖 API Reference

### Authentication

- `POST /api/login` - User login
- `POST /api/logout` - User logout
- `GET /api/user` - Get authenticated user

### Menu Items

- `GET /api/menu-items` - List all menu items
- `POST /api/menu-items` - Create menu item (Admin/Manager)
- `GET /api/menu-items/{id}` - Get menu item details
- `PUT /api/menu-items/{id}` - Update menu item (Admin/Manager)
- `DELETE /api/menu-items/{id}` - Delete menu item (Admin/Manager)

### Orders

- `POST /api/orders` - Create new order
- `GET /api/orders/active` - Get active orders
- `GET /api/orders/{id}` - Get order details
- `PUT /api/orders/{id}` - Update order

### Payments

- `POST /api/payments` - Process payment

### Additional Endpoints

- `GET /api/categories` - List categories
- `GET /api/modifiers` - List modifiers
- `GET /api/tables` - List tables

---

## 📈 Performance Metrics

Based on performance testing with MySQL:

| Endpoint | Response Time | Status |
|----------|--------------|--------|
| GET /api/categories | 2.87ms | ✅ Excellent |
| GET /api/modifiers | 1.92ms | ✅ Excellent |
| GET /api/menu-items | 7.19ms | ✅ Excellent |
| GET /api/tables | 5.17ms | ✅ Excellent |
| GET /api/orders/active | 11.56ms | ✅ Excellent |

All endpoints respond within **25ms** with optimized indexes.

See [Performance Comparison](./testing/PERFORMANCE_COMPARISON.md) for detailed analysis.

---

## 🗄️ Database

### Tables

- `users` - User accounts
- `roles` - User roles (Admin, Manager, Cashier, Waiter)
- `tables` - Restaurant tables
- `categories` - Menu categories
- `menu_items` - Menu items
- `modifiers` - Item modifiers (add-ons)
- `orders` - Customer orders
- `order_items` - Order line items
- `payments` - Payment records

### Indexes (Optimized)

29 indexes have been added across 8 tables for optimal query performance.

See [Performance Comparison](./testing/PERFORMANCE_COMPARISON.md) for index details.

---

## 🎯 Project Status

Track overall project progress in [TASKS_COMPLETED.md](./TASKS_COMPLETED.md)

### Completed Features

- ✅ Authentication & Authorization
- ✅ Menu Management API
- ✅ Order Management API
- ✅ Payment Processing API
- ✅ Real-time Broadcasting
- ✅ Database Optimization
- ✅ Comprehensive Testing
- ✅ Performance Benchmarking

### In Progress

- 🚧 Frontend UI Components
- 🚧 Real-time Updates
- 🚧 Reporting Dashboard

---

## 🤝 Contributing

When adding new documentation:

1. Place API docs in `docs/api/`
2. Place testing docs in `docs/testing/`
3. Place setup guides in `docs/setup/`
4. Place task/feature docs in `docs/tasks/`
5. Update this README with new links

---

## 📝 License

This project is proprietary software. All rights reserved.

---

**Last Updated:** November 1, 2025
