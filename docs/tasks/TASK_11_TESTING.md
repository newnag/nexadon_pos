# Task 11: Order Taking View - Quick Testing Guide

## 🚀 Quick Start

### 1. Start Servers
```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev
```

### 2. Open Browser
```
http://localhost:8000
```

---

## 🧪 Test Scenarios

### **Test 1: View Order Interface**
**Steps:**
1. Login with test account (Admin/Manager/Cashier/Waiter)
2. Navigate to `/tables` page
3. Click on an **available table** (green)
4. Should navigate to `/orders?table_id={id}`

**Expected Results:**
- ✅ Three-column layout displays
- ✅ Left: Categories list with "All Items" button
- ✅ Middle: Menu items in 3-column grid
- ✅ Right: Empty cart with "Cart is empty" message
- ✅ Header shows table number (e.g., "Table: T-01")

---

### **Test 2: Category Filtering**
**Steps:**
1. On OrderView page
2. Click "All Items" (should be selected by default)
3. Click "Appetizers" category
4. Click "Main Courses" category
5. Click "Desserts" category
6. Click "All Items" again

**Expected Results:**
- ✅ "All Items": Shows all menu items
- ✅ "Appetizers": Shows only appetizer items
- ✅ "Main Courses": Shows only main course items
- ✅ Selected category has blue background
- ✅ Menu items grid updates instantly

---

### **Test 3: Search Menu Items**
**Steps:**
1. In search box, type "chicken"
2. Type "pizza"
3. Type "coffee"
4. Clear search box

**Expected Results:**
- ✅ Results filter as you type
- ✅ Shows items matching name or category
- ✅ "No items found" if no matches
- ✅ Clearing search shows all items again

---

### **Test 4: Add Item to Cart (Simple)**
**Steps:**
1. Click "Chicken Wings" menu item card
2. Modal opens
3. Leave quantity at 1
4. Leave modifiers unchecked
5. Leave notes empty
6. Click "Add to Cart"

**Expected Results:**
- ✅ Modal opens with item details
- ✅ Price displayed correctly
- ✅ Modal closes after clicking "Add to Cart"
- ✅ Item appears in cart (right column)
- ✅ Cart shows: name, quantity (1), price
- ✅ Total updates to item price

---

### **Test 5: Add Item with Modifiers**
**Steps:**
1. Click "Burger" (or any item with modifiers)
2. Set quantity to 2
3. Check "Extra Cheese" (+฿1.00)
4. Check "Bacon" (+฿2.00)
5. Add special instructions: "No onions"
6. Click "Add to Cart"

**Expected Results:**
- ✅ Quantity shows "2"
- ✅ Selected modifiers shown in modal
- ✅ Cart item shows modifiers: "+ Extra Cheese (฿1.00), + Bacon (฿2.00)"
- ✅ Cart item shows note: "Note: No onions"
- ✅ Subtotal calculated correctly: (base + modifiers) × quantity
  - Example: (8.99 + 1.00 + 2.00) × 2 = ฿23.98

---

### **Test 6: Adjust Quantity in Cart**
**Steps:**
1. Add "Chicken Wings" to cart (qty 1)
2. In cart, click "+" button on the item
3. Click "+" again
4. Click "-" button
5. Click "-" twice more (should remove item)

**Expected Results:**
- ✅ Quantity increases to 2, then 3
- ✅ Subtotal updates each time
- ✅ Total amount updates
- ✅ Quantity decreases to 2, then 1
- ✅ Item removed when quantity reaches 0
- ✅ Cart shows "Cart is empty" again

---

### **Test 7: Remove Item from Cart**
**Steps:**
1. Add 3 different items to cart
2. Click "X" button on the middle item

**Expected Results:**
- ✅ Middle item removed immediately
- ✅ Other 2 items remain
- ✅ Total amount recalculated
- ✅ Item count updates (shows "2 items")

---

