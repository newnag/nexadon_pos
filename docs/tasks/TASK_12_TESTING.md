# Task 12: Billing and Payment View - Quick Testing Guide

## 🚀 Quick Start

### Prerequisites
```bash
# Ensure servers are running
Terminal 1: php artisan serve
Terminal 2: npm run dev

# Create test order if needed
# Navigate to /orders?table_id=1
# Add items and send to kitchen
```

---

## 🧪 Test Scenarios

### **Test 1: Access Billing Page**

**Steps:**
1. Login as Cashier/Manager/Admin
2. Navigate to `/tables`
3. Click an occupied table (red)
4. Click "View Order #X" button in OrderView header
5. Should navigate to `/billing/{orderId}`

**Expected Results:**
- ✅ Billing page loads
- ✅ Order details displayed
- ✅ All sections visible (order info, items, price summary, payment methods)
- ✅ No loading errors

---

### **Test 2: Order Information Display**

**Verify Each Field:**
- Order Number: #10
- Table: T-05
- Server: John Waiter
- Date & Time: Oct 31, 2025, 10:30 AM
- Status: Pending (yellow badge)

**Expected Results:**
- ✅ All fields show correct data
- ✅ Date/time formatted properly
- ✅ Status badge has correct color

---

### **Test 3: Order Items Display**

**Check Each Item Shows:**
- Menu item name × quantity
- Category name
- Modifiers with price changes
- Special notes (if any)
- Item subtotal
- Unit price

**Example:**
```
Chicken Wings × 2
Appetizers
Add-ons: Extra Spicy (+฿1.00)
Note: No onions
฿19.98
฿8.99 each
```

**Expected Results:**
- ✅ All items listed
- ✅ Modifiers displayed correctly
- ✅ Notes shown in italic
- ✅ Subtotals accurate

---

### **Test 4: Price Calculations**

**Given:**
- Order total from database: ฿117.00

**Verify Calculations:**
```
Subtotal: ฿100.00 (117 / 1.17)
VAT (7%): ฿7.00 (100 × 0.07)
Service Charge (10%): ฿10.00 (100 × 0.10)
Total: ฿117.00
```

**Expected Results:**
- ✅ Subtotal = total / 1.17
- ✅ VAT = subtotal × 0.07
- ✅ Service = subtotal × 0.10
- ✅ Total = subtotal + VAT + service
- ✅ All amounts show 2 decimal places

---

### **Test 5: Split Bill Feature**

**Steps:**
1. Toggle "Split Bill" switch to ON
2. Default should be 2 people
3. Click "+" button 3 times → Should be 5 people
4. Click "−" button 2 times → Should be 3 people
5. Try to decrease below 2 → Should stop at 2

**Example Calculation:**
```
Total: ฿117.00
Split: 3 people
Per Person: ฿39.00
```

**Expected Results:**
- ✅ Toggle works
- ✅ +/− buttons functional
- ✅ Minimum 2 people enforced
- ✅ Per-person amount calculates correctly
- ✅ Formula shown: "3 people × ฿39.00 = ฿117.00"
- ✅ Turning toggle OFF hides split section

---

### **Test 6: Payment Method Selection**

**Steps:**
1. Click "Cash" button
2. Verify it highlights (blue border + blue background)
3. Click "Credit Card" button
4. Verify Cash de-selects, Credit Card highlights
5. Try clicking all 4 methods

**Expected Results:**
- ✅ Only one method selected at a time
- ✅ Selected: blue border, blue background, shadow
- ✅ Unselected: gray border, white background
- ✅ Hover effect on all buttons
- ✅ Icons display correctly

---

### **Test 7: Process Payment - Cash**

**Steps:**
1. Select "Cash" payment method
2. Click "Process Payment - ฿117.00" button
3. Confirmation dialog appears
4. Click "OK"

**Expected Results:**
- ✅ Confirmation dialog: "Confirm payment of ฿117.00 via cash?"
- ✅ Button shows "Processing..." with spinner
- ✅ Button disabled during processing
- ✅ Success modal appears after ~1-2 seconds
- ✅ Modal shows:
  - Green checkmark icon
  - "Payment Successful!" message
  - Order #10
  - Table T-05
  - Payment Method: cash
- ✅ Two action buttons: "Print Receipt" and "Return to Tables"

**Verify Database:**
```sql
SELECT * FROM payments WHERE order_id = 10;
-- Should show new payment record

SELECT status FROM orders WHERE id = 10;
-- Should be 'completed'

SELECT status FROM tables WHERE id = 5;
-- Should be 'available'
```

---

### **Test 8: Print Receipt**

