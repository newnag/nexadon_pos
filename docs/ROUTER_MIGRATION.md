# Router Migration: Vue Router → Inertia.js

**Date:** November 1, 2025  
**Status:** Partially Complete  
**Migration Type:** Vue Router to Inertia.js Router

---

## 📋 Overview

This project uses **Inertia.js** as the primary routing mechanism (SSR-friendly), not Vue Router. This document tracks the migration of components that were incorrectly using Vue Router.

---

## ✅ Completed Migrations

### Layouts

#### 1. `AuthenticatedLayout.vue`
- ✅ Changed `useRouter()`, `useRoute()` → `router`, `usePage()`
- ✅ Changed `<router-link>` → `<Link>`
- ✅ Changed `router.push()` → `router.visit()`
- ✅ Changed `route.path` → `page.url`

**Before:**
```typescript
import { useRouter, useRoute } from 'vue-router';
const router = useRouter();
const route = useRoute();
```

**After:**
```typescript
import { Link, router, usePage } from '@inertiajs/vue3';
const page = usePage();
const currentPath = computed(() => page.url);
```

#### 2. `DefaultLayout.vue`
- ✅ Changed `useRouter()`, `useRoute()` → `router`, `usePage()`
- ✅ Changed all `<router-link>` → `<Link>`
- ✅ Changed `router.push()` → `router.visit()`
- ✅ Changed `route.path` → `page.url`

---

### Pages

#### 3. `Auth/Login.vue`
- ✅ Changed `useRouter()` → `router` from Inertia
- ✅ Changed `router.push('/dashboard')` → `router.visit('/dashboard')`
- ✅ Fixed test credentials email to `@nexadon.com`

#### 4. `Dashboard.vue`
- ✅ Added `Link` import from Inertia
- ✅ Changed all 4 `<router-link>` → `<Link>`
- ✅ Removed Vue Router import (was not used in script)

#### 5. `NotFound.vue`
- ✅ Added `Link` import from Inertia
- ✅ Changed `<router-link>` → `<Link>`

#### 6. `TableMapView.vue`
- ✅ Changed `useRouter()` → `router` from Inertia
- ✅ Changed `router.push({ path, query })` → `router.visit(url?query=params)`
- ✅ Simplified query string handling

**Before:**
```typescript
router.push({
    path: '/orders',
    query: { table_id: table.id },
});
```

**After:**
```typescript
router.visit(`/orders?table_id=${table.id}`);
```

---

## ⚠️ Files with Vue Router (Commented Out)

These files have Vue Router imports/usage but are **already commented out**. No action needed unless you want to remove the comments entirely:

### 7. `TableView.vue`
- ⚠️ Has commented Vue Router code (lines 42, 51, 72)
- Status: **Already disabled** - safe to leave as-is or remove comments

### 8. `OrderTaking.vue`
- ⚠️ Has commented Vue Router code (lines 136, 163, 235)
- Status: **Already disabled** - safe to leave as-is or remove comments

---

## ✅ Recently Migrated Files

### 9. `OrderView.vue` ✅ COMPLETED
**Location:** `resources/js/pages/OrderView.vue`

**Changes Made:**
```typescript
// Before
import { useRoute, useRouter } from 'vue-router';
const route = useRoute();
const router = useRouter();
const tableId = route.query.table_id;
router.push('/tables');

// After
import { router, usePage } from '@inertiajs/vue3';
const page = usePage();
const getQueryParam = (name: string) => new URLSearchParams(window.location.search).get(name);
const tableId = getQueryParam('table_id');
router.visit('/tables');
```

**Key Updates:**
- ✅ Changed imports to Inertia.js
- ✅ Added `getQueryParam()` helper for URL query strings
- ✅ Replaced all `route.query.*` with `getQueryParam()`
- ✅ Changed `router.push()` → `router.visit()`
- ✅ Updated watch to monitor `page.url` instead of `route.query`

---

### 10. `BillingView.vue` ✅ COMPLETED
**Location:** `resources/js/pages/BillingView.vue`

**Changes Made:**
```typescript
// Before
import { useRoute, useRouter } from 'vue-router';
const route = useRoute();
const router = useRouter();
const orderId = route.params.orderId;
router.push('/tables');
router.back();

// After
import { router, usePage } from '@inertiajs/vue3';
const page = usePage();
const getOrderIdFromUrl = () => window.location.pathname.split('/').pop();
const orderId = getOrderIdFromUrl();
router.visit('/tables');
returnToTables(); // Custom function
```

