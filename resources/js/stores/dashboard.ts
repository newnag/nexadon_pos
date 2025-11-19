import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '@/lib/api';

export interface DashboardStats {
    activeOrdersCount: number;
    availableTablesCount: number;
    todaySales: string;
    todayCustomers: number;
    recentOrders: RecentOrder[];
}

export interface RecentOrder {
    id: number;
    table_number: string;
    user_name: string;
    status: string;
    total_amount: string;
    created_at: string;
}

export const useDashboardStore = defineStore('dashboard', () => {
    // State
    const stats = ref<DashboardStats | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    // Actions
    const fetchStats = async () => {
        try {
            loading.value = true;
            error.value = null;
            const response = await api.get('/dashboard/stats');
            stats.value = response.data;
        } catch (err: any) {
            error.value = err.response?.data?.message || 'Failed to fetch dashboard stats';
            console.error('Error fetching dashboard stats:', err);
        } finally {
            loading.value = false;
        }
    };

    return {
        // State
        stats,
        loading,
        error,

        // Actions
        fetchStats,
    };
});
