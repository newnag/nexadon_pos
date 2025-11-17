<template>
    <DefaultLayout>
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <button
                        @click="returnToTables"
                        class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        กลับไปที่โต๊ะ
                    </button>
                    <h1 class="text-3xl font-bold text-gray-900">ชำระเงิน</h1>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <p class="mt-4 text-gray-600">กำลังโหลดข้อมูลออเดอร์...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาดในการโหลดออเดอร์</h3>
                            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Content -->
                <div v-else-if="order" class="space-y-6">
                    <!-- Order Info Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">หมายเลขออเดอร์</p>
                                <p class="text-lg font-semibold text-gray-900">#{{ order.id }}</p>
                            </div>
                            <div v-if="order.order_type === 'dine-in' && order.table">
                                <p class="text-sm text-gray-600">โต๊ะ</p>
                                <p class="text-lg font-semibold text-gray-900">{{ order.table.table_number }}</p>
                            </div>
                            <div v-else-if="order.order_type === 'takeaway'">
                                <p class="text-sm text-gray-600">ชื่อลูกค้า</p>
                                <p class="text-lg font-semibold text-gray-900">{{ order.customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">พนักงาน</p>
                                <p class="text-lg font-semibold text-gray-900">{{ order.user.name }}</p>
                            </div>
                            <div v-if="order.order_type === 'takeaway'">
                                <p class="text-sm text-gray-600">เบอร์โทร</p>
                                <p class="text-lg font-semibold text-gray-900">{{ order.customer_phone }}</p>
                            </div>
                            <div :class="order.order_type === 'takeaway' ? 'col-span-2' : ''">
                                <p class="text-sm text-gray-600">วันที่ & เวลา</p>
                                <p class="text-lg font-semibold text-gray-900">{{ formatDateTime(order.created_at) }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-4 flex gap-2">
                            <span
                                :class="[
                                    'inline-block px-3 py-1 text-sm font-semibold rounded-full',
                                    order.order_type === 'takeaway' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800'
                                ]"
                            >
                                {{ order.order_type === 'takeaway' ? '🥡 กลับบ้าน' : '🍽️ ทานที่ร้าน' }}
                            </span>
                            <span
                                :class="[
                                    'inline-block px-3 py-1 text-sm font-semibold rounded-full',
                                    order.status === 'completed' ? 'bg-green-100 text-green-800' :
                                    order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                    'bg-gray-100 text-gray-800'
                                ]"
                            >
                                {{ capitalizeStatus(order.status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Order Items Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">รายการอาหาร</h2>
                        </div>
                        <div class="divide-y divide-gray-200">
                            <div
                                v-for="item in order.order_items"
                                :key="item.id"
                                class="px-6 py-4"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900">
                                            {{ item.menu_item.name }}
                                            <span class="text-gray-500 font-normal">× {{ item.quantity }}</span>
                                        </h3>
                                        <p class="text-sm text-gray-600">{{ item.menu_item.category.name }}</p>

                                        <!-- Modifiers -->
                                        <div v-if="item.modifiers && item.modifiers.length > 0" class="mt-1">
                                            <p class="text-xs text-gray-500">
                                                ตัวเลือกเพิ่มเติม:
                                                <span v-for="(mod, index) in item.modifiers" :key="mod.id">
                                                    {{ mod.name }} (+฿{{ mod.price_change }}){{ index < item.modifiers.length - 1 ? ', ' : '' }}
                                                </span>
                                            </p>
                                        </div>

                                        <!-- Notes -->
                                        <div v-if="item.notes" class="mt-1">
                                            <p class="text-xs text-gray-500 italic">หมายเหตุ: {{ item.notes }}</p>
                                        </div>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-lg font-semibold text-gray-900">฿{{ item.subtotal }}</p>
                                        <p class="text-xs text-gray-500">฿{{ item.menu_item.price }} ต่อรายการ</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Split Bill Feature -->
                    <div class="bg-blue-50 rounded-lg shadow-md p-6 border border-blue-200">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                แบ่งบิล
                            </h2>
                            <label class="inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="splitBillEnabled"
                                    class="sr-only peer"
                                />
                                <div class="relative w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div v-if="splitBillEnabled" class="space-y-3">
                            <div class="flex items-center space-x-4">
                                <label class="text-sm font-medium text-gray-700 min-w-[100px]">จำนวนคน:</label>
                                <div class="flex items-center space-x-2">
                                    <button
                                        @click="splitCount = Math.max(2, splitCount - 1)"
                                        class="w-8 h-8 bg-white border border-gray-300 rounded-full hover:bg-gray-100 flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="text-xl font-bold text-gray-900 w-12 text-center">{{ splitCount }}</span>
                                    <button
                                        @click="splitCount++"
                                        class="w-8 h-8 bg-blue-600 text-white rounded-full hover:bg-blue-700 flex items-center justify-center"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-4 border border-blue-300">
                                <p class="text-sm text-gray-600 mb-2">ยอดเงินต่อคน:</p>
                                <p class="text-3xl font-bold text-blue-600">฿{{ amountPerPerson.toFixed(2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ splitCount }} คน × ฿{{ amountPerPerson.toFixed(2) }} = ฿{{ finalTotal.toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Summary Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">สรุปราคา</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-700">
                                <span>ราคาสินค้า</span>
                                <span class="font-semibold">฿{{ subtotal.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>ภาษีมูลค่าเพิ่ม (7%)</span>
                                <span class="font-semibold">฿{{ vatAmount.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-700">
                                <span>ค่าบริการ (10%)</span>
                                <span class="font-semibold">฿{{ serviceCharge.toFixed(2) }}</span>
                            </div>
                            <div class="border-t-2 border-gray-300 pt-3 flex justify-between text-xl font-bold text-gray-900">
                                <span>ยอดรวมทั้งสิ้น</span>
                                <span class="text-green-600">฿{{ finalTotal.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods Card -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">เลือกวิธีชำระเงิน</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <button
                                v-for="method in paymentMethods"
                                :key="method.value"
                                @click="selectPaymentMethod(method.value)"
                                :disabled="processing || !!order.payment"
                                :class="[
                                    'p-6 border-2 rounded-lg transition-all duration-200',
                                    selectedPaymentMethod === method.value
                                        ? 'border-blue-600 bg-blue-50 shadow-md'
                                        : 'border-gray-300 hover:border-blue-400 hover:shadow-md',
                                    processing || order.payment ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                                ]"
                            >
                                <div class="flex flex-col items-center">
                                    <div
                                        :class="[
                                            'w-16 h-16 rounded-full flex items-center justify-center mb-3',
                                            selectedPaymentMethod === method.value ? 'bg-blue-600' : 'bg-gray-200'
                                        ]"
                                    >
                                        <svg
                                            class="w-8 h-8"
                                            :class="selectedPaymentMethod === method.value ? 'text-white' : 'text-gray-600'"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="method.icon" />
                                        </svg>
                                    </div>
                                    <span
                                        :class="[
                                            'font-semibold',
                                            selectedPaymentMethod === method.value ? 'text-blue-600' : 'text-gray-900'
                                        ]"
                                    >
                                        {{ method.label }}
                                    </span>
                                </div>
                            </button>
                        </div>

                        <!-- Cash Payment Input -->
                        <div v-if="selectedPaymentMethod === 'cash' && !order.payment" class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                เงินที่รับมา (Cash Received)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg">฿</span>
                                <input
                                    type="number"
                                    v-model.number="cashReceived"
                                    @input="calculateChange"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg text-lg font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            
                            <!-- Quick Amount Buttons -->
                            <div class="grid grid-cols-4 gap-2 mt-3">
                                <button
                                    v-for="amount in quickAmounts"
                                    :key="amount"
                                    @click="cashReceived = amount; calculateChange()"
                                    type="button"
                                    class="px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition"
                                >
                                    ฿{{ amount }}
                                </button>
                                <button
                                    @click="cashReceived = finalTotal; calculateChange()"
                                    type="button"
                                    class="px-3 py-2 bg-green-100 border border-green-300 rounded-lg text-sm font-medium hover:bg-green-200 transition"
                                >
                                    พอดี
                                </button>
                            </div>

                            <!-- Change Display -->
                            <div v-if="cashReceived > 0" class="mt-4 p-4 rounded-lg" :class="changeAmount >= 0 ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium" :class="changeAmount >= 0 ? 'text-green-800' : 'text-red-800'">
                                        {{ changeAmount >= 0 ? 'เงินทอน (Change):' : 'เงินไม่พอ (Insufficient):' }}
                                    </span>
                                    <span class="text-2xl font-bold" :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-600'">
                                        ฿{{ Math.abs(changeAmount).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Already Paid Notice -->
                        <div v-if="order.payment" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-green-800">ชำระเงินสำเร็จแล้ว</p>
                                    <p class="text-xs text-green-700">
                                        ชำระผ่าน {{ formatPaymentMethodThai(order.payment.payment_method) }} - ฿{{ order.payment.amount }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Process Payment Button -->
                        <button
                            v-else
                            @click="processPayment"
                            :disabled="!selectedPaymentMethod || processing || (selectedPaymentMethod === 'cash' && (cashReceived <= 0 || cashReceived < finalTotal))"
                            class="w-full mt-6 px-6 py-4 bg-green-600 text-white text-lg font-semibold rounded-lg hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition shadow-lg"
                        >
                            <span v-if="!processing">
                                {{ selectedPaymentMethod === 'cash' && cashReceived >= finalTotal 
                                    ? `ดำเนินการชำระเงิน - รับ ฿${cashReceived.toFixed(2)} ทอน ฿${changeAmount.toFixed(2)}` 
                                    : `ดำเนินการชำระเงิน - ฿${finalTotal.toFixed(2)}` 
                                }}
                            </span>
                            <span v-else class="flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                กำลังดำเนินการ...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Modal -->
        <Teleport to="body">
            <div
                v-if="showSuccessModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="closeSuccessModal"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-8 text-center">
                    <!-- Success Icon -->
                    <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <!-- Success Message -->
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">ชำระเงินสำเร็จ!</h3>
                    <p class="text-gray-600 mb-6">
                        ชำระเงินจำนวน ฿{{ finalTotal.toFixed(2) }} เรียบร้อยแล้ว
                    </p>

                    <!-- Order Details -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">หมายเลขออเดอร์:</span>
                            <span class="font-semibold text-gray-900">#{{ order?.id }}</span>
                        </div>
                        <div v-if="order?.order_type === 'dine-in' && order?.table" class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">โต๊ะ:</span>
                            <span class="font-semibold text-gray-900">{{ order.table.table_number }}</span>
                        </div>
                        <div v-else-if="order?.order_type === 'takeaway'" class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">ลูกค้า:</span>
                            <span class="font-semibold text-gray-900">{{ order.customer_name }} ({{ order.customer_phone }})</span>
                        </div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-600">วิธีชำระเงิน:</span>
                            <span class="font-semibold text-gray-900">{{ formatPaymentMethodThai(selectedPaymentMethod) }}</span>
                        </div>
                        <div v-if="selectedPaymentMethod === 'cash' && cashReceived > 0" class="border-t pt-2 mt-2">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600">เงินที่รับมา:</span>
                                <span class="font-semibold text-gray-900">฿{{ cashReceived.toFixed(2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">เงินทอน:</span>
                                <span class="font-semibold text-green-600">฿{{ changeAmount.toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <!-- <button
                            @click="printReceipt"
                            class="w-full px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                        >
                            พิมพ์ใบเสร็จ
                        </button> -->
                        <button
                            @click="returnToTables"
                            class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition"
                        >
                            กลับไปที่โต๊ะ
                        </button>
                    </div>
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
                    <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <!-- Alert Message -->
                    <div class="text-center mb-6">
                        <p class="text-lg text-gray-800 whitespace-pre-line">{{ alertMessage }}</p>
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

        <!-- Confirm Modal -->
        <Teleport to="body">
            <div
                v-if="showConfirmModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                @click.self="handleCancelConfirm"
            >
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <!-- Confirm Icon -->
                    <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <!-- Confirm Message -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">ยืนยันการดำเนินการ</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ confirmMessage }}</p>
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
                            class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition"
                        >
                            ยืนยัน
                        </button>
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
import api from '@/lib/api';

interface MenuItem {
    id: number;
    name: string;
    price: string;
    category: {
        id: number;
        name: string;
    };
}

interface Modifier {
    id: number;
    name: string;
    price_change: string;
}

interface OrderItem {
    id: number;
    quantity: number;
    notes: string | null;
    subtotal: string;
    menu_item: MenuItem;
    modifiers: Modifier[];
}

interface Order {
    id: number;
    status: string;
    order_type: 'dine-in' | 'takeaway';
    customer_name?: string;
    customer_phone?: string;
    total_amount: string;
    created_at: string;
    table?: {
        id: number;
        table_number: string;
        status: string;
    };
    user: {
        id: number;
        name: string;
        role: string;
    };
    order_items: OrderItem[];
    payment?: {
        id: number;
        payment_method: string;
        amount: string;
    };
}

// Get order ID from URL
const getOrderIdFromUrl = (): string | null => {
    if (typeof window !== 'undefined') {
        const pathParts = window.location.pathname.split('/');
        const lastPart = pathParts[pathParts.length - 1];
        
        // Check if last part is a valid number (order ID)
        if (lastPart && !isNaN(Number(lastPart)) && lastPart !== 'billing') {
            return lastPart;
        }
    }
    return null;
};

// State
const order = ref<Order | null>(null);
const loading = ref(false);
const error = ref('');
const processing = ref(false);
const selectedPaymentMethod = ref('');
const showSuccessModal = ref(false);
const splitBillEnabled = ref(false);
const splitCount = ref(2);

// Alert/Confirm Modal states
const showAlertModal = ref(false);
const showConfirmModal = ref(false);
const alertMessage = ref('');
const confirmMessage = ref('');
const confirmCallback = ref<(() => void) | null>(null);

// Cash payment states
const cashReceived = ref(0);
const changeAmount = ref(0);

// Quick amount buttons (common banknotes)
const quickAmounts = computed(() => {
    const total = finalTotal.value;
    return [
        100,
        500,
        1000,
        Math.ceil(total / 100) * 100, // Round up to nearest 100
    ].filter((amount, index, arr) => arr.indexOf(amount) === index).sort((a, b) => a - b);
});

// Payment methods
const paymentMethods = [
    {
        value: 'cash',
        label: 'เงินสด',
        icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z',
    },
    {
        value: 'qr_payment',
        label: 'QR Payment',
        icon: 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
    },
];

// Computed: Price calculations
const subtotal = computed(() => {
    if (!order.value) return 0;
    // Order total already includes everything, so we need to reverse calculate
    // Assuming: subtotal + VAT(7%) + Service(10%) = total
    // total = subtotal * 1.17 (7% VAT + 10% service on subtotal)
    // subtotal = total / 1.17
    const total = parseFloat(order.value.total_amount);
    return total / 1.17;
});

const vatAmount = computed(() => {
    return subtotal.value * 0.07; // 7% VAT
});

const serviceCharge = computed(() => {
    return subtotal.value * 0.10; // 10% service charge
});

const finalTotal = computed(() => {
    return subtotal.value + vatAmount.value + serviceCharge.value;
});

const amountPerPerson = computed(() => {
    return finalTotal.value / splitCount.value;
});

// Fetch order details
const fetchOrderDetails = async (orderId: string) => {
    loading.value = true;
    error.value = '';

    try {
        const response = await api.get(`/orders/${orderId}`);
        order.value = response.data.data;
    } catch (err: any) {
        console.error('Failed to fetch order details:', err);
        error.value = err.response?.data?.message || 'Failed to load order details. Please try again.';
    } finally {
        loading.value = false;
    }
};

// Select payment method
const selectPaymentMethod = (method: string) => {
    if (!processing.value && !order.value?.payment) {
        selectedPaymentMethod.value = method;
        // Reset cash fields when switching payment method
        if (method !== 'cash') {
            cashReceived.value = 0;
            changeAmount.value = 0;
        }
    }
};

// Calculate change for cash payment
const calculateChange = () => {
    if (selectedPaymentMethod.value === 'cash') {
        changeAmount.value = cashReceived.value - finalTotal.value;
    }
};

// Show alert modal
const showAlert = (message: string) => {
    alertMessage.value = message;
    showAlertModal.value = true;
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

// Convert payment method to Title Case format expected by API
const formatPaymentMethod = (method: string): string => {
    const methodMap: Record<string, string> = {
        'cash': 'Cash',
        'credit_card': 'Credit Card',
        'debit_card': 'Debit Card',
        'qr_payment': 'QR Payment',
    };
    return methodMap[method] || method;
};

// Convert payment method to Thai
const formatPaymentMethodThai = (method: string): string => {
    const methodMap: Record<string, string> = {
        'cash': 'เงินสด',
        'Cash': 'เงินสด',
        'credit_card': 'บัตรเครดิต',
        'Credit Card': 'บัตรเครดิต',
        'debit_card': 'บัตรเดบิต',
        'Debit Card': 'บัตรเดบิต',
        'qr_payment': 'QR Payment',
        'QR Payment': 'QR Payment',
    };
    return methodMap[method] || method;
};

// Process payment
const processPayment = async () => {
    if (!selectedPaymentMethod.value || !order.value || processing.value) return;

    // Validate cash payment
    if (selectedPaymentMethod.value === 'cash') {
        if (cashReceived.value <= 0) {
            showAlert('กรุณากรอกจำนวนเงินที่รับมา');
            return;
        }
        if (cashReceived.value < finalTotal.value) {
            showAlert(`เงินไม่เพียงพอ ต้องการอีก ฿${(finalTotal.value - cashReceived.value).toFixed(2)}`);
            return;
        }
    }

    const confirmMsg = selectedPaymentMethod.value === 'cash'
        ? `รับเงิน ฿${cashReceived.value.toFixed(2)}\nทอนเงิน ฿${changeAmount.value.toFixed(2)}\n\nยืนยันการชำระเงิน?`
        : `ยืนยันการชำระเงิน ฿${finalTotal.value.toFixed(2)} ผ่าน ${formatPaymentMethodThai(selectedPaymentMethod.value)}?`;

    showConfirm(confirmMsg, async () => {
        processing.value = true;

        try {
            const paymentData = {
                order_id: order.value!.id,
                payment_method: formatPaymentMethod(selectedPaymentMethod.value),
                amount: finalTotal.value.toFixed(2),
            };

            await api.post('/payments', paymentData);

            // Refresh order details to show payment
            await fetchOrderDetails(String(order.value!.id));

            // Show success modal
            showSuccessModal.value = true;

        } catch (err: any) {
            console.error('Payment failed:', err);
            const message = err.response?.data?.message || 'เกิดข้อผิดพลาดในการชำระเงิน กรุณาลองใหม่อีกครั้ง';
            showAlert(message);
        } finally {
            processing.value = false;
        }
    });
};

// Close success modal
const closeSuccessModal = () => {
    showSuccessModal.value = false;
};

// Print receipt
// const printReceipt = () => {
//     window.print();
//     closeSuccessModal();
// };

// Return to tables
const returnToTables = () => {
    router.visit('/tables');
};

// Format date/time
const formatDateTime = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleString('th-TH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Capitalize status
const capitalizeStatus = (status: string) => {
    const statusMap: Record<string, string> = {
        'pending': 'รอดำเนินการ',
        'completed': 'สำเร็จ',
        'cancelled': 'ยกเลิก',
    };
    return statusMap[status] || status.charAt(0).toUpperCase() + status.slice(1);
};

// Initialize
onMounted(() => {
    const orderId = getOrderIdFromUrl();
    if (orderId) {
        fetchOrderDetails(orderId);
    } else {
        // No order ID - redirect to tables to select an order
        console.log('No order ID provided, redirecting to tables');
        router.visit('/tables');
    }
});
</script>

<style scoped>
@media print {
    body * {
        visibility: hidden;
    }
    .bg-white,
    .bg-white * {
        visibility: visible;
    }
    button,
    .no-print {
        display: none !important;
    }
}
</style>
