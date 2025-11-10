# Order Management API Documentation

## Overview

The Order Management API provides endpoints for creating, viewing, and managing orders in the POS system. All endpoints require authentication using Laravel Sanctum.

## Authentication

All endpoints require a valid session. Users must be authenticated via the `/api/login` endpoint.

### Headers Required
```
Accept: application/json
Content-Type: application/json
X-CSRF-TOKEN: {csrf-token}
```

## Endpoints

### 1. Create Order

Creates a new order with menu items and modifiers. Automatically calculates the total amount and updates the table status to "occupied".

**Endpoint:** `POST /api/orders`

**Authorization:** All authenticated users (Admin, Manager, Cashier, Waiter)

**Request Body:**
```json
{
  "table_id": 1,
  "order_items": [
    {
      "menu_item_id": 5,
      "quantity": 2,
      "notes": "No ice",
      "modifier_ids": [1, 3]
    },
    {
      "menu_item_id": 12,
      "quantity": 1,
      "notes": "Extra spicy",
      "modifier_ids": []
    }
  ]
}
```

**Request Validation:**
- `table_id` (required, integer): Must exist in tables
- `order_items` (required, array): Must contain at least 1 item
- `order_items.*.menu_item_id` (required, integer): Must exist in menu_items
- `order_items.*.quantity` (required, integer): Minimum 1
- `order_items.*.notes` (optional, string): Maximum 500 characters
- `order_items.*.modifier_ids` (optional, array): Each ID must exist in modifiers

