<template>
    <AuthenticatedLayout>
        <div class="py-4 md:py-6">
            <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4 md:mb-6">สั่งอาหาร</h1>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                    <!-- Menu Items - Left Side (2 columns) -->
                    <div class="lg:col-span-2 space-y-4">
                        <div class="bg-white rounded-lg shadow-sm p-3 md:p-4">
                            <h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">รายการอาหาร</h2>
                            
                            <!-- Category Filter -->
                            <div class="mb-3 md:mb-4">
                                <select
                                    v-model="selectedCategory"
                                    class="w-full px-3 md:px-4 py-2 text-sm md:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">หมวดหมู่ทั้งหมด</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                        {{ cat.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- Menu Items Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
                                <button
                                    v-for="item in filteredMenuItems"
                                    :key="item.id"
                                    @click="addToCart(item)"
                                    :disabled="!item.is_available"
                                    class="p-3 md:p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-left"
                                >
                                    <p class="font-semibold text-sm md:text-base text-gray-900 truncate mb-1">{{ item.name }}</p>
                                    <p class="text-xs md:text-sm text-gray-500 mb-1">{{ item.category?.name }}</p>
                                    <p class="text-base md:text-lg font-bold text-blue-600 mt-1 md:mt-2">฿{{ item.price }}</p>
                                    <div v-if="!item.is_available" class="mt-1">
                                        <span class="inline-block px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-700 rounded">
                                            ไม่พร้อม
                                        </span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cart - Right Side (1 column) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-sm p-3 md:p-4 lg:sticky lg:top-4">
                            <h2 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">ออเดอร์ปัจจุบัน</h2>

                            <!-- Table Info (Display Only - Dine-in) -->
                            <div v-if="currentTable" class="mb-3 md:mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-xs md:text-sm text-blue-600 font-medium">โต๊ะที่เลือก</p>
                                        <p class="text-base md:text-lg font-bold text-blue-900">โต๊ะ {{ currentTable.table_number }}</p>
                                        <p v-if="currentTable.status === 'occupied'" class="text-xs text-orange-600 font-medium mt-1">
                                            (มีออเดอร์อยู่แล้ว)
                                        </p>
                                    </div>
                                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                
                                <!-- Go to Billing Button (shown when table has active order) -->
                                <button
                                    v-if="currentTable.status === 'occupied' && currentTable.active_order"
                                    @click="goToBilling"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    ไปชำระเงิน
                                </button>
                            </div>

                            <!-- Warning if no table -->
                            <div v-else class="mb-3 md:mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-yellow-800">ไม่ได้เลือกโต๊ะ</p>
                                        <p class="text-xs text-yellow-700 mt-1">กรุณาเลือกโต๊ะก่อนสั่งอาหาร</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart Items -->
                            <div class="space-y-2 md:space-y-3 mb-3 md:mb-4 max-h-60 md:max-h-96 overflow-y-auto">
                                <div
                                    v-for="(item, index) in cartStore.items"
                                    :key="index"
                                    class="flex items-center justify-between p-2 md:p-3 bg-gray-50 rounded-lg"
                                >
                                    <div class="flex-1 min-w-0 pr-2">
                                        <p class="font-medium text-sm md:text-base text-gray-900 truncate">{{ item.menuItem.name }}</p>
                                        <p class="text-xs md:text-sm text-gray-500">฿{{ item.menuItem.price }} x {{ item.quantity }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 md:gap-2 flex-shrink-0">
                                        <button
                                            @click="cartStore.decreaseQuantity(index)"
                                            class="w-7 h-7 md:w-8 md:h-8 bg-gray-200 hover:bg-gray-300 rounded-full text-sm md:text-base font-semibold"
                                        >
                                            -
                                        </button>
                                        <span class="w-6 md:w-8 text-center font-semibold text-sm md:text-base">{{ item.quantity }}</span>
                                        <button
                                            @click="cartStore.increaseQuantity(index)"
                                            class="w-7 h-7 md:w-8 md:h-8 bg-gray-200 hover:bg-gray-300 rounded-full text-sm md:text-base font-semibold"
                                        >
                                            +
                                        </button>
                                        <button
                                            @click="cartStore.removeItem(index)"
                                            class="w-7 h-7 md:w-8 md:h-8 bg-red-100 hover:bg-red-200 text-red-600 rounded-full ml-1 md:ml-2 text-lg md:text-xl font-semibold"
                                        >
                                            ×
                                        </button>
                                    </div>
                                </div>

                                <div v-if="cartStore.items.length === 0" class="text-center py-6 md:py-8 text-gray-500 text-sm md:text-base">
                                    ยังไม่มีรายการในตะกร้า
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t pt-3 md:pt-4 mb-3 md:mb-4">
                                <div class="flex justify-between items-center text-lg md:text-xl font-bold">
                                    <span>ยอดรวม:</span>
                                    <span class="text-blue-600">฿{{ cartStore.totalAmount.toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="space-y-2">
                                <button
                                    @click="placeOrder"
                                    :disabled="!canPlaceOrder || submitting"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base disabled:opacity-50 disabled:cursor-not-allowed transition"
                                >
                                    {{ submitting ? 'กำลังสั่งอาหาร...' : 'ยืนยันการสั่งอาหาร' }}
                                </button>
                                <button
                                    @click="cartStore.clearCart()"
                                    :disabled="cartStore.items.length === 0"
                                    class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 py-2.5 md:py-3 rounded-lg font-semibold text-sm md:text-base transition disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    ล้างตะกร้า
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Detail Modal -->
        <Teleport to="body">
            <div
                v-if="showItemModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeItemModal"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-4 md:p-6 max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-900">{{ selectedItem?.name }}</h3>
                            <p class="text-xs md:text-sm text-gray-600">{{ selectedItem?.category?.name }}</p>
                            <p class="text-base md:text-lg font-bold text-blue-600 mt-1">฿{{ selectedItem?.price }}</p>
                        </div>
                        <button @click="closeItemModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">จำนวน</label>
                        <div class="flex items-center space-x-4">
                            <button
                                @click="modalQuantity = Math.max(1, modalQuantity - 1)"
                                class="w-10 h-10 bg-gray-200 rounded-full hover:bg-gray-300 flex items-center justify-center"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                            </button>
                            <span class="text-xl font-semibold w-12 text-center">{{ modalQuantity }}</span>
                            <button
                                @click="modalQuantity++"
                                class="w-10 h-10 bg-blue-600 text-white rounded-full hover:bg-blue-700 flex items-center justify-center"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modifiers -->
                    <div v-if="selectedItem?.modifiers && selectedItem.modifiers.length > 0" class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">ตัวเลือกเพิ่มเติม</label>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            <label
                                v-for="modifier in selectedItem.modifiers"
                                :key="modifier.id"
                                class="flex items-center p-2 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :value="modifier"
                                    v-model="selectedModifiers"
                                    class="w-4 h-4 text-blue-600 rounded"
                                />
                                <span class="ml-3 flex-1 text-sm text-gray-900">{{ modifier.name }}</span>
                                <span class="text-sm font-semibold text-gray-700">
                                    {{ parseFloat(modifier.price_change) >= 0 ? '+' : '' }}฿{{ modifier.price_change }}
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">คำสั่งพิเศษ</label>
                        <textarea
                            v-model="modalNotes"
                            rows="3"
                            placeholder="เช่น ไม่ใส่หอม, เผ็ดน้อย..."
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>

                    <!-- Add to Cart Button -->
                    <button
                        @click="addToCartFromModal"
                        class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                    >
                        เพิ่มในตะกร้า
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- Alert Modal -->
        <Teleport to="body">
            <div
                v-if="showAlertModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeAlertModal"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-sm w-full p-6 animate-fade-in">
                    <!-- Icon -->
                    <div class="flex justify-center mb-4">
                        <!-- Success Icon -->
                        <div
                            v-if="alertType === 'success'"
                            class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center"
                        >
                            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <!-- Error Icon -->
                        <div
                            v-else-if="alertType === 'error'"
                            class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center"
                        >
                            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <!-- Warning Icon -->
                        <div
                            v-else
                            class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center"
                        >
                            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Message -->
                    <p class="text-center text-gray-900 text-base font-medium mb-6">
                        {{ alertMessage }}
                    </p>

                    <!-- Close Button -->
                    <button
                        @click="closeAlertModal"
                        :class="{
                            'bg-green-600 hover:bg-green-700': alertType === 'success',
                            'bg-red-600 hover:bg-red-700': alertType === 'error',
                            'bg-yellow-600 hover:bg-yellow-700': alertType === 'warning'
                        }"
                        class="w-full px-6 py-3 text-white font-semibold rounded-lg transition"
                    >
                        ตกลง
                    </button>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
// import { useRouter } from 'vue-router';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import { useCartStore } from '@/stores/cart';
import api from '@/lib/api';

interface MenuItem {
    id: number;
    name: string;
    price: string;
    is_available: boolean;
    category?: {
        id: number;
        name: string;
    };
    modifiers?: Modifier[];
}

interface Category {
    id: number;
    name: string;
}

interface Table {
    id: number;
    table_number: string;
    status: string;
    active_order?: {
        id: number;
        status: string;
    };
}

interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

// const router = useRouter();
const cartStore = useCartStore();

const menuItems = ref<MenuItem[]>([]);
const categories = ref<Category[]>([]);
const currentTable = ref<Table | null>(null);
const selectedTable = ref('');
const selectedCategory = ref('');
const submitting = ref(false);

// Modal state
const showItemModal = ref(false);
const selectedItem = ref<MenuItem | null>(null);
const modalQuantity = ref(1);
const selectedModifiers = ref<Modifier[]>([]);
const modalNotes = ref('');

// Alert modal state
const showAlertModal = ref(false);
const alertMessage = ref('');
const alertType = ref<'success' | 'error' | 'warning'>('success');

const showAlert = (message: string, type: 'success' | 'error' | 'warning' = 'success') => {
    alertMessage.value = message;
    alertType.value = type;
    showAlertModal.value = true;
};

const closeAlertModal = () => {
    showAlertModal.value = false;
};

// Check if order can be placed (dine-in only)
const canPlaceOrder = computed(() => {
    if (cartStore.items.length === 0) return false;
    return currentTable.value !== null;
});

const filteredMenuItems = computed(() => {
    if (!selectedCategory.value) return menuItems.value;
    return menuItems.value.filter((item) => item.category?.id === parseInt(selectedCategory.value));
});

const fetchMenuItems = async () => {
    try {
        const response = await api.get('/menu-items', {
            params: {
                is_available: true,
                with: 'modifiers'
            }
        });
        menuItems.value = response.data.data;

        // Extract categories
        const uniqueCategories = new Map<number, Category>();
        response.data.data.forEach((item: MenuItem) => {
            if (item.category) {
                uniqueCategories.set(item.category.id, item.category);
            }
        });
        categories.value = Array.from(uniqueCategories.values());
    } catch (error) {
        console.error('Failed to fetch menu items:', error);
    }
};

const fetchAvailableTables = async (tableId: number) => {
    try {
        const response = await api.get(`/tables/${tableId}`);
        currentTable.value = response.data.data;
        selectedTable.value = tableId.toString();
    } catch (error) {
        console.error('Failed to fetch table details:', error);
    }
};

const addToCart = (item: MenuItem) => {
    if (!item.category) {
        showAlert('รายการอาหารไม่ถูกต้อง: ไม่มีหมวดหมู่', 'error');
        return;
    }
    
    // Open modal instead of directly adding to cart
    selectedItem.value = item;
    modalQuantity.value = 1;
    selectedModifiers.value = [];
    modalNotes.value = '';
    showItemModal.value = true;
};

const closeItemModal = () => {
    showItemModal.value = false;
    selectedItem.value = null;
};

const addToCartFromModal = () => {
    if (!selectedItem.value) return;

    cartStore.addItem(
        selectedItem.value as any,
        modalQuantity.value,
        modalNotes.value,
        selectedModifiers.value
    );

    closeItemModal();
};

const placeOrder = async () => {
    if (cartStore.items.length === 0) return;

    // Validate table selection (dine-in only)
    if (!selectedTable.value) {
        showAlert('กรุณาเลือกโต๊ะก่อนสั่งอาหาร', 'warning');
        return;
    }

    submitting.value = true;
    try {
        const orderData: any = {
            order_type: 'dine-in',
            order_items: cartStore.getOrderItems(),
            table_id: parseInt(selectedTable.value),
        };

        // If table has existing order, update it instead of creating new one
        if (currentTable.value?.status === 'occupied' && currentTable.value.active_order) {
            await api.put(`/orders/${currentTable.value.active_order.id}`, orderData);
            showAlert('เพิ่มรายการอาหารสำเร็จ!', 'success');
        } else {
            await api.post('/orders', orderData);
            showAlert('สั่งอาหารสำเร็จ!', 'success');
        }

        cartStore.clearCart();
        
        // Redirect to tables view after brief delay
        setTimeout(() => {
            window.location.href = '/tables';
        }, 1500);
    } catch (error: any) {
        console.error('Failed to place order:', error);
        
        // Check if it's a table occupied error
        if (error.response?.status === 400 && error.response?.data?.message?.includes('already occupied')) {
            showAlert('โต๊ะนี้มีคนใช้งานอยู่แล้ว กรุณาเลือกโต๊ะอื่น', 'error');
        } else {
            showAlert(error.response?.data?.message || 'ไม่สามารถสั่งอาหารได้ กรุณาลองใหม่อีกครั้ง', 'error');
        }
    } finally {
        submitting.value = false;
    }
};

const goToBilling = () => {
    if (currentTable.value?.active_order?.id) {
        window.location.href = `/billing/${currentTable.value.active_order.id}`;
    }
};

// Get table_id from URL parameter
const getTableIdFromUrl = (): string | null => {
    if (typeof window !== 'undefined') {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('table_id');
    }
    return null;
};

onMounted(async () => {
    await fetchMenuItems();
    
    // Auto-select table if table_id is in URL
    const tableId = getTableIdFromUrl();
    if (tableId) {
        await fetchAvailableTables(parseInt(tableId));
    }
});
</script>
