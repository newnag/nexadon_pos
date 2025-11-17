<template>
  <DefaultLayout>
    <div class="min-h-screen bg-gray-100 p-6">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">จอแสดงผลครัว</h1>
        <p class="text-gray-600 mt-1">จัดการออเดอร์แบบเรียลไทม์</p>
      </div>

      <!-- Connection Status -->
      <div v-if="!isConnected" class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-yellow-800 text-sm">⚠️ กำลังเชื่อมต่อกับจอแสดงผลครัว...</p>
      </div>

      <div v-else class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 text-sm">✓ เชื่อมต่อกับจอแสดงผลครัวแล้ว</p>
      </div>

      <!-- Orders Grid -->
      <div v-if="orders.length === 0" class="text-center py-20">
        <div class="text-gray-400 text-6xl mb-4">🍽️</div>
        <h3 class="text-xl font-semibold text-gray-600 mb-2">ไม่มีออเดอร์ที่กำลังดำเนินการ</h3>
        <p class="text-gray-500">ออเดอร์จะปรากฏที่นี่เมื่อลูกค้าสั่งอาหาร</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="order in sortedOrders"
          :key="order.order_id"
          class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 hover:shadow-xl"
          :class="{
            'ring-2 ring-blue-400': hasStatus(order, 'pending'),
            'ring-2 ring-orange-400': hasStatus(order, 'cooking'),
            'ring-2 ring-green-400': allItemsReady(order)
          }"
        >
          <!-- Order Header -->
          <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4"
            :class="{
              'from-orange-600 to-orange-700': order.order_type === 'takeaway'
            }"
          >
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-3xl font-bold mb-1">
                  <template v-if="order.order_type === 'takeaway'">
                    🥡 {{ order.customer_name }}
                  </template>
                  <template v-else>
                    โต๊ะ {{ order.table?.table_number || '-' }}
                  </template>
                </h3>
                <p class="text-blue-100 text-sm" v-if="order.order_type === 'takeaway'">{{ order.customer_phone }}</p>
                <p class="text-blue-100 text-sm">ออเดอร์ #{{ order.order_id }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm text-blue-100">{{ formatTime(order.created_at) }}</p>
                <p class="text-xs text-blue-200">{{ timeAgo(order.created_at) }}</p>
              </div>
            </div>
            <p class="text-sm text-blue-100 mt-2">พนักงาน: {{ order.waiter.name }}</p>
          </div>

          <!-- Order Items -->
          <div class="p-4 space-y-3 max-h-96 overflow-y-auto">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="border rounded-lg p-3 transition-colors"
              :class="{
                'bg-gray-50 border-gray-300': item.status === 'pending',
                'bg-orange-50 border-orange-300': item.status === 'cooking',
                'bg-green-50 border-green-300': item.status === 'ready'
              }"
            >
              <!-- Item Header -->
              <div class="flex justify-between items-start mb-2">
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-900">{{ item.menu_item.name }}</span>
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-0.5 rounded">
                      × {{ item.quantity }}
                    </span>
                  </div>
                  <p class="text-xs text-gray-500 mt-1">{{ item.menu_item.category }}</p>
                </div>
                <span
                  class="text-xs font-semibold px-2 py-1 rounded"
                  :class="{
                    'bg-gray-200 text-gray-700': item.status === 'pending',
                    'bg-orange-200 text-orange-700': item.status === 'cooking',
                    'bg-green-200 text-green-700': item.status === 'ready'
                  }"
                >
                  {{ item.status.toUpperCase() }}
                </span>
              </div>

              <!-- Modifiers -->
              <div v-if="item.modifiers && item.modifiers.length > 0" class="mb-2">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="modifier in item.modifiers"
                    :key="modifier.id"
                    class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded"
                  >
                    + {{ modifier.name }}
                  </span>
                </div>
              </div>

              <!-- Notes -->
              <div v-if="item.notes" class="mb-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-sm">
                <p class="text-xs font-semibold text-yellow-800 mb-1">📝 หมายเหตุ:</p>
                <p class="text-yellow-900">{{ item.notes }}</p>
              </div>

              <!-- Status Buttons -->
              <div class="flex gap-2 mt-3">
                <button
                  v-if="item.status === 'pending'"
                  @click="updateItemStatus(item.id, 'cooking')"
                  :disabled="updatingItems.has(item.id)"
                  class="flex-1 bg-orange-500 hover:bg-orange-600 disabled:bg-gray-300 text-white text-sm font-medium py-2 px-3 rounded transition-colors"
                >
                  {{ updatingItems.has(item.id) ? '...' : '🔥 เริ่มทำ' }}
                </button>

                <button
                  v-if="item.status === 'cooking'"
                  @click="updateItemStatus(item.id, 'ready')"
                  :disabled="updatingItems.has(item.id)"
                  class="flex-1 bg-green-500 hover:bg-green-600 disabled:bg-gray-300 text-white text-sm font-medium py-2 px-3 rounded transition-colors"
                >
                  {{ updatingItems.has(item.id) ? '...' : '✓ เสร็จแล้ว' }}
                </button>

                <button
                  v-if="item.status === 'ready'"
                  class="flex-1 bg-gray-200 text-gray-500 text-sm font-medium py-2 px-3 rounded cursor-not-allowed"
                  disabled
                >
                  ✓ พร้อมเสิร์ฟ
                </button>
              </div>
            </div>
          </div>

          <!-- Order Footer -->
          <div
            v-if="allItemsReady(order)"
            class="bg-green-100 border-t border-green-200 p-3 text-center"
          >
            <p class="text-green-800 font-semibold text-sm">✓ ทุกรายการพร้อมเสิร์ฟแล้ว!</p>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import DefaultLayout from '@/layouts/DefaultLayout.vue';
