CAFELIA UPDATED INVENTORY + PAYMENT

Files to replace:
ROOT: menu.php, cart.php, checkout.php, orders.php
ADMIN: products.php, edit-product.php, add-stock.php (NEW), orders.php, sidebar.php
DATABASE: cafelia_inventory_payment_update.sql

Rules implemented:
- Maximum 6 units per product for customers.
- Quantity cannot exceed actual stock.
- JavaScript prevents quantity from going above stock/6 and updates displayed totals immediately.
- Server-side PHP repeats stock and quantity checks.
- Edit Product has NO stock input and NO stock update.
- Add Stock increases existing inventory.
- Completed orders have no status dropdown and are locked server-side.
- Cash/GCash payment is displayed in admin and customer orders.
- GCash receipt/reference number is saved in orders.gcash_receipt.
- Checkout reduces stock atomically and marks zero-stock products unavailable.
- Existing historical order quantities are not changed.

IMPORTANT: run the SQL migration before testing GCash checkout.
