<template>
  <DefaultLayout>
    <div class="p-3 sm:p-4 lg:p-6">
      <!-- Header Section -->
      <div class="mb-4 sm:mb-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4">
          <div class="min-w-0">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">จัดการเมนู</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">จัดการรายการอาหารในร้าน</p>
          </div>
          <button 
            @click="openCreateModal" 
            class="w-full sm:w-auto flex-shrink-0 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 sm:py-3 px-4 sm:px-6 rounded-lg shadow-lg transition-colors flex items-center justify-center gap-2"
          >
            <span class="text-xl">+</span>
            <span class="hidden sm:inline">เพิ่มรายการใหม่</span>
            <span class="sm:hidden">เพิ่มเมนู</span>
          </button>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-white rounded-lg shadow-md p-3 sm:p-4 mb-4 sm:mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">ค้นหา</label>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="ค้นหารายการอาหาร..." 
              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
            />
          </div>
          <div>
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">หมวดหมู่</label>
            <select 
              v-model="filterCategory" 
              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">หมวดหมู่ทั้งหมด</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
            </select>
          </div>
          <div class="sm:col-span-2 lg:col-span-1">
            <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1.5 sm:mb-2">สถานะ</label>
            <select 
              v-model="filterAvailability" 
              class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">สถานะทั้งหมด</option>
              <option value="1">พร้อมให้บริการ</option>
              <option value="0">ไม่พร้อมให้บริการ</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12 sm:py-20">
        <div class="inline-block animate-spin rounded-full h-10 w-10 sm:h-12 sm:w-12 border-b-2 border-blue-600"></div>
        <p class="text-sm sm:text-base text-gray-600 mt-4">กำลังโหลดรายการอาหาร...</p>
      </div>

      <!-- Table for Desktop, Cards for Mobile -->
      <div v-else>
        <!-- Desktop Table View (hidden on mobile) -->
        <div class="hidden md:block bg-white rounded-lg shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อรายการ</th>
                  <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">หมวดหมู่</th>
                  <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ราคา</th>
                  <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                  <th class="px-4 lg:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="filteredMenuItems.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center text-gray-500">ไม่พบรายการอาหาร</td>
                </tr>
                <tr v-for="item in filteredMenuItems" :key="item.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 lg:px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.name }}</div>
                  </td>
                  <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-900">{{ item.category?.name || 'N/A' }}</span>
                  </td>
                  <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-semibold text-gray-900">฿{{ parseFloat(item.price).toFixed(2) }}</span>
                  </td>
                  <td class="px-4 lg:px-6 py-4 whitespace-nowrap">
                    <span class="px-2 lg:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full" :class="item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ item.is_available ? 'พร้อม' : 'ไม่พร้อม' }}
                    </span>
                  </td>
                  <td class="px-4 lg:px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button @click="openEditModal(item)" class="text-blue-600 hover:text-blue-900 mr-3 lg:mr-4">แก้ไข</button>
                    <button @click="openDeleteModal(item)" class="text-red-600 hover:text-red-900">ลบ</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Mobile Card View (visible on mobile) -->
        <div class="md:hidden space-y-3">
          <div v-if="filteredMenuItems.length === 0" class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
            ไม่พบรายการอาหาร
          </div>
          
          <div
            v-for="item in filteredMenuItems"
            :key="item.id"
            class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow"
          >
            <div class="flex justify-between items-start mb-3">
              <div class="flex-1 min-w-0 mr-3">
                <h3 class="text-base font-semibold text-gray-900 truncate">{{ item.name }}</h3>
                <p class="text-sm text-gray-600 mt-0.5">{{ item.category?.name || 'N/A' }}</p>
              </div>
              <div class="flex-shrink-0">
                <span class="text-base font-bold text-blue-600">฿{{ parseFloat(item.price).toFixed(2) }}</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="item.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                {{ item.is_available ? 'พร้อมให้บริการ' : 'ไม่พร้อมให้บริการ' }}
              </span>
              
              <div class="flex gap-2">
                <button 
                  @click="openEditModal(item)" 
                  class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors"
                >
                  แก้ไข
                </button>
                <button 
                  @click="openDeleteModal(item)" 
                  class="px-3 py-1.5 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-lg transition-colors"
                >
                  ลบ
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" @click.self="closeModal">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>
      <div class="relative z-50 w-full max-w-2xl">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
          <div class="bg-blue-600 px-4 sm:px-6 py-3 sm:py-4">
            <h3 class="text-lg sm:text-xl font-semibold text-white">{{ isEditMode ? 'แก้ไขรายการอาหาร' : 'เพิ่มรายการอาหารใหม่' }}</h3>
          </div>
          <form @submit.prevent="saveMenuItem" class="px-4 sm:px-6 py-4">
            <div class="space-y-3 sm:space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">ชื่อรายการ <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" required class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="เช่น พิซซ่ามาร์เกอริต้า" />
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">หมวดหมู่ <span class="text-red-500">*</span></label>
                  <select v-model="form.category_id" required class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">เลือกหมวดหมู่</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1.5">ราคา (฿) <span class="text-red-500">*</span></label>
                  <input v-model="form.price" type="number" step="0.01" min="0" required class="w-full px-3 sm:px-4 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="0.00" />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ตัวเลือกเพิ่มเติม</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-32 sm:max-h-40 overflow-y-auto border border-gray-300 rounded-lg p-3">
                  <label v-for="modifier in modifiers" :key="modifier.id" class="flex items-center space-x-2">
                    <input type="checkbox" :value="modifier.id" v-model="form.modifier_ids" class="rounded text-blue-600 focus:ring-blue-500" />
                    <span class="text-xs sm:text-sm text-gray-700">{{ modifier.name }} ({{ parseFloat(modifier.price_change) >= 0 ? '+' : '' }}฿{{ parseFloat(modifier.price_change).toFixed(2) }})</span>
                  </label>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input type="checkbox" v-model="form.is_available" class="sr-only peer" />
                  <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  <span class="ml-3 text-sm font-medium text-gray-700">{{ form.is_available ? 'พร้อมให้บริการ' : 'ไม่พร้อมให้บริการ' }}</span>
                </label>
              </div>
            </div>
            <div v-if="errorMessage" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
            <div class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
              <button type="button" @click="closeModal" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">ยกเลิก</button>
              <button type="submit" :disabled="saving" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors disabled:bg-gray-400">
                {{ saving ? 'กำลังบันทึก...' : isEditMode ? 'อัปเดตรายการ' : 'เพิ่มรายการ' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" @click.self="closeDeleteModal">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeDeleteModal"></div>
      <div class="relative z-50 w-full max-w-md">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
          <div class="bg-red-600 px-4 sm:px-6 py-3 sm:py-4">
            <h3 class="text-lg sm:text-xl font-semibold text-white">ลบรายการอาหาร</h3>
          </div>
          <div class="px-4 sm:px-6 py-4">
            <p class="text-sm sm:text-base text-gray-700">คุณต้องการลบ <strong>{{ itemToDelete?.name }}</strong> ใช่หรือไม่?</p>
            <p class="text-xs sm:text-sm text-gray-500 mt-2">การกระทำนี้ไม่สามารถยกเลิกได้</p>
          </div>
          <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 sm:gap-3">
            <button type="button" @click="closeDeleteModal" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-colors">ยกเลิก</button>
            <button @click="confirmDelete" :disabled="deleting" class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors disabled:bg-gray-400">
              {{ deleting ? 'กำลังลบ...' : 'ลบ' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
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
  is_available: boolean;
  category_id: number;
  category?: Category;
  modifiers?: Modifier[];
}

interface MenuItemForm {
  name: string;
  category_id: number | string;
  price: number | string;
  is_available: boolean;
  modifier_ids: number[];
}

const menuItems = ref<MenuItem[]>([]);
const categories = ref<Category[]>([]);
const modifiers = ref<Modifier[]>([]);
const loading = ref(true);
const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditMode = ref(false);
const saving = ref(false);
const deleting = ref(false);
const errorMessage = ref('');
const itemToDelete = ref<MenuItem | null>(null);
const editingItemId = ref<number | null>(null);

const searchQuery = ref('');
const filterCategory = ref<number | string>('');
const filterAvailability = ref<string>('');

const form = ref<MenuItemForm>({
  name: '',
  category_id: '',
  price: '',
  is_available: true,
  modifier_ids: [],
});

const filteredMenuItems = computed(() => {
  let filtered = [...menuItems.value];
  
  // Search by name
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter((item) =>
      item.name.toLowerCase().includes(query)
    );
  }
  
  // Filter by category
  if (filterCategory.value !== '') {
    const categoryId = Number(filterCategory.value);
    filtered = filtered.filter((item) => item.category_id === categoryId);
  }
  
  // Filter by availability
  if (filterAvailability.value !== '') {
    const isAvailable = filterAvailability.value === '1';
    filtered = filtered.filter((item) => {
      // Handle both boolean and number (1/0) values
      const itemAvailable = typeof item.is_available === 'boolean' 
        ? item.is_available 
        : Boolean(item.is_available);
      return itemAvailable === isAvailable;
    });
  }
  
  return filtered;
});

const fetchMenuItems = async () => {
  try {
    loading.value = true;
    const response = await api.get('/menu-items', {
      params: {
        per_page: 1000 // Get all items
      }
    });
    
    // Handle both paginated and non-paginated responses
    let items: any[] = [];
    if (response.data.data && Array.isArray(response.data.data)) {
      items = response.data.data;
    } else if (Array.isArray(response.data)) {
      items = response.data;
    } else {
      console.error('Unexpected menu items response format:', response.data);
      menuItems.value = [];
      return;
    }
    
    // Transform items to ensure category_id is available
    menuItems.value = items.map(item => ({
      ...item,
      category_id: item.category_id || item.category?.id
    }));
  } catch (error: any) {
    console.error('Failed to fetch menu items:', error.response?.data || error.message);
    alert('Failed to load menu items. Please try again.');
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const response = await api.get('/categories');
    categories.value = response.data.data || [];
  } catch (error: any) {
    console.error('Failed to fetch categories:', error.response?.data || error.message);
  }
};

const fetchModifiers = async () => {
  try {
    const response = await api.get('/modifiers');
    modifiers.value = response.data.data || [];
  } catch (error: any) {
    console.error('Failed to fetch modifiers:', error.response?.data || error.message);
  }
};

const openCreateModal = () => {
  isEditMode.value = false;
  editingItemId.value = null;
  resetForm();
  showModal.value = true;
};

const openEditModal = (item: MenuItem) => {
  isEditMode.value = true;
  editingItemId.value = item.id;
  form.value = {
    name: item.name,
    category_id: item.category_id,
    price: parseFloat(item.price),
    is_available: item.is_available,
    modifier_ids: item.modifiers ? item.modifiers.map((m) => m.id) : [],
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  errorMessage.value = '';
  resetForm();
};

const resetForm = () => {
  form.value = {
    name: '',
    category_id: '',
    price: '',
    is_available: true,
    modifier_ids: [],
  };
};

const saveMenuItem = async () => {
  errorMessage.value = '';
  saving.value = true;
  try {
    const payload = {
      ...form.value,
      category_id: Number(form.value.category_id),
      price: parseFloat(form.value.price.toString()),
    };
    if (isEditMode.value && editingItemId.value) {
      await api.put(`/menu-items/${editingItemId.value}`, payload);
    } else {
      await api.post('/menu-items', payload);
    }
    await fetchMenuItems();
    closeModal();
  } catch (error: any) {
    console.error('Failed to save menu item:', error);
    errorMessage.value = error.response?.data?.message || 'Failed to save menu item. Please try again.';
  } finally {
    saving.value = false;
  }
};

const openDeleteModal = (item: MenuItem) => {
  itemToDelete.value = item;
  showDeleteModal.value = true;
};

const closeDeleteModal = () => {
  showDeleteModal.value = false;
  itemToDelete.value = null;
};

const confirmDelete = async () => {
  if (!itemToDelete.value) return;
  deleting.value = true;
  try {
    await api.delete(`/menu-items/${itemToDelete.value.id}`);
    await fetchMenuItems();
    closeDeleteModal();
  } catch (error: any) {
    console.error('Failed to delete menu item:', error.response?.data || error.message);
    alert('Failed to delete menu item. Please try again.');
  } finally {
    deleting.value = false;
  }
};

onMounted(async () => {
  await Promise.all([
    fetchMenuItems(),
    fetchCategories(),
    fetchModifiers(),
  ]);
});
</script>
