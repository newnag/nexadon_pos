# Task 12: Billing and Payment View - Implementation Documentation

## Overview
Complete billing and payment processing interface for restaurant orders. Features order details display, price breakdown with VAT and service charges, split bill functionality, multiple payment methods, and receipt printing.

---

## 📁 Files Created/Modified

### **1. BillingView.vue** (NEW)
**Location:** `resources/js/pages/BillingView.vue`

**Component Structure:**
```
┌──────────────────────────────────────────────────────────────┐
│ Header: Back Button | "Billing & Payment"                     │
├──────────────────────────────────────────────────────────────┤
│                                                                │
│ Order Info Card                                                │
│ • Order #, Table, Server, Date/Time, Status                   │
│                                                                │
│ Order Items Card                                               │
│ • List of all items with quantities, modifiers, notes         │
│ • Individual item subtotals                                    │
│                                                                │
│ Split Bill Feature (Toggle)                                    │
│ • Number of people selector (2+ with +/- buttons)             │
│ • Amount per person calculation                               │
│                                                                │
│ Price Summary Card                                             │
│ • Subtotal                                                     │
│ • VAT (7%)                                                     │
│ • Service Charge (10%)                                         │
│ • Final Total (large, bold)                                    │
│                                                                │
│ Payment Methods Card                                           │
│ • 4 payment method buttons (Cash, Credit, Debit, QR)         │
│ • Visual selection feedback                                    │
│ • Process Payment button (shows total amount)                 │
│                                                                │
│ Success Modal (After Payment)                                  │
│ • Success icon and message                                     │
│ • Order details summary                                        │
│ • Print Receipt button                                         │
│ • Return to Tables button                                      │
│                                                                │
└──────────────────────────────────────────────────────────────┘
```

---

## 🎯 Key Features

### **A. Order Details Display**

Fetches comprehensive order information from `GET /api/orders/{id}`:

```typescript
interface Order {
    id: number;
    status: string;
    total_amount: string;
    created_at: string;
    table: {
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
```

**Displayed Information:**
- Order Number with # prefix
- Table number
- Server name (user who created order)
- Date and time (formatted)
- Status badge (color-coded)

---

### **B. Order Items List**

Each item displays:
- **Menu item name** with quantity (e.g., "Chicken Wings × 2")
- **Category** (e.g., "Appetizers")
- **Modifiers** with price changes (e.g., "+ Extra Spicy (+฿1.00)")
- **Special notes** if any (italic, gray)
- **Item subtotal** (right-aligned)
- **Unit price** (small text below subtotal)

```vue
<div class="order-item">
  <h3>Chicken Wings × 2</h3>
  <p>Appetizers</p>
  <p>Add-ons: Extra Spicy (+฿1.00), Extra Sauce (+฿0.50)</p>
  <p>Note: No onions</p>
  <p class="subtotal">฿19.98</p>
  <p class="unit-price">฿8.99 each</p>
</div>
```

---

### **C. Price Calculations**

**Formula:**
```
Subtotal = Order Total / 1.17
VAT (7%) = Subtotal × 0.07
Service Charge (10%) = Subtotal × 0.10
Final Total = Subtotal + VAT + Service Charge
```

**Why divide by 1.17?**
- Backend `total_amount` already includes VAT and service charge
- To reverse-calculate: subtotal × 1.07 × 1.10 = total
- Therefore: subtotal = total / 1.17

**Display:**
```
Subtotal:           ฿100.00
VAT (7%):           ฿7.00
Service Charge (10%): ฿10.00
─────────────────────────────
Total:              ฿117.00
```

---

### **D. Split Bill Feature**

**Toggle Switch:**
- Blue toggle button to enable/disable
- Default: disabled (split count = 2)

**When Enabled:**
- Number of people selector with +/- buttons
- Minimum: 2 people
- Maximum: unlimited (practical limit ~20)

**Calculation Display:**
```typescript
const amountPerPerson = computed(() => {
    return finalTotal.value / splitCount.value;
});
```

**Visual Display:**
```
Split Bill: [ON]
Number of People: [−] 4 [+]

Amount per person: ฿29.25
4 people × ฿29.25 = ฿117.00
```

**Use Cases:**
- Group dining
- Splitting evenly among friends
- Corporate lunches
- Family meals

---

### **E. Payment Methods**

