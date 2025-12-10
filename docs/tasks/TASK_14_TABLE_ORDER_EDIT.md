# Task: Enable Edit/Cancel Order in Table Management

## Completed Work
- [x] Modified `resources/js/pages/OrderTaking.vue`:
    - Updated `fetchAvailableTables` to fetch existing order details if the table is occupied.
    - Implemented `fetchOrderDetails` to populate the cart with existing items (preserving `order_item_id`).
    - Added "Cancel Order" button in the sidebar when a table with an active order is selected.
    - Implemented `cancelOrder` function to set order status to 'cancelled'.

## Technical Details
- **Integration**: Reused the "Sync" logic implemented for Takeaway orders.
- **Flow**:
    1. User clicks an occupied table in `TableMapView`.
    2. Navigates to `OrderTaking` with `table_id`.
    3. `OrderTaking` detects occupied status and fetches order details.
    4. Cart is populated.
    5. User can add/remove items or cancel the entire order.
    6. "Place Order" sends a PUT request with the updated item list.
    7. "Cancel Order" sends a PUT request with `status: 'cancelled'`.

## Verification
- Verified `OrderTaking.vue` logic for fetching and populating data.
- Verified `cancelOrder` calls the correct API endpoint.
- Verified `placeOrder` handles both Create (POST) and Update (PUT) scenarios.
