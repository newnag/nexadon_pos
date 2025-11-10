# Vue 3 Project Setup Documentation

## Overview

This project uses Vue 3 with Vite, integrated alongside Laravel Inertia.js for a modern, reactive POS system. The setup includes Vue Router for routing, Pinia for state management, Axios for HTTP requests, and Tailwind CSS for styling.

## Technology Stack

- **Vue 3.5** - Progressive JavaScript framework
- **Vite 7** - Next-generation frontend tooling
- **TypeScript 5** - Type-safe development
- **Vue Router 4** - Official routing library for Vue.js
- **Pinia** - Official state management for Vue
- **Axios** - Promise-based HTTP client
- **Tailwind CSS 4** - Utility-first CSS framework
- **Inertia.js** - Modern monolith framework (already configured)

## Installation

### Prerequisites

- Node.js 18+ 
- npm or yarn
- Laravel 11+

### Install Dependencies

```bash
# Install required packages
npm install vue-router@4 pinia axios

# Or using yarn
yarn add vue-router@4 pinia axios
```

### Verify Installation

After installation, your `package.json` should include:

```json
{
  "dependencies": {
    "vue": "^3.5.13",
    "vue-router": "^4.x.x",
    "pinia": "^2.x.x",
    "axios": "^1.x.x",
    "tailwindcss": "^4.1.1",
    "@inertiajs/vue3": "^2.0.0"
  }
}
```

---

## Project Structure

```
resources/
├── js/
│   ├── app.ts                     # Main entry point (Inertia.js)
│   ├── router/
│   │   └── index.ts              # Vue Router configuration
│   ├── stores/
│   │   ├── auth.ts               # Authentication store
│   │   ├── cart.ts               # Shopping cart store
│   │   └── orders.ts             # Orders store
│   ├── lib/
│   │   └── api.ts                # Axios configuration & API helpers
│   ├── layouts/
│   │   ├── AuthenticatedLayout.vue  # Layout for authenticated users
│   │   └── GuestLayout.vue          # Layout for guest users
│   ├── pages/
│   │   ├── Auth/
│   │   │   └── Login.vue         # Login page
│   │   ├── Dashboard.vue         # Dashboard page
│   │   ├── MenuManagement.vue    # Menu management (to be created)
│   │   ├── OrderTaking.vue       # Order taking (to be created)
│   │   ├── TableView.vue         # Table view (to be created)
│   │   ├── Billing.vue           # Billing page (to be created)
│   │   ├── KitchenDisplay.vue    # Kitchen display (to be created)
│   │   ├── Reports.vue           # Reports (to be created)
│   │   └── NotFound.vue          # 404 page
│   └── types/
│       └── index.ts              # TypeScript type definitions
└── css/
    └── app.css                   # Tailwind CSS imports
```

---

## Configuration

### 1. Vue Router

**Location:** `resources/js/router/index.ts`

Configured with:
- History mode for clean URLs
- Route-level code splitting (lazy loading)
- Navigation guards for authentication
- Role-based access control

**Routes:**
- `/login` - Login page (guest only)
- `/dashboard` - Dashboard (authenticated)
- `/menu` - Menu management (Admin/Manager)
- `/orders` - Order taking (all authenticated users)
- `/tables` - Table view (all authenticated users)
- `/billing/:orderId` - Billing page (Admin/Manager/Cashier)
- `/kitchen` - Kitchen display (Admin/Manager)
- `/reports` - Reports (Admin/Manager)

**Navigation Guards:**
- Check authentication status
- Verify user roles
- Redirect to login if not authenticated
- Redirect to dashboard if guest accessing protected route

### 2. Pinia State Management

**Stores Created:**

#### Auth Store (`stores/auth.ts`)
Manages user authentication state and operations.

**State:**
- `user` - Current user object
- `loading` - Loading state
- `error` - Error messages

**Getters:**
- `isAuthenticated` - Check if user is logged in
- `userRole` - Get current user's role
- `isAdmin`, `isManager`, `isCashier`, `isWaiter` - Role checks

**Actions:**
- `login(email, password)` - User login
- `logout()` - User logout
- `fetchUser()` - Fetch current user data
- `initialize()` - Initialize auth state
- `hasRole(roles)` - Check if user has specific role(s)

**Usage:**
```vue
<script setup>
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();

// Check if authenticated
if (authStore.isAuthenticated) {
  console.log('User:', authStore.user?.name);
}

// Check role
if (authStore.hasRole(['Admin', 'Manager'])) {
  // Show admin features
}

// Login
await authStore.login('admin@example.com', 'password');

// Logout
await authStore.logout();
</script>
```

#### Cart Store (`stores/cart.ts`)
Manages shopping cart for order taking.

**State:**
- `items` - Array of cart items
- `selectedTable` - Selected table ID

**Getters:**
- `itemCount` - Total number of items
- `totalAmount` - Total order amount
- `isEmpty` - Check if cart is empty

