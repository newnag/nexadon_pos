# Menu Items API Documentation

## Overview
Complete API documentation for Menu Items CRUD operations with role-based access control.

---

## Base URL
```
http://localhost:8000/api
```

---

## Authentication
All endpoints require authentication using Laravel Sanctum.

Include session cookies with your requests for SPA authentication.

---

## Authorization

### Read Operations (All authenticated users)
- `GET /menu-items` - List all menu items
- `GET /menu-items/{id}` - Get single menu item

### Write Operations (Admin & Manager only)
- `POST /menu-items` - Create new menu item
- `PUT/PATCH /menu-items/{id}` - Update menu item
- `DELETE /menu-items/{id}` - Delete menu item

---

## Endpoints

### 1. List All Menu Items

**Endpoint:** `GET /api/menu-items`

**Authorization:** All authenticated users

**Query Parameters:**

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `category_id` | integer | Filter by category | `?category_id=1` |
| `is_available` | boolean | Filter by availability | `?is_available=1` |
| `search` | string | Search by name | `?search=burger` |
| `sort_by` | string | Sort field (default: name) | `?sort_by=price` |
| `sort_order` | string | asc/desc (default: asc) | `?sort_order=desc` |
| `per_page` | integer | Items per page (default: 15) | `?per_page=20` |

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/menu-items?category_id=1&per_page=10" \
  -H "Accept: application/json" \
  -b cookies.txt
```

**Example Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Classic Beef Burger",
      "price": "10.99",
      "is_available": true,
      "category": {
        "id": 5,
        "name": "Burgers & Sandwiches"
      },
      "modifiers": [
        {
          "id": 4,
          "name": "Extra Cheese",
          "price_change": "1.50"
        },
        {
          "id": 5,
          "name": "Extra Bacon",
          "price_change": "2.00"
        }
      ],
      "created_at": "2025-10-30 17:30:00",
      "updated_at": "2025-10-30 17:30:00"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/menu-items?page=1",
    "last": "http://localhost:8000/api/menu-items?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/menu-items?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 15,
    "to": 15,
    "total": 65
  }
}
```

---

### 2. Get Single Menu Item

**Endpoint:** `GET /api/menu-items/{id}`

**Authorization:** All authenticated users

**Path Parameters:**
- `id` (required) - Menu item ID

**Example Request:**
```bash
curl -X GET "http://localhost:8000/api/menu-items/1" \
  -H "Accept: application/json" \
  -b cookies.txt
```

**Example Response (200 OK):**
```json
{
  "data": {
    "id": 1,
    "name": "Classic Beef Burger",
    "price": "10.99",
    "is_available": true,
    "category": {
      "id": 5,
      "name": "Burgers & Sandwiches"
    },
    "modifiers": [
      {
        "id": 4,
        "name": "Extra Cheese",
        "price_change": "1.50"
      },
      {
        "id": 5,
        "name": "Extra Bacon",
        "price_change": "2.00"
      }
    ],
    "created_at": "2025-10-30 17:30:00",
    "updated_at": "2025-10-30 17:30:00"
  }
}
```

**Error Response (404 Not Found):**
```json
{
  "message": "Menu item not found."
}
```

---

### 3. Create New Menu Item

**Endpoint:** `POST /api/menu-items`

**Authorization:** Admin & Manager only

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | Yes | Menu item name (max 255) |
| `price` | decimal | Yes | Price (0-99999.99) |
| `category_id` | integer | Yes | Category ID (must exist) |
| `is_available` | boolean | No | Availability (default: true) |
| `modifier_ids` | array | No | Array of modifier IDs |

**Example Request:**
```bash
curl -X POST "http://localhost:8000/api/menu-items" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "name": "Deluxe Cheeseburger",
    "price": 13.99,
    "category_id": 5,
    "is_available": true,
    "modifier_ids": [4, 5, 6, 7]
  }'
```

**Example Response (201 Created):**
```json
{
  "message": "Menu item created successfully.",
  "data": {
    "id": 66,
    "name": "Deluxe Cheeseburger",
    "price": "13.99",
    "is_available": true,
    "category": {
      "id": 5,
      "name": "Burgers & Sandwiches"
    },
    "modifiers": [
      {
        "id": 4,
        "name": "Extra Cheese",
        "price_change": "1.50"
      },
      {
        "id": 5,
        "name": "Extra Bacon",
        "price_change": "2.00"
      }
    ],
    "created_at": "2025-10-31 10:15:00",
    "updated_at": "2025-10-31 10:15:00"
  }
}
```

**Validation Error Response (422 Unprocessable Entity):**
```json
{
  "message": "The name field is required. (and 1 more error)",
  "errors": {
    "name": [
      "Menu item name is required."
    ],
    "price": [
      "Price is required."
    ]
  }
}
```

**Authorization Error Response (403 Forbidden):**
```json
{
  "message": "Unauthorized. This action requires one of the following roles: Admin, Manager",
  "required_roles": ["Admin", "Manager"],
  "your_role": "Cashier"
}
```

---

### 4. Update Menu Item

**Endpoint:** `PUT/PATCH /api/menu-items/{id}`

**Authorization:** Admin & Manager only

**Path Parameters:**
- `id` (required) - Menu item ID

**Request Body:** (All fields optional for PATCH)

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string | No | Menu item name (max 255) |
| `price` | decimal | No | Price (0-99999.99) |
| `category_id` | integer | No | Category ID (must exist) |
| `is_available` | boolean | No | Availability status |
| `modifier_ids` | array | No | Array of modifier IDs |