**4 Options Available:**
1. **Cash** - Physical currency
2. **Credit Card** - Visa, Mastercard, etc.
3. **Debit Card** - Direct bank debit
4. **QR Payment** - PromptPay, Thai QR, etc.

**UI Design:**
- Grid layout (2×2)
- Icon for each method (SVG)
- Visual selection (blue border + blue background)
- Hover effects (border change, shadow)
- Disabled when already paid

**Selection Logic:**
```typescript
const selectPaymentMethod = (method: string) => {
    if (!processing.value && !order.value?.payment) {
        selectedPaymentMethod.value = method;
    }
};
```

---

### **F. Payment Processing**

**Process Payment Button:**
- Shows total amount: "Process Payment - ฿117.00"
- Disabled when:
  - No payment method selected
  - Payment is processing
  - Order already paid
- Shows loading spinner during processing

**Workflow:**
```typescript
1. User selects payment method
2. Clicks "Process Payment" button
3. Confirmation dialog appears
4. If confirmed:
   a. Call POST /api/payments
   b. Show loading state
   c. On success:
      - Refresh order details
      - Show success modal
   d. On error:
      - Show error alert
      - Keep payment method selected
```

**API Request:**
```json
POST /api/payments
{
  "order_id": 10,
  "payment_method": "cash",
  "amount": "117.00"
}
```

**API Response (Success):**
```json
{
  "message": "Payment processed successfully.",
  "data": {
    "id": 5,
    "order_id": 10,
    "payment_method": "cash",
    "amount": "117.00",
    ...
  }
}
```

---

### **G. Success Modal**

Appears after successful payment:

**Content:**
- ✅ Large green checkmark icon
- "Payment Successful!" heading
- Confirmation message with amount
- Order details summary:
  - Order number
  - Table number
  - Payment method

**Action Buttons:**
1. **Print Receipt** - Triggers browser print dialog
2. **Return to Tables** - Navigates to `/tables`

**Print Functionality:**
```typescript
const printReceipt = () => {
    window.print(); // Browser native print
    closeSuccessModal();
};
```

**CSS for Print:**
```css
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
```

---

### **H. Already Paid State**

If order is already paid (order.payment exists):

**UI Changes:**
- ✅ Green notice banner: "Payment Completed"
- Shows payment method and amount
- Payment method buttons disabled
- Process Payment button hidden

**Display:**
```
✅ Payment Completed
   Paid via Cash - ฿117.00
```

---

## 🔌 API Integration

### **1. Fetch Order Details**

```http
GET /api/orders/{orderId}

Headers:
  Authorization: Bearer {token}

Response (200):
{
  "data": {
    "id": 10,
    "status": "pending",
    "total_amount": "117.00",
    "table": {
      "id": 5,
      "table_number": "T-05",
      "status": "occupied"
    },
    "user": {
      "id": 2,
      "name": "John Waiter",
      "role": "Waiter"
    },
    "order_items": [
      {
        "id": 25,
        "quantity": 2,
        "notes": "No onions",
        "subtotal": "19.98",
        "menu_item": {
          "id": 1,
          "name": "Chicken Wings",
          "price": "8.99",
          "category": {
            "id": 1,
            "name": "Appetizers"
          }
        },
        "modifiers": [
          {
            "id": 1,
            "name": "Extra Spicy",
            "price_change": "1.00"
          }
        ]
      }
    ],
    "payment": null,
    "created_at": "2025-10-31T10:30:00Z",
    "updated_at": "2025-10-31T10:30:00Z"
  }
}
```

---

### **2. Process Payment**

```http
POST /api/payments

Headers:
  Authorization: Bearer {token}
  Content-Type: application/json

Body:
{
  "order_id": 10,
  "payment_method": "cash",
  "amount": "117.00"
}

Response (201):
{
  "message": "Payment processed successfully.",
  "data": {
    "id": 5,
    "order_id": 10,
    "payment_method": "cash",
    "amount": "117.00",
    "created_at": "2025-10-31T11:00:00Z",
    "order": {
      "id": 10,
      "status": "completed",
      "table": {
        "id": 5,
        "table_number": "T-05",
        "status": "available"
      }
    }
  }
}
```

**Backend Actions (Automatic):**
1. Create payment record
2. Update order status: `pending` → `completed`
3. Update table status: `occupied` → `available`
4. All in a database transaction

---

## 🎨 UI/UX Features

### **1. Visual Feedback**