**Actions:**
- `addItem(menuItem, quantity, notes, modifiers)` - Add item to cart
- `updateItem(index, updates)` - Update cart item
- `removeItem(index)` - Remove item from cart
- `increaseQuantity(index)` - Increase item quantity
- `decreaseQuantity(index)` - Decrease item quantity
- `setTable(tableId)` - Set selected table
- `clearCart()` - Clear all items
- `getOrderItems()` - Format items for API request

**Usage:**
```vue
<script setup>
import { useCartStore } from '@/stores/cart';

const cartStore = useCartStore();

// Add item
cartStore.addItem(menuItem, 2, 'No ice', [modifier1, modifier2]);

// Get total
console.log('Total:', cartStore.totalAmount);

// Submit order
const orderItems = cartStore.getOrderItems();
await api.post('/orders', {
  table_id: cartStore.selectedTable,
  order_items: orderItems
});

// Clear cart
cartStore.clearCart();
</script>
```

#### Orders Store (`stores/orders.ts`)
Manages order data and operations.

**State:**
- `activeOrders` - List of active orders
- `currentOrder` - Currently selected order
- `loading` - Loading state
- `error` - Error messages

**Actions:**
- `fetchActiveOrders()` - Fetch all active orders
- `fetchOrder(orderId)` - Fetch single order
- `createOrder(tableId, orderItems)` - Create new order
- `updateOrder(orderId, orderItems, status)` - Update order
- `addNewOrder(order)` - Add order to list (for real-time updates)
- `removeOrder(orderId)` - Remove order from list
- `updateOrderStatus(orderId, status)` - Update order status

**Usage:**
```vue
<script setup>
import { useOrdersStore } from '@/stores/orders';

const ordersStore = useOrdersStore();

// Fetch orders
await ordersStore.fetchActiveOrders();

// Create order
const result = await ordersStore.createOrder(1, orderItems);

// Update order status
ordersStore.updateOrderStatus(45, 'preparing');
</script>
```

### 3. Axios HTTP Client

**Location:** `resources/js/lib/api.ts`

**Configuration:**
- Base URL: `/api`
- Timeout: 30 seconds
- CSRF token automatically included
- Credentials included for session authentication

**Request Interceptor:**
- Adds CSRF token from meta tag to headers
- Automatically includes session credentials

**Response Interceptor:**
- Handles common HTTP errors (401, 403, 404, 422, 429, 500)
- Redirects to login on 401 Unauthorized
- Logs errors to console

**Helper Functions:**

```typescript
// Authentication
auth.getCsrfCookie()
auth.login(email, password)
auth.logout()
auth.getUser()

// Menu Items
menuItems.getAll(params)
menuItems.getOne(id)
menuItems.create(data)
menuItems.update(id, data)
menuItems.delete(id)

// Orders
orders.getActive()
orders.getOne(id)
orders.create(data)
orders.update(id, data)

// Payments
payments.process(data)
```

**Usage:**
```vue
<script setup>
import api, { auth, menuItems, orders } from '@/lib/api';

// Direct API call
const response = await api.get('/menu-items');

// Using helper functions
await auth.login('admin@example.com', 'password');
const items = await menuItems.getAll({ category: 'Beverages' });
const order = await orders.create({ table_id: 1, order_items: [...] });
</script>
```

### 4. Tailwind CSS

**Location:** `resources/css/app.css`

Tailwind CSS 4 is already configured with custom theming.

**Custom Theme:**
```css
@theme inline {
    --font-sans: Instrument Sans, ui-sans-serif, system-ui, sans-serif;
}
```

**Usage:**
```vue
<template>
  <div class="bg-white shadow-sm rounded-lg p-6">
    <h1 class="text-2xl font-bold text-gray-900">Title</h1>
    <p class="text-sm text-gray-600">Description</p>
  </div>
</template>
```

---

## Development

### Running the Development Server

```bash
# Start Vite dev server
npm run dev

# In another terminal, start Laravel
php artisan serve
```

### Building for Production

```bash
# Build frontend assets
npm run build

# Build with SSR support
npm run build:ssr
```

### Code Formatting

```bash
# Format code
npm run format

# Check formatting
npm run format:check
```

### Linting

```bash
# Run ESLint
npm run lint
```

---

## Creating New Pages

### 1. Create Vue Component

Create a new file in `resources/js/pages/`:

```vue
<!-- resources/js/pages/MyPage.vue -->
<template>
  <AuthenticatedLayout>
    <div>
      <h1>My Page</h1>
      <!-- Your content here -->
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { onMounted } from 'vue';

onMounted(() => {
  console.log('Page mounted');
});
</script>
```

### 2. Add Route

Add the route to `resources/js/router/index.ts`:

```typescript
{
  path: '/my-page',
  name: 'my-page',
  component: () => import('@/pages/MyPage.vue'),
  meta: {
    layout: AuthenticatedLayout,
    auth: true,
    roles: ['Admin', 'Manager'],
    title: 'My Page',
  },
}
```

### 3. Add Navigation Link

Add link to `resources/js/layouts/AuthenticatedLayout.vue`:

```vue
<router-link
  to="/my-page"
  class="inline-flex items-center px-4 py-2 text-sm font-medium"
  :class="isActive('/my-page')"
>
  My Page
</router-link>
```

---

## Creating New Stores

### 1. Create Store File

