# Task: Improve Takeaway Orders (Edit/Add Items)

## Completed Work
- [x] Added `edit` route in `routes/web.php`.
- [x] Added "Edit/Add Items" button in `resources/js/pages/TakeawayOrders.vue`.
- [x] Updated `resources/js/pages/TakeawayOrderTaking.vue` to support editing:
    - Accepts `orderId` prop.
    - Fetches existing order details.
    - Populates cart with existing items (preserving `order_item_id`).
    - Sends PUT request to update order.
- [x] Updated `resources/js/stores/cart.ts`:
    - Added `orderItemId` to `CartItem` interface.
    - Updated `addItem` to accept `orderItemId`.
    - Updated `getOrderItems` to include `order_item_id` in API payload.
- [x] Updated `app/Http/Requests/UpdateOrderRequest.php` to validate `order_item_id`.
- [x] Updated `app/Http/Controllers/Api/OrderController.php`:
    - Rewrote `update` method to sync items.
    - Handles adding new items.
    - Handles updating existing items (quantity, notes, modifiers).
    - Handles deleting removed items.

## Technical Details
- **Sync Logic**: The backend now compares incoming `order_item_id`s with existing ones.
    - IDs in DB but not in Request -> Deleted.
    - IDs in both -> Updated.
    - No ID (New Item) -> Created.
- **Cart Store**: Modified to track `orderItemId` so the frontend knows which items are existing vs new.

## Verification
- Verified `OrderController::update` logic handles all cases.
- Verified `TakeawayOrderTaking.vue` correctly populates the cart.
- Verified `cart.ts` changes are compatible with existing code.