**Status Colors:**
- **Completed**: Green (`bg-green-100 text-green-800`)
- **Pending**: Yellow (`bg-yellow-100 text-yellow-800`)
- **Cancelled**: Gray (`bg-gray-100 text-gray-800`)

**Payment Method Selection:**
- **Unselected**: Gray border, white background
- **Selected**: Blue border, blue background, shadow
- **Hover**: Blue border (lighter), shadow
- **Disabled**: Gray, reduced opacity, cursor not-allowed

### **2. Responsive Design**

**Desktop (>1024px):**
- Max width: 1024px
- Centered content
- Full-width cards with padding

**Tablet (768px - 1024px):**
- Adjusted padding
- 2-column payment method grid maintained

**Mobile (<768px):**
- Stacked layout
- Full-width elements
- Touch-friendly button sizes

### **3. Loading States**

**Order Loading:**
- Spinner with animation
- "Loading order details..." message
- Centered in white card

**Payment Processing:**
- Button shows spinner + "Processing..."
- Button disabled
- Payment methods disabled

### **4. Error Handling**

**Order Fetch Error:**
- Red alert box
- Error icon
- Error message from API
- Centered display

**Payment Error:**
- Browser alert dialog
- Error message from API or generic
- Keeps form state (method still selected)
- User can retry

---

## 🔄 User Flow

### **Standard Payment Flow**

1. **Navigate to Billing**:
   - From TableMapView: Click occupied table → View Order button
   - From OrderView: After sending order
   - From Dashboard: Quick action or order list
   - URL: `/billing/{orderId}`

2. **Review Order**:
   - See all items with modifiers
   - Check total amount
   - Verify table and server info

3. **Optional: Enable Split Bill**:
   - Toggle split bill switch
   - Adjust number of people
   - See per-person amount

4. **Select Payment Method**:
   - Click on Cash, Credit, Debit, or QR
   - Button highlights in blue

5. **Process Payment**:
   - Click "Process Payment" button
   - Confirm in dialog
   - Wait for processing

6. **Success**:
   - Success modal appears
   - Option to print receipt
   - Return to tables

---

### **Split Bill Scenario**

**Example: 4 Friends Dining**

```
Order Total: ฿468.00
Split among: 4 people

Calculation:
- Subtotal: ฿400.00
- VAT (7%): ฿28.00
- Service (10%): ฿40.00
- Total: ฿468.00

Per Person: ฿117.00
```

**In Practice:**
1. Waiter enables split bill
2. Sets to 4 people
3. Shows each friend: "Your share is ฿117.00"
4. Process payment (restaurant might do 4 separate transactions or one combined)
5. Receipt shows split details

---

## 💡 Implementation Details

### **1. Price Calculation Strategy**

Backend already includes VAT and service charge in `order.total_amount`. Frontend reverse-calculates for display:

```typescript
// Backend calculation (in OrderController):
$itemPrice = $menuItem->price;
$modifiersTotal = $orderItem->modifiers()->sum('price_change');
$itemSubtotal = ($itemPrice + $modifiersTotal) * $quantity;
$orderSubtotal += $itemSubtotal;

// Order total includes VAT and service
$finalTotal = $orderSubtotal * 1.17; // 7% VAT + 10% service

// Frontend reverse calculation:
const subtotal = computed(() => {
    return parseFloat(order.value.total_amount) / 1.17;
});
const vat = computed(() => subtotal.value * 0.07);
const service = computed(() => subtotal.value * 0.10);
```

**Why This Approach?**
- Single source of truth (backend)
- Consistent calculations
- No floating-point discrepancies
- Frontend only displays breakdown

---

### **2. Payment Validation**

**Frontend Validation:**
- Payment method required
- Amount must be positive
- Order ID must be valid

**Backend Validation (PaymentController):**
```php
// Check if already paid
if ($order->payment) {
    return 422; // Already paid
}

// Check order status
if (in_array($order->status, ['cancelled'])) {
    return 422; // Cannot pay cancelled order
}

// Verify amount matches
if (bccomp($request->amount, $order->total_amount, 2) !== 0) {
    return 422; // Amount mismatch
}
```

**Why bccomp?**
- Precise decimal comparison
- Avoids floating-point issues
- Returns: -1 (less), 0 (equal), 1 (greater)

---

### **3. Split Bill Implementation**

Split bill is a **display feature only** - it doesn't affect the actual payment amount sent to API.

**Current Implementation:**
- Shows per-person amount
- Helps staff/customers understand division
- Total amount remains unchanged

