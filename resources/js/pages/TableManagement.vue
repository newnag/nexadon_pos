<template>
    <DefaultLayout>
        <div class="p-3 sm:p-4 lg:p-6">
            <!-- Header -->
            <div class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4">
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">จัดการโต๊ะ</h1>
                        <p class="text-sm sm:text-base text-gray-600 mt-1">เพิ่ม แก้ไข หรือลบโต๊ะในร้าน</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="w-full sm:w-auto flex-shrink-0 px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">เพิ่มโต๊ะใหม่</span>
                        <span class="sm:hidden">เพิ่มโต๊ะ</span>
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-sm sm:text-base text-gray-600">กำลังโหลดข้อมูล...</p>
            </div>

            <!-- Tables Grid -->
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-6">
                <div
                    v-for="table in tables"
                    :key="table.id"
                    class="bg-white rounded-lg shadow-md p-4 sm:p-5 lg:p-6 border-2"
                    :class="{
                        'border-green-500': table.status === 'available',
                        'border-red-500': table.status === 'occupied',
                        'border-yellow-500': table.status === 'reserved'
                    }"
                >
                    <!-- Table Header -->
                    <div class="mb-3 sm:mb-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-base sm:text-lg lg:text-xl font-bold text-gray-900 truncate flex-1 mr-2">{{ table.table_number }}</h3>
                            <div class="flex gap-1 flex-shrink-0">
                                <button
                                    @click="openEditModal(table)"
                                    class="p-1 sm:p-1.5 text-blue-600 hover:bg-blue-50 rounded transition"
                                    title="แก้ไข"
                                >
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteTable(table)"
                                    class="p-1 sm:p-1.5 text-red-600 hover:bg-red-50 rounded transition"
                                    title="ลบ"
                                    :disabled="table.status === 'occupied'"
                                    :class="{ 'opacity-50 cursor-not-allowed': table.status === 'occupied' }"
                                >
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <span
                            :class="[
                                'inline-block px-2 py-0.5 sm:py-1 text-xs font-semibold rounded-full',
                                table.status === 'available' ? 'bg-green-100 text-green-800' :
                                table.status === 'occupied' ? 'bg-red-100 text-red-800' :
                                'bg-yellow-100 text-yellow-800'
                            ]"
                        >
                            {{ getStatusText(table.status) }}
                        </span>
                    </div>

                    <!-- Active Orders Info -->
                    <div v-if="table.active_order" class="mt-3 sm:mt-4 p-2 sm:p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600">ออเดอร์ปัจจุบัน:</p>
                        <p class="text-xs sm:text-sm font-semibold text-gray-900 truncate">#{{ table.active_order.id }}</p>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!loading && tables.length === 0" class="text-center py-12">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">ยังไม่มีโต๊ะ</h3>
                <p class="mt-1 text-xs sm:text-sm text-gray-500">เริ่มต้นด้วยการเพิ่มโต๊ะใหม่</p>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-4 sm:p-6">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-start mb-4 sm:mb-6">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                            {{ isEditing ? 'แก้ไขโต๊ะ' : 'เพิ่มโต๊ะใหม่' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="saveTable">
                        <!-- Table Number -->
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                หมายเลขโต๊ะ *
                            </label>
                            <input
                                v-model="formData.table_number"
                                type="text"
                                placeholder="เช่น T01, โต๊ะ 1"
                                required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Status -->
                        <div class="mb-4 sm:mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                สถานะ
                            </label>
                            <select
                                v-model="formData.status"
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="available">ว่าง</option>
                                <option value="occupied">มีลูกค้า</option>
                                <option value="reserved">จอง</option>
                            </select>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-xs sm:text-sm text-red-800">{{ errorMessage }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:gap-3">
                            <button
                                type="button"
                                @click="closeModal"
                                class="w-full px-4 sm:px-6 py-2.5 sm:py-3 bg-white border border-gray-300 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-50 transition"
                            >
                                ยกเลิก
                            </button>
                            <button
                                type="submit"
                                :disabled="submitting"
                                class="w-full px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition"
                            >
                                <span v-if="!submitting">{{ isEditing ? 'บันทึก' : 'เพิ่มโต๊ะ' }}</span>
                                <span v-else>กำลังบันทึก...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Alert Modal -->
        <Teleport to="body">
            <div
                v-if="showAlertModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="showAlertModal = false"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <!-- Alert Icon -->
                    <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <!-- Alert Message -->
                    <div class="text-center mb-6">
                        <p class="text-lg text-gray-800">{{ alertMessage }}</p>
                    </div>

                    <!-- Close Button -->
                    <button
                        @click="showAlertModal = false"
                        class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                    >
                        ตกลง
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Confirm Delete Modal -->
        <Teleport to="body">
            <div
                v-if="showConfirmModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="handleCancelConfirm"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <!-- Confirm Icon -->
                    <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Confirm Message -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">ยืนยันการลบ</h3>
                        <p class="text-gray-700">{{ confirmMessage }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <button
                            @click="handleCancelConfirm"
                            class="flex-1 px-6 py-3 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
                        >
                            ยกเลิก
                        </button>
                        <button
                            @click="handleConfirm"
                            class="flex-1 px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition"
                        >
                            ลบ
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import api from '@/lib/api';

interface Table {
    id: number;
    table_number: string;
    status: 'available' | 'occupied' | 'reserved';
    active_order?: {
        id: number;
    };
}

// State
const tables = ref<Table[]>([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingTable = ref<Table | null>(null);
const submitting = ref(false);
const errorMessage = ref('');

// Alert/Confirm states
const showAlertModal = ref(false);
const showConfirmModal = ref(false);
const alertMessage = ref('');
const confirmMessage = ref('');
const confirmCallback = ref<(() => void) | null>(null);

// Form data
const formData = ref({
    table_number: '',
    status: 'available' as 'available' | 'occupied' | 'reserved',
});

// Get status text
const getStatusText = (status: string): string => {
    const statusMap: Record<string, string> = {
        'available': 'ว่าง',
        'occupied': 'มีลูกค้า',
        'reserved': 'จอง',
    };
    return statusMap[status] || status;
};

// Fetch tables
const fetchTables = async () => {
    loading.value = true;
    try {
        const response = await api.get('/tables');
        tables.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch tables:', error);
    } finally {
        loading.value = false;
    }
};

// Open create modal
const openCreateModal = () => {
    isEditing.value = false;
    editingTable.value = null;
    formData.value = {
        table_number: '',
        status: 'available',
    };
    errorMessage.value = '';
    showModal.value = true;
};

// Open edit modal
const openEditModal = (table: Table) => {
    isEditing.value = true;
    editingTable.value = table;
    formData.value = {
        table_number: table.table_number,
        status: table.status,
    };
    errorMessage.value = '';
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    editingTable.value = null;
    errorMessage.value = '';
};

// Save table (create or update)
const saveTable = async () => {
    if (submitting.value) return;

    errorMessage.value = '';
    submitting.value = true;

    try {
        if (isEditing.value && editingTable.value) {
            // Update existing table
            await api.put(`/tables/${editingTable.value.id}`, formData.value);
            alertMessage.value = 'แก้ไขโต๊ะสำเร็จ';
        } else {
            // Create new table
            await api.post('/tables', formData.value);
            alertMessage.value = 'เพิ่มโต๊ะสำเร็จ';
        }

        closeModal();
        showAlertModal.value = true;
        await fetchTables();

    } catch (error: any) {
        console.error('Failed to save table:', error);
        errorMessage.value = error.response?.data?.message || 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
    } finally {
        submitting.value = false;
    }
};

// Show confirm modal
const showConfirm = (message: string, callback: () => void) => {
    confirmMessage.value = message;
    confirmCallback.value = callback;
    showConfirmModal.value = true;
};

// Confirm action
const handleConfirm = () => {
    showConfirmModal.value = false;
    if (confirmCallback.value) {
        confirmCallback.value();
        confirmCallback.value = null;
    }
};

// Cancel confirmation
const handleCancelConfirm = () => {
    showConfirmModal.value = false;
    confirmCallback.value = null;
};

// Delete table
const deleteTable = (table: Table) => {
    if (table.status === 'occupied') {
        alertMessage.value = 'ไม่สามารถลบโต๊ะที่มีลูกค้าอยู่ได้';
        showAlertModal.value = true;
        return;
    }

    showConfirm(
        `คุณต้องการลบโต๊ะ "${table.table_number}" หรือไม่?\nการกระทำนี้ไม่สามารถย้อนกลับได้`,
        async () => {
            try {
                await api.delete(`/tables/${table.id}`);
                alertMessage.value = 'ลบโต๊ะสำเร็จ';
                showAlertModal.value = true;
                await fetchTables();
            } catch (error: any) {
                console.error('Failed to delete table:', error);
                alertMessage.value = error.response?.data?.message || 'ไม่สามารถลบโต๊ะได้ กรุณาลองใหม่อีกครั้ง';
                showAlertModal.value = true;
            }
        }
    );
};

// Initialize
onMounted(() => {
    fetchTables();
});
</script>
