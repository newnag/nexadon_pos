# Authentication System - Quick Test Guide

## Prerequisites

1. **Start Backend Server**
   ```bash
   php artisan serve
   ```

2. **Start Frontend Dev Server**
   ```bash
   npm run dev
   ```

3. **Seed Database** (if not done)
   ```bash
   php artisan db:seed
   ```

---

## Test Scenarios

### ✅ Test 1: Login Flow

1. Open browser: `http://localhost:8000/login`
2. Enter credentials:
   - Email: `admin@example.com`
   - Password: `password`
3. Click "Sign in"
4. **Expected**: Redirect to `/dashboard`
5. **Expected**: See sidebar with navigation
6. **Expected**: See "Admin" role in sidebar

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 2: Dashboard Access

1. After login, you should see:
   - Nexadon POS logo in sidebar
   - User name and role at bottom of sidebar
   - Navigation menu items based on role
   - Top bar with page title and datetime
   - 4 stat cards (Revenue, Orders, Tables, Menu Items)
   - Quick action buttons

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 3: Role-Based Menu Visibility

**Login as Admin** (`admin@example.com`)
- ✅ Should see: Dashboard, Menu, Orders, Tables, Billing, Kitchen, Reports

**Login as Manager** (`manager@example.com`)
- ✅ Should see: Dashboard, Menu, Orders, Tables, Kitchen, Reports

**Login as Cashier** (`cashier@example.com`)
- ✅ Should see: Dashboard, Orders, Tables, Billing

**Login as Waiter** (`waiter@example.com`)
- ✅ Should see: Dashboard, Orders, Tables

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 4: Protected Routes

1. Logout (click Logout button in sidebar)
2. Try to access: `http://localhost:8000/dashboard`
3. **Expected**: Redirect to `/login`
4. Try to access: `http://localhost:8000/menu`
5. **Expected**: Redirect to `/login`

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 5: Role Permissions

1. Login as **Waiter** (`waiter@example.com / password`)
2. Try to access: `http://localhost:8000/menu`
3. **Expected**: Redirect to `/dashboard` (no permission)
4. **Expected**: Menu link NOT visible in sidebar

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 6: Logout Flow

1. Login with any account
2. Click "Logout" button in sidebar
3. Confirm logout
4. **Expected**: Redirect to `/login`
5. **Expected**: User data cleared
6. Try accessing `/dashboard`
7. **Expected**: Redirect back to `/login`

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 7: Mobile Responsive

1. Resize browser to mobile size (< 768px)
2. **Expected**: See hamburger menu button
3. Click hamburger button
4. **Expected**: Sidebar slides in from left
5. Click outside sidebar
6. **Expected**: Sidebar closes

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 8: Invalid Login

1. Go to login page
2. Enter invalid credentials:
   - Email: `wrong@example.com`
   - Password: `wrongpassword`
3. Click "Sign in"
4. **Expected**: Error message displayed
5. **Expected**: Stay on login page

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 9: Navigation Between Pages

1. Login as Admin
2. Click "Menu" in sidebar
3. **Expected**: Navigate to `/menu`
4. **Expected**: Page title changes to "Menu Management"
5. **Expected**: "Menu" link highlighted in sidebar
6. Click "Dashboard"
7. **Expected**: Navigate back to `/dashboard`

**Status**: ⬜ Pass | ⬜ Fail

---

### ✅ Test 10: Sidebar User Info

1. Login with any account
2. Check bottom of sidebar
3. **Expected**: See user avatar with first letter
4. **Expected**: See full user name
5. **Expected**: See user role
6. **Expected**: See red "Logout" button

**Status**: ⬜ Pass | ⬜ Fail

---

## Common Issues & Solutions

### Issue: Login doesn't redirect

**Check**:
```bash
# Terminal 1
php artisan serve

# Terminal 2  
npm run dev
```

**Solution**: Make sure both servers are running

---

### Issue: 419 CSRF token mismatch

**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
```

---

### Issue: User data not showing

**Solution**:
1. Open browser DevTools (F12)
2. Check Console for errors
3. Check Network tab for failed API calls
4. Verify database has seeded users

---

### Issue: Sidebar not showing

**Solution**:
1. Check browser console for JavaScript errors
2. Verify Tailwind CSS is loaded
3. Try hard refresh (Ctrl+Shift+R)

---

## Test Results Summary

| Test | Status | Notes |
|------|--------|-------|
| Login Flow | ⬜ | |
| Dashboard Access | ⬜ | |
| Role-Based Menu | ⬜ | |
| Protected Routes | ⬜ | |
| Role Permissions | ⬜ | |
| Logout Flow | ⬜ | |
| Mobile Responsive | ⬜ | |
| Invalid Login | ⬜ | |
| Navigation | ⬜ | |
| Sidebar User Info | ⬜ | |

---

## Test Accounts

```
Admin:
  Email: admin@example.com
  Password: password
  Access: Full system access

Manager:
  Email: manager@example.com
  Password: password
  Access: Menu, Orders, Kitchen, Reports

Cashier:
  Email: cashier@example.com
  Password: password
  Access: Orders, Billing, Tables

Waiter:
  Email: waiter@example.com
  Password: password
  Access: Orders, Tables
```

---

## Next Steps After Testing

1. ✅ All tests pass → Proceed to Task 10
2. ⚠️ Some tests fail → Debug and fix issues
3. 🔍 Need help → Check AUTHENTICATION.md for detailed docs