**Future Enhancement Options:**
1. **Multiple Payment Records**: Create separate payment records per person
2. **Partial Payments**: Allow paying portion now, rest later
3. **Individual Items**: Assign specific items to specific people
4. **Bill Splitting Service**: Integrate with apps like Splitwise

---

### **4. Print Receipt Functionality**

Uses browser's native print dialog:

```typescript
const printReceipt = () => {
    window.print();
    closeSuccessModal();
};
```

**Print Styles:**
- Hide buttons, navigation, modal backdrop
- Show only order details and payment info
- Clean, receipt-like formatting

**Enhancement Options:**
- Generate PDF receipt
- Email receipt to customer
- SMS receipt confirmation
- Save to order history

---

## 🔐 Security & Validation

### **1. Authorization**

**Route Protection:**
```typescript
{
    path: '/billing/:orderId',
    name: 'billing.show',
    component: BillingView,
    meta: {
        auth: true,
        roles: ['Admin', 'Manager', 'Cashier'], // Only billing staff
    },
}
```

**API Authorization:**
- Backend checks `auth:sanctum` middleware
- Backend checks `CheckRole` middleware
- Only authorized users can process payments

---

### **2. Data Integrity**

**Frontend:**
- TypeScript interfaces enforce type safety
- Computed properties auto-recalculate
- Reactive state updates UI instantly

**Backend:**
- Database transactions ensure atomicity
- Rollback on any error
- Foreign key constraints
- Validation rules on all inputs

---

### **3. Error Prevention**

**Prevent Double Payment:**
```typescript
// Frontend check
:disabled="processing || !!order.payment"

// Backend check
if ($order->payment) {
    return response()->json(['message' => 'Already paid'], 422);
}
```

**Prevent Amount Mismatch:**
```php
if (bccomp($request->amount, $order->total_amount, 2) !== 0) {
    return response()->json([
        'message' => 'Amount mismatch',
        'expected' => $order->total_amount,
        'received' => $request->amount,
    ], 422);
}
```

---

## 🧪 Testing Scenarios

### **Test 1: View Billing Page**
1. Navigate to `/billing/1` (replace 1 with valid order ID)
2. Verify order details display correctly
3. Check all items, modifiers, notes show
4. Verify price calculations (subtotal, VAT, service, total)

**Expected:**
- ✅ All order info visible
- ✅ Items list complete
- ✅ Calculations accurate

---

### **Test 2: Split Bill**
1. Enable split bill toggle
2. Increase to 5 people
3. Decrease to 2 people
4. Verify per-person amount updates

**Expected:**
- ✅ Toggle switches on/off
- ✅ +/- buttons work
- ✅ Per-person calculation correct
- ✅ Minimum 2 people enforced

---

### **Test 3: Select Payment Method**
1. Click "Cash" button
2. Verify it highlights
3. Click "Credit Card" button
4. Verify Cash de-selects, Credit Card highlights

**Expected:**
- ✅ Only one method selected at a time
- ✅ Visual feedback (blue border/background)
- ✅ Selection persists until changed

---

### **Test 4: Process Payment (Cash)**
1. Select "Cash" method
2. Click "Process Payment"
3. Confirm in dialog
4. Wait for success modal

**Expected:**
- ✅ Confirmation dialog appears
- ✅ Button shows "Processing..." with spinner
- ✅ Success modal shows after ~1-2 seconds
- ✅ Order status updates to "Completed"
- ✅ Green "Payment Completed" banner appears

---

### **Test 5: Print Receipt**
1. Complete payment
2. In success modal, click "Print Receipt"
3. Print dialog should open

**Expected:**
- ✅ Print dialog appears
- ✅ Print preview shows order details
- ✅ Buttons and UI elements hidden in preview
- ✅ Clean receipt formatting

---

### **Test 6: Already Paid Order**
1. Navigate to billing page of completed order
2. Verify payment methods are disabled
3. Verify green "Payment Completed" banner shows

**Expected:**
- ✅ Cannot select payment methods
- ✅ Process Payment button hidden
- ✅ Shows payment method and amount from record
- ✅ All order details still visible

---

### **Test 7: Error Handling**
1. Stop Laravel server
2. Try to load billing page
3. Verify error message displays

**Expected:**
- ✅ Red error banner
- ✅ Error message shown
- ✅ No crash or blank page

---

### **Test 8: Amount Validation**
1. Manually edit API request (browser dev tools)
2. Change amount to wrong value
3. Submit payment

