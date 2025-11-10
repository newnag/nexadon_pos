# Payment & Billing API Documentation

## Overview

The Payment API provides endpoints for processing payments and completing orders in the POS system. All endpoints require authentication using Laravel Sanctum.

## Authentication

All endpoints require a valid session. Users must be authenticated via the `/api/login` endpoint.

### Headers Required
```
Accept: application/json
Content-Type: application/json
X-CSRF-TOKEN: {csrf-token}
```

## Endpoints

### 1. Process Payment

Processes payment for an order. Validates the payment amount against the order total, creates a payment record, updates the order status to "completed", and sets the table status to "available".

**Endpoint:** `POST /api/payments`

**Authorization:** All authenticated users (Admin, Manager, Cashier, Waiter)

**Request Body:**
```json
{
  "order_id": 45,
  "payment_method": "Credit Card",
  "amount": "450.00"
}
```

**Request Validation:**
- `order_id` (required, integer): Must exist in orders table
- `payment_method` (required, string): Must be one of: `Cash`, `Credit Card`, `Debit Card`, `QR Payment`
- `amount` (required, numeric): Must be greater than or equal to 0, must match order total

**Success Response (201 Created):**
```json
{
  "message": "Payment processed successfully.",
  "data": {
    "id": 12,
    "order": {
      "id": 45,
      "table_number": "T-001",
      "status": "completed",
      "total_amount": "450.00"
    },
    "payment_method": "Credit Card",
    "amount": "450.00",
    "created_at": "2024-01-15T11:30:00.000000Z",
    "updated_at": "2024-01-15T11:30:00.000000Z"
  }
}
```

**Error Responses:**

**422 Unprocessable Entity** - Order already paid:
```json
{
  "message": "This order has already been paid."
}
```

**422 Unprocessable Entity** - Cannot pay cancelled order:
```json
{
  "message": "Cannot process payment for a cancelled order."
}
```

**422 Unprocessable Entity** - Amount mismatch:
```json
{
  "message": "Payment amount does not match order total.",
  "expected": "450.00",
  "received": "400.00"
}
```

**422 Unprocessable Entity** - Validation error:
```json
{
  "message": "The order id field is required. (and 2 more errors)",
  "errors": {
    "order_id": [
      "The order id field is required."
    ],
    "payment_method": [
      "Payment method must be one of: Cash, Credit Card, Debit Card, QR Payment."
    ],
    "amount": [
      "Payment amount is required."
    ]
  }
}
```

**404 Not Found** - Order not found:
```json
{
  "message": "Order not found."
}
```

**500 Internal Server Error** - Payment processing failed:
```json
{
  "message": "Failed to process payment.",
  "error": "Database connection error"
}
```

---

## Payment Methods

The system supports the following payment methods:

1. **Cash** - Physical cash payment
2. **Credit Card** - Credit card payment (Visa, Mastercard, etc.)
3. **Debit Card** - Debit card payment
4. **QR Payment** - QR code payment (PromptPay, TrueMoney, etc.)

---

## Payment Workflow

### Complete Order Flow

```
1. Customer orders food/drinks
   ↓
2. Order created (status: 'pending')
   ↓
3. Kitchen prepares (status: 'preparing')
   ↓
4. Food ready (status: 'ready')
   ↓
5. Customer asks for bill
   ↓
6. Cashier processes payment (POST /api/payments)
   ↓
7. Order status → 'completed'
   Table status → 'available'
   ↓
8. Transaction complete
```

### Payment Processing Steps

When `POST /api/payments` is called:

1. **Validation:** Validates request data (order_id, payment_method, amount)

2. **Order Check:** Verifies order exists and loads relationships

3. **Payment Status:** Checks if order is already paid
   - If already paid → Reject with error

4. **Order Status:** Checks if order can be paid
   - If cancelled → Reject with error
   - Other statuses → Continue

5. **Amount Verification:** Compares payment amount with order total
   - If mismatch → Reject with exact amounts in error
   - If match → Continue

6. **Payment Creation:** Creates payment record in database

7. **Order Update:** Sets order status to `completed`

8. **Table Release:** Sets table status to `available`

9. **Response:** Returns payment details with order info

---

## Business Logic

### Payment Amount Validation

The system uses precise decimal comparison to verify that the payment amount matches the order total exactly:

```php
bccomp($payment_amount, $order_total, 2) === 0
```

This ensures accuracy to 2 decimal places, preventing issues with floating-point arithmetic.

### Order Status Requirements

