import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

// Import layouts
const AuthenticatedLayout = () => import('@/layouts/AuthenticatedLayout.vue');
const GuestLayout = () => import('@/layouts/GuestLayout.vue');

// Import pages
const Login = () => import('@/pages/Auth/Login.vue');
const Dashboard = () => import('@/pages/Dashboard.vue');
const MenuManagement = () => import('@/pages/MenuManagement.vue');
const OrderView = () => import('@/pages/OrderView.vue');
const TableMapView = () => import('@/pages/TableMapView.vue');
const BillingView = () => import('@/pages/BillingView.vue');
const KDSView = () => import('@/pages/KDSView.vue');
const Reports = () => import('@/pages/Reports.vue');

const routes: RouteRecordRaw[] = [
    // Guest routes
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: {
            layout: GuestLayout,
            guest: true,
        },
    },

    // Authenticated routes
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager', 'Cashier', 'Waiter'],
        },
    },
    {
        path: '/menu',
        name: 'menu.index',
        component: MenuManagement,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager'],
            title: 'Menu Management',
        },
    },
    {
        path: '/orders',
        name: 'orders.index',
        component: OrderView,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager', 'Cashier', 'Waiter'],
            title: 'Take Order',
        },
    },
    {
        path: '/tables',
        name: 'tables.index',
        component: TableMapView,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager', 'Cashier', 'Waiter'],
            title: 'Tables',
        },
    },
    {
        path: '/billing/:orderId',
        name: 'billing.show',
        component: BillingView,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager', 'Cashier'],
            title: 'Billing',
        },
    },
    {
        path: '/kitchen',
        name: 'kitchen.display',
        component: KDSView,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager'],
            title: 'Kitchen Display',
        },
    },
    {
        path: '/reports',
        name: 'reports.index',
        component: Reports,
        meta: {
            layout: AuthenticatedLayout,
            auth: true,
            roles: ['Admin', 'Manager'],
            title: 'Reports',
        },
    },

    // 404 Not Found
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/NotFound.vue'),
        meta: {
            layout: GuestLayout,
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation guards
router.beforeEach(async (to, _from, next) => {
    const { useAuthStore } = await import('@/stores/auth');
    const authStore = useAuthStore();

    // Check if route requires authentication
    if (to.meta.auth && !authStore.isAuthenticated) {
        return next({ name: 'login', query: { redirect: to.fullPath } });
    }

    // Check if guest-only route and user is authenticated
    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    // Check role permissions
    if (to.meta.roles && authStore.user) {
        const hasPermission = (to.meta.roles as string[]).includes(authStore.user.role.name);
        if (!hasPermission) {
            return next({ name: 'dashboard' });
        }
    }

    next();
});

export default router;
