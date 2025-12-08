import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface MenuItem {
    id: number;
    name: string;
    price: string;
    is_available: boolean;
    category: {
        id: number;
        name: string;
    };
    modifiers?: Modifier[];
}

export interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

export interface CartItem {
    orderItemId?: number; // Optional ID for existing order items
    menuItem: MenuItem;
    quantity: number;
    notes: string;
    selectedModifiers: Modifier[];
    subtotal: number;
}

export const useCartStore = defineStore('cart', () => {
    // State
    const items = ref<CartItem[]>([]);
    const selectedTable = ref<number | null>(null);

    // Getters
    const itemCount = computed(() => {
        return items.value.reduce((total, item) => total + item.quantity, 0);
    });

    const totalAmount = computed(() => {
        return items.value.reduce((total, item) => total + item.subtotal, 0);
    });

    const isEmpty = computed(() => items.value.length === 0);

    // Calculate subtotal for a cart item
    const calculateSubtotal = (item: CartItem): number => {
        const basePrice = parseFloat(item.menuItem.price);
        const modifiersTotal = item.selectedModifiers.reduce(
            (sum, mod) => sum + parseFloat(mod.price_change),
            0
        );
        return (basePrice + modifiersTotal) * item.quantity;
    };

    // Actions
    const addItem = (
        menuItem: MenuItem,
        quantity: number = 1,
        notes: string = '',
        selectedModifiers: Modifier[] = [],
        orderItemId?: number
    ) => {
        const newItem: CartItem = {
            orderItemId,
            menuItem,
            quantity,
            notes,
            selectedModifiers,
            subtotal: 0,
        };
        newItem.subtotal = calculateSubtotal(newItem);
        items.value.push(newItem);
    };

    const updateItem = (index: number, updates: Partial<CartItem>) => {
        if (index >= 0 && index < items.value.length) {
            items.value[index] = { ...items.value[index], ...updates };
            items.value[index].subtotal = calculateSubtotal(items.value[index]);
        }
    };

    const removeItem = (index: number) => {
        if (index >= 0 && index < items.value.length) {
            items.value.splice(index, 1);
        }
    };

    const increaseQuantity = (index: number) => {
        if (index >= 0 && index < items.value.length) {
            items.value[index].quantity++;
            items.value[index].subtotal = calculateSubtotal(items.value[index]);
        }
    };

    const decreaseQuantity = (index: number) => {
        if (index >= 0 && index < items.value.length) {
            if (items.value[index].quantity > 1) {
                items.value[index].quantity--;
                items.value[index].subtotal = calculateSubtotal(items.value[index]);
            } else {
                removeItem(index);
            }
        }
    };

    const setTable = (tableId: number) => {
        selectedTable.value = tableId;
    };

    const clearCart = () => {
        items.value = [];
        selectedTable.value = null;
    };

    // Format items for API request
    const getOrderItems = () => {
        return items.value.map((item) => ({
            order_item_id: item.orderItemId,
            menu_item_id: item.menuItem.id,
            quantity: item.quantity,
            notes: item.notes,
            modifier_ids: item.selectedModifiers.map((mod) => mod.id),
        }));
    };

    return {
        // State
        items,
        selectedTable,

        // Getters
        itemCount,
        totalAmount,
        isEmpty,

        // Actions
        addItem,
        updateItem,
        removeItem,
        increaseQuantity,
        decreaseQuantity,
        setTable,
        clearCart,
        getOrderItems,
    };
});