Orders can only be paid if they are NOT in `cancelled` status. Valid statuses for payment:
- `pending` - Order created but not yet prepared
- `preparing` - Order being prepared in kitchen
- `ready` - Order ready for serving
- Any other custom status

### Duplicate Payment Prevention

The system checks if a payment already exists for the order before processing. Each order can only have one payment record.

### Transaction Safety

Payment processing uses database transactions to ensure data integrity:
- All changes (payment creation, order update, table update) happen atomically
- If any step fails, all changes are rolled back
- Database remains consistent even if errors occur

### Table Management

When a payment is successfully processed:
1. Order status changes to `completed`
2. Table status changes to `available`
3. Table becomes available for new customers

---

## Integration Examples

### Complete Order-to-Payment Flow

#### Step 1: Create Order
```bash
POST /api/orders
{
  "table_id": 1,
  "order_items": [
    {
      "menu_item_id": 5,
      "quantity": 2,
      "modifier_ids": [1, 3]
    }
  ]
}

Response:
{
  "data": {
    "id": 45,
    "total_amount": "450.00",
    "status": "pending"
  }
}
```

#### Step 2: Add More Items (Optional)
```bash
PUT /api/orders/45
{
  "order_items": [
    {
      "menu_item_id": 8,
      "quantity": 1
    }
  ],
  "status": "preparing"
}

Response:
{
  "data": {
    "id": 45,
    "total_amount": "555.00",
    "status": "preparing"
  }
}
```

#### Step 3: Get Order for Billing
```bash
GET /api/orders/45

Response:
{
  "data": {
    "id": 45,
    "total_amount": "555.00",
    "order_items": [...],
    "payment": null
  }
}
```

#### Step 4: Process Payment
```bash
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Credit Card",
  "amount": "555.00"
}

Response:
{
  "message": "Payment processed successfully.",
  "data": {
    "id": 12,
    "order": {
      "id": 45,
      "status": "completed"
    },
    "payment_method": "Credit Card",
    "amount": "555.00"
  }
}
```

---

## Testing Examples

### Using cURL

**Process Payment (Cash):**
```bash
curl -X POST http://your-domain.test/api/payments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "order_id": 45,
    "payment_method": "Cash",
    "amount": "450.00"
  }'
```

**Process Payment (Credit Card):**
```bash
curl -X POST http://your-domain.test/api/payments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "order_id": 45,
    "payment_method": "Credit Card",
    "amount": "450.00"
  }'
```

**Process Payment (QR Payment):**
```bash
curl -X POST http://your-domain.test/api/payments \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "order_id": 45,
    "payment_method": "QR Payment",
    "amount": "450.00"
  }'
```

### Test Scenarios

#### Scenario 1: Successful Payment
```json
// Request
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Cash",
  "amount": "450.00"
}

// Expected: 201 Created
// Order status → completed
// Table status → available
```

#### Scenario 2: Amount Mismatch
```json
// Request
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Cash",
  "amount": "400.00"  // Order total is 450.00
}

// Expected: 422 Unprocessable Entity
// Error: "Payment amount does not match order total."
```

#### Scenario 3: Duplicate Payment
```json
// Request (order already paid)
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Cash",
  "amount": "450.00"
}

// Expected: 422 Unprocessable Entity
// Error: "This order has already been paid."
```

#### Scenario 4: Invalid Payment Method
```json
// Request
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Bitcoin",
  "amount": "450.00"
}

// Expected: 422 Unprocessable Entity
// Error: "Payment method must be one of: Cash, Credit Card, Debit Card, QR Payment."
```

#### Scenario 5: Cancelled Order
```json
// Request (order status is 'cancelled')
POST /api/payments
{
  "order_id": 45,
  "payment_method": "Cash",
  "amount": "450.00"
}

// Expected: 422 Unprocessable Entity
// Error: "Cannot process payment for a cancelled order."
```

---

## Frontend Implementation

### React/Vue Example

```javascript
// Payment processing function
async function processPayment(orderId, paymentMethod, amount) {
  try {
    // Get CSRF token
    await fetch('/sanctum/csrf-cookie');
    
    // Process payment
    const response = await fetch('/api/payments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      credentials: 'include',
      body: JSON.stringify({
        order_id: orderId,
        payment_method: paymentMethod,
        amount: amount
      })
    });
    
    const data = await response.json();
    
    if (response.ok) {
      console.log('Payment successful:', data);
      // Redirect to success page or show confirmation
      return data;
    } else {
      console.error('Payment failed:', data.message);
      // Show error message to user
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('Error processing payment:', error);
    throw error;
  }
}

// Usage example
const order = { id: 45, total_amount: "450.00" };
processPayment(order.id, "Credit Card", order.total_amount)
  .then(payment => {
    alert('Payment successful!');
    // Update UI, redirect, etc.
  })
  .catch(error => {
    alert('Payment failed: ' + error.message);
  });
```

