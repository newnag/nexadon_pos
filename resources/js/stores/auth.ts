import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import api, { auth } from '@/lib/api';

export interface User {
    id: number;
    name: string;
    email: string;
    role: {
        id: number;
        name: string;
    };
}

export const useAuthStore = defineStore('auth', () => {
    // Get user from Inertia page props
    const page = usePage();
    
    // State - initialize from Inertia props if available
    const user = ref<User | null>((page.props.auth as any)?.user || null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    // Getters
    const isAuthenticated = computed(() => user.value !== null);
    const userRole = computed(() => user.value?.role?.name || null);
    const isAdmin = computed(() => userRole.value === 'Admin');
    const isManager = computed(() => userRole.value === 'Manager');
    const isCashier = computed(() => userRole.value === 'Cashier');
    const isWaiter = computed(() => userRole.value === 'Waiter');

    // Check if user has specific role(s)
    const hasRole = (roles: string | string[]): boolean => {
        if (!user.value?.role?.name) return false;
        const roleArray = Array.isArray(roles) ? roles : [roles];
        return roleArray.includes(user.value.role.name);
    };

    // Sync with Inertia props when they change
    const syncWithInertiaProps = () => {
        const inertiaUser = (page.props.auth as any)?.user;
        if (inertiaUser && (!user.value || user.value.id !== inertiaUser.id)) {
            user.value = inertiaUser;
        }
    };

    // Watch for Inertia page props changes (for client-side navigation)
    if (typeof window !== 'undefined') {
        (window as any).addEventListener('inertia:success', syncWithInertiaProps);
    }

    // Actions
    const fetchUser = async () => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.get('/user');
            user.value = response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch user';
            user.value = null;
        } finally {
            loading.value = false;
        }
    };

    const login = async (email: string, password: string) => {
        try {
            loading.value = true;
            error.value = null;

            // Get CSRF cookie first
            await auth.getCsrfCookie();

            // Attempt login
            await api.post('/login', { email, password });

            // Fetch user data
            await fetchUser();

            return { success: true };
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Login failed';
            return { success: false, error: error.value };
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            loading.value = true;
            await api.post('/logout');
            user.value = null;
        } catch (err: any) {
            console.error('Logout error:', err);
        } finally {
            loading.value = false;
        }
    };

    // Initialize: Try to fetch user on store creation
    const initialize = async () => {
        try {
            await fetchUser();
        } catch {
            // User not authenticated
            user.value = null;
        }
    };

    return {
        // State
        user,
        loading,
        error,

        // Getters
        isAuthenticated,
        userRole,
        isAdmin,
        isManager,
        isCashier,
        isWaiter,

        // Methods
        hasRole,
        fetchUser,
        login,
        logout,
        initialize,
    };
});