import echo from '@/lib/echo';
import api from '@/lib/api';

interface Modifier {
  id: number;
  name: string;
}

interface MenuItem {
  id: number;
  name: string;
  category: string;
}

interface OrderItem {
  id: number;
  menu_item: MenuItem;
  quantity: number;
  notes: string | null;
  modifiers: Modifier[];
  status: 'pending' | 'cooking' | 'ready';
}

interface Table {
  id: number;
  number: string;
}

interface Waiter {
  id: number;
  name: string;
}

interface Order {
  order_id: number;
  table: Table;
  waiter: Waiter;
  status: string;
  total_amount: string;
  items: OrderItem[];
  created_at: string;
}

const orders = ref<Order[]>([]);
const isConnected = ref(false);
const updatingItems = ref(new Set<number>());
const previousItemIds = ref<Set<number>>(new Set()); // Track item IDs to detect new items

// Computed: Sort orders by status priority (pending first, then cooking, then ready)
const sortedOrders = computed(() => {
  return [...orders.value].sort((a, b) => {
    const getOrderPriority = (order: Order) => {
      if (hasStatus(order, 'pending')) return 1;
      if (hasStatus(order, 'cooking')) return 2;
      if (allItemsReady(order)) return 3;
      return 4;
    };

    const priorityA = getOrderPriority(a);
    const priorityB = getOrderPriority(b);

    if (priorityA !== priorityB) {
      return priorityA - priorityB;
    }

    // If same priority, sort by time (newest first)
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  }).map(order => ({
    ...order,
    // Sort items within each order: pending first, then cooking, then ready
    items: [...order.items].sort((a, b) => {
      const statusPriority = { pending: 1, cooking: 2, ready: 3 };
      return statusPriority[a.status] - statusPriority[b.status];
    })
  }));
});

// Check if order has any items with specific status
const hasStatus = (order: Order, status: string): boolean => {
  return order.items.some((item) => item.status === status);
};

// Check if all items in order are ready
const allItemsReady = (order: Order): boolean => {
  return order.items.length > 0 && order.items.every((item) => item.status === 'ready');
};

// Format time for display
const formatTime = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

// Calculate time ago
const timeAgo = (dateString: string): string => {
  const now = new Date().getTime();
  const created = new Date(dateString).getTime();
  const diffMinutes = Math.floor((now - created) / 60000);

  if (diffMinutes < 1) return 'เมื่อสักครู่';
  if (diffMinutes === 1) return '1 นาทีที่แล้ว';
  if (diffMinutes < 60) return `${diffMinutes} นาทีที่แล้ว`;

  const diffHours = Math.floor(diffMinutes / 60);
  if (diffHours === 1) return '1 ชั่วโมงที่แล้ว';
  return `${diffHours} ชั่วโมงที่แล้ว`;
};

// Update item status via API
const updateItemStatus = async (itemId: number, newStatus: 'cooking' | 'ready') => {
  if (updatingItems.value.has(itemId)) return;

  updatingItems.value.add(itemId);

  try {
    const response = await api.put(`/order-items/${itemId}/status`, {
      status: newStatus,
    });
    
    console.log('Status update success:', response.data);

    // Update local state immediately for responsive UI
    orders.value = orders.value.map((order) => ({
      ...order,
      items: order.items.map((item) =>
        item.id === itemId ? { ...item, status: newStatus } : item
      ),
    }));

    // Show success feedback
    console.log(`✓ Item ${itemId} status updated to ${newStatus}`);

    // Refresh data from server to ensure consistency
    await fetchActiveOrders();

    // Remove completed orders after 5 minutes
    if (newStatus === 'ready') {
      setTimeout(() => {
        removeOrderIfAllReady(itemId);
      }, 5 * 60 * 1000); // 5 minutes
    }
  } catch (error: any) {
    console.error('Failed to update item status:', error.response?.data || error.message);
    alert(`ไม่สามารถอัปเดตสถานะได้: ${error.response?.data?.message || error.message}`);
    
    // Refresh on error to sync with server state
    await fetchActiveOrders();
  } finally {
    updatingItems.value.delete(itemId);
  }
};

