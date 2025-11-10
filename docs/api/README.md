# 🔌 API Documentation

This directory contains comprehensive API documentation for the Nexadon POS system.

## 📄 Available Documentation

### Authentication

- **[API_AUTHENTICATION.md](./API_AUTHENTICATION.md)**
  - Laravel Sanctum setup and configuration
  - SPA authentication flow
  - Token-based authentication
  - CORS configuration

- **[AUTHENTICATION.md](./AUTHENTICATION.md)**
  - Login endpoint
  - Logout endpoint
  - User profile endpoint
  - Request/response examples

### Resources

- **[MENU_ITEMS_API.md](./MENU_ITEMS_API.md)**
  - List menu items (with filters)
  - Create menu item
  - Update menu item
  - Delete menu item
  - Role-based access control

- **[ORDER_API.md](./ORDER_API.md)**
  - Create order
  - List active orders
  - Get order details
  - Update order
  - Order calculation logic

- **[PAYMENT_API.md](./PAYMENT_API.md)**
  - Process payment
  - Payment methods
  - Order completion flow
  - Table status updates

---

## 🌐 Base URL

```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

---

## 🔐 Authentication

All API endpoints (except login) require authentication using Laravel Sanctum.

### For SPA (Same Domain)

```javascript
// Login first to get session
await axios.post('/api/login', {
  email: 'user@example.com',
  password: 'password'
});

// Subsequent requests use session cookie
const response = await axios.get('/api/menu-items');
```

### For Mobile/External Apps

```javascript
// Get token from login
const { data } = await axios.post('/api/login', {
  email: 'user@example.com',
  password: 'password'
});

// Use token in headers
axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
```

---

## 📋 Quick Reference

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/login` | User login | ❌ |
| POST | `/api/logout` | User logout | ✅ |
| GET | `/api/user` | Get user profile | ✅ |

### Menu Items Endpoints

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| GET | `/api/menu-items` | List items | All |
| GET | `/api/menu-items/{id}` | Get item | All |
| POST | `/api/menu-items` | Create item | Admin, Manager |
| PUT | `/api/menu-items/{id}` | Update item | Admin, Manager |
| DELETE | `/api/menu-items/{id}` | Delete item | Admin, Manager |

### Order Endpoints

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| POST | `/api/orders` | Create order | All |
| GET | `/api/orders/active` | List active | All |
| GET | `/api/orders/{id}` | Get order | All |
| PUT | `/api/orders/{id}` | Update order | All |

### Payment Endpoints

| Method | Endpoint | Description | Role |
|--------|----------|-------------|------|
| POST | `/api/payments` | Process payment | Cashier, Admin, Manager |

### Other Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/categories` | List categories | ✅ |
| GET | `/api/modifiers` | List modifiers | ✅ |
| GET | `/api/tables` | List tables | ✅ |

---

## 🎯 Common Request Headers

```
Accept: application/json
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

For token-based auth:
```
Authorization: Bearer {token}
```

---

## 📊 Response Format

### Success Response

```json
{
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

### Error Response

```json
{
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

### Status Codes

| Code | Meaning | When Used |
|------|---------|-----------|
| 200 | OK | Successful GET/PUT/PATCH |
| 201 | Created | Successful POST |
| 204 | No Content | Successful DELETE |
| 400 | Bad Request | Invalid request data |
| 401 | Unauthorized | Not authenticated |
| 403 | Forbidden | No permission |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable | Validation failed |
| 500 | Server Error | Internal error |

---

## 🔒 Role-Based Access Control

### User Roles

1. **Admin** - Full system access
2. **Manager** - Manage menu, view reports
3. **Cashier** - Process payments
4. **Waiter** - Create orders, view menu

### Permission Matrix

| Action | Admin | Manager | Cashier | Waiter |
|--------|-------|---------|---------|--------|
| View menu items | ✅ | ✅ | ✅ | ✅ |
| Create menu items | ✅ | ✅ | ❌ | ❌ |
| Update menu items | ✅ | ✅ | ❌ | ❌ |
| Delete menu items | ✅ | ✅ | ❌ | ❌ |
| Create orders | ✅ | ✅ | ✅ | ✅ |
| View orders | ✅ | ✅ | ✅ | ✅ |
| Process payments | ✅ | ✅ | ✅ | ❌ |

---

## 🧪 Testing Endpoints

### Using cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password"}'

# Get menu items
curl -X GET http://localhost:8000/api/menu-items \
  -H "Accept: application/json" \
  --cookie "session_cookie"
```

### Using Postman

1. Import API collection
2. Set base URL: `http://localhost:8000/api`
3. Login to get session/token
4. Test other endpoints

---

## 📈 Rate Limiting

Default rate limits:
- **Authenticated requests:** 60 per minute
- **Login attempts:** 5 per minute

Rate limit headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## 🔄 API Versioning

Current version: **v1** (implicit)

Future versions will use URL prefix:
```
/api/v2/menu-items
```

---

## 🌍 CORS Configuration

Allowed origins:
- `http://localhost:5173` (Vite dev server)
- `http://localhost:3000` (Alternative dev)
- Your production domain

Allowed methods: GET, POST, PUT, PATCH, DELETE, OPTIONS

---

## 📚 Additional Resources

- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [RESTful API Design](https://restfulapi.net/)

---

**Last Updated:** November 1, 2025