**Example Request:**
```bash
curl -X PUT "http://localhost:8000/api/menu-items/66" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "name": "Premium Deluxe Cheeseburger",
    "price": 14.99,
    "is_available": true
  }'
```

**Example Response (200 OK):**
```json
{
  "message": "Menu item updated successfully.",
  "data": {
    "id": 66,
    "name": "Premium Deluxe Cheeseburger",
    "price": "14.99",
    "is_available": true,
    "category": {
      "id": 5,
      "name": "Burgers & Sandwiches"
    },
    "modifiers": [
      {
        "id": 4,
        "name": "Extra Cheese",
        "price_change": "1.50"
      }
    ],
    "created_at": "2025-10-31 10:15:00",
    "updated_at": "2025-10-31 10:30:00"
  }
}
```

---

### 5. Delete Menu Item

**Endpoint:** `DELETE /api/menu-items/{id}`

**Authorization:** Admin & Manager only

**Path Parameters:**
- `id` (required) - Menu item ID

**Example Request:**
```bash
curl -X DELETE "http://localhost:8000/api/menu-items/66" \
  -H "Accept: application/json" \
  -b cookies.txt
```

**Example Response (200 OK):**
```json
{
  "message": "Menu item deleted successfully."
}
```

**Error Response (404 Not Found):**
```json
{
  "message": "Menu item not found."
}
```

---

## Error Responses

### 401 Unauthorized
User is not authenticated.
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
User doesn't have required role.
```json
{
  "message": "Unauthorized. This action requires one of the following roles: Admin, Manager",
  "required_roles": ["Admin", "Manager"],
  "your_role": "Waiter"
}
```

### 404 Not Found
Resource not found.
```json
{
  "message": "Menu item not found."
}
```

### 422 Unprocessable Entity
Validation errors.
```json
{
  "message": "The price must be a valid number.",
  "errors": {
    "price": [
      "Price must be a valid number."
    ]
  }
}
```

---

## Testing with Different Roles

### As Admin (Full Access)
```bash
# Login as admin
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"admin@nexadon.com","password":"password"}'

# Can create, read, update, delete
curl -X POST "http://localhost:8000/api/menu-items" \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"name":"Test Item","price":9.99,"category_id":1}'
```

### As Manager (Full Access)
```bash
# Login as manager
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"manager@nexadon.com","password":"password"}'

# Can create, read, update, delete
curl -X DELETE "http://localhost:8000/api/menu-items/66" \
  -b cookies.txt
```

### As Cashier (Read Only)
```bash
# Login as cashier
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"cashier@nexadon.com","password":"password"}'

# Can only read
curl -X GET "http://localhost:8000/api/menu-items" \
  -b cookies.txt

# Cannot create (403 Forbidden)
curl -X POST "http://localhost:8000/api/menu-items" \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"name":"Test","price":9.99,"category_id":1}'
```

### As Waiter (Read Only)
```bash
# Login as waiter
curl -X POST "http://localhost:8000/api/login" \
  -H "Content-Type: application/json" \
  -c cookies.txt \
  -d '{"email":"waiter@nexadon.com","password":"password"}'

# Can only read
curl -X GET "http://localhost:8000/api/menu-items/1" \
  -b cookies.txt
```

---

## Frontend Integration (React/Vue Example)

### Setup
```javascript
import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://localhost:8000';
```

### Fetch Menu Items
```javascript
// Get all menu items with filters
const fetchMenuItems = async () => {
  try {
    const response = await axios.get('/api/menu-items', {
      params: {
        category_id: 1,
        is_available: true,
        per_page: 20
      }
    });
    return response.data.data;
  } catch (error) {
    console.error('Error fetching menu items:', error.response.data);
  }
};
```

### Create Menu Item (Admin/Manager)
```javascript
const createMenuItem = async (data) => {
  try {
    const response = await axios.post('/api/menu-items', {
      name: data.name,
      price: data.price,
      category_id: data.categoryId,
      is_available: true,
      modifier_ids: data.modifierIds
    });
    return response.data;
  } catch (error) {
    if (error.response.status === 403) {
      alert('You do not have permission to create menu items');
    } else if (error.response.status === 422) {
      console.error('Validation errors:', error.response.data.errors);
    }
  }
};
```

### Update Menu Item (Admin/Manager)
```javascript
const updateMenuItem = async (id, data) => {
  try {
    const response = await axios.put(`/api/menu-items/${id}`, data);
    return response.data;
  } catch (error) {
    console.error('Error updating menu item:', error.response.data);
  }
};
```

### Delete Menu Item (Admin/Manager)
```javascript
const deleteMenuItem = async (id) => {
  try {
    await axios.delete(`/api/menu-items/${id}`);
    alert('Menu item deleted successfully');
  } catch (error) {
    console.error('Error deleting menu item:', error.response.data);
  }
};
```

---

## Rate Limiting

API endpoints are subject to rate limiting:
- **60 requests per minute** for authenticated users
- Exceeding limits returns `429 Too Many Requests`

---

## Best Practices

1. **Always include error handling** in your requests
2. **Validate data** on frontend before sending to API
3. **Check user role** before showing create/update/delete UI
4. **Use pagination** for large datasets
5. **Cache menu items** on frontend to reduce API calls
6. **Handle 403 errors** gracefully with user-friendly messages

---

## Next Steps

Now that Menu Items CRUD is complete, you can:
1. Create similar controllers for Categories, Tables, Orders
2. Add more filtering and search options
3. Implement bulk operations
4. Add image upload for menu items
5. Create admin dashboard UI
