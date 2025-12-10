# Task: Add Cancel Order Functionality

## Completed Work
- [x] Modified `resources/js/pages/TakeawayOrders.vue`:
    - Added "Cancel Order" button (Red trash icon) for unpaid orders.
    - Implemented `cancelOrder` function that calls `PUT /orders/{id}` with `status: 'cancelled'`.
- [x] Modified `resources/js/pages/TakeawayOrderTaking.vue`:
    - Added "Cancel Order" button in the sidebar when in Edit Mode (`props.orderId` exists).
    - Implemented `cancelOrder` function.

## Technical Details
- **Cancel Logic**: Uses the existing `OrderController::update` endpoint which accepts `status`.
- **UI**: Added red buttons with confirmation dialogs to prevent accidental cancellation.
- **Menu Item Cancellation**: Handled via the existing "Edit Order" flow where removing an item from the cart and saving will delete it from the order.

## Verification
- Verified "Cancel Order" button appears in list view.
- Verified "Cancel Order" button appears in edit view.
- Verified confirmation dialogs work.
