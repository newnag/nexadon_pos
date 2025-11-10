# Task 9: Authentication Pages Documentation

## Overview

Complete authentication system with login, logout, protected routes, and role-based access control.

---

## Components Created

### 1. **AuthLayout.vue**
**Location**: `resources/js/layouts/AuthLayout.vue`

**Purpose**: Layout wrapper for authentication pages (login, register, forgot password)

**Features**:
- Centered card design with gradient background
- Logo and branding
- Responsive layout
- Footer with copyright

**Usage**:
```vue
<template>
  <AuthLayout>
    <h2>Your Auth Form Here</h2>
    <!-- Form content -->
  </AuthLayout>
</template>
```

---

### 2. **DefaultLayout.vue**
**Location**: `resources/js/layouts/DefaultLayout.vue`

**Purpose**: Main layout for authenticated pages with sidebar navigation

**Features**:
- ✅ Fixed sidebar with navigation links
- ✅ Role-based menu visibility
- ✅ User profile section at bottom
- ✅ Logout button with confirmation
- ✅ Mobile responsive with hamburger menu
- ✅ Page title in top bar
- ✅ Real-time date/time display
- ✅ Active route highlighting
- ✅ SVG icons for each menu item

**Navigation Items**:
- **Dashboard** - All users
- **Menu Management** - Admin, Manager only
- **Order Taking** - All users
- **Tables** - All users
- **Billing** - Admin, Manager, Cashier only
- **Kitchen Display** - Admin, Manager only
- **Reports** - Admin, Manager only

**Usage**:
```vue
<template>
  <DefaultLayout>
    <div class="py-6">
      <!-- Your page content -->
    </div>
  </DefaultLayout>
</template>
```

---

### 3. **Login.vue**
**Location**: `resources/js/pages/Auth/Login.vue`

**Purpose**: User authentication form

**Features**:
- ✅ Email and password inputs with validation
- ✅ Loading state during submission
- ✅ Error message display
- ✅ Token/session storage via auth store
- ✅ Redirect to dashboard on success
- ✅ Test credentials displayed for development
- ✅ Form validation (required fields, email format)

**Form Fields**:
- Email (required, email format)
- Password (required, min 6 characters)

**Test Credentials**:
```
Admin:   admin@example.com / password
Manager: manager@example.com / password
Cashier: cashier@example.com / password
Waiter:  waiter@example.com / password
```

**Login Flow**:
1. User enters credentials
2. Click "Sign in" button
3. Form validates inputs
4. API call to `/api/login` endpoint
5. Store user data and token in auth store
6. Redirect to `/dashboard`

---

## Authentication System

### Navigation Guards

**Location**: `resources/js/router/index.ts`

**Implemented Guards**:

1. **Authentication Check**
   - Redirects to login if user not authenticated
   - Applies to all routes with `meta.auth = true`

2. **Guest-Only Routes**
   - Redirects authenticated users away from login
   - Applies to routes with `meta.guest = true`

3. **Role-Based Access Control**
   - Checks if user has required role
   - Redirects to dashboard if permission denied
   - Uses `meta.roles` array

**Example Route Configuration**:
```typescript
{
  path: '/menu',
  name: 'menu',
  component: MenuManagement,
  meta: {
    auth: true,
    roles: ['Admin', 'Manager']
  }
}
```

### Auth Store

**Location**: `resources/js/stores/auth.ts`

**State**:
- `user`: User object with id, name, email, role
- `loading`: Loading state for async operations
- `error`: Error message string

**Computed Properties**:
- `isAuthenticated`: Boolean, checks if user exists
- `userRole`: Current user's role name
- `isAdmin`: Boolean, checks if role is Admin
- `isManager`: Boolean, checks if role is Manager
- `isCashier`: Boolean, checks if role is Cashier
- `isWaiter`: Boolean, checks if role is Waiter

**Methods**:
- `login(email, password)`: Authenticate user
- `logout()`: Clear user session and redirect
- `fetchUser()`: Get current authenticated user
- `hasRole(roles)`: Check if user has specific role(s)

**Usage Example**:
```typescript
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Check authentication
if (authStore.isAuthenticated) {
  console.log('User is logged in');
}

// Check role
if (authStore.hasRole(['Admin', 'Manager'])) {
  // Show admin/manager features
}

// Login
const result = await authStore.login('admin@example.com', 'password');
if (result.success) {
  router.push('/dashboard');
}

// Logout
await authStore.logout();
```

---

## Route Protection

### Protected Routes

All routes except login are protected by default:

```typescript
meta: {
  auth: true  // Requires authentication
}
```

### Role-Specific Routes

Routes that require specific roles:

```typescript
// Admin & Manager only
{
  path: '/menu',
  meta: {
    auth: true,
    roles: ['Admin', 'Manager']
  }
}

// Admin, Manager, Cashier only
{
  path: '/billing',
  meta: {
    auth: true,
    roles: ['Admin', 'Manager', 'Cashier']
  }
}
```

### Guest-Only Routes

Routes accessible only when not authenticated:

```typescript
{
  path: '/login',
  meta: {
    guest: true  // Redirects authenticated users
  }
}
```

---

## User Roles & Permissions

### Role Hierarchy

1. **Admin** - Full system access
   - All menu items visible
   - Can manage menu, orders, billing, kitchen, reports
   - Full CRUD permissions