**Expected:**
- ✅ Backend rejects with 422 error
- ✅ Error message: "Amount mismatch"
- ✅ Frontend shows error alert

---

## 📊 Data Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  OrderView (Send to Kitchen)                            │
│  → Creates order → Table status: occupied               │
│                                                         │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  TableMapView                                           │
│  → Click occupied table → "View Order" button           │
│                                                         │
└────────────────────────┬────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  BillingView.vue                                        │
│                                                         │
│  ┌─────────────────────────────────────────────────┐  │
│  │ fetchOrderDetails(orderId)                      │  │
│  │ → GET /api/orders/{id}                          │  │
│  │ → Load order with items, modifiers, payment     │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
│                       ▼                                 │
│  ┌─────────────────────────────────────────────────┐  │
│  │ Display Order Details                           │  │
│  │ • Order info, items, modifiers                  │  │
│  │ • Calculate subtotal, VAT, service, total       │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
│                       ▼                                 │
│  ┌─────────────────────────────────────────────────┐  │
│  │ Optional: Enable Split Bill                     │  │
│  │ • Set number of people                          │  │
│  │ • Show per-person amount                        │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
│                       ▼                                 │
│  ┌─────────────────────────────────────────────────┐  │
│  │ Select Payment Method                           │  │
│  │ • Cash, Credit, Debit, QR                       │  │
│  │ • Visual selection feedback                     │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
│                       ▼                                 │
│  ┌─────────────────────────────────────────────────┐  │
│  │ processPayment()                                │  │
│  │ → POST /api/payments                            │  │
│  │ → Body: {order_id, payment_method, amount}     │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
└───────────────────────┼─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  PaymentController (Laravel)                            │
│                                                         │
│  ┌─────────────────────────────────────────────────┐  │
│  │ 1. Validate order exists & not paid             │  │
│  │ 2. Verify amount matches order total            │  │
│  │ 3. Create payment record                        │  │
│  │ 4. Update order status → 'completed'            │  │
│  │ 5. Update table status → 'available'            │  │
│  │ 6. Return payment resource                      │  │
│  └────────────────────┬────────────────────────────┘  │
│                       │                                 │
└───────────────────────┼─────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  BillingView Success Modal                              │
│  • Show success message                                 │
│  • Display payment details                              │
│  • Print Receipt button → window.print()                │
│  • Return to Tables → router.push('/tables')            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🚀 Future Enhancements

### **1. Advanced Split Bill**
- Assign specific items to specific people
- Different payment methods per person
- Partial payment support
- Save split preferences

### **2. Receipt Features**
- PDF generation
- Email receipt
- SMS receipt
- QR code for digital receipt
- Receipt history

### **3. Payment Integration**
- Credit card processing API (Stripe, Omise)
- QR payment integration (PromptPay API)
- Digital wallet support
- Cryptocurrency payments

### **4. Reporting**
- Payment method analytics
- Peak billing times
- Average bill amount
- Split bill usage statistics

### **5. Tips & Gratuity**
- Add tip percentage selector (10%, 15%, 20%, Custom)
- Tip distribution to staff
- Tip reporting

### **6. Discounts & Promotions**
- Apply discount codes
- Happy hour pricing
- Member discounts
- Loyalty points redemption

---

## ✅ Task 12 Completion Status

- [x] Create BillingView.vue component
- [x] Fetch order details from API
- [x] Display all order items with modifiers
- [x] Calculate subtotal, VAT, service charge, total
- [x] Implement 4 payment method buttons
- [x] Add split bill feature with people counter
- [x] Integrate payment API (POST /api/payments)
- [x] Show success modal with print option
- [x] Handle already paid state
- [x] Add loading and error states
- [x] Update router configuration
- [x] Fix TypeScript errors
- [x] Add print functionality
- [x] Implement responsive design

**Status:** ✅ **COMPLETE**

---

## 📝 Summary

Task 12 delivers a complete billing and payment solution with:
- Comprehensive order details display
- Transparent price breakdown (subtotal, VAT, service)
- Flexible split bill feature
- Multiple payment method support
- Smooth payment processing workflow
- Professional success confirmation
- Print receipt capability
- Robust error handling
- Type-safe TypeScript implementation
- Clean, intuitive UI/UX

The component integrates seamlessly with existing backend APIs and provides restaurant staff with all tools needed for efficient billing and payment processing.
