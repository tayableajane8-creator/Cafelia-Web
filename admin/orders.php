<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

function e($value): string {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$allowed_statuses = ["Pending", "Processing", "Completed", "Cancelled"];
$message = "";
$error = "";

/* UPDATE ORDER STATUS - COMPLETED ORDERS ARE LOCKED */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = (int)($_POST["order_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    if ($order_id > 0 && in_array($status, $allowed_statuses, true)) {
        $check = $conn->prepare(
            "SELECT status FROM orders WHERE id = ? LIMIT 1"
        );

        if ($check) {
            $check->bind_param("i", $order_id);
            $check->execute();

            $result = $check->get_result();
            $current = $result->fetch_assoc();

            $check->close();

            /* COMPLETED ORDERS CANNOT BE CHANGED */
            if ($current && $current["status"] === "Completed") {
                header("Location: orders.php?locked=1");
                exit();
            }

            $stmt = $conn->prepare(
                "UPDATE orders SET status = ? WHERE id = ?"
            );

            if ($stmt) {
                $stmt->bind_param("si", $status, $order_id);

                if ($stmt->execute()) {
                    $message = "Order #{$order_id} updated successfully.";
                } else {
                    $error = "Unable to update the order.";
                }

                $stmt->close();
            } else {
                $error = "Database error.";
            }
        } else {
            $error = "Database error.";
        }
    }

    if ($message !== "" || $error !== "") {
        header(
            "Location: orders.php?" .
            ($message !== "" ? "updated=1" : "error=1")
        );
        exit();
    }
}

/* FILTERS */
$search = trim($_GET["search"] ?? "");
$status_filter = trim($_GET["status"] ?? "");

/* STATISTICS */
$total_orders = 0;
$pending_orders = 0;
$processing_orders = 0;
$completed_orders = 0;
$cancelled_orders = 0;
$total_sales = 0;

$stats = $conn->query(
    "SELECT
        COUNT(*) AS total_orders,
        SUM(status = 'Pending') AS pending_orders,
        SUM(status = 'Processing') AS processing_orders,
        SUM(status = 'Completed') AS completed_orders,
        SUM(status = 'Cancelled') AS cancelled_orders,
        COALESCE(
            SUM(
                CASE
                    WHEN status = 'Completed'
                    THEN total_amount
                    ELSE 0
                END
            ),
            0
        ) AS total_sales
     FROM orders"
);

if ($stats) {
    $data = $stats->fetch_assoc();

    $total_orders = (int)($data["total_orders"] ?? 0);
    $pending_orders = (int)($data["pending_orders"] ?? 0);
    $processing_orders = (int)($data["processing_orders"] ?? 0);
    $completed_orders = (int)($data["completed_orders"] ?? 0);
    $cancelled_orders = (int)($data["cancelled_orders"] ?? 0);
    $total_sales = (float)($data["total_sales"] ?? 0);
}

/* ORDERS QUERY */
$sql = "
    SELECT
        orders.id,
        orders.total_amount,
        orders.payment_method,
        orders.gcash_receipt,
        orders.status,
        orders.order_date,
        users.name,
        users.email
    FROM orders
    INNER JOIN users
        ON orders.user_id = users.id
    WHERE 1=1
";

$params = [];
$types = "";

/* SEARCH */
if ($search !== "") {
    $sql .= "
        AND (
            orders.id LIKE ?
            OR users.name LIKE ?
            OR users.email LIKE ?
            OR orders.payment_method LIKE ?
            OR orders.gcash_receipt LIKE ?
        )
    ";

    $search_value = "%{$search}%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types = "sssss";
}

/* STATUS FILTER */
if (in_array($status_filter, $allowed_statuses, true)) {
    $sql .= " AND orders.status = ?";

    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($sql);
$orders = false;

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $orders = $stmt->get_result();
}

$updated = isset($_GET["updated"]);
$error_message = isset($_GET["error"]);
$locked_message = isset($_GET["locked"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Orders | Cafelia Admin</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #2b1710;
            --espresso-dark: #1d0e09;
            --coffee: #5b3425;
            --caramel: #b9824f;
            --cream: #f7f1e8;
            --cream-dark: #eee3d4;
            --white: #ffffff;
            --text: #30211b;
            --muted: #8b7b70;
            --border: #eadfd3;
            --green: #3f7656;
            --red: #a84a45;
            --shadow: 0 18px 45px rgba(43,23,16,.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: #f6f1ea;
            color: var(--text);
            font-family: "DM Sans", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        select,
        input {
            font: inherit;
        }

        .main {
            width: calc(100% - 260px);
            margin-left: 260px;
            min-height: 100vh;
            padding: 34px 38px 50px;
        }

        .topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 30px;
        }

        .eyebrow {
            margin-bottom: 7px;
            color: var(--caramel);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .topbar h1 {
            font-family: "Playfair Display", serif;
            font-size: clamp(30px, 3vw, 43px);
            line-height: 1.1;
            color: var(--espresso);
        }

        .topbar p {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px 9px 9px;
            background: rgba(255,255,255,.7);
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(43,23,16,.04);
        }

        .avatar {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            background: var(--espresso);
            color: white;
            font-weight: 700;
            font-size: 13px;
        }

        .admin-user small {
            display: block;
            margin-bottom: 2px;
            color: var(--muted);
            font-size: 10px;
        }

        .admin-user strong {
            font-size: 12px;
        }

        /* STATISTICS */

        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 24px;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            padding: 20px;
            background: rgba(255,255,255,.82);
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -20px;
            bottom: -28px;
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background: rgba(185,130,79,.08);
        }

        .stat-title {
            color: var(--muted);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .stat-value {
            margin-top: 8px;
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 27px;
            font-weight: 700;
        }

        .stat-meta {
            margin-top: 5px;
            color: var(--muted);
            font-size: 11px;
        }

        .stat-card.sales .stat-value {
            color: var(--green);
        }

        /* CARD */

        .card {
            overflow: hidden;
            background: rgba(255,255,255,.86);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: var(--shadow);
        }

        .card-head {
            padding: 22px 24px;
            border-bottom: 1px solid var(--border);
        }

        .card-title h2 {
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 22px;
        }

        .card-title p {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        /* FILTERS */

        .filters {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 18px;
        }

        .search-box {
            position: relative;
            flex: 1;
        }

        .search-box span {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 13px;
        }

        .search-box input,
        .filter-select {
            width: 100%;
            height: 43px;
            border: 1px solid var(--border);
            border-radius: 11px;
            outline: none;
            background: #fffdfa;
            color: var(--text);
            font-size: 12px;
            transition: .2s ease;
        }

        .search-box input {
            padding: 0 14px 0 39px;
        }

        .filter-select {
            width: 180px;
            padding: 0 12px;
            cursor: pointer;
        }

        .search-box input:focus,
        .filter-select:focus {
            border-color: var(--caramel);
            box-shadow: 0 0 0 3px rgba(185,130,79,.10);
        }

        .filter-btn {
            height: 43px;
            padding: 0 18px;
            border: 0;
            border-radius: 11px;
            background: var(--espresso);
            color: white;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            transition: .2s ease;
        }

        .filter-btn:hover {
            background: var(--coffee);
            transform: translateY(-1px);
        }

        .clear-btn {
            height: 43px;
            padding: 0 16px;
            display: flex;
            align-items: center;
            border: 1px solid var(--border);
            border-radius: 11px;
            background: white;
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
        }

        /* TABLE */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1320px;
            border-collapse: collapse;
        }

        thead {
            background: #faf6f0;
        }

        th {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            color: #9b8b7f;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .10em;
            text-align: left;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 17px 20px;
            border-bottom: 1px solid #f0e8df;
            vertical-align: middle;
            font-size: 12px;
        }

        tbody tr {
            transition: .18s ease;
        }

        tbody tr:hover {
            background: #fdfaf6;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .order-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 54px;
            padding: 7px 10px;
            border-radius: 9px;
            background: #f2e8dc;
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
        }

        .customer {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .customer-avatar {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--cream-dark);
            color: var(--coffee);
            font-size: 11px;
            font-weight: 800;
        }

        .customer-name {
            color: var(--espresso);
            font-weight: 700;
        }

        .customer-email {
            margin-top: 2px;
            color: var(--muted);
            font-size: 10px;
        }

        .amount {
            color: var(--espresso);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        /* ORDERED ITEMS */
        .items-cell {
            min-width: 300px;
        }

        .ordered-items {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .ordered-item {
            display: flex;
            align-items: center;
            gap: 9px;
        }

        .ordered-item-image,
        .ordered-item-placeholder {
            width: 42px;
            height: 42px;
            flex: 0 0 42px;
            border-radius: 9px;
        }

        .ordered-item-image {
            object-fit: cover;
            border: 1px solid var(--border);
            background: var(--cream);
        }

        .ordered-item-placeholder {
            display: grid;
            place-items: center;
            background: var(--cream);
            color: var(--coffee);
            font-size: 15px;
        }

        .ordered-item-info {
            min-width: 0;
        }

        .ordered-item-name {
            color: var(--espresso);
            font-size: 11px;
            font-weight: 800;
            line-height: 1.3;
        }

        .ordered-item-meta {
            margin-top: 2px;
            color: var(--muted);
            font-size: 10px;
            line-height: 1.3;
        }

        .qty-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 5px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #f2e8dc;
            color: var(--coffee);
            font-size: 9px;
            font-weight: 800;
            white-space: nowrap;
        }

        .items-empty {
            color: var(--muted);
            font-size: 10px;
        }

        .date {
            color: #66584f;
            white-space: nowrap;
            font-size: 11px;
        }

        /* PAYMENT */

        .payment-cell {
            min-width: 175px;
        }

        .payment-method {
            display: inline-flex;
            align-items: center;
            min-width: 72px;
            margin-bottom: 4px;
            padding: 5px 9px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 800;
        }

        .payment-method.gcash {
            background: #eee8f8;
            border: 1px solid #ddd2ef;
            color: #694e91;
        }

        .payment-method.cash {
            background: #e7f3eb;
            border: 1px solid #cbe4d3;
            color: #39704e;
        }

        .receipt-number {
            max-width: 190px;
            color: #7d6b5e;
            font-size: 10px;
            font-weight: 600;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .cash-note,
        .no-receipt {
            color: var(--muted);
            font-size: 10px;
        }

        .no-receipt {
            color: var(--red);
        }

        /* STATUS */

        .status-form {
            margin: 0;
        }

        .status-select {
            min-width: 125px;
            height: 35px;
            padding: 0 28px 0 10px;
            border-radius: 9px;
            outline: none;
            cursor: pointer;
            font-size: 10px;
            font-weight: 800;
            border: 1px solid transparent;
        }

        .status-select.pending {
            background: #fff4dd;
            border-color: #f2dfb5;
            color: #9a671e;
        }

        .status-select.processing {
            background: #eee9ff;
            border-color: #ddd3fa;
            color: #6652a7;
        }

        .status-select.completed {
            background: #e7f3eb;
            border-color: #cbe4d3;
            color: #39704e;
        }

        .status-select.cancelled {
            background: #f8e8e6;
            border-color: #edd0cd;
            color: #9c4945;
        }

        /* COMPLETED - NO ARROW */

        .completed-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 125px;
            height: 35px;
            padding: 0 12px;
            border-radius: 9px;
            background: #e7f3eb;
            border: 1px solid #cbe4d3;
            color: #39704e;
            font-size: 10px;
            font-weight: 800;
        }

        .completed-status::before {
            content: "✓";
            margin-right: 6px;
            font-size: 11px;
        }

        /* EMPTY */

        .empty {
            padding: 65px 25px;
            text-align: center;
        }

        .empty-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            margin: 0 auto 15px;
            border-radius: 17px;
            background: var(--cream);
            font-size: 22px;
        }

        .empty h3 {
            color: var(--espresso);
            font-family: "Playfair Display", serif;
            font-size: 20px;
        }

        .empty p {
            margin-top: 5px;
            color: var(--muted);
            font-size: 12px;
        }

        /* TOAST */

        .toast {
            position: fixed;
            top: 24px;
            right: 25px;
            z-index: 999;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 17px;
            border: 1px solid #cde4d3;
            border-radius: 12px;
            background: #edf8f0;
            color: #39704e;
            box-shadow: 0 15px 35px rgba(43,23,16,.12);
            font-size: 12px;
            font-weight: 700;
            animation: slideIn .25s ease;
        }

        .toast.error {
            border-color: #eccdca;
            background: #fbefee;
            color: #9c4945;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE */

        @media (max-width: 1250px) {
            .stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .main {
                width: 100%;
                margin-left: 0;
                padding: 25px 20px 40px;
            }
        }

        @media (max-width: 650px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .topbar {
                flex-direction: column;
            }

            .admin-user {
                width: 100%;
            }

            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select,
            .filter-btn,
            .clear-btn {
                width: 100%;
            }
        }

        @media (max-width: 430px) {
            .stats {
                grid-template-columns: 1fr;
            }

            .main {
                padding: 20px 14px 35px;
            }

            .card-head {
                padding: 18px;
            }
        }

    </style>

</head>

<body>

<?php
$active_admin_page = "orders";
include "sidebar.php";
?>

<?php if ($updated): ?>

    <div class="toast">
        ✓ Order status updated successfully.
    </div>

<?php endif; ?>

<?php if ($error_message): ?>

    <div class="toast error">
        ✕ Unable to update the order.
    </div>

<?php endif; ?>

<?php if ($locked_message): ?>

    <div class="toast error">
        🔒 Completed orders cannot be changed.
    </div>

<?php endif; ?>


<main class="main">

    <header class="topbar">

        <div>

            <div class="eyebrow">
                Cafelia Administration
            </div>

            <h1>
                Orders
            </h1>

            <p>
                Track customer purchases and manage order progress.
            </p>

        </div>


        <div class="admin-user">

            <div class="avatar">
                <?php
                echo e(
                    strtoupper(
                        substr($admin_name, 0, 1)
                    )
                );
                ?>
            </div>

            <div>

                <small>
                    Signed in as
                </small>

                <strong>
                    <?php echo e($admin_name); ?>
                </strong>

            </div>

        </div>

    </header>


    <!-- STATISTICS -->

    <section class="stats">

        <div class="stat-card">

            <div class="stat-title">
                Total Orders
            </div>

            <div class="stat-value">
                <?php echo $total_orders; ?>
            </div>

            <div class="stat-meta">
                All customer orders
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Pending
            </div>

            <div class="stat-value">
                <?php echo $pending_orders; ?>
            </div>

            <div class="stat-meta">
                Waiting for processing
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Processing
            </div>

            <div class="stat-value">
                <?php echo $processing_orders; ?>
            </div>

            <div class="stat-meta">
                Currently preparing
            </div>

        </div>


        <div class="stat-card">

            <div class="stat-title">
                Completed
            </div>

            <div class="stat-value">
                <?php echo $completed_orders; ?>
            </div>

            <div class="stat-meta">
                Successfully fulfilled
            </div>

        </div>


        <div class="stat-card sales">

            <div class="stat-title">
                Completed Sales
            </div>

            <div class="stat-value">
                ₱<?php echo number_format($total_sales, 2); ?>
            </div>

            <div class="stat-meta">
                From completed orders
            </div>

        </div>

    </section>


    <!-- ORDERS -->

    <section class="card">

        <div class="card-head">

            <div class="card-title">

                <h2>
                    Order Management
                </h2>

                <p>
                    Search customers or update an order's current status.
                </p>

            </div>


            <form
                class="filters"
                method="GET"
                action="orders.php"
            >

                <div class="search-box">

                    <span>
                        ⌕
                    </span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search order, customer, or email..."
                        value="<?php echo e($search); ?>"
                    >

                </div>


                <select
                    class="filter-select"
                    name="status"
                >

                    <option value="">
                        All Statuses
                    </option>

                    <?php foreach ($allowed_statuses as $filter_status): ?>

                        <option
                            value="<?php echo e($filter_status); ?>"
                            <?php
                            echo $status_filter === $filter_status
                                ? "selected"
                                : "";
                            ?>
                        >
                            <?php echo e($filter_status); ?>
                        </option>

                    <?php endforeach; ?>

                </select>


                <button
                    type="submit"
                    class="filter-btn"
                >
                    Filter
                </button>


                <?php if ($search !== "" || $status_filter !== ""): ?>

                    <a
                        href="orders.php"
                        class="clear-btn"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <div class="table-wrap">

            <?php if ($orders && $orders->num_rows > 0): ?>

                <table>

                    <thead>

                        <tr>

                            <th>
                                Order
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Ordered Items
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Payment
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Order Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($order = $orders->fetch_assoc()): ?>

                        <?php

                        $customer_name =
                            $order["name"] ?? "Customer";

                        $initials =
                            strtoupper(
                                substr(
                                    trim($customer_name),
                                    0,
                                    1
                                )
                            );

                        $status_class =
                            strtolower(
                                $order["status"] ?? ""
                            );

                        $payment_method =
                            $order["payment_method"] ?? "Cash";

                        $gcash_receipt =
                            trim(
                                $order["gcash_receipt"] ?? ""
                            );

                        ?>

                        <tr>

                            <!-- ORDER -->

                            <td>

                                <span class="order-number">

                                    #
                                    <?php
                                    echo (int)$order["id"];
                                    ?>

                                </span>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer">

                                    <div class="customer-avatar">

                                        <?php
                                        echo e($initials);
                                        ?>

                                    </div>


                                    <div>

                                        <div class="customer-name">

                                            <?php
                                            echo e(
                                                $customer_name
                                            );
                                            ?>

                                        </div>


                                        <div class="customer-email">

                                            <?php
                                            echo e(
                                                $order["email"] ?? ""
                                            );
                                            ?>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- ORDERED ITEMS -->

                            <td class="items-cell">

                                <?php
                                $item_stmt = $conn->prepare(
                                    "SELECT
                                        oi.product_name,
                                        oi.quantity,
                                        oi.price,
                                        oi.subtotal,
                                        p.image
                                     FROM order_items AS oi
                                     LEFT JOIN products AS p
                                        ON p.id = oi.product_id
                                     WHERE oi.order_id = ?
                                     ORDER BY oi.id ASC"
                                );

                                $order_items = [];

                                if ($item_stmt) {

                                    $item_stmt->bind_param(
                                        "i",
                                        $order["id"]
                                    );

                                    if ($item_stmt->execute()) {

                                        $item_result =
                                            $item_stmt->get_result();

                                        while (
                                            $item_row =
                                            $item_result->fetch_assoc()
                                        ) {
                                            $order_items[] = $item_row;
                                        }
                                    }

                                    $item_stmt->close();
                                }
                                ?>

                                <?php if (!empty($order_items)): ?>

                                    <div class="ordered-items">

                                        <?php foreach ($order_items as $item): ?>

                                            <?php
                                            $item_image = trim(
                                                (string)($item["image"] ?? "")
                                            );
                                            ?>

                                            <div class="ordered-item">

                                                <?php if ($item_image !== ""): ?>

                                                    <img
                                                        src="../image/<?php echo e($item_image); ?>"
                                                        alt="<?php echo e($item["product_name"]); ?>"
                                                        class="ordered-item-image"
                                                        onerror="this.style.display='none';this.nextElementSibling.style.display='grid';"
                                                    >

                                                    <div
                                                        class="ordered-item-placeholder"
                                                        style="display:none;"
                                                    >
                                                        ☕
                                                    </div>

                                                <?php else: ?>

                                                    <div class="ordered-item-placeholder">
                                                        ☕
                                                    </div>

                                                <?php endif; ?>

                                                <div class="ordered-item-info">

                                                    <div class="ordered-item-name">

                                                        <?php
                                                        echo e(
                                                            $item["product_name"]
                                                        );
                                                        ?>

                                                        <span class="qty-badge">
                                                            Qty:
                                                            <?php
                                                            echo (int)$item["quantity"];
                                                            ?>
                                                        </span>

                                                    </div>

                                                    <div class="ordered-item-meta">

                                                        ₱<?php
                                                        echo number_format(
                                                            (float)$item["price"],
                                                            2
                                                        );
                                                        ?>
                                                        each
                                                        · Subtotal ₱<?php
                                                        echo number_format(
                                                            (float)$item["subtotal"],
                                                            2
                                                        );
                                                        ?>

                                                    </div>

                                                </div>

                                            </div>

                                        <?php endforeach; ?>

                                    </div>

                                <?php else: ?>

                                    <div class="items-empty">
                                        No order items found.
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- TOTAL -->

                            <td>

                                <div class="amount">

                                    ₱<?php
                                    echo number_format(
                                        (float)$order["total_amount"],
                                        2
                                    );
                                    ?>

                                </div>

                            </td>


                            <!-- PAYMENT -->

                            <td class="payment-cell">

                                <?php
                                if (
                                    strcasecmp(
                                        $payment_method,
                                        "GCash"
                                    ) === 0
                                ):
                                ?>

                                    <div class="payment-method gcash">
                                        GCash
                                    </div>


                                    <?php if ($gcash_receipt !== ""): ?>

                                        <div class="receipt-number">

                                            Receipt:
                                            <?php
                                            echo e(
                                                $gcash_receipt
                                            );
                                            ?>

                                        </div>

                                    <?php else: ?>

                                        <div class="no-receipt">
                                            No receipt number
                                        </div>

                                    <?php endif; ?>


                                <?php else: ?>

                                    <div class="payment-method cash">
                                        Cash
                                    </div>

                                    <div class="cash-note">
                                        Cash payment
                                    </div>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td class="status-cell">

                                <?php if (($order["status"] ?? "") === "Completed"): ?>

                                    <div class="completed-status">
                                        Completed
                                    </div>

                                <?php else: ?>

                                    <form
                                        method="POST"
                                        class="status-form"
                                    >

                                        <input
                                            type="hidden"
                                            name="order_id"
                                            value="<?php echo (int)$order["id"]; ?>"
                                        >

                                        <select
                                            name="status"
                                            class="status-select <?php echo e($status_class); ?>"
                                            onchange="this.form.submit()"
                                        >

                                            <?php foreach ($allowed_statuses as $option_status): ?>

                                                <option
                                                    value="<?php echo e($option_status); ?>"
                                                    <?php echo (($order["status"] ?? "") === $option_status) ? "selected" : ""; ?>
                                                >
                                                    <?php echo e($option_status); ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </form>

                                <?php endif; ?>

                            </td>


                            <!-- ORDER DATE -->

                            <td>

                                <div class="date">

                                    <?php
                                    echo date(
                                        "M d, Y",
                                        strtotime(
                                            $order["order_date"]
                                        )
                                    );
                                    ?>

                                    <br>

                                    <?php
                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $order["order_date"]
                                        )
                                    );
                                    ?>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>


            <?php else: ?>

                <div class="empty">

                    <div class="empty-icon">
                        ☕
                    </div>

                    <h3>
                        No orders found
                    </h3>

                    <p>

                        <?php

                        if (
                            $search !== "" ||
                            $status_filter !== ""
                        ) {

                            echo
                                "Try changing your search or filter.";

                        } else {

                            echo
                                "Customer orders will appear here.";

                        }

                        ?>

                    </p>

                </div>

            <?php endif; ?>

        </div>

    </section>

</main>


<script>

setTimeout(function () {

    const toast =
        document.querySelector(".toast");

    if (toast) {

        toast.style.opacity = "0";
        toast.style.transform =
            "translateY(-8px)";

        setTimeout(function () {

            toast.remove();

        }, 250);

    }

}, 3000);

</script>

</body>
</html>