### **Test 8: Send to Kitchen (New Order)**
**Steps:**
1. Start with empty cart
2. Add "Chicken Wings" (qty 2)
3. Add "Garlic Bread" (qty 1)
4. Add "Coke" (qty 2)
5. Verify total in cart
6. Click "Send to Kitchen"

**Expected Results:**
- ✅ Button shows "Sending..." while processing
- ✅ Success alert: "Order sent to kitchen successfully!"
- ✅ Cart cleared automatically
- ✅ Redirected to `/tables` page
- ✅ Table status changed to "occupied" (red)
- ✅ Table card shows "Order #X" and total amount

**Verify in Database:**
```sql
SELECT * FROM orders WHERE table_id = 1 ORDER BY id DESC LIMIT 1;
SELECT * FROM order_items WHERE order_id = [last_order_id];
```

---

### **Test 9: Add to Existing Order**
**Steps:**
1. From `/tables`, click an **occupied table** (red)
2. Should navigate to `/orders?table_id={id}&order_id={existing_order_id}`
3. Header should show "View Order #X" button
4. Add "Dessert" to cart
5. Click "Send to Kitchen"

**Expected Results:**
- ✅ Existing order details shown in header
- ✅ "View Order" button visible
- ✅ Can add items to cart as normal
- ✅ Success alert: "Order updated successfully!"
- ✅ Cart cleared, redirected to tables
- ✅ Order total increased in database

---

### **Test 10: Clear Cart**
**Steps:**
1. Add 3 items to cart
2. Click "Clear Cart" button
3. Confirmation dialog appears
4. Click "OK"

**Expected Results:**
- ✅ Confirmation dialog shows
- ✅ All items removed from cart
- ✅ Total reset to ฿0.00
- ✅ Shows "Cart is empty" message
- ✅ "Send to Kitchen" button disabled

---

### **Test 11: Unavailable Items**
**Steps:**
1. In database, set one menu item to `is_available = 0`
2. Refresh OrderView page
3. Find the unavailable item
4. Try to click it

**Expected Results:**
- ✅ Item card shows "Unavailable" red badge
- ✅ Clicking shows alert: "Item is currently unavailable."
- ✅ Modal does not open

**SQL to test:**
```sql
UPDATE menu_items SET is_available = 0 WHERE id = 1;
-- Then refresh page, then revert:
UPDATE menu_items SET is_available = 1 WHERE id = 1;
```

---

### **Test 12: No Table Selected**
**Steps:**
1. Manually navigate to `/orders` (without query params)
2. Add items to cart
3. Click "Send to Kitchen"

**Expected Results:**
- ✅ Alert: "No table selected. Please select a table first."
- ✅ Cart NOT cleared
- ✅ Stays on OrderView page

---

### **Test 13: Empty Cart Submit**
**Steps:**
1. Navigate to `/orders?table_id=1`
2. Do NOT add any items
3. Try to click "Send to Kitchen"

**Expected Results:**
- ✅ Button is disabled (grayed out, not clickable)
- ✅ Cursor shows "not-allowed"
- ✅ Nothing happens when clicked

---

### **Test 14: Modal Cancel**
**Steps:**
1. Click menu item to open modal
2. Set quantity to 3
3. Select modifiers
4. Type notes
5. Click "X" button (top-right corner)

**Expected Results:**
- ✅ Modal closes
- ✅ Item NOT added to cart
- ✅ No changes to cart

**Repeat with clicking outside modal:**
1. Open modal again
2. Click dark background outside modal

**Expected Results:**
- ✅ Modal closes
- ✅ Item NOT added to cart

---

### **Test 15: Multiple Items with Same Menu Item**
**Steps:**
1. Add "Burger" with "Extra Cheese" (qty 1)
2. Add "Burger" with "Bacon" (qty 2)
3. Add "Burger" with no modifiers (qty 1)

**Expected Results:**
- ✅ Three separate entries in cart
- ✅ Each shows different modifiers
- ✅ Each has correct subtotal
- ✅ Total = sum of all three

---

