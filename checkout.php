<?php

session_start();
require_once "config/database.php";

/* =========================================================
   CUSTOMER ACCESS
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$error = "";
$old = [
    "customer_name" => "",
    "customer_email" => "",
    "phone" => "",
    "address" => "",
    "payment_method" => ""
];

/* =========================================================
   DATABASE HELPERS
========================================================= */

function getCartProducts(mysqli $conn, int $user_id): array
{
    $products = [];

    $sql = "
        SELECT
            c.product_id,
            c.quantity,
            p.name,
            p.price
        FROM cart AS c
        INNER JOIN products AS p
            ON p.id = c.product_id
        WHERE c.user_id = ?
          AND p.status = 'available'
          AND c.quantity > 0
        ORDER BY c.id ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Unable to load the cart.");
    }

    $stmt->bind_param("i", $user_id);

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Unable to load the cart.");
    }

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row["product_id"] = (int) $row["product_id"];
        $row["quantity"] = (int) $row["quantity"];
        $row["price"] = (float) $row["price"];
        $row["subtotal"] = $row["price"] * $row["quantity"];

        $products[] = $row;
    }

    $stmt->close();

    return $products;
}

function getCartCount(mysqli $conn, int $user_id): int
{
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS cart_count
         FROM cart
         WHERE user_id = ?"
    );

    if (!$stmt) {
        return 0;
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return (int) ($row["cart_count"] ?? 0);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

/* =========================================================
   GET CUSTOMER
========================================================= */

$stmt = $conn->prepare(
    "SELECT name, email
     FROM users
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error. Please try again later.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$stmt->close();

if (!$user) {
    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit();
}

/* =========================================================
   INITIAL FORM VALUES
========================================================= */

$old["customer_name"] = (string) ($user["name"] ?? "");
$old["customer_email"] = (string) ($user["email"] ?? "");

/* =========================================================
   LOAD CART
========================================================= */

try {
    $cart_products = getCartProducts($conn, $user_id);
} catch (Exception $e) {
    error_log("Cafelia checkout cart error: " . $e->getMessage());
    $cart_products = [];
    $error = "We couldn't load your cart. Please return to your cart and try again.";
}

$grand_total = 0.00;

foreach ($cart_products as $product) {
    $grand_total += (float) $product["subtotal"];
}

$cart_count = getCartCount($conn, $user_id);

/* =========================================================
   REDIRECT IF CART IS EMPTY
========================================================= */

if (empty($cart_products) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit();
}

/* =========================================================
   PLACE ORDER
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $old["customer_name"] = trim($_POST["customer_name"] ?? "");
    $old["customer_email"] = trim($_POST["customer_email"] ?? "");
    $old["phone"] = trim($_POST["phone"] ?? "");
    $old["address"] = trim($_POST["address"] ?? "");
    $old["payment_method"] = trim($_POST["payment_method"] ?? "");

    /* -------------------------
       VALIDATION
    ------------------------- */

    if (
        $old["customer_name"] === "" ||
        $old["customer_email"] === "" ||
        $old["phone"] === "" ||
        $old["address"] === "" ||
        $old["payment_method"] === ""
    ) {
        $error = "Please complete all required checkout fields.";

    } elseif (!filter_var($old["customer_email"], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";

    } elseif (strlen($old["customer_name"]) < 2) {
        $error = "Please enter your complete name.";

    } elseif (!preg_match("/^[0-9+()\\-\\s]{7,20}$/", $old["phone"])) {
        $error = "Please enter a valid phone number.";

    } elseif (!in_array(
        $old["payment_method"],
        ["Cash on Delivery", "GCash"],
        true
    )) {
        $error = "Please select a valid payment method.";

    } else {

        /*
         * Re-read the cart immediately before checkout.
         * This prevents an outdated checkout page from creating
         * an order using old quantities.
         */
        try {
            $cart_products = getCartProducts($conn, $user_id);
        } catch (Exception $e) {
            error_log("Cafelia checkout refresh error: " . $e->getMessage());
            $cart_products = [];
            $error = "We couldn't verify your cart. Please try again.";
        }

        $grand_total = 0.00;

        foreach ($cart_products as $product) {
            $grand_total += (float) $product["subtotal"];
        }

        if (empty($cart_products)) {

            $error = "Your cart is empty. Please add an item before checking out.";

        } elseif ($grand_total <= 0) {

            $error = "Your cart total is invalid. Please return to your cart.";

        } else {

            $transaction_started = false;

            try {

                /* =================================================
                   START TRANSACTION
                ================================================= */

                $conn->begin_transaction();
                $transaction_started = true;

                /* =================================================
                   CREATE ORDER
                ================================================= */

                $status = "Pending";

                $order_stmt = $conn->prepare(
                    "INSERT INTO orders
                    (
                        user_id,
                        customer_name,
                        customer_email,
                        phone,
                        address,
                        total_amount,
                        payment_method,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );

                if (!$order_stmt) {
                    throw new Exception(
                        "Unable to prepare the order record: " . $conn->error
                    );
                }

                $order_stmt->bind_param(
                    "issssdss",
                    $user_id,
                    $old["customer_name"],
                    $old["customer_email"],
                    $old["phone"],
                    $old["address"],
                    $grand_total,
                    $old["payment_method"],
                    $status
                );

                if (!$order_stmt->execute()) {
                    $db_error = $order_stmt->error;
                    $order_stmt->close();

                    throw new Exception(
                        "Order insert failed: " . $db_error
                    );
                }

                $order_id = (int) $conn->insert_id;

                $order_stmt->close();

                if ($order_id <= 0) {
                    throw new Exception("The order ID could not be created.");
                }

                /* =================================================
                   CREATE ORDER ITEMS
                ================================================= */

                $item_stmt = $conn->prepare(
                    "INSERT INTO order_items
                    (
                        order_id,
                        product_id,
                        product_name,
                        price,
                        quantity,
                        subtotal
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if (!$item_stmt) {
                    throw new Exception(
                        "Unable to prepare order items: " . $conn->error
                    );
                }

                foreach ($cart_products as $product) {

                    $product_id = (int) $product["product_id"];
                    $product_name = (string) $product["name"];
                    $price = (float) $product["price"];
                    $quantity = (int) $product["quantity"];
                    $subtotal = (float) $product["subtotal"];

                    if ($product_id <= 0 || $quantity <= 0) {
                        throw new Exception("Invalid product in cart.");
                    }

                    $item_stmt->bind_param(
                        "iisdid",
                        $order_id,
                        $product_id,
                        $product_name,
                        $price,
                        $quantity,
                        $subtotal
                    );

                    if (!$item_stmt->execute()) {
                        $db_error = $item_stmt->error;
                        $item_stmt->close();

                        throw new Exception(
                            "Order item insert failed: " . $db_error
                        );
                    }
                }

                $item_stmt->close();

                /* =================================================
                   REDUCE PRODUCT STOCK
                ================================================= */
                $stock_stmt = $conn->prepare(
                    "UPDATE products
                     SET stock = stock - ?
                     WHERE id = ?
                       AND stock >= ?"
                );

                if (!$stock_stmt) {
                    throw new Exception(
                        "Unable to prepare stock update: " . $conn->error
                    );
                }

                foreach ($cart_products as $product) {
                    $product_id = (int) $product["product_id"];
                    $quantity = (int) $product["quantity"];

                    if ($product_id <= 0 || $quantity <= 0) {
                        throw new Exception("Invalid product quantity in cart.");
                    }

                    $stock_stmt->bind_param(
                        "iii",
                        $quantity,
                        $product_id,
                        $quantity
                    );

                    if (!$stock_stmt->execute()) {
                        $db_error = $stock_stmt->error;
                        $stock_stmt->close();
                        throw new Exception(
                            "Stock update failed: " . $db_error
                        );
                    }

                    if ($stock_stmt->affected_rows !== 1) {
                        $stock_stmt->close();
                        throw new Exception(
                            "Not enough stock available for " .
                            (string) $product["name"] . "."
                        );
                    }
                }

                $stock_stmt->close();

                /* =================================================
                   AUTO MARK SOLD-OUT PRODUCTS INACTIVE
                ================================================= */
                $conn->query(
                    "UPDATE products
                     SET status = 'inactive'
                     WHERE stock <= 0"
                );

                /* =================================================
                   CLEAR DATABASE CART
                ================================================= */

                $clear_stmt = $conn->prepare(
                    "DELETE FROM cart
                     WHERE user_id = ?"
                );

                if (!$clear_stmt) {
                    throw new Exception(
                        "Unable to prepare cart cleanup: " . $conn->error
                    );
                }

                $clear_stmt->bind_param("i", $user_id);

                if (!$clear_stmt->execute()) {
                    $db_error = $clear_stmt->error;
                    $clear_stmt->close();

                    throw new Exception(
                        "Cart cleanup failed: " . $db_error
                    );
                }

                $clear_stmt->close();

                /* =================================================
                   COMMIT EVERYTHING
                ================================================= */

                if (!$conn->commit()) {
                    throw new Exception("The order could not be committed.");
                }

                $transaction_started = false;

                /*
                 * The order is now permanently stored in:
                 *   orders
                 *   order_items
                 *
                 * Its initial status is Pending, so the admin
                 * Orders page can display and manage it.
                 */
                header("Location: orders.php?success=1&order=" . $order_id);
                exit();

            } catch (Throwable $e) {

                if ($transaction_started) {
                    $conn->rollback();
                }

                error_log(
                    "Cafelia checkout error for user {$user_id}: " .
                    $e->getMessage()
                );

                $error =
                    "We couldn't place your order right now. " .
                    "Please check your information and try again.";
            }
        }
    }
}

/* =========================================================
   RELOAD CART COUNT AFTER POST FAILURE
========================================================= */

$cart_count = getCartCount($conn, $user_id);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Checkout | Cafelia</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #2b1710;
            --espresso-dark: #1d0e09;
            --coffee: #5b3425;
            --caramel: #b9824f;
            --caramel-light: #d8ae80;
            --cream: #f7f1e8;
            --cream-dark: #eee3d4;
            --paper: #fffdfa;
            --white: #ffffff;
            --text: #30211b;
            --muted: #88776b;
            --border: #e8dbce;
            --success: #39704e;
            --danger: #a34d47;
            --shadow: 0 24px 70px rgba(43, 23, 16, .10);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(
                    circle at 8% 8%,
                    rgba(185, 130, 79, .10),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 94% 30%,
                    rgba(91, 52, 37, .07),
                    transparent 28%
                ),
                #f7f2eb;
            color: var(--text);
            font-family: "DM Sans", sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(
                    rgba(43, 23, 16, .018) 1px,
                    transparent 1px
                );
            background-size: 100% 6px;
            opacity: .25;
            z-index: -1;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea,
        select {
            font: inherit;
        }

        /* =====================================================
           NAVBAR
        ===================================================== */

        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;
            min-height: 78px;
            padding: 14px clamp(20px, 5vw, 72px);
            background: rgba(43, 23, 16, .94);
            border-bottom: 1px solid rgba(255, 255, 255, .09);
            box-shadow: 0 12px 35px rgba(31, 16, 10, .12);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .logo {
            flex: 0 0 auto;
        }

        .logo a {
            display: inline-flex;
            align-items: center;
        }

        .logo-text {
            color: #f8eee2;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 27px;
            font-weight: 700;
            letter-spacing: .16em;
            line-height: 1;
        }

        .main-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            margin-left: auto;
        }

        .main-nav > a {
            padding: 10px 13px;
            border: 1px solid transparent;
            border-radius: 10px;
            color: rgba(255, 247, 238, .68);
            font-size: 12px;
            font-weight: 600;
            transition: .2s ease;
        }

        .main-nav > a:hover,
        .main-nav > a.active {
            color: #fffaf4;
            background: rgba(255, 255, 255, .07);
            border-color: rgba(255, 255, 255, .08);
        }

        .nav-cart {
            position: relative;
            display: inline-flex !important;
            align-items: center;
            gap: 7px;
        }

        .cart-count {
            display: inline-grid;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            place-items: center;
            border-radius: 20px;
            background: var(--caramel);
            color: white;
            font-size: 9px;
            font-weight: 800;
        }

        .account-menu {
            position: relative;
            margin-left: 5px;
        }

        .account-trigger {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 5px 10px 5px 6px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 13px;
            background: rgba(255,255,255,.065);
            color: #fff8f1;
            cursor: pointer;
            transition: .2s ease;
        }

        .account-trigger:hover,
        .account-menu.open .account-trigger {
            background: rgba(255,255,255,.11);
            border-color: rgba(255,255,255,.18);
        }

        .profile-avatar,
        .dropdown-avatar {
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: linear-gradient(
                145deg,
                #c58b58,
                #8b5938
            );
            color: white;
            font-weight: 800;
        }

        .profile-avatar {
            width: 30px;
            height: 30px;
            font-size: 10px;
        }

        .profile-name {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 11px;
            font-weight: 700;
        }

        .account-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 235px;
            padding: 9px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-5px);
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 17px;
            background: rgba(39, 20, 13, .97);
            box-shadow: 0 22px 50px rgba(20, 10, 6, .28);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            transition: .2s ease;
        }

        .account-menu.open .account-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-profile {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 9px;
        }

        .dropdown-avatar {
            width: 38px;
            height: 38px;
            font-size: 12px;
            flex: 0 0 38px;
        }

        .dropdown-profile-info {
            min-width: 0;
        }

        .dropdown-profile-info strong {
            display: block;
            overflow: hidden;
            color: #fffaf5;
            font-size: 12px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dropdown-profile-info span {
            display: block;
            margin-top: 3px;
            overflow: hidden;
            color: rgba(255,255,255,.47);
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dropdown-divider {
            height: 1px;
            margin: 7px 3px;
            background: rgba(255,255,255,.08);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 40px;
            padding: 0 9px;
            border-radius: 10px;
            color: rgba(255,255,255,.70);
            transition: .18s ease;
        }

        .dropdown-item:hover {
            background: rgba(255,255,255,.07);
            color: white;
        }

        .dropdown-item-icon {
            width: 24px;
            text-align: center;
            font-size: 12px;
        }

        .dropdown-item-text {
            font-size: 11px;
            font-weight: 600;
        }

        .dropdown-logout {
            color: #e9b0aa;
        }

        /* =====================================================
           PAGE HEADER
        ===================================================== */

        .checkout-hero {
            position: relative;
            overflow: hidden;
            padding: 70px 24px 54px;
            background:
                linear-gradient(
                    120deg,
                    rgba(43,23,16,.98),
                    rgba(91,52,37,.96)
                );
            color: white;
        }

        .checkout-hero::before {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            right: -120px;
            top: -190px;
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 50%;
        }

        .checkout-hero::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            left: -110px;
            bottom: -150px;
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 50%;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
            width: min(1180px, 100%);
            margin: auto;
        }

        .hero-eyebrow {
            margin-bottom: 9px;
            color: #d8ad7e;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .20em;
            text-transform: uppercase;
        }

        .checkout-hero h1 {
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(36px, 5vw, 58px);
            font-weight: 600;
            line-height: 1;
        }

        .checkout-hero p {
            max-width: 520px;
            margin-top: 13px;
            color: rgba(255,255,255,.63);
            font-size: 13px;
            line-height: 1.7;
        }

        /* =====================================================
           CHECKOUT LAYOUT
        ===================================================== */

        .checkout-wrap {
            width: min(1180px, calc(100% - 40px));
            margin: -28px auto 70px;
            position: relative;
            z-index: 2;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(320px, .75fr);
            gap: 22px;
            align-items: start;
        }

        .panel {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: rgba(255,255,255,.90);
            box-shadow: var(--shadow);
        }

        .panel-head {
            padding: 25px 28px 20px;
            border-bottom: 1px solid var(--border);
        }

        .panel-kicker {
            margin-bottom: 5px;
            color: var(--caramel);
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .panel-head h2 {
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 24px;
            font-weight: 600;
        }

        .panel-head p {
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
        }

        .form-body {
            padding: 26px 28px 30px;
        }

        .form-section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 3px 0 17px;
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
        }

        .section-number {
            display: grid;
            width: 24px;
            height: 24px;
            place-items: center;
            border-radius: 8px;
            background: var(--cream-dark);
            color: var(--coffee);
            font-size: 10px;
            font-weight: 800;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 17px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            color: #5b4a40;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .required {
            color: var(--caramel);
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            border: 1px solid #e4d7ca;
            outline: none;
            border-radius: 12px;
            background: #fffdfa;
            color: var(--text);
            font-size: 12px;
            transition: .2s ease;
        }

        .form-group input,
        .form-group select {
            height: 47px;
            padding: 0 14px;
        }

        .form-group textarea {
            min-height: 112px;
            padding: 13px 14px;
            resize: vertical;
            line-height: 1.5;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #b3a59a;
        }

        .form-group input:hover,
        .form-group textarea:hover,
        .form-group select:hover {
            border-color: #d6c5b5;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--caramel);
            box-shadow: 0 0 0 4px rgba(185,130,79,.10);
            background: white;
        }

        .input-hint {
            color: #9b8b7e;
            font-size: 9px;
            line-height: 1.4;
        }

        .payment-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .payment-option label {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 62px;
            padding: 10px 12px;
            border: 1px solid #e4d7ca;
            border-radius: 13px;
            background: #fffdfa;
            color: var(--text);
            cursor: pointer;
            transition: .2s ease;
        }

        .payment-option label:hover {
            border-color: #d4b89b;
            transform: translateY(-1px);
        }

        .payment-option input:checked + label {
            border-color: var(--caramel);
            background: #fff8f0;
            box-shadow: 0 0 0 3px rgba(185,130,79,.09);
        }

        .payment-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 10px;
            background: var(--cream-dark);
            color: var(--coffee);
            font-size: 14px;
        }

        .payment-copy strong {
            display: block;
            font-size: 11px;
        }

        .payment-copy span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 9px;
        }

        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 22px;
            padding: 13px 14px;
            border-radius: 12px;
            font-size: 11px;
            line-height: 1.5;
        }

        .alert.error {
            border: 1px solid #ecd0cc;
            background: #fcf0ef;
            color: var(--danger);
        }

        .alert-icon {
            font-weight: 800;
        }

        .form-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 25px;
            padding-top: 21px;
            border-top: 1px solid var(--border);
        }

        .secure-note {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #8e7d70;
            font-size: 9px;
            line-height: 1.4;
        }

        .secure-icon {
            display: grid;
            width: 28px;
            height: 28px;
            place-items: center;
            border-radius: 9px;
            background: var(--cream);
            color: var(--coffee);
        }

        .place-order-btn {
            min-height: 47px;
            padding: 0 23px;
            border: 0;
            border-radius: 12px;
            background: var(--espresso);
            color: white;
            cursor: pointer;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .03em;
            box-shadow: 0 10px 25px rgba(43,23,16,.18);
            transition: .2s ease;
        }

        .place-order-btn:hover {
            background: var(--coffee);
            transform: translateY(-1px);
            box-shadow: 0 13px 30px rgba(43,23,16,.22);
        }

        .place-order-btn:active {
            transform: translateY(0);
        }

        /* =====================================================
           SUMMARY
        ===================================================== */

        .summary-panel {
            position: sticky;
            top: 100px;
        }

        .summary-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 22px 23px;
            border-bottom: 1px solid var(--border);
        }

        .summary-head h2 {
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 21px;
            font-weight: 600;
        }

        .item-count {
            padding: 6px 9px;
            border-radius: 9px;
            background: var(--cream);
            color: var(--coffee);
            font-size: 9px;
            font-weight: 800;
        }

        .summary-items {
            padding: 8px 23px;
        }

        .summary-item {
            display: grid;
            grid-template-columns: 47px minmax(0, 1fr) auto;
            gap: 11px;
            align-items: center;
            padding: 14px 0;
            border-bottom: 1px solid #f0e7de;
        }

        .summary-item:last-child {
            border-bottom: 0;
        }

        .item-icon {
            display: grid;
            width: 47px;
            height: 47px;
            place-items: center;
            border-radius: 12px;
            background:
                linear-gradient(
                    145deg,
                    #efe1d2,
                    #f8f0e7
                );
            color: var(--coffee);
            font-size: 17px;
        }

        .item-details {
            min-width: 0;
        }

        .item-details strong {
            display: block;
            overflow: hidden;
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .item-details span {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 9px;
        }

        .item-price {
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
            white-space: nowrap;
        }

        .summary-bottom {
            padding: 19px 23px 23px;
            border-top: 1px solid var(--border);
            background: #fcf8f3;
        }

        .summary-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 10px;
            color: var(--muted);
            font-size: 10px;
        }

        .summary-row span:last-child {
            color: var(--text);
            font-weight: 700;
        }

        .summary-total {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed #dbcabb;
        }

        .summary-total span {
            color: var(--espresso);
            font-size: 12px;
            font-weight: 800;
        }

        .summary-total strong {
            color: var(--espresso);
            font-family: "Playfair Display", Georgia, serif;
            font-size: 27px;
        }

        .back-cart {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-top: 17px;
            color: var(--coffee);
            font-size: 10px;
            font-weight: 800;
        }

        .back-cart:hover {
            color: var(--caramel);
        }

        .trust-list {
            display: grid;
            gap: 10px;
            margin: 18px 23px 22px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }

        .trust-row {
            display: flex;
            align-items: center;
            gap: 9px;
            color: #8b796c;
            font-size: 9px;
        }

        .trust-row b {
            color: var(--success);
            font-size: 11px;
        }

        /* =====================================================
           FOOTER
        ===================================================== */

        .footer {
            padding: 42px 24px 24px;
            background: var(--espresso-dark);
            color: white;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 1.4fr .7fr .7fr;
            gap: 35px;
            width: min(1180px, 100%);
            margin: auto;
        }

        .footer h3 {
            color: #fff5eb;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 24px;
        }

        .footer h4 {
            margin-bottom: 10px;
            color: #fff3e8;
            font-size: 10px;
            letter-spacing: .10em;
            text-transform: uppercase;
        }

        .footer p,
        .footer a {
            color: rgba(255,255,255,.52);
            font-size: 10px;
            line-height: 1.8;
        }

        .footer a {
            display: block;
            transition: .2s ease;
        }

        .footer a:hover {
            color: #fff;
        }

        .footer-bottom {
            width: min(1180px, 100%);
            margin: 30px auto 0;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 980px) {

            .main-nav > a:not(.nav-cart) {
                display: none;
            }

            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .summary-panel {
                position: static;
            }

            .summary-panel {
                order: -1;
            }
        }

        @media (max-width: 700px) {

            .navbar {
                min-height: 68px;
                padding: 12px 17px;
            }

            .logo-text {
                font-size: 21px;
            }

            .profile-name {
                display: none;
            }

            .account-trigger {
                padding-right: 5px;
            }

            .checkout-hero {
                padding: 54px 20px 70px;
            }

            .checkout-wrap {
                width: min(100% - 24px, 600px);
                margin-top: -35px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .payment-options {
                grid-template-columns: 1fr;
            }

            .panel-head,
            .form-body {
                padding-left: 20px;
                padding-right: 20px;
            }

            .form-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .place-order-btn {
                width: 100%;
            }

            .footer-content {
                grid-template-columns: 1fr 1fr;
            }

            .footer-content > :first-child {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 430px) {

            .checkout-hero h1 {
                font-size: 38px;
            }

            .checkout-wrap {
                width: calc(100% - 16px);
            }

            .summary-head,
            .summary-items,
            .summary-bottom {
                padding-left: 17px;
                padding-right: 17px;
            }

            .trust-list {
                margin-left: 17px;
                margin-right: 17px;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .footer-content > :first-child {
                grid-column: auto;
            }
        }

    </style>

</head>

<body>

<header class="navbar">

    <div class="logo">
        <a href="index.php" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>
    </div>

    <nav class="main-nav">

        <a href="index.php">
            Home
        </a>

        <a href="menu.php">
            Menu
        </a>

        <a href="orders.php">
            My Orders
        </a>

        <a href="cart.php" class="nav-cart active">
            Cart
            <?php if ($cart_count > 0): ?>
                <span class="cart-count">
                    <?php echo $cart_count; ?>
                </span>
            <?php endif; ?>
        </a>

        <div class="account-menu">

            <button
                type="button"
                class="account-trigger"
                aria-expanded="false"
                aria-label="Open account menu"
                onclick="toggleAccountMenu(this)"
            >

                <span class="profile-avatar">
                    <?php
                    echo e(
                        strtoupper(
                            substr(
                                trim((string) $user["name"]),
                                0,
                                1
                            )
                        )
                    );
                    ?>
                </span>

                <span class="profile-name">
                    <?php echo e((string) $user["name"]); ?>
                </span>

            </button>

            <div class="account-dropdown">

                <div class="dropdown-profile">

                    <div class="dropdown-avatar">
                        <?php
                        echo e(
                            strtoupper(
                                substr(
                                    trim((string) $user["name"]),
                                    0,
                                    1
                                )
                            )
                        );
                        ?>
                    </div>

                    <div class="dropdown-profile-info">
                        <strong>
                            <?php echo e((string) $user["name"]); ?>
                        </strong>

                        <span>
                            <?php echo e((string) $user["email"]); ?>
                        </span>
                    </div>

                </div>

                <div class="dropdown-divider"></div>

                <a
                    href="profile.php"
                    class="dropdown-item"
                >
                    <span class="dropdown-item-icon">◉</span>
                    <span class="dropdown-item-text">
                        My Profile
                    </span>
                </a>

                <a
                    href="orders.php"
                    class="dropdown-item"
                >
                    <span class="dropdown-item-icon">▤</span>
                    <span class="dropdown-item-text">
                        My Orders
                    </span>
                </a>

                <a
                    href="cart.php"
                    class="dropdown-item"
                >
                    <span class="dropdown-item-icon">🛒</span>
                    <span class="dropdown-item-text">
                        My Cart
                    </span>
                </a>

                <div class="dropdown-divider"></div>

                <a
                    href="logout.php"
                    class="dropdown-item dropdown-logout"
                >
                    <span class="dropdown-item-icon">⇥</span>
                    <span class="dropdown-item-text">
                        Logout
                    </span>
                </a>

            </div>

        </div>

    </nav>

</header>


<section class="checkout-hero">

    <div class="hero-inner">

        <div class="hero-eyebrow">
            CAFELIA
        </div>

        <h1>
            Checkout
        </h1>

        <p>
            You're almost there. Confirm your details,
            choose your payment method, and we'll prepare
            your order with care.
        </p>

    </div>

</section>


<main class="checkout-wrap">

    <div class="checkout-grid">

        <!-- =================================================
             CUSTOMER FORM
        ================================================== -->

        <section class="panel">

            <div class="panel-head">

                <div class="panel-kicker">
                    Order Details
                </div>

                <h2>
                    Complete your order
                </h2>

                <p>
                    Your account information is already filled in.
                    Please confirm the details below.
                </p>

            </div>


            <div class="form-body">

                <?php if ($error !== ""): ?>

                    <div class="alert error">

                        <span class="alert-icon">!</span>

                        <span>
                            <?php echo e($error); ?>
                        </span>

                    </div>

                <?php endif; ?>


                <form
                    action="checkout.php"
                    method="POST"
                    id="checkoutForm"
                >

                    <div class="form-section-title">
                        <span class="section-number">1</span>
                        Customer Information
                    </div>


                    <div class="form-grid">

                        <div class="form-group">

                            <label for="customer_name">
                                Full Name
                                <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                id="customer_name"
                                name="customer_name"
                                value="<?php echo e($old["customer_name"]); ?>"
                                placeholder="Your full name"
                                maxlength="100"
                                autocomplete="name"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="customer_email">
                                Email Address
                                <span class="required">*</span>
                            </label>

                            <input
                                type="email"
                                id="customer_email"
                                name="customer_email"
                                value="<?php echo e($old["customer_email"]); ?>"
                                placeholder="you@example.com"
                                maxlength="150"
                                autocomplete="email"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="phone">
                                Phone Number
                                <span class="required">*</span>
                            </label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                value="<?php echo e($old["phone"]); ?>"
                                placeholder="09XXXXXXXXX"
                                maxlength="20"
                                autocomplete="tel"
                                inputmode="tel"
                                required
                            >

                            <span class="input-hint">
                                Example: 09123456789
                            </span>

                        </div>


                        <div class="form-group full">

                            <label for="address">
                                Delivery Address
                                <span class="required">*</span>
                            </label>

                            <textarea
                                id="address"
                                name="address"
                                placeholder="House number, street, barangay, municipality/city, province"
                                maxlength="500"
                                autocomplete="street-address"
                                required
                            ><?php echo e($old["address"]); ?></textarea>

                            <span class="input-hint">
                                Please provide enough detail so your order can be delivered correctly.
                            </span>

                        </div>

                    </div>


                    <div
                        class="form-section-title"
                        style="margin-top: 30px;"
                    >
                        <span class="section-number">2</span>
                        Payment Method
                    </div>


                    <div class="payment-options">

                        <div class="payment-option">

                            <input
                                type="radio"
                                id="payment_cod"
                                name="payment_method"
                                value="Cash on Delivery"
                                <?php
                                echo $old["payment_method"] === "Cash on Delivery"
                                    ? "checked"
                                    : "";
                                ?>
                                required
                            >

                            <label for="payment_cod">

                                <span class="payment-icon">
                                    ₱
                                </span>

                                <span class="payment-copy">
                                    <strong>
                                        Cash on Delivery
                                    </strong>

                                    <span>
                                        Pay when your order arrives.
                                    </span>
                                </span>

                            </label>

                        </div>


                        <div class="payment-option">

                            <input
                                type="radio"
                                id="payment_gcash"
                                name="payment_method"
                                value="GCash"
                                <?php
                                echo $old["payment_method"] === "GCash"
                                    ? "checked"
                                    : "";
                                ?>
                            >

                            <label for="payment_gcash">

                                <span class="payment-icon">
                                    G
                                </span>

                                <span class="payment-copy">
                                    <strong>
                                        GCash
                                    </strong>

                                    <span>
                                        Select GCash for your order.
                                    </span>
                                </span>

                            </label>

                        </div>

                    </div>


                    <div class="form-footer">

                        <div class="secure-note">

                            <span class="secure-icon">
                                ✓
                            </span>

                            <span>
                                Your order will be saved securely
                                to your Cafelia account.
                            </span>

                        </div>

                        <button
                            type="submit"
                            class="place-order-btn"
                            id="placeOrderBtn"
                        >
                            Place Order · ₱<?php
                                echo number_format(
                                    $grand_total,
                                    2
                                );
                            ?>
                        </button>

                    </div>

                </form>

            </div>

        </section>


        <!-- =================================================
             ORDER SUMMARY
        ================================================== -->

        <aside class="panel summary-panel">

            <div class="summary-head">

                <h2>
                    Your Order
                </h2>

                <span class="item-count">
                    <?php echo $cart_count; ?>
                    <?php echo $cart_count === 1 ? "item" : "items"; ?>
                </span>

            </div>


            <div class="summary-items">

                <?php foreach ($cart_products as $product): ?>

                    <div class="summary-item">

                        <div class="item-icon">
                            ☕
                        </div>

                        <div class="item-details">

                            <strong>
                                <?php
                                echo e(
                                    (string) $product["name"]
                                );
                                ?>
                            </strong>

                            <span>
                                Qty <?php
                                echo (int) $product["quantity"];
                                ?>
                                ×
                                ₱<?php
                                echo number_format(
                                    (float) $product["price"],
                                    2
                                );
                                ?>
                            </span>

                        </div>

                        <div class="item-price">
                            ₱<?php
                            echo number_format(
                                (float) $product["subtotal"],
                                2
                            );
                            ?>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>


            <div class="summary-bottom">

                <div class="summary-row">
                    <span>
                        Subtotal
                    </span>

                    <span>
                        ₱<?php
                        echo number_format(
                            $grand_total,
                            2
                        );
                        ?>
                    </span>
                </div>


                <div class="summary-row">
                    <span>
                        Delivery
                    </span>

                    <span>
                        ₱0.00
                    </span>
                </div>


                <div class="summary-total">

                    <span>
                        Total
                    </span>

                    <strong>
                        ₱<?php
                        echo number_format(
                            $grand_total,
                            2
                        );
                        ?>
                    </strong>

                </div>


                <a
                    href="cart.php"
                    class="back-cart"
                >
                    ← Back to Cart
                </a>

            </div>


            <div class="trust-list">

                <div class="trust-row">
                    <b>✓</b>
                    Your order is connected to your account.
                </div>

                <div class="trust-row">
                    <b>✓</b>
                    Order status starts as Pending.
                </div>

                <div class="trust-row">
                    <b>✓</b>
                    You can track the order from My Orders.
                </div>

            </div>

        </aside>

    </div>

</main>


<footer class="footer">

    <div class="footer-content">

        <div>

            <h3>
                Cafelia
            </h3>

            <p>
                Coffee, treats, and moments
                worth remembering.
            </p>

        </div>


        <div>

            <h4>
                Explore
            </h4>

            <a href="index.php">
                Home
            </a>

            <a href="menu.php">
                Menu
            </a>

            <a href="about.php">
                About
            </a>

            <a href="contact.php">
                Contact
            </a>

        </div>


        <div>

            <h4>
                Customer
            </h4>

            <a href="profile.php">
                My Profile
            </a>

            <a href="orders.php">
                My Orders
            </a>

            <a href="cart.php">
                Cart
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        <p>
            © <?php echo date("Y"); ?>
            Cafelia. All Rights Reserved.
        </p>

    </div>

</footer>


<script>

/* =========================================================
   ACCOUNT MENU
========================================================= */

function toggleAccountMenu(button) {

    const menu = button.closest(".account-menu");

    document
        .querySelectorAll(".account-menu.open")
        .forEach(function (item) {

            if (item !== menu) {

                item.classList.remove("open");

                const trigger =
                    item.querySelector(".account-trigger");

                if (trigger) {
                    trigger.setAttribute(
                        "aria-expanded",
                        "false"
                    );
                }
            }
        });

    const isOpen =
        menu.classList.toggle("open");

    button.setAttribute(
        "aria-expanded",
        isOpen ? "true" : "false"
    );
}


document.addEventListener(
    "click",
    function (event) {

        document
            .querySelectorAll(".account-menu.open")
            .forEach(function (menu) {

                if (!menu.contains(event.target)) {

                    menu.classList.remove("open");

                    const trigger =
                        menu.querySelector(
                            ".account-trigger"
                        );

                    if (trigger) {
                        trigger.setAttribute(
                            "aria-expanded",
                            "false"
                        );
                    }
                }
            });
    }
);


document.addEventListener(
    "keydown",
    function (event) {

        if (event.key === "Escape") {

            document
                .querySelectorAll(".account-menu.open")
                .forEach(function (menu) {

                    menu.classList.remove("open");

                    const trigger =
                        menu.querySelector(
                            ".account-trigger"
                        );

                    if (trigger) {
                        trigger.setAttribute(
                            "aria-expanded",
                            "false"
                        );
                    }
                });
        }
    }
);


/* =========================================================
   PREVENT DOUBLE SUBMISSION
========================================================= */

const checkoutForm =
    document.getElementById("checkoutForm");

const placeOrderBtn =
    document.getElementById("placeOrderBtn");

if (checkoutForm && placeOrderBtn) {

    checkoutForm.addEventListener(
        "submit",
        function () {

            placeOrderBtn.disabled = true;

            placeOrderBtn.style.opacity = ".72";
            placeOrderBtn.style.cursor = "wait";

            placeOrderBtn.textContent =
                "Placing Order...";

        }
    );
}

</script>

</body>
</html>