2. **Manager** - Management access
   - Can manage menu items
   - View kitchen display
   - Access reports
   - Cannot modify system settings

3. **Cashier** - POS operations
   - Take orders
   - Process payments
   - View billing
   - Cannot access menu management or reports

4. **Waiter** - Service operations
   - Take orders
   - View tables
   - Cannot access billing or kitchen

---

## API Integration

### Authentication Endpoints

**Base URL**: `http://localhost:8000/api`

1. **Login**
   - Endpoint: `POST /login`
   - Body: `{ email, password }`
   - Response: `{ user: {...}, token: "..." }`

2. **Logout**
   - Endpoint: `POST /logout`
   - Headers: `Authorization: Bearer {token}`
   - Response: `{ message: "Logged out" }`

3. **Get User**
   - Endpoint: `GET /user`
   - Headers: `Authorization: Bearer {token}`
   - Response: `{ user: {...} }`

### Token Storage

Tokens are stored in:
- **localStorage**: For persistent sessions
- **Axios headers**: Automatically attached to requests
- **Auth store**: For reactive state management

---

## Security Features

✅ **CSRF Protection**
- CSRF token included in all requests
- Token fetched from `/sanctum/csrf-cookie`

✅ **Session Management**
- Laravel Sanctum for stateful authentication
- Secure session cookies
- Token expiration handling

✅ **Route Guards**
- Authentication checks on every route
- Role-based access control
- Automatic redirect to login

✅ **Error Handling**
- 401: Redirect to login
- 403: Show permission denied
- 422: Display validation errors
- Network errors: User-friendly messages

---

## Testing Authentication

### Manual Testing Steps

1. **Login Flow**
   ```
   1. Visit http://localhost:8000/login
   2. Enter: admin@example.com / password
   3. Click "Sign in"
   4. Should redirect to /dashboard
   5. Check user name in sidebar
   ```

2. **Logout Flow**
   ```
   1. Click "Logout" button in sidebar
   2. Confirm logout
   3. Should redirect to /login
   4. Try accessing /dashboard - should redirect to login
   ```

3. **Protected Routes**
   ```
   1. Logout
   2. Try to access /dashboard
   3. Should redirect to /login
   4. Login as Waiter
   5. Try to access /menu (Admin/Manager only)
   6. Should redirect to /dashboard
   ```

4. **Role Permissions**
   ```
   Admin:   See all menu items
   Manager: See all except system settings
   Cashier: See Dashboard, Orders, Tables, Billing
   Waiter:  See Dashboard, Orders, Tables
   ```

### Automated Testing (Future)

```typescript
// tests/Feature/AuthenticationTest.php
test('user can login with valid credentials')
test('user cannot login with invalid credentials')
test('authenticated user can access dashboard')
test('guest cannot access protected routes')
test('user without permission cannot access restricted routes')
```

---

## Troubleshooting

### Issue: Login redirects to login page

**Solution**:
- Check if API returns correct user object
- Verify token is stored in localStorage
- Check browser console for errors
- Ensure CSRF token is fetched

### Issue: Protected routes accessible without login

**Solution**:
- Check router guards are implemented
- Verify `meta.auth = true` on routes
- Check auth store `isAuthenticated` computed

### Issue: User sees menu items they shouldn't

**Solution**:
- Check role-based `v-if` directives
- Verify `hasRole()` method in auth store
- Check user role is correctly set

### Issue: Logout doesn't work

**Solution**:
- Check `/api/logout` endpoint
- Verify token is sent in headers
- Clear localStorage manually if needed
- Check network tab for API errors

---

## File Structure

```
resources/js/
├── layouts/
│   ├── AuthLayout.vue         (Login page layout)
│   ├── DefaultLayout.vue      (Authenticated pages layout)
│   ├── AuthenticatedLayout.vue (Alternative layout)
│   └── GuestLayout.vue        (Alternative guest layout)
├── pages/
│   └── Auth/
│       └── Login.vue          (Login form)
├── stores/
│   └── auth.ts                (Authentication store)
├── router/
│   └── index.ts               (Router with guards)
└── lib/
    └── api.ts                 (Axios instance with auth)
```

---

## Next Steps

1. **Add Registration Page** (if needed)
   - Create Register.vue component
   - Add route for `/register`
   - Implement registration API

2. **Add Forgot Password** (if needed)
   - Create ForgotPassword.vue
   - Create ResetPassword.vue
   - Add password reset API endpoints

3. **Add Remember Me**
   - Add checkbox to login form
   - Store token with longer expiration
   - Update login API to accept remember flag

4. **Add Email Verification**
   - Send verification email on registration
   - Add verify email route
   - Block unverified users from certain features

5. **Add Two-Factor Authentication**
   - QR code generation
   - TOTP verification
   - Backup codes

---

## Summary

✅ **AuthLayout.vue** - Beautiful login page layout  
✅ **DefaultLayout.vue** - Feature-rich sidebar layout  
✅ **Login.vue** - Secure login form with validation  
✅ **Navigation Guards** - Route protection implemented  
✅ **Auth Store** - Complete authentication state management  
✅ **Role-Based Access** - Granular permission control  
✅ **Token Management** - Secure token storage and handling  
✅ **Error Handling** - User-friendly error messages  
✅ **Mobile Responsive** - Works on all devices  

**Task 9: COMPLETED** ✅