**Steps:**
1. Complete payment (Test 7)
2. In success modal, click "Print Receipt"
3. Browser print dialog opens

**Expected Results:**
- ✅ Print dialog appears
- ✅ Print preview shows:
  - Order details
  - Items list
  - Price summary
  - Payment info
- ✅ Buttons/navigation hidden in preview
- ✅ Clean formatting
- ✅ Modal closes after print

---

### **Test 9: Return to Tables**

**Steps:**
1. Complete payment
2. In success modal, click "Return to Tables"

**Expected Results:**
- ✅ Navigates to `/tables`
- ✅ Table shows as "available" (green)
- ✅ No active order on table card

---

### **Test 10: Already Paid Order**

**Steps:**
1. Navigate to billing page of a completed order
2. URL: `/billing/{paid_order_id}`

**Expected Results:**
- ✅ Order details display normally
- ✅ Green banner shows: "Payment Completed"
- ✅ Banner shows: "Paid via Cash - ฿117.00"
- ✅ Payment method buttons disabled (grayed out)
- ✅ "Process Payment" button NOT visible
- ✅ Cannot select payment methods

---

### **Test 11: Payment with Credit Card**

**Steps:**
1. Access unpaid order billing page
2. Select "Credit Card" method
3. Process payment

**Expected Results:**
- ✅ Same workflow as cash
- ✅ Success modal shows "credit_card" as payment method
- ✅ Database payment record has payment_method = 'credit_card'

**Test All Methods:**
- Cash
- Credit Card
- Debit Card
- QR Payment

---

### **Test 12: Split Bill + Payment**

**Steps:**
1. Enable split bill
2. Set to 4 people
3. Verify per-person amount: ฿29.25 (for ฿117 total)
4. Select payment method
5. Process payment

**Expected Results:**
- ✅ Split bill shown during process
- ✅ Full amount (not per-person) sent to API
- ✅ Payment processes correctly
- ✅ Total ฿117.00 recorded (not ฿29.25)

**Note:** Split bill is display-only, doesn't affect actual payment amount.

---

### **Test 13: Amount Mismatch (Backend Validation)**

**Steps:**
1. Open browser DevTools
2. Go to Network tab
3. Select payment method
4. Click "Process Payment"
5. In DevTools, find the POST /api/payments request
6. Right-click → Copy as cURL
7. Modify amount in cURL command
8. Execute modified request

**Expected Results:**
- ✅ Backend returns 422 error
- ✅ Error message: "Payment amount does not match order total."
- ✅ Response includes expected and received amounts
- ✅ Order status NOT changed
- ✅ Table status NOT changed

---

### **Test 14: Double Payment Prevention**

**Steps:**
1. Complete payment for order #10
2. Open another browser tab
3. Navigate to `/billing/10` again
4. Try to process payment again

**Expected Results:**
- ✅ Page shows "Payment Completed" banner
- ✅ Payment methods disabled
- ✅ Cannot process payment again

**Or if trying via API:**
```bash
curl -X POST http://localhost:8000/api/payments \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"order_id":10,"payment_method":"cash","amount":"117.00"}'
```

**Expected Response:**
```json
{
  "message": "This order has already been paid."
}
```
- ✅ Returns 422 status code

---

### **Test 15: Cancelled Order**

**Steps:**
1. In database, update order status to 'cancelled'
```sql
UPDATE orders SET status = 'cancelled' WHERE id = 10;
```
2. Navigate to `/billing/10`
3. Try to process payment

**Expected Results:**
- ✅ Order loads normally
- ✅ Status shows "Cancelled" (gray badge)
- ✅ Can select payment method
- ✅ Clicking "Process Payment" succeeds on frontend
- ✅ Backend returns 422 error
- ✅ Error: "Cannot process payment for a cancelled order."

**Cleanup:**
```sql
UPDATE orders SET status = 'pending' WHERE id = 10;
```

---

### **Test 16: Network Error Handling**

**Steps:**
1. Stop Laravel server: Ctrl+C
2. Access billing page in browser
3. Try to load order

**Expected Results:**
- ✅ Loading spinner appears
- ✅ After timeout, red error banner shows
- ✅ Error message displayed
- ✅ Page doesn't crash

**Then:**
1. Restart Laravel server
2. Reload page

**Expected Results:**
- ✅ Order loads successfully

---

### **Test 17: Invalid Order ID**

**Steps:**
1. Navigate to `/billing/99999` (non-existent ID)

**Expected Results:**
- ✅ Loading spinner appears
- ✅ Red error banner shows
- ✅ Error: "Order not found" or similar
- ✅ Back button still functional

---

### **Test 18: Role Permissions**

