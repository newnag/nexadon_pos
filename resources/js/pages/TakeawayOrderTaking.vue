<template>
    <DefaultLayout>
        <div class="min-h-screen bg-gray-50">
            <div class="max-w-7xl mx-auto p-6">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <button
                            @click="returnToTakeawayList"
                            class="text-sm text-gray-600 hover:text-gray-900 mb-2 flex items-center"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            กลับไปรายการออเดอร์
                        </button>
                        <h1 class="text-3xl font-bold text-gray-900">🥡 สร้างออเดอร์กลับบ้าน</h1>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Menu Selection (Left Side) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Customer Info Card -->
                        <div class="bg-orange-50 border-2 border-orange-200 rounded-lg p-6">
                            <h2 class="text-lg font-semibold text-orange-900 mb-4">ข้อมูลลูกค้า</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        ชื่อลูกค้า <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="customerName"
                                        type="text"
                                        placeholder="กรอกชื่อลูกค้า"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        เบอร์โทรศัพท์
                                    </label>
                                    <input
                                        v-model="customerPhone"
                                        type="tel"
                                        placeholder="08X-XXX-XXXX"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="bg-white rounded-lg shadow-md p-4">
                            <div class="flex gap-2 overflow-x-auto pb-2">
                                <button
                                    @click="selectedCategory = null"
                                    :class="[
                                        'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition',
                                        selectedCategory === null
                                            ? 'bg-orange-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    ทั้งหมด
                                </button>
                                <button
                                    v-for="category in categories"
                                    :key="category.id"
                                    @click="selectedCategory = category.id"
                                    :class="[
                                        'px-4 py-2 rounded-lg font-medium whitespace-nowrap transition',
                                        selectedCategory === category.id
                                            ? 'bg-orange-600 text-white'
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                                    ]"
                                >
                                    {{ category.name }}
                                </button>
                            </div>
                        </div>

                        <!-- Menu Items Grid -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-semibold mb-4">เลือกเมนู</h2>
                            
                            <div v-if="loadingMenu" class="text-center py-12">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600"></div>
                                <p class="mt-2 text-gray-600">กำลังโหลดเมนู...</p>
                            </div>

                            <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <button
                                    v-for="item in filteredMenuItems"
                                    :key="item.id"
                                    @click="addToCart(item)"
                                    class="bg-white border-2 border-gray-200 rounded-lg p-4 hover:border-orange-500 hover:shadow-md transition text-left"
                                >
                                    <div class="aspect-square bg-gray-100 rounded-lg mb-2 flex items-center justify-center">
                                        <span class="text-4xl">🍽️</span>
                                    </div>
                                    <h3 class="font-semibold text-gray-900 text-sm mb-1">{{ item.name }}</h3>
                                    <p class="text-xs text-gray-500 mb-2">{{ item.category.name }}</p>
                                    <p class="text-orange-600 font-bold">฿{{ parseFloat(item.price).toFixed(2) }}</p>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cart (Right Sidebar) -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md p-6 sticky top-6">
                            <h2 class="text-xl font-semibold mb-4">รายการสั่ง</h2>

                            <!-- Cart Items -->
                            <div v-if="cartStore.items.length === 0" class="text-center py-8 text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <p>ยังไม่มีรายการ</p>
                            </div>

                            <div v-else class="space-y-3 mb-6 max-h-96 overflow-y-auto">
                                <div
                                    v-for="(item, index) in cartStore.items"
                                    :key="index"
                                    class="border border-gray-200 rounded-lg p-3"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <div class="flex-1">
                                            <h4 class="font-semibold text-sm">{{ item.menuItem.name }}</h4>
                                            <p class="text-xs text-gray-500">฿{{ parseFloat(item.menuItem.price).toFixed(2) }}</p>
                                            
                                            <!-- Modifiers -->
                                            <div v-if="item.selectedModifiers && item.selectedModifiers.length > 0" class="mt-1">
                                                <p class="text-xs text-gray-500">
                                                    + {{ item.selectedModifiers.map(m => m.name).join(', ') }}
                                                </p>
                                            </div>
                                            
                                            <!-- Notes -->
                                            <div v-if="item.notes" class="mt-1">
                                                <p class="text-xs text-gray-500 italic">หมายเหตุ: {{ item.notes }}</p>
                                            </div>
                                        </div>
                                        <button
                                            @click="cartStore.removeItem(index)"
                                            class="text-red-500 hover:text-red-700"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Quantity Controls -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <button
                                                @click="cartStore.decreaseQuantity(index)"
                                                class="w-8 h-8 bg-gray-100 rounded-full hover:bg-gray-200 flex items-center justify-center"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                </svg>
                                            </button>
                                            <span class="font-semibold w-8 text-center">{{ item.quantity }}</span>
                                            <button
                                                @click="cartStore.increaseQuantity(index)"
                                                class="w-8 h-8 bg-orange-100 rounded-full hover:bg-orange-200 flex items-center justify-center"
                                            >
                                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                        </div>
                                        <span class="font-bold text-orange-600">
                                            ฿{{ (item.subtotal || 0).toFixed(2) }}
                                        </span>
                                    </div>

                                    <!-- Notes input removed - now using modal -->
                                </div>
                            </div>

                            <!-- Total -->
                            <div class="border-t pt-4 mb-4">
                                <div class="flex justify-between items-center text-xl font-bold">
                                    <span>ยอดรวม</span>
                                    <span class="text-orange-600">฿{{ (cartStore.totalAmount || 0).toFixed(2) }}</span>
                                </div>
                            </div>

                            <!-- Customer Info Warning -->
                            <div v-if="!canPlaceOrder && cartStore.items.length > 0" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                <p class="text-sm text-yellow-800">
                                    ⚠️ กรุณากรอกข้อมูลลูกค้าก่อนสั่งออเดอร์
                                </p>
                            </div>

                            <!-- Place Order Button -->
                            <button
                                @click="placeOrder"
                                :disabled="!canPlaceOrder || placingOrder"
                                :class="[
                                    'w-full py-4 rounded-lg font-semibold text-white transition shadow-lg',
                                    canPlaceOrder && !placingOrder
                                        ? 'bg-orange-600 hover:bg-orange-700'
                                        : 'bg-gray-300 cursor-not-allowed'
                                ]"
                            >
                                <span v-if="!placingOrder">สั่งออเดอร์ (฿{{ (cartStore.totalAmount || 0).toFixed(2) }})</span>
                                <span v-else class="flex items-center justify-center">
                                    <svg class="animate-spin h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    กำลังสั่งออเดอร์...
                                </span>
                            </button>

                            <!-- Clear Cart -->
                            <button
                                v-if="cartStore.items.length > 0"
                                @click="clearCart"
                                class="w-full mt-2 py-2 text-sm text-gray-600 hover:text-red-600 font-medium"
                            >
                                ล้างรายการทั้งหมด
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Item Modal -->
        <Teleport to="body">
            <div
                v-if="showItemModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeItemModal"
            >
                <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="sticky top-0 bg-orange-600 text-white p-4 rounded-t-lg flex justify-between items-center">
                        <h3 class="text-xl font-bold">{{ selectedItem?.name }}</h3>
                        <button
                            @click="closeItemModal"
                            class="text-white hover:text-orange-100 text-2xl"
                        >
                            ×
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="p-6">
                        <!-- Item Info -->
                        <div class="mb-4">
                            <p class="text-gray-600 text-sm">{{ selectedItem?.category.name }}</p>
                            <p class="text-2xl font-bold text-orange-600">฿{{ selectedItem ? parseFloat(selectedItem.price).toFixed(2) : '0.00' }}</p>
                        </div>

                        <!-- Modifiers -->
                        <div v-if="selectedItem?.modifiers && selectedItem.modifiers.length > 0" class="mb-6">
                            <h4 class="font-semibold mb-3">ตัวเลือกเพิ่มเติม</h4>
                            <div class="space-y-2">
                                <label
                                    v-for="modifier in selectedItem.modifiers"
                                    :key="modifier.id"
                                    class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="modifier"
                                        v-model="selectedModifiers"
                                        class="w-5 h-5 text-orange-600 rounded focus:ring-orange-500"
                                    />
                                    <div class="ml-3 flex-1">
                                        <p class="font-medium text-gray-900">{{ modifier.name }}</p>
                                        <p class="text-sm text-gray-500">+฿{{ parseFloat(modifier.price_change).toFixed(2) }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Quantity -->
                        <div class="mb-6">
                            <h4 class="font-semibold mb-3">จำนวน</h4>
                            <div class="flex items-center space-x-4">
                                <button
                                    @click="modalQuantity = Math.max(1, modalQuantity - 1)"
                                    class="w-10 h-10 bg-gray-100 rounded-full hover:bg-gray-200 flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                    </svg>
                                </button>
                                <span class="text-2xl font-bold w-12 text-center">{{ modalQuantity }}</span>
                                <button
                                    @click="modalQuantity++"
                                    class="w-10 h-10 bg-orange-600 text-white rounded-full hover:bg-orange-700 flex items-center justify-center"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-6">
                            <h4 class="font-semibold mb-3">หมายเหตุ</h4>
                            <textarea
                                v-model="modalNotes"
                                rows="3"
                                placeholder="ระบุคำขอพิเศษ เช่น ไม่ใส่ผัก, น้ำแข็งแยก..."
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                            ></textarea>
                        </div>

                        <!-- Total Price -->
                        <div class="mb-6 p-4 bg-orange-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900">ยอดรวม</span>
                                <span class="text-2xl font-bold text-orange-600">
                                    ฿{{ calculateModalTotal().toFixed(2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <button
                                @click="closeItemModal"
                                class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition"
                            >
                                ยกเลิก
                            </button>
                            <button
                                @click="addToCartFromModal"
                                class="flex-1 px-6 py-3 bg-orange-600 text-white font-semibold rounded-lg hover:bg-orange-700 transition"
                            >
                                เพิ่มในรายการ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import { useCartStore } from '@/stores/cart';
import api from '@/lib/api';

interface Category {
    id: number;
    name: string;
}

interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

interface MenuItem {
    id: number;
    name: string;
    price: string;
    category: Category;
    is_available: boolean;
    modifiers?: Modifier[];
}

const cartStore = useCartStore();

const categories = ref<Category[]>([]);
const menuItems = ref<MenuItem[]>([]);
const selectedCategory = ref<number | null>(null);
const loadingMenu = ref(false);
const placingOrder = ref(false);

// Customer info
const customerName = ref('');
const customerPhone = ref('');

// Modal state
const showItemModal = ref(false);
const selectedItem = ref<MenuItem | null>(null);
const modalQuantity = ref(1);
const selectedModifiers = ref<Modifier[]>([]);
const modalNotes = ref('');

// Computed
const filteredMenuItems = computed(() => {
    if (selectedCategory.value === null) {
        return menuItems.value.filter(item => item.is_available);
    }
    return menuItems.value.filter(
        item => item.is_available && item.category.id === selectedCategory.value
    );
});

const canPlaceOrder = computed(() => {
    return cartStore.items.length > 0 
        && customerName.value.trim() !== '';
});

// Methods
const fetchCategories = async () => {
    try {
        const response = await api.get('/categories');
        categories.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch categories:', error);
    }
};

const fetchMenuItems = async () => {
    loadingMenu.value = true;
    try {
        const response = await api.get('/menu-items', {
            params: {
                is_available: true,
                all: true  // Get all items without pagination
            },
            headers: {
                'Cache-Control': 'no-cache'
            }
        });
        menuItems.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch menu items:', error);
    } finally {
        loadingMenu.value = false;
    }
};

const addToCart = (item: MenuItem) => {
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

const calculateModalTotal = (): number => {
    if (!selectedItem.value) return 0;
    
    const basePrice = parseFloat(selectedItem.value.price);
    const modifiersTotal = selectedModifiers.value.reduce(
        (sum, mod) => sum + parseFloat(mod.price_change),
        0
    );
    return (basePrice + modifiersTotal) * modalQuantity.value;
};

const addToCartFromModal = () => {
    if (!selectedItem.value) return;
    
    cartStore.addItem(
        selectedItem.value,
        modalQuantity.value,
        modalNotes.value,
        selectedModifiers.value
    );
    
    closeItemModal();
};

const clearCart = () => {
    if (confirm('คุณต้องการล้างรายการทั้งหมดใช่หรือไม่?')) {
        cartStore.clearCart();
    }
};

const placeOrder = async () => {
    if (!canPlaceOrder.value || placingOrder.value) return;

    placingOrder.value = true;

    try {
        const orderData = {
            order_type: 'takeaway',
            customer_name: customerName.value.trim(),
            customer_phone: customerPhone.value.trim(),
            order_items: cartStore.getOrderItems(),
        };

        const response = await api.post('/orders', orderData);

        // Clear cart and customer info
        cartStore.clearCart();
        customerName.value = '';
        customerPhone.value = '';

        // Show success message
        alert(`✅ สร้างออเดอร์สำเร็จ!\n\nลูกค้า: ${orderData.customer_name}\nออเดอร์ #${response.data.data.id}`);

        // Return to takeaway orders list
        returnToTakeawayList();

    } catch (error: any) {
        console.error('Failed to place order:', error);
        alert('❌ ไม่สามารถสร้างออเดอร์ได้ กรุณาลองใหม่อีกครั้ง\n\n' + 
              (error.response?.data?.message || 'เกิดข้อผิดพลาด'));
    } finally {
        placingOrder.value = false;
    }
};

const returnToTakeawayList = () => {
    router.visit('/takeaway');
};

onMounted(() => {
    fetchCategories();
    fetchMenuItems();
    cartStore.clearCart(); // Clear cart when entering this page
});
</script>
