<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$product_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$product_id || $product_id <= 0) {
    header("Location: products.php?delete_error=invalid");
    exit();
}

/* Get product */
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
$stmt->execute();

$result = $stmt->get_result();
$product = $result->fetch_assoc();

$stmt->close();

if (!$product) {
    $conn->close();
    header("Location: products.php?delete_error=not_found");
    exit();
}

$product_name = $product["name"];
$image_name   = $product["image"];

/* Check if product is already part of an order */
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
$history_stmt->execute();

$history_result = $history_stmt->get_result();
$history = $history_result->fetch_assoc();

$history_stmt->close();

$order_count = (int) ($history["order_count"] ?? 0);

/*
 * If the product has order history,
 * don't physically delete it.
 * Mark it unavailable instead.
 */
if ($order_count > 0) {

    $update_stmt = $conn->prepare(
        "UPDATE products
         SET status = 'unavailable'
         WHERE id = ?"
    );

    if (!$update_stmt) {
        $conn->close();
        header("Location: products.php?delete_error=database");
        exit();
    }

    $update_stmt->bind_param("i", $product_id);

    if ($update_stmt->execute()) {
        $update_stmt->close();
        $conn->close();

        header("Location: products.php?archived=1");
        exit();
    }

    $update_stmt->close();
    $conn->close();

    header("Location: products.php?delete_error=database");
    exit();
}

/*
 * Product has never been ordered.
 * It can be safely deleted.
 */
$conn->begin_transaction();

try {

    /* Remove from carts first */
    $cart_stmt = $conn->prepare(
        "DELETE FROM cart
         WHERE product_id = ?"
    );

    if (!$cart_stmt) {
        throw new Exception("Unable to prepare cart cleanup.");
    }

    $cart_stmt->bind_param("i", $product_id);
    $cart_stmt->execute();
    $cart_stmt->close();

    /* Delete product */
    $delete_stmt = $conn->prepare(
        "DELETE FROM products
         WHERE id = ?"
    );

    if (!$delete_stmt) {
        throw new Exception("Unable to prepare product deletion.");
    }

    $delete_stmt->bind_param("i", $product_id);

    if (!$delete_stmt->execute()) {
        throw new Exception($delete_stmt->error);
    }

    if ($delete_stmt->affected_rows !== 1) {
        throw new Exception("Product could not be deleted.");
    }

    $delete_stmt->close();

    $conn->commit();

    /* Delete product image */
    if (!empty($image_name)) {
        $safe_image_name = basename($image_name);
        $image_path = "../image/" . $safe_image_name;

        if (is_file($image_path)) {
            @unlink($image_path);
        }
    }

    $conn->close();

    header("Location: products.php?deleted=1");
    exit();

} catch (Throwable $e) {

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