### Billing Page Component Example

```javascript
// Billing page component
function BillingPage({ orderId }) {
  const [order, setOrder] = useState(null);
  const [paymentMethod, setPaymentMethod] = useState('Cash');
  const [loading, setLoading] = useState(false);
  
  useEffect(() => {
    // Fetch order details
    fetch(`/api/orders/${orderId}`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'include'
    })
      .then(res => res.json())
      .then(data => setOrder(data.data));
  }, [orderId]);
  
  const handlePayment = async () => {
    if (!order) return;
    
    setLoading(true);
    try {
      await processPayment(order.id, paymentMethod, order.total_amount);
      alert('Payment processed successfully!');
      // Redirect or update UI
    } catch (error) {
      alert('Payment failed: ' + error.message);
    } finally {
      setLoading(false);
    }
  };
  
  if (!order) return <div>Loading...</div>;
  
  return (
    <div>
      <h2>Bill for Table {order.table.table_number}</h2>
      <div>
        {order.order_items.map(item => (
          <div key={item.id}>
            {item.menu_item.name} x{item.quantity} = {item.subtotal}
          </div>
        ))}
      </div>
      <div>Total: {order.total_amount} THB</div>
      
      <select value={paymentMethod} onChange={e => setPaymentMethod(e.target.value)}>
        <option value="Cash">Cash</option>
        <option value="Credit Card">Credit Card</option>
        <option value="Debit Card">Debit Card</option>
        <option value="QR Payment">QR Payment</option>
      </select>
      
      <button onClick={handlePayment} disabled={loading}>
        {loading ? 'Processing...' : 'Process Payment'}
      </button>
    </div>
  );
}
```

---

## Error Handling Best Practices

### Client-Side Validation

Before sending payment request:
1. Verify order ID is valid
2. Check payment method is selected
3. Ensure amount matches order total
4. Confirm order is not already paid

### Server Response Handling

```javascript
// Handle different error responses
async function handlePaymentResponse(response) {
  const data = await response.json();
  
  switch (response.status) {
    case 201:
      // Success - payment processed
      return { success: true, data: data.data };
      
    case 422:
      // Validation error or business logic error
      if (data.message.includes('already been paid')) {
        return { success: false, error: 'ORDER_ALREADY_PAID', message: data.message };
      } else if (data.message.includes('amount does not match')) {
        return { success: false, error: 'AMOUNT_MISMATCH', message: data.message };
      } else if (data.message.includes('cancelled order')) {
        return { success: false, error: 'ORDER_CANCELLED', message: data.message };
      }
      return { success: false, error: 'VALIDATION_ERROR', message: data.message };
      
    case 404:
      // Order not found
      return { success: false, error: 'ORDER_NOT_FOUND', message: 'Order not found' };
      
    case 500:
      // Server error
      return { success: false, error: 'SERVER_ERROR', message: 'Payment processing failed' };
      
    default:
      return { success: false, error: 'UNKNOWN_ERROR', message: 'An error occurred' };
  }
}
```

---

## Database Schema

### payments table

| Column | Type | Description |
|--------|------|-------------|
| id | bigint unsigned | Primary key |
| order_id | bigint unsigned | Foreign key to orders table |
| payment_method | string | Payment method used |
| amount | decimal(10,2) | Payment amount |
| created_at | timestamp | Payment creation time |
| updated_at | timestamp | Payment update time |

---

## Related Documentation

- [API Authentication Documentation](./API_AUTHENTICATION.md)
- [Order Management API Documentation](./ORDER_API.md)
- [Menu Items API Documentation](./MENU_ITEMS_API.md)
- [Database Seeders Documentation](./SEEDERS_DOCUMENTATION.md)

---

## Notes

- All prices are stored and returned as decimal strings with 2 decimal places
- Timestamps are in ISO 8601 format (UTC)
- Payment processing uses database transactions for data integrity
- CSRF protection is enabled for all POST requests
- Each order can only have one payment record
- Table status automatically changes to "available" after payment
- Order status automatically changes to "completed" after payment
