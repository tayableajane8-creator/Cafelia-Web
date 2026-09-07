<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";
$message = "";
$error = "";

/* UPDATE ORDER STATUS */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = (int) ($_POST["order_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    $allowed_statuses = [
        "Pending",
        "Processing",
        "Completed",
        "Cancelled"
    ];

    if ($order_id > 0 && in_array($status, $allowed_statuses, true)) {
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
    }

    if ($message !== "" || $error !== "") {
        $redirect = "orders.php";

        if ($message !== "") {
            $redirect .= "?updated=1";
        } else {
            $redirect .= "?error=1";
        }

        header("Location: " . $redirect);
        exit();
    }
}

/* FILTERS */
$search = trim($_GET["search"] ?? "");
$status_filter = trim($_GET["status"] ?? "");

$allowed_filter_statuses = [
    "Pending",
    "Processing",
    "Completed",
    "Cancelled"
];

/* ORDER STATISTICS */
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
        COALESCE(SUM(CASE WHEN status = 'Completed' THEN total_amount ELSE 0 END), 0) AS total_sales
     FROM orders"
);

if ($stats) {
    $stats_data = $stats->fetch_assoc();

    $total_orders = (int) ($stats_data["total_orders"] ?? 0);
    $pending_orders = (int) ($stats_data["pending_orders"] ?? 0);
    $processing_orders = (int) ($stats_data["processing_orders"] ?? 0);
    $completed_orders = (int) ($stats_data["completed_orders"] ?? 0);
    $cancelled_orders = (int) ($stats_data["cancelled_orders"] ?? 0);
    $total_sales = (float) ($stats_data["total_sales"] ?? 0);
}

/* BUILD ORDER QUERY */
$sql = "
    SELECT
        orders.id,
        orders.total_amount,
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