**Success Response (201 Created):**
```json
{
  "message": "Order created successfully.",
  "data": {
    "id": 45,
    "table": {
      "id": 1,
      "number": "T-001",
      "status": "occupied",
      "capacity": 4
    },
    "user": {
      "id": 3,
      "name": "Sarah Johnson",
      "email": "sarah@example.com",
      "role": {
        "id": 4,
        "name": "Waiter"
      }
    },
    "status": "pending",
    "total_amount": "450.00",
    "order_items": [
      {
        "id": 102,
        "menu_item": {
          "id": 5,
          "name": "Iced Coffee",
          "price": "80.00",
          "category": {
            "id": 2,
            "name": "Beverages"
          }
        },
        "quantity": 2,
        "notes": "No ice",
        "modifiers": [
          {
            "id": 1,
            "name": "Extra Shot",
            "price_change": "20.00"
          },
          {
            "id": 3,
            "name": "Oat Milk",
            "price_change": "15.00"
          }
        ],
        "subtotal": "230.00"
      },
      {
        "id": 103,
        "menu_item": {
          "id": 12,
          "name": "Spicy Chicken Wings",
          "price": "220.00",
          "category": {
            "id": 5,
            "name": "Appetizers"
          }
        },
        "quantity": 1,
        "notes": "Extra spicy",
        "modifiers": [],
        "subtotal": "220.00"
      }
    ],
    "payment": null,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Responses:**

**422 Unprocessable Entity** - Table already occupied:
```json
{
  "message": "Table is already occupied. Please select another table."
}
```

**422 Unprocessable Entity** - Menu item unavailable:
```json
{
  "message": "Menu item 'Iced Coffee' is currently unavailable."
}
```

**422 Unprocessable Entity** - Validation error:
```json
{
  "message": "The table id field is required. (and 1 more error)",
  "errors": {
    "table_id": [
      "The table id field is required."
    ],
    "order_items": [
      "The order items field must have at least 1 items."
    ]
  }
}
```

---

### 2. Get Active Orders

Retrieves all orders that are not completed or cancelled.

**Endpoint:** `GET /api/orders/active`

**Authorization:** All authenticated users

**Query Parameters:** None

**Success Response (200 OK):**
```json
{
  "data": [
    {
      "id": 45,
      "table": {
        "id": 1,
        "number": "T-001",
        "status": "occupied",
        "capacity": 4
      },
      "user": {
        "id": 3,
        "name": "Sarah Johnson",
        "email": "sarah@example.com",
        "role": {
          "id": 4,
          "name": "Waiter"
        }
      },
      "status": "pending",
      "total_amount": "450.00",
      "order_items": [
        {
          "id": 102,
          "menu_item": {
            "id": 5,
            "name": "Iced Coffee",
            "price": "80.00",
            "category": {
              "id": 2,
              "name": "Beverages"
            }
          },
          "quantity": 2,
          "notes": "No ice",
          "modifiers": [
            {
              "id": 1,
              "name": "Extra Shot",
              "price_change": "20.00"
            }
          ],
          "subtotal": "200.00"
        }
      ],
      "payment": null,
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    },
    {
      "id": 44,
      "table": {
        "id": 3,
        "number": "T-003",
        "status": "occupied",
        "capacity": 2
      },
      "user": {
        "id": 5,
        "name": "Mike Wilson",
        "email": "mike@example.com",
        "role": {
          "id": 4,
          "name": "Waiter"
        }
      },
      "status": "preparing",
      "total_amount": "680.00",
      "order_items": [
        {
          "id": 98,
          "menu_item": {
            "id": 20,
            "name": "Grilled Salmon",
            "price": "340.00",
            "category": {
              "id": 3,
              "name": "Main Course"
            }
          },
          "quantity": 2,
          "notes": null,
          "modifiers": [],
          "subtotal": "680.00"
        }
      ],
      "payment": null,
      "created_at": "2024-01-15T09:15:00.000000Z",
      "updated_at": "2024-01-15T09:20:00.000000Z"
    }
  ]
}
```

---

### 3. Get Single Order

Retrieves detailed information about a specific order (useful for the billing page).

**Endpoint:** `GET /api/orders/{order}`

**Authorization:** All authenticated users

**URL Parameters:**
- `order` (required, integer): Order ID

**Success Response (200 OK):**
```json
{
  "data": {
    "id": 45,
    "table": {
      "id": 1,
      "number": "T-001",
      "status": "occupied",
      "capacity": 4
    },
    "user": {
      "id": 3,
      "name": "Sarah Johnson",
      "email": "sarah@example.com",
      "role": {
        "id": 4,
        "name": "Waiter"
      }
    },
    "status": "pending",
    "total_amount": "450.00",
    "order_items": [
      {
        "id": 102,
        "menu_item": {
          "id": 5,
          "name": "Iced Coffee",
          "price": "80.00",
          "category": {
            "id": 2,
            "name": "Beverages"
          }
        },
        "quantity": 2,
        "notes": "No ice",
        "modifiers": [
          {
            "id": 1,
            "name": "Extra Shot",
            "price_change": "20.00"
          },
          {
            "id": 3,
            "name": "Oat Milk",
            "price_change": "15.00"
          }
        ],
        "subtotal": "230.00"
      },
      {
        "id": 103,
        "menu_item": {
          "id": 12,
          "name": "Spicy Chicken Wings",
          "price": "220.00",
          "category": {
            "id": 5,
            "name": "Appetizers"
          }
        },
        "quantity": 1,
        "notes": "Extra spicy",
        "modifiers": [],
        "subtotal": "220.00"
      }
    ],
    "payment": null,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

**Error Response:**

**404 Not Found:**
```json
{
  "message": "Order not found."
}
```

---

### 4. Update Order (Add Items)

Updates an existing order by adding more items. Recalculates the total amount. Can also update the order status.

**Endpoint:** `PUT /api/orders/{order}` or `PATCH /api/orders/{order}`

**Authorization:** All authenticated users

**URL Parameters:**
- `order` (required, integer): Order ID

**Request Body:**
```json
{
  "order_items": [
    {
      "menu_item_id": 8,
      "quantity": 1,
      "notes": "Less sugar",
      "modifier_ids": [2]
    }
  ],
  "status": "preparing"
}
```

**Request Validation:**
- `order_items` (required, array): Must contain at least 1 item
- `order_items.*.menu_item_id` (required, integer): Must exist in menu_items
- `order_items.*.quantity` (required, integer): Minimum 1
- `order_items.*.notes` (optional, string): Maximum 500 characters
- `order_items.*.modifier_ids` (optional, array): Each ID must exist in modifiers
- `status` (optional, string): One of: pending, preparing, ready, completed, cancelled

**Success Response (200 OK):**
```json
{
  "message": "Order updated successfully.",
  "data": {
    "id": 45,
    "table": {
      "id": 1,
      "number": "T-001",
      "status": "occupied",
      "capacity": 4
    },
    "user": {
      "id": 3,
      "name": "Sarah Johnson",
      "email": "sarah@example.com",
      "role": {
        "id": 4,
        "name": "Waiter"
      }
    },
    "status": "preparing",
    "total_amount": "555.00",
    "order_items": [
      {
        "id": 102,
        "menu_item": {
          "id": 5,
          "name": "Iced Coffee",
          "price": "80.00",
          "category": {
            "id": 2,
            "name": "Beverages"
          }
        },
        "quantity": 2,
        "notes": "No ice",
        "modifiers": [
          {
            "id": 1,
            "name": "Extra Shot",
            "price_change": "20.00"
          },
          {
            "id": 3,
            "name": "Oat Milk",
            "price_change": "15.00"
          }
        ],
        "subtotal": "230.00"
      },
      {
        "id": 103,
        "menu_item": {
          "id": 12,
          "name": "Spicy Chicken Wings",
          "price": "220.00",
          "category": {
            "id": 5,
            "name": "Appetizers"
          }
        },
        "quantity": 1,
        "notes": "Extra spicy",
        "modifiers": [],
        "subtotal": "220.00"
      },
      {
        "id": 104,
        "menu_item": {
          "id": 8,
          "name": "Thai Iced Tea",
          "price": "95.00",
          "category": {
            "id": 2,
            "name": "Beverages"
          }
        },
        "quantity": 1,
        "notes": "Less sugar",
        "modifiers": [
          {
            "id": 2,
            "name": "Pearl Topping",
            "price_change": "10.00"
          }
        ],
        "subtotal": "105.00"
      }
    ],
    "payment": null,
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:35:00.000000Z"
  }
}
```

**Error Responses:**

**422 Unprocessable Entity** - Cannot modify completed order:
```json
{
  "message": "Cannot modify a completed or cancelled order."
}
```

**422 Unprocessable Entity** - Menu item unavailable:
```json
{
  "message": "Menu item 'Thai Iced Tea' is currently unavailable."
}
```

**404 Not Found:**
```json
{
  "message": "Order not found."
}
```

---

## Order Statuses

The order status follows this workflow:

1. **pending** - Order created, waiting to be prepared
2. **preparing** - Kitchen is preparing the order
3. **ready** - Order is ready to be served
4. **completed** - Order has been served and paid
5. **cancelled** - Order was cancelled

## Price Calculation

The order total is calculated as follows:

```
For each order item:
  item_subtotal = (menu_item_price + sum_of_modifier_prices) × quantity

total_amount = sum_of_all_item_subtotals
```

**Example:**
```
Order Item 1: Iced Coffee (80฿) × 2
  + Extra Shot (20฿)
  + Oat Milk (15฿)
  = (80 + 20 + 15) × 2 = 230฿

Order Item 2: Chicken Wings (220฿) × 1
  = 220 × 1 = 220฿

Total: 230 + 220 = 450฿
```

## Business Logic

### Creating Orders

1. **Table Availability Check:** Before creating an order, the system checks if the selected table is available. If the table is already occupied, the request will be rejected.

2. **Menu Item Availability:** Each menu item's availability is checked. If any item is unavailable, the entire order creation is rolled back.

3. **Automatic Total Calculation:** The system automatically calculates the order total based on menu item prices, modifiers, and quantities.

4. **Table Status Update:** When an order is created, the associated table's status is automatically updated to "occupied".

5. **Transaction Safety:** Order creation uses database transactions to ensure data integrity. If any step fails, all changes are rolled back.

### Updating Orders

1. **Status Restriction:** Only orders with status "pending", "preparing", or "ready" can be modified. Completed or cancelled orders cannot be updated.

2. **Adding Items Only:** The update endpoint only supports adding new items to existing orders. It does not support removing or modifying existing items.

3. **Recalculation:** When new items are added, the order's total amount is recalculated by adding the new items' subtotals to the existing total.

### Active Orders

Active orders are those with status NOT IN ('completed', 'cancelled'). This includes:
- pending
- preparing
- ready

## Testing Examples

### Using cURL

**Create Order:**
```bash
curl -X POST http://your-domain.test/api/orders \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "table_id": 1,
    "order_items": [
      {
        "menu_item_id": 5,
        "quantity": 2,
        "notes": "No ice",
        "modifier_ids": [1, 3]
      }
    ]
  }'
```

**Get Active Orders:**
```bash
curl -X GET http://your-domain.test/api/orders/active \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie"
```

**Get Single Order:**
```bash
curl -X GET http://your-domain.test/api/orders/45 \
  -H "Accept: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie"
```

**Update Order:**
```bash
curl -X PUT http://your-domain.test/api/orders/45 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: your-csrf-token" \
  --cookie "laravel_session=your-session-cookie" \
  -d '{
    "order_items": [
      {
        "menu_item_id": 8,
        "quantity": 1,
        "modifier_ids": [2]
      }
    ],
    "status": "preparing"
  }'
```

## Related Documentation

- [API Authentication Documentation](./API_AUTHENTICATION.md)
- [Menu Items API Documentation](./MENU_ITEMS_API.md)
- [Database Seeders Documentation](./SEEDERS_DOCUMENTATION.md)

## Notes

- All prices are stored and returned as decimal strings with 2 decimal places
- Timestamps are in ISO 8601 format (UTC)
- All endpoints require authentication via Laravel Sanctum session
- CSRF protection is enabled for all state-changing requests (POST, PUT, PATCH, DELETE)
