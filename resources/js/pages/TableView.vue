<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-6">Table Management</h1>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    <div
                        v-for="table in tables"
                        :key="table.id"
                        @click="viewTable(table)"
                        class="bg-white rounded-lg shadow-sm p-6 cursor-pointer hover:shadow-md transition-shadow"
                    >
                        <div class="text-center">
                            <p class="text-lg font-semibold text-gray-900 mb-2">{{ table.table_number }}</p>
                            <span
                                :class="[
                                    'inline-block px-3 py-1 rounded-full text-sm font-semibold',
                                    table.status === 'available'
                                        ? 'bg-green-100 text-green-800'
                                        : table.status === 'occupied'
                                        ? 'bg-red-100 text-red-800'
                                        : 'bg-gray-100 text-gray-800',
                                ]"
                            >
                                {{ table.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="tables.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No tables found.</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
// import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

interface Table {
    id: number;
    table_number: string;
    status: string;
}

// const router = useRouter();
const tables = ref<Table[]>([]);

const fetchTables = async () => {
    try {
        // TODO: Create tables API endpoint
        // Mock data for now
        tables.value = [
            { id: 1, table_number: 'T-001', status: 'available' },
            { id: 2, table_number: 'T-002', status: 'occupied' },
            { id: 3, table_number: 'T-003', status: 'available' },
            { id: 4, table_number: 'T-004', status: 'reserved' },
            { id: 5, table_number: 'T-005', status: 'available' },
        ];
    } catch (error) {
        console.error('Failed to fetch tables:', error);
    }
};

const viewTable = (table: Table) => {
    alert(`View table details: ${table.table_number} - To be implemented`);
    // router.push(`/tables/${table.id}`);
};

onMounted(() => {
    fetchTables();
});
</script>