**Key Updates:**
- ✅ Changed imports to Inertia.js
- ✅ Added `getOrderIdFromUrl()` helper for route params
- ✅ Replaced `route.params.*` with URL parsing
- ✅ Changed `router.push()` → `router.visit()`
- ✅ Changed `router.back()` → `returnToTables()` function

---

## 📦 Vue Router Configuration

### router/index.ts
**Status:** Not used by Inertia pages

**Current State:**
```typescript
// This file exists but is commented out in app.ts
// Can be deleted or kept for potential standalone pages
```

**Recommendation:**
- **Option 1:** Delete `router/index.ts` if not needed
- **Option 2:** Keep for potential non-Inertia pages (e.g., public marketing pages)

---

## 🔧 stores/
**Status:** ✅ No Vue Router usage detected

All Pinia stores are clean and don't use Vue Router.

---

## 📊 Migration Summary

| Category | Total | Completed | Remaining |
|----------|-------|-----------|-----------|
| **Layouts** | 2 | 2 ✅ | 0 |
| **Pages (Active)** | 7 | 7 ✅ | 0 |
| **Pages (Commented)** | 2 | N/A | 0 (safe to ignore) |
| **Stores** | All | N/A | 0 (never used Vue Router) |
| **Config Files** | 1 | 0 | 1 (optional cleanup) |

**Overall Progress:** 7/7 active files migrated (100%) ✅

---

## 🎯 Next Actions

### ✅ All Critical Migrations Complete!

All active pages and layouts now use Inertia.js routing correctly.

### Optional (Low Priority) - Cleanup:
1. Remove commented Vue Router code from `TableView.vue` (lines 42, 51, 72)
2. Remove commented Vue Router code from `OrderTaking.vue` (lines 136, 163, 235)
3. Delete or archive `router/index.ts` (not used)
4. Remove `vue-router` from `package.json` dependencies (optional)
5. Remove Vue Router import comment from `app.ts` (line 8)

---

## 🔄 Migration Pattern Reference

### Pattern 1: Simple Link
```vue
<!-- Before -->
<router-link to="/dashboard">Dashboard</router-link>

<!-- After -->
<Link href="/dashboard">Dashboard</Link>
```

### Pattern 2: Dynamic Link with Props
```vue
<!-- Before -->
<router-link :to="`/orders/${order.id}`">View Order</router-link>

<!-- After -->
<Link :href="`/orders/${order.id}`">View Order</Link>
```

### Pattern 3: Programmatic Navigation
```typescript
// Before
router.push('/dashboard');
router.push({ path: '/orders', query: { id: 123 } });

// After
router.visit('/dashboard');
router.visit('/orders?id=123');
```

### Pattern 4: Current Route/Path
```typescript
// Before
import { useRoute } from 'vue-router';
const route = useRoute();
const currentPath = route.path;
const routeParams = route.params;

// After
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const currentPath = computed(() => page.url);
const routeProps = computed(() => page.props);
```

### Pattern 5: Route Guards (if needed)
Inertia doesn't have route guards. Use middleware on Laravel backend instead:
```php
// routes/web.php
Route::get('/admin', function () {
    return Inertia::render('Admin/Dashboard');
})->middleware(['auth', 'role:admin']);
```

---

## 📚 Resources

- [Inertia.js Routing Docs](https://inertiajs.com/routing)
- [Inertia.js Links](https://inertiajs.com/links)
- [Inertia.js Manual Visits](https://inertiajs.com/manual-visits)

---

## ✅ Testing Checklist

After completing migrations, test:
- [ ] Login flow works (`/login` → `/dashboard`)
- [ ] All navigation links work
- [ ] Table selection navigates correctly
- [ ] Order creation/update flow works
- [ ] Billing/payment flow works
- [ ] Back/forward browser buttons work
- [ ] Direct URL access works (e.g., `/orders/123`)

---

## 🎉 Migration Complete!

All 7 active files have been successfully migrated from Vue Router to Inertia.js:

1. ✅ AuthenticatedLayout.vue
2. ✅ DefaultLayout.vue
3. ✅ Auth/Login.vue
4. ✅ Dashboard.vue
5. ✅ NotFound.vue
6. ✅ TableMapView.vue
7. ✅ OrderView.vue
8. ✅ BillingView.vue

The application now fully uses Inertia.js for routing, which provides better SSR support and seamless integration with Laravel.

---

**Last Updated:** November 1, 2025  
**Status:** ✅ COMPLETE  
**Updated By:** GitHub Copilot