### **Test 16: Large Order**
**Steps:**
1. Add 10 different menu items to cart
2. Scroll cart area
3. Adjust quantities
4. Remove some items
5. Send to kitchen

**Expected Results:**
- ✅ Cart scrollable (right column has overflow-y-auto)
- ✅ All operations work smoothly
- ✅ Total always accurate
- ✅ Order submits successfully

---

### **Test 17: Network Error Handling**
**Steps:**
1. Stop Laravel server: `Ctrl+C` in Terminal 1
2. In browser, add items to cart
3. Click "Send to Kitchen"
4. Restart Laravel server

**Expected Results:**
- ✅ Error alert: "Failed to send order. Please try again."
- ✅ Cart NOT cleared
- ✅ User can retry
- ✅ After server restart, retry succeeds

---

### **Test 18: Role Permissions**
**Steps:**
1. Login as **Waiter** role
2. Navigate to `/orders?table_id=1`

**Expected Results:**
- ✅ Full access to OrderView
- ✅ Can add items, send orders

**Steps:**
1. Login as **Kitchen** role
2. Try to navigate to `/orders`

**Expected Results:**
- ✅ Redirected to dashboard (no permission)

---

### **Test 19: Responsive Design**
**Steps:**
1. Resize browser window to:
   - Desktop (1920px)
   - Tablet (768px)
   - Mobile (375px)

**Expected Results:**
- ✅ Desktop: Full 3-column layout
- ✅ Tablet: Columns adjust, still usable
- ✅ Mobile: Layout may need improvement (future enhancement)

---

### **Test 20: Real-time Updates**
**Steps:**
1. Open OrderView on Table 1
2. In another browser tab, open Kitchen Display
3. In first tab, send order to kitchen
4. Check Kitchen Display

**Expected Results:**
- ✅ New order appears in Kitchen Display automatically
- ✅ No page refresh needed (via Laravel Broadcasting)

---

## 🐛 Common Issues & Fixes

### **Issue: "Menu items not loading"**
**Cause:** Laravel server not running or database empty  
**Fix:**
```bash
# Check server
php artisan serve

# Check database has items
php artisan db:seed --class=MenuItemSeeder
```

---

### **Issue: "Categories not showing"**
**Cause:** No menu items fetched  
**Fix:** Check browser console for API errors

---

### **Issue: "Cart not updating"**
**Cause:** Pinia store not initialized  
**Fix:** Verify `app.ts` imports and uses Pinia:
```typescript
import { createPinia } from 'pinia';
app.use(createPinia());
```

---

### **Issue: "Modal not closing"**
**Cause:** JavaScript error in modal  
**Fix:** Check browser console for errors

---

### **Issue: "Total amount wrong"**
**Cause:** Subtotal calculation logic error  
**Fix:** Verify cart store `calculateSubtotal()` method

---

## ✅ Success Criteria

All tests should pass with:
- ✅ No console errors
- ✅ No visual glitches
- ✅ Fast, responsive interactions
- ✅ Accurate calculations
- ✅ Database correctly updated
- ✅ Smooth navigation flow

---

## 📊 Test Coverage Summary

| Feature | Test Cases | Status |
|---------|-----------|--------|
| Layout & Navigation | 1, 19 | ⬜ |
| Category Filtering | 2 | ⬜ |
| Search | 3 | ⬜ |
| Add to Cart | 4, 5, 15 | ⬜ |
| Cart Management | 6, 7, 10, 16 | ⬜ |
| Order Submission | 8, 9, 13, 17 | ⬜ |
| Modifiers | 5 | ⬜ |
| Availability | 11 | ⬜ |
| Edge Cases | 12, 14, 17 | ⬜ |
| Permissions | 18 | ⬜ |
| Real-time | 20 | ⬜ |

---

## 🎯 Next Steps After Testing

1. Fix any bugs found during testing
2. Optimize performance if needed
3. Add mobile responsive improvements
4. Implement image upload for menu items
5. Move to Task 12 (if any) or deploy!

---

**Happy Testing! 🚀**
