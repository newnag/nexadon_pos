<template>
    <DefaultLayout>
        <div class="p-3 sm:p-4 lg:p-6">
            <!-- Header -->
            <div class="mb-4 sm:mb-6">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4">
                    <div class="min-w-0">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">จัดการตัวเลือกเพิ่มเติม</h1>
                        <p class="text-sm sm:text-base text-gray-600 mt-1">เพิ่ม แก้ไข หรือลบตัวเลือกเสริมสำหรับเมนูอาหาร</p>
                    </div>
                    <button
                        @click="openCreateModal"
                        class="w-full sm:w-auto flex-shrink-0 px-4 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span class="hidden sm:inline">เพิ่มตัวเลือกใหม่</span>
                        <span class="sm:hidden">เพิ่มตัวเลือก</span>
                    </button>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                <div class="flex items-start gap-2 sm:gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-xs sm:text-sm text-blue-800">
                        <p class="font-semibold mb-1">ตัวเลือกเพิ่มเติม (Modifiers)</p>
                        <p class="leading-relaxed">เป็นตัวเลือกเสริมที่สามารถเพิ่มให้กับเมนูอาหาร เช่น ไข่ดาว +฿10, ชีส +฿15, ไม่ใส่หอม ฿0 เป็นต้น</p>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-sm sm:text-base text-gray-600">กำลังโหลดข้อมูล...</p>
            </div>

            <!-- Table for Desktop, Cards for Mobile -->
            <div v-else>
                <!-- Desktop Table -->
                <div class="hidden md:block bg-white rounded-lg shadow-md overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ชื่อตัวเลือก
                                </th>
                                <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ราคาเพิ่มเติม
                                </th>
                                <th class="px-4 lg:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    จัดการ
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="modifier in modifiers" :key="modifier.id" class="hover:bg-gray-50">
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ modifier.name }}</div>
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <span :class="parseFloat(modifier.price_change) >= 0 ? 'text-green-600' : 'text-red-600'" class="font-semibold">
                                            {{ parseFloat(modifier.price_change) >= 0 ? '+' : '' }}฿{{ modifier.price_change }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        @click="openEditModal(modifier)"
                                        class="text-blue-600 hover:text-blue-900 mr-3 lg:mr-4"
                                    >
                                        แก้ไข
                                    </button>
                                    <button
                                        @click="deleteModifier(modifier)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        ลบ
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="modifiers.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">ยังไม่มีตัวเลือกเพิ่มเติม</h3>
                        <p class="mt-1 text-sm text-gray-500">เริ่มต้นด้วยการเพิ่มตัวเลือกใหม่</p>
                    </div>
                </div>

                <!-- Mobile Card View -->
                <div class="md:hidden space-y-3">
                    <div v-if="modifiers.length === 0" class="bg-white rounded-lg shadow p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-sm text-gray-500">ยังไม่มีตัวเลือกเพิ่มเติม</p>
                    </div>

                    <div
                        v-for="modifier in modifiers"
                        :key="modifier.id"
                        class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1 min-w-0 mr-3">
                                <h3 class="text-base font-semibold text-gray-900">{{ modifier.name }}</h3>
                            </div>
                            <div class="flex-shrink-0">
                                <span :class="parseFloat(modifier.price_change) >= 0 ? 'text-green-600' : 'text-red-600'" class="text-base font-bold">
                                    {{ parseFloat(modifier.price_change) >= 0 ? '+' : '' }}฿{{ modifier.price_change }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex gap-2">
                            <button 
                                @click="openEditModal(modifier)" 
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                            >
                                แก้ไข
                            </button>
                            <button 
                                @click="deleteModifier(modifier)" 
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                            >
                                ลบ
                            </button>
                        </div>
                    </div>
                </div>
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
                            {{ isEditing ? 'แก้ไขตัวเลือกเพิ่มเติม' : 'เพิ่มตัวเลือกใหม่' }}
                        </h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="saveModifier">
                        <!-- Name -->
                        <div class="mb-3 sm:mb-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                ชื่อตัวเลือก *
                            </label>
                            <input
                                v-model="formData.name"
                                type="text"
                                placeholder="เช่น ไข่ดาว, ชีส, ไม่ใส่หอม"
                                required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                        </div>

                        <!-- Price Change -->
                        <div class="mb-4 sm:mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 sm:mb-2">
                                ราคาเพิ่มเติม (฿) *
                            </label>
                            <input
                                v-model.number="formData.price_change"
                                type="number"
                                step="0.01"
                                placeholder="0.00"
                                required
                                class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            />
                            <p class="mt-1.5 text-xs text-gray-500">
                                ใส่ 0 สำหรับตัวเลือกที่ไม่เพิ่มราคา หรือ ติดลบสำหรับส่วนลด
                            </p>
                        </div>

                        <!-- Examples -->
                        <div class="mb-4 sm:mb-6 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs font-semibold text-gray-700 mb-2">ตัวอย่าง:</p>
                            <ul class="text-xs text-gray-600 space-y-1">
                                <li>• ไข่ดาว → +10 (เพิ่มราคา 10 บาท)</li>
                                <li>• ไม่ใส่หอม → 0 (ไม่เพิ่มราคา)</li>
                                <li>• ส่วนลดพนักงาน → -20 (ลดราคา 20 บาท)</li>
                            </ul>
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
                                <span v-if="!submitting">{{ isEditing ? 'บันทึก' : 'เพิ่ม' }}</span>
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

interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

// State
const modifiers = ref<Modifier[]>([]);
const loading = ref(false);
const showModal = ref(false);
const isEditing = ref(false);
const editingModifier = ref<Modifier | null>(null);
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
    name: '',
    price_change: 0,
});

