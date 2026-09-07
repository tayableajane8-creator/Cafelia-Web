<?php

session_start();
require_once "../config/database.php";

// ==========================================
// CHECK ADMIN LOGIN
// ==========================================

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// CHECK PRODUCT ID
// ==========================================

$product_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$product_id || $product_id <= 0) {
    header("Location: products.php?delete_error=invalid");
    exit();
}

// ==========================================
// GET PRODUCT
// ==========================================

$stmt = $conn->prepare(
    "SELECT id, name, image
     FROM products
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    header("Location: products.php?delete_error=database");
    exit();
}

$stmt->bind_param("i", $product_id);

if (!$stmt->execute()) {
    $stmt->close();
    header("Location: products.php?delete_error=database");
    exit();
}

$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    $conn->close();
    header("Location: products.php?delete_error=not_found");
    exit();
}

$product_name = $product["name"] ?? "";
$image_name = $product["image"] ?? "";

// ==========================================
// CHECK ORDER HISTORY
// ==========================================
// Products that already appear in an order should not be
// physically deleted because the order history depends on them.

$history_stmt = $conn->prepare(
    "SELECT COUNT(*) AS order_count
     FROM order_items
     WHERE product_id = ?"
);

if (!$history_stmt) {
    $conn->close();
    header("Location: products.php?delete_error=database");
    exit();
}

$history_stmt->bind_param("i", $product_id);

if (!$history_stmt->execute()) {
    $history_stmt->close();
    $conn->close();
    header("Location: products.php?delete_error=database");
    exit();
}

$history_result = $history_stmt->get_result();
$history = $history_result->fetch_assoc();
$order_count = (int) ($history["order_count"] ?? 0);

$history_stmt->close();

if ($order_count > 0) {
    $conn->close();
    header("Location: products.php?delete_error=ordered");
    exit();
}

// ==========================================
// DELETE PRODUCT SAFELY
// ==========================================

$conn->begin_transaction();

try {

    // Remove the product from any customer's cart first.
    // This prevents a foreign-key constraint from blocking deletion.
    $cart_stmt = $conn->prepare(
        "DELETE FROM cart WHERE product_id = ?"
    );

    if (!$cart_stmt) {
        throw new Exception("Unable to prepare cart cleanup.");
    }

    $cart_stmt->bind_param("i", $product_id);

    if (!$cart_stmt->execute()) {
        $cart_stmt->close();
        throw new Exception("Unable to remove product from carts.");
    }

    $cart_stmt->close();

    // Delete the product itself.
    $delete_stmt = $conn->prepare(
        "DELETE FROM products WHERE id = ?"
    );

    if (!$delete_stmt) {
        throw new Exception("Unable to prepare product deletion.");
    }

    $delete_stmt->bind_param("i", $product_id);

    if (!$delete_stmt->execute()) {
        $db_error = $delete_stmt->error;
        $delete_stmt->close();
        throw new Exception($db_error);
    }

    if ($delete_stmt->affected_rows !== 1) {
        $delete_stmt->close();
        throw new Exception("The product could not be deleted.");
    }

    $delete_stmt->close();

    // Commit database changes before touching the image file.
    if (!$conn->commit()) {
        throw new Exception("The product deletion could not be completed.");
    }

    // ==========================================
    // DELETE PRODUCT IMAGE
    // ==========================================

    if ($image_name !== "") {
        // The products table stores the image filename.
        // basename() prevents accidental path traversal.
        $safe_image_name = basename($image_name);
        $image_path = "../image/" . $safe_image_name;

        if (is_file($image_path)) {
            @unlink($image_path);
        }
    }

    $conn->close();

    // ==========================================
    // SUCCESS
    // ==========================================

    header("Location: products.php?deleted=1");
    exit();

} catch (Throwable $e) {

    // Undo all database changes if anything failed.
    $conn->rollback();
    $conn->close();

    error_log(
        "Cafelia product deletion error for product {$product_id}: " .
        $e->getMessage()
    );

    header("Location: products.php?delete_error=database");
    exit();
}
?>