```typescript
// resources/js/stores/myStore.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/lib/api';

export const useMyStore = defineStore('myStore', () => {
  // State
  const items = ref([]);
  const loading = ref(false);
  
  // Getters
  const itemCount = computed(() => items.value.length);
  
  // Actions
  const fetchItems = async () => {
    loading.value = true;
    try {
      const response = await api.get('/my-endpoint');
      items.value = response.data;
    } catch (error) {
      console.error('Error:', error);
    } finally {
      loading.value = false;
    }
  };
  
  return {
    items,
    loading,
    itemCount,
    fetchItems,
  };
});
```

### 2. Use Store in Component

```vue
<script setup>
import { useMyStore } from '@/stores/myStore';
import { onMounted } from 'vue';

const myStore = useMyStore();

onMounted(async () => {
  await myStore.fetchItems();
});
</script>

<template>
  <div>
    <p>Count: {{ myStore.itemCount }}</p>
    <div v-if="myStore.loading">Loading...</div>
    <ul v-else>
      <li v-for="item in myStore.items" :key="item.id">
        {{ item.name }}
      </li>
    </ul>
  </div>
</template>
```

---

## TypeScript Integration

### Path Aliases

The project is configured with TypeScript path aliases:

```typescript
// Use @ instead of relative paths
import { useAuthStore } from '@/stores/auth';
import api from '@/lib/api';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
```

### Type Definitions

Create type definitions in `resources/js/types/`:

```typescript
// resources/js/types/index.ts
export interface User {
  id: number;
  name: string;
  email: string;
  role: Role;
}

export interface Role {
  id: number;
  name: string;
}

export interface MenuItem {
  id: number;
  name: string;
  price: string;
  category: Category;
}
```

---

## Best Practices

### 1. Component Structure

```vue
<template>
  <!-- Template -->
</template>

<script setup lang="ts">
// Imports
import { ref, computed, onMounted } from 'vue';

// Props & Emits
const props = defineProps<{ id: number }>();
const emit = defineEmits<{ (e: 'update', value: string): void }>();

// State
const loading = ref(false);

// Computed
const isReady = computed(() => !loading.value);

// Methods
const handleClick = () => {
  emit('update', 'value');
};

// Lifecycle
onMounted(() => {
  // Initialize
});
</script>

<style scoped>
/* Component-specific styles */
</style>
```

### 2. Error Handling

```vue
<script setup>
import { ref } from 'vue';

const error = ref<string | null>(null);
const loading = ref(false);

const fetchData = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await api.get('/endpoint');
    // Handle success
  } catch (err: any) {
    error.value = err.response?.data?.message || 'An error occurred';
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div v-if="error" class="text-red-600">{{ error }}</div>
  <div v-if="loading">Loading...</div>
  <div v-else>Content</div>
</template>
```

### 3. Composables

Create reusable logic in composables:

```typescript
// resources/js/composables/useApi.ts
import { ref } from 'vue';
import api from '@/lib/api';

export function useApi<T>(url: string) {
  const data = ref<T | null>(null);
  const loading = ref(false);
  const error = ref<string | null>(null);

  const fetch = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await api.get(url);
      data.value = response.data;
    } catch (err: any) {
      error.value = err.message;
    } finally {
      loading.value = false;
    }
  };

  return { data, loading, error, fetch };
}
```

---

## Integration with Inertia.js

This project supports both Vue Router and Inertia.js:

- **Inertia.js** - For Laravel-rendered pages (already configured)
- **Vue Router** - For standalone SPA pages (newly added)

You can use both approaches in the same application:
- Use Inertia.js for server-rendered pages with Laravel data
- Use Vue Router for client-side only pages like dashboards

---

## Troubleshooting

### Issue: Module not found errors

**Solution:**
```bash
npm install vue-router@4 pinia axios
npm run dev
```

### Issue: CSRF token mismatch

**Solution:**
Ensure CSRF token meta tag exists in `resources/views/app.blade.php`:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### Issue: 401 Unauthorized

**Solution:**
1. Check if user is logged in
2. Verify CSRF cookie is set
3. Check Sanctum configuration

### Issue: Routes not working

**Solution:**
1. Check router configuration
2. Verify routes are registered
3. Check navigation guards

---

## Related Documentation

- [API Authentication Documentation](./API_AUTHENTICATION.md)
- [Order Management API](./ORDER_API.md)
- [Payment API](./PAYMENT_API.md)
- [Kitchen Display System](./KDS_SETUP.md)
- [Laravel Inertia.js](https://inertiajs.com/)
- [Vue 3 Documentation](https://vuejs.org/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Vue Router Documentation](https://router.vuejs.org/)

---

## Summary

The Vue 3 setup provides:
- ✅ Modern reactive framework with TypeScript
- ✅ Client-side routing with Vue Router
- ✅ State management with Pinia
- ✅ HTTP client with Axios
- ✅ Utility-first styling with Tailwind CSS
- ✅ Authentication & authorization
- ✅ Role-based access control
- ✅ Reusable layouts and components
- ✅ Type-safe development
- ✅ Integration with Laravel backend

Ready to build a complete POS system! 🚀