// Fetch modifiers
const fetchModifiers = async () => {
    loading.value = true;
    try {
        const response = await api.get('/modifiers');
        modifiers.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch modifiers:', error);
    } finally {
        loading.value = false;
    }
};

// Open create modal
const openCreateModal = () => {
    isEditing.value = false;
    editingModifier.value = null;
    formData.value = {
        name: '',
        price_change: 0,
    };
    errorMessage.value = '';
    showModal.value = true;
};

// Open edit modal
const openEditModal = (modifier: Modifier) => {
    isEditing.value = true;
    editingModifier.value = modifier;
    formData.value = {
        name: modifier.name,
        price_change: parseFloat(modifier.price_change),
    };
    errorMessage.value = '';
    showModal.value = true;
};

// Close modal
const closeModal = () => {
    showModal.value = false;
    editingModifier.value = null;
    errorMessage.value = '';
};

// Save modifier (create or update)
const saveModifier = async () => {
    if (submitting.value) return;

    errorMessage.value = '';
    submitting.value = true;

    try {
        if (isEditing.value && editingModifier.value) {
            // Update existing modifier
            await api.put(`/modifiers/${editingModifier.value.id}`, formData.value);
            alertMessage.value = 'แก้ไขตัวเลือกเพิ่มเติมสำเร็จ';
        } else {
            // Create new modifier
            await api.post('/modifiers', formData.value);
            alertMessage.value = 'เพิ่มตัวเลือกเพิ่มเติมสำเร็จ';
        }

        closeModal();
        showAlertModal.value = true;
        await fetchModifiers();

    } catch (error: any) {
        console.error('Failed to save modifier:', error);
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

// Delete modifier
const deleteModifier = (modifier: Modifier) => {
    showConfirm(
        `คุณต้องการลบตัวเลือก "${modifier.name}" หรือไม่?\nการกระทำนี้ไม่สามารถย้อนกลับได้`,
        async () => {
            try {
                await api.delete(`/modifiers/${modifier.id}`);
                alertMessage.value = 'ลบตัวเลือกเพิ่มเติมสำเร็จ';
                showAlertModal.value = true;
                await fetchModifiers();
            } catch (error: any) {
                console.error('Failed to delete modifier:', error);
                alertMessage.value = error.response?.data?.message || 'ไม่สามารถลบตัวเลือกได้ กรุณาลองใหม่อีกครั้ง';
                showAlertModal.value = true;
            }
        }
    );
};

// Initialize
onMounted(() => {
    fetchModifiers();
});
</script>