if ($search !== "") {
    $sql .= "
        AND (
            orders.id LIKE ?
            OR users.name LIKE ?
            OR users.email LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}

if (in_array($status_filter, $allowed_filter_statuses, true)) {
    $sql .= " AND orders.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$sql .= " ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $orders = $stmt->get_result();
} else {
    $orders = false;
}

$updated = isset($_GET["updated"]);
$error_message = isset($_GET["error"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Orders | Cafelia Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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
            --orange: #a96f32;
            --shadow: 0 18px 45px rgba(43, 23, 16, .08);
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

        /* SIDEBAR */

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            padding: 28px 18px;
            background: linear-gradient(
                180deg,
                var(--espresso-dark),
                var(--espresso)
            );
            color: white;
            z-index: 100;
        }

        .brand {
            padding: 8px 14px 30px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }

        .brand h2 {
            font-family: "Playfair Display", serif;
            font-size: 29px;
            letter-spacing: .02em;
        }

        .brand p {
            margin-top: 4px;
            color: rgba(255,255,255,.48);
            font-size: 11px;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .admin-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 22px 10px 14px;
            color: rgba(255,255,255,.48);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .admin-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #9bc79f;
            box-shadow: 0 0 0 4px rgba(155,199,159,.10);
        }

        .nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nav a {
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 48px;
            padding: 0 14px;
            border: 1px solid transparent;
            border-radius: 13px;
            color: rgba(255,255,255,.67);
            font-size: 13px;
            font-weight: 600;
            transition: .2s ease;
        }

        .nav a:hover {
            background: rgba(255,255,255,.07);
            color: white;
        }

        .nav a.active {
            background: rgba(255,255,255,.11);
            border-color: rgba(255,255,255,.08);
            color: white;
        }

        .nav-icon {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border-radius: 9px;
            background: rgba(255,255,255,.06);
            font-size: 13px;
        }

        .nav a.active .nav-icon {
            background: var(--caramel);
        }

        .nav-spacer {
            height: 16px;
        }

        .nav-divider {
            height: 1px;
            margin: 10px 10px 12px;
            background: rgba(255,255,255,.08);
        }

        /* MAIN */

        .main {
            margin-left: 250px;
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

        /* STAT CARDS */

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

        /* CONTENT CARD */

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

        .clear-btn:hover {
            color: var(--espresso);
            border-color: #d9c9ba;
        }

        /* TABLE */

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 850px;
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

        .date {
            color: #66584f;
            white-space: nowrap;
            font-size: 11px;
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
            .sidebar {
                position: static;
                width: 100%;
                height: auto;
            }

            .brand {
                padding-bottom: 20px;
            }

            .nav {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
            }

            .nav-divider,
            .nav-spacer {
                display: none;
            }

            .main {
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

            .nav {
                grid-template-columns: repeat(2, 1fr);
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


<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="brand">
        <h2>Cafelia</h2>
        <p>Management System</p>
    </div>

    <div class="admin-label">
        <span class="admin-dot"></span>
        Admin Panel
    </div>

    <nav class="nav">

        <a href="dashboard.php">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="products.php">
            <span class="nav-icon">☕</span>
            Products
        </a>

        <a href="orders.php" class="active">
            <span class="nav-icon">▤</span>
            Orders
        </a>

        <a href="customers.php">
            <span class="nav-icon">♙</span>
            Customers
        </a>

        <a href="reports.php">
            <span class="nav-icon">▥</span>
            Reports
        </a>

        <div class="nav-divider"></div>

        <a href="../index.php">
            <span class="nav-icon">↗</span>
            View Website
        </a>

        <a href="../logout.php">
            <span class="nav-icon">⇥</span>
            Logout
        </a>

    </nav>

</aside>


<!-- MAIN -->

<main class="main">

    <header class="topbar">

        <div>
            <div class="eyebrow">Cafelia Administration</div>

            <h1>Orders</h1>

            <p>
                Track customer purchases and manage order progress.
            </p>
        </div>

        <div class="admin-user">

            <div class="avatar">
                <?php
                echo strtoupper(
                    substr($admin_name, 0, 1)
                );
                ?>
            </div>

            <div>
                <small>Signed in as</small>
                <strong>
                    <?php echo htmlspecialchars($admin_name); ?>
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

                <h2>Order Management</h2>

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

                    <span>⌕</span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search order, customer, or email..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <select
                    class="filter-select"
                    name="status"
                >

                    <option value="">
                        All Statuses
                    </option>

                    <?php foreach ($allowed_filter_statuses as $filter_status): ?>

                        <option
                            value="<?php echo $filter_status; ?>"
                            <?php
                            echo $status_filter === $filter_status
                                ? "selected"
                                : "";
                            ?>
                        >
                            <?php echo $filter_status; ?>
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
                                Total
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
                        $customer_name = $order["name"];
                        $initials = strtoupper(
                            substr(trim($customer_name), 0, 1)
                        );

                        $status_class = strtolower(
                            $order["status"]
                        );
                        ?>

                        <tr>

                            <td>

                                <span class="order-number">
                                    #<?php echo (int) $order["id"]; ?>
                                </span>

                            </td>


                            <td>

                                <div class="customer">

                                    <div class="customer-avatar">
                                        <?php echo htmlspecialchars($initials); ?>
                                    </div>

                                    <div>

                                        <div class="customer-name">
                                            <?php
                                            echo htmlspecialchars(
                                                $customer_name
                                            );
                                            ?>
                                        </div>

                                        <div class="customer-email">
                                            <?php
                                            echo htmlspecialchars(
                                                $order["email"]
                                            );
                                            ?>
                                        </div>

                                    </div>

                                </div>

                            </td>


                            <td>

                                <div class="amount">
                                    ₱<?php
                                    echo number_format(
                                        (float) $order["total_amount"],
                                        2
                                    );
                                    ?>
                                </div>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?php
                                        echo (int) $order["id"];
                                        ?>"
                                    >

                                    <select
                                        name="status"
                                        class="status-select <?php echo htmlspecialchars($status_class); ?>"
                                        onchange="this.form.submit()"
                                    >

                                        <option
                                            value="Pending"
                                            <?php
                                            echo $order["status"] === "Pending"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="Processing"
                                            <?php
                                            echo $order["status"] === "Processing"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Processing
                                        </option>

                                        <option
                                            value="Completed"
                                            <?php
                                            echo $order["status"] === "Completed"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Completed
                                        </option>

                                        <option
                                            value="Cancelled"
                                            <?php
                                            echo $order["status"] === "Cancelled"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Cancelled
                                        </option>

                                    </select>

                                </form>

                            </td>


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

                    <h3>No orders found</h3>

                    <p>
                        <?php
                        if ($search !== "" || $status_filter !== "") {
                            echo "Try changing your search or filter.";
                        } else {
                            echo "Customer orders will appear here.";
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
        const toast = document.querySelector(".toast");

        if (toast) {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-8px)";

            setTimeout(function () {
                toast.remove();
            }, 250);
        }
    }, 3000);

</script>

</body>
</html>