**Steps:**
1. Login as **Waiter** role
2. Try to access `/billing/1`

**Expected Results:**
- ✅ Redirected to `/dashboard`
- ✅ Access denied (Waiter not in allowed roles)

**Then:**
1. Login as **Cashier** role
2. Access `/billing/1`

**Expected Results:**
- ✅ Full access granted
- ✅ Can process payment

**Allowed Roles:**
- Admin ✅
- Manager ✅
- Cashier ✅
- Waiter ❌
- Kitchen ❌

---

### **Test 19: Responsive Design**

**Resize Browser Window:**
- Desktop: 1920px
- Tablet: 768px
- Mobile: 375px

**Expected Results:**
- ✅ Desktop: Full layout, spacious
- ✅ Tablet: Adjusted padding, 2×2 payment grid maintained
- ✅ Mobile: Stacked elements, full-width buttons
- ✅ No horizontal scroll
- ✅ All text readable
- ✅ Buttons touch-friendly

---

### **Test 20: Complete Order Flow**

**Full E2E Test:**

1. **Create Order:**
   - Navigate to `/tables`
   - Click available table (green)
   - Add 3 different menu items with modifiers
   - Send to kitchen
   - Table turns red (occupied)

2. **Access Billing:**
   - Click occupied table
   - Click "View Order" button in OrderView
   - Should navigate to billing page

3. **Review:**
   - Verify all items correct
   - Check price calculations
   - Enable split bill for 3 people
   - Verify per-person amount

4. **Pay:**
   - Select QR Payment
   - Click "Process Payment"
   - Confirm
   - Wait for success

5. **Complete:**
   - Print receipt (verify preview)
   - Return to tables
   - Verify table is green (available)

**Expected Results:**
- ✅ All steps complete smoothly
- ✅ No errors at any stage
- ✅ Database reflects changes
- ✅ Table status cycle complete

---

## 🐛 Common Issues & Solutions

### **Issue: "Order not loading"**
**Cause:** Server not running or database empty  
**Fix:**
```bash
# Check server
php artisan serve

# Check orders exist
php artisan tinker
>>> Order::count();
```

---

### **Issue: "Price calculations wrong"**
**Cause:** Backend order total doesn't include VAT/service  
**Fix:** Verify backend calculation in OrderController includes 1.17 multiplier

---

### **Issue: "Payment button disabled"**
**Cause:** No payment method selected or order already paid  
**Fix:** Select a payment method; check order.payment is null

---

### **Issue: "Success modal not showing"**
**Cause:** API error or network issue  
**Fix:** Check browser console for errors; verify API response

---

### **Issue: "Print doesn't work"**
**Cause:** Browser print blocked or no printer configured  
**Fix:** Allow print in browser settings; check print preview

---

## ✅ Success Criteria

All tests should pass with:
- ✅ No console errors
- ✅ All UI elements functional
- ✅ Accurate calculations
- ✅ Database correctly updated
- ✅ Proper error handling
- ✅ Smooth user experience

---

## 📊 Test Coverage Checklist

| Feature | Test Cases | Status |
|---------|-----------|--------|
| Page Access | 1, 17, 18 | ⬜ |
| Order Display | 2, 3 | ⬜ |
| Price Calculations | 4 | ⬜ |
| Split Bill | 5, 12 | ⬜ |
| Payment Methods | 6, 11 | ⬜ |
| Payment Processing | 7, 9 | ⬜ |
| Already Paid | 10 | ⬜ |
| Print Receipt | 8 | ⬜ |
| Error Handling | 13, 14, 15, 16 | ⬜ |
| Permissions | 18 | ⬜ |
| Responsive | 19 | ⬜ |
| E2E Flow | 20 | ⬜ |

---

## 🎯 Quick Test Commands

```bash
# Create test order via Tinker
php artisan tinker
>>> $order = Order::create(['table_id' => 1, 'user_id' => 1, 'status' => 'pending', 'total_amount' => 117.00]);
>>> $item = $order->orderItems()->create(['menu_item_id' => 1, 'quantity' => 2, 'subtotal' => 19.98]);
>>> $order->id; // Note the ID

# Check payment records
>>> Payment::where('order_id', 10)->first();

# Reset order for re-testing
>>> $order = Order::find(10);
>>> $order->payment()->delete();
>>> $order->update(['status' => 'pending']);
>>> $order->table->update(['status' => 'occupied']);
```

---

## 🚀 Next Steps After Testing

1. Fix any bugs found
2. Optimize performance
3. Add print receipt template
4. Implement tip/gratuity feature
5. Add discount/promo code support
6. Move to next task or deploy!

---

**Happy Testing! 💳**