// Remove order if all items are ready (auto-cleanup)
const removeOrderIfAllReady = (itemId: number) => {
  const order = orders.value.find((o) => o.items.some((item) => item.id === itemId));
  if (order && allItemsReady(order)) {
    orders.value = orders.value.filter((o) => o.order_id !== order.order_id);
  }
};

// Fetch active orders on mount
const fetchActiveOrders = async () => {
  try {
    const response = await api.get('/orders/active');
    
    const previousCount = orders.value.length;
    
    // Transform API response to match our Order interface
    const newOrders = response.data.data.map((order: any) => ({
      order_id: order.id,
      table: order.table,
      waiter: order.user,
      status: order.status,
      total_amount: order.total_amount,
      items: order.order_items.map((item: any) => ({
        id: item.id,
        menu_item: {
          id: item.menu_item.id,
          name: item.menu_item.name,
          category: item.menu_item.category?.name || 'Other',
        },
        quantity: item.quantity,
        notes: item.notes,
        modifiers: item.modifiers || [],
        status: item.status || 'pending',
      })),
      created_at: order.created_at,
    }));
    
    // Check for new items in existing orders
    let hasNewItems = false;
    if (previousItemIds.value.size > 0) {
      newOrders.forEach((order: Order) => {
        order.items.forEach((item: OrderItem) => {
          if (!previousItemIds.value.has(item.id)) {
            hasNewItems = true;
            console.log(`New item detected: ${item.menu_item.name} (ID: ${item.id})`);
          }
        });
      });
    }
    
    // Update orders
    orders.value = newOrders;
    
    // Update item IDs tracking
    const newItemIds = new Set<number>();
    orders.value.forEach(order => {
      order.items.forEach(item => newItemIds.add(item.id));
    });
    previousItemIds.value = newItemIds;
    
    // Play sound if new orders arrived (only when using polling)
    const currentCount = orders.value.length;
    if (previousCount > 0 && currentCount > previousCount) {
      console.log('New order detected via polling!');
      playNotificationSound();
    }
    
    // Play sound if new items added to existing orders
    if (hasNewItems) {
      console.log('New items detected in existing orders!');
      playNotificationSound();
    }
    
    // Mark as connected after successful data fetch
    isConnected.value = true;
  } catch (error) {
    console.error('Failed to fetch active orders:', error);
    isConnected.value = false;
  }
};

// Setup real-time order listening
onMounted(() => {
  // Fetch existing active orders (this will set isConnected = true on success)
  fetchActiveOrders();

  // Listen to kitchen channel for new orders (only if WebSocket is enabled)
  if (echo) {
    echo
      .private('kitchen-channel')
      .listen('.order.placed', (event: Order) => {
        console.log('New order received:', event);

        // Add new order to the list
        orders.value.push(event);

        // Optional: Play notification sound
        playNotificationSound();
      })
      .error((error: any) => {
        console.error('Echo connection error:', error);
        isConnected.value = false;
      });
    
    console.info('WebSocket enabled. Listening for real-time updates.');
  } else {
    console.info('WebSocket not enabled. Using polling for updates.');
    // Fallback to polling - refresh every 10 seconds
    setInterval(() => {
      fetchActiveOrders();
    }, 10000);
  }
});

// Cleanup on unmount
onUnmounted(() => {
  if (echo) {
    echo.leave('kitchen-channel');
  }
});

// Play notification sound (optional)
const playNotificationSound = () => {
  console.log('🔊 Attempting to play sound...');
  
  try {
    const AudioContextClass = window.AudioContext || (window as any).webkitAudioContext;
    const audioContext = new AudioContextClass();
    
    const playBeep = (freq: number, dur: number, delay: number = 0) => {
      setTimeout(() => {
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        osc.connect(gain);
        gain.connect(audioContext.destination);
        osc.frequency.value = freq;
        const now = audioContext.currentTime;
        gain.gain.setValueAtTime(0.3, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + dur);
        osc.start(now);
        osc.stop(now + dur);
        console.log(`Beep: ${freq}Hz`);
      }, delay);
    };
    
    playBeep(880, 0.2, 0);
    playBeep(1108, 0.2, 250);
    console.log('✓ Sound played');
  } catch (e) {
    console.error('Sound error:', e);
  }
};
</script>
