# API Authentication Setup

This POS system uses Laravel Sanctum for SPA (Single Page Application) authentication.

## Configuration

### Environment Variables
Make sure these are set in your `.env` file:

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173,127.0.0.1:3000,127.0.0.1:5173
```

### CSRF Protection
For SPA authentication, your frontend needs to first make a request to `/sanctum/csrf-cookie` to get the CSRF token before making login requests.

## API Endpoints

### Public Endpoints (No Authentication Required)

#### Login
```
POST /api/login
Content-Type: application/json

Body:
{
  "email": "user@example.com",
  "password": "password",
  "remember": false  // optional
}

Response (200):
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role_id": 1,
    "role": {
      "id": 1,
      "name": "Admin"
    }
  }
}
```

### Protected Endpoints (Authentication Required)

All protected endpoints require the user to be authenticated. Include credentials (cookies) with your requests.

#### Get Current User
```
GET /api/user

Response (200):
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role_id": 1,
    "role": {
      "id": 1,
      "name": "Admin"
    }
  }
}
```

#### Logout
```
POST /api/logout

Response (200):
{
  "message": "Logout successful"
}
```

## Frontend Implementation (React/Vue Example)

### Setup Axios
```javascript
import axios from 'axios';

// Configure axios
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:8000';
```

### Login Flow
```javascript
// Step 1: Get CSRF cookie
await axios.get('/sanctum/csrf-cookie');

// Step 2: Login
const response = await axios.post('/api/login', {
  email: 'user@example.com',
  password: 'password',
  remember: true
});

console.log(response.data.user);
```

### Making Authenticated Requests
```javascript
// After login, all requests will include the session cookie automatically
const user = await axios.get('/api/user');
console.log(user.data);

// Logout
await axios.post('/api/logout');
```

## Adding More Protected Routes

To add more protected API routes, edit `routes/api.php` and add them inside the `auth:sanctum` middleware group:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // Add your routes here
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('menu-items', MenuItemController::class);
    Route::apiResource('orders', OrderController::class);
});
```

## Testing Authentication

You can test the authentication using tools like Postman or curl:

```bash
# Get CSRF cookie
curl -X GET http://localhost:8000/sanctum/csrf-cookie \
  -c cookies.txt

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -c cookies.txt \
  -d '{"email":"user@example.com","password":"password"}'

# Get user profile
curl -X GET http://localhost:8000/api/user \
  -b cookies.txt

# Logout
curl -X POST http://localhost:8000/api/logout \
  -b cookies.txt
```

## Security Notes

1. **CSRF Protection**: Sanctum uses Laravel's CSRF protection for SPA authentication
2. **Session-based**: Authentication is session-based, not token-based
3. **Stateful Domains**: Only domains listed in `SANCTUM_STATEFUL_DOMAINS` can authenticate
4. **HTTPS in Production**: Always use HTTPS in production environments
5. **SameSite Cookies**: Sessions use `lax` SameSite setting by default
