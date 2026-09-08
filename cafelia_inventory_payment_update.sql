-- Cafelia inventory + GCash receipt update
-- Run this once in phpMyAdmin while using the Cafelia database.

ALTER TABLE orders
ADD COLUMN IF NOT EXISTS gcash_receipt VARCHAR(100) NULL AFTER payment_method;

-- Optional cleanup for any product that has zero stock but is still marked available.
UPDATE products
SET status = 'unavailable'
WHERE stock <= 0;

-- New products can be marked available again automatically when stock is added.
