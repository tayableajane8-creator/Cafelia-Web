<?php

session_start();

require_once "../config/database.php";

/* ADMIN AUTHENTICATION */
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

/* DASHBOARD DATA */
$total_products = 0;
$total_customers = 0;
$total_orders = 0;
$total_sales = 0;

/* TOTAL PRODUCTS */
$product_query = $conn->query(
    "SELECT COUNT(*) AS total FROM products"
);

if ($product_query) {
    $data = $product_query->fetch_assoc();
    $total_products = (int) $data["total"];
}

/* TOTAL CUSTOMERS */
$customer_query = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users
     WHERE role = 'customer'"
);

if ($customer_query) {
    $data = $customer_query->fetch_assoc();
    $total_customers = (int) $data["total"];
}

/* TOTAL ORDERS */
$order_query = $conn->query(
    "SELECT COUNT(*) AS total FROM orders"
);

if ($order_query) {
    $data = $order_query->fetch_assoc();
    $total_orders = (int) $data["total"];
}

/* TOTAL COMPLETED SALES */
$sales_query = $conn->query(
    "SELECT COALESCE(SUM(total_amount), 0) AS total
     FROM orders
     WHERE status = 'Completed'"
);

if ($sales_query) {
    $data = $sales_query->fetch_assoc();
    $total_sales = (float) $data["total"];
}

/* RECENT ORDERS */
$recent_orders = $conn->query(
    "SELECT
        orders.id,
        users.name,
        orders.total_amount,
        orders.status,
        orders.order_date
     FROM orders
     INNER JOIN users
        ON orders.user_id = users.id
     ORDER BY orders.order_date DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cafelia Admin Dashboard</title>

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
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #24150f;
            --dark-coffee: #321d14;
            --coffee: #5a3827;
            --caramel: #b98252;
            --gold: #d8a36d;

            --cream: #f7f1e8;
            --cream-light: #fcf9f4;
            --white: #ffffff;

            --text: #302118;
            --muted: #87766a;

            --border: rgba(80, 50, 34, .10);

            --shadow:
                0 18px 45px rgba(49, 30, 20, .08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;

            font-family:
                "DM Sans",
                Arial,
                sans-serif;

            color: var(--text);

            background:
                radial-gradient(
                    circle at 80% 10%,
                    rgba(216, 163, 109, .13),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #f8f3eb,
                    #eee2d4
                );
        }

        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;

            top: 0;
            left: 0;

            width: 255px;
            height: 100vh;

            padding: 30px 18px;

            display: flex;
            flex-direction: column;

            background:
                linear-gradient(
                    160deg,
                    var(--espresso),
                    #3b2418
                );

            color: white;

            box-shadow:
                8px 0 35px rgba(36, 21, 15, .10);

            z-index: 100;
        }

        .sidebar-brand {
            padding: 10px 14px 30px;
        }

        .sidebar-brand h1 {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 31px;
            letter-spacing: .05em;
        }

        .sidebar-brand p {
            margin-top: 6px;

            color: rgba(255,255,255,.45);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .admin-profile {
            margin: 0 6px 28px;

            padding: 14px;

            display: flex;
            align-items: center;

            gap: 12px;

            border:
                1px solid rgba(255,255,255,.10);

            border-radius: 16px;

            background:
                rgba(255,255,255,.06);

            backdrop-filter: blur(12px);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;

            flex-shrink: 0;

            display: grid;
            place-items: center;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    var(--gold),
                    var(--caramel)
                );

            color: white;

            font-weight: 800;
            font-size: 15px;
        }

        .admin-info {
            min-width: 0;
        }

        .admin-info strong {
            display: block;

            overflow: hidden;

            color: white;

            font-size: 13px;

            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .admin-info span {
            display: block;

            margin-top: 3px;

            color: rgba(255,255,255,.42);

            font-size: 10px;

            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;

            gap: 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;

            gap: 13px;

            padding: 13px 15px;

            border-radius: 12px;

            color: rgba(255,255,255,.62);

            font-size: 13px;
            font-weight: 600;

            text-decoration: none;

            transition: .2s ease;
        }

        .sidebar-nav a:hover {
            color: white;

            background:
                rgba(255,255,255,.07);

            transform: translateX(2px);
        }

        .sidebar-nav a.active {
            color: white;

            background:
                linear-gradient(
                    135deg,
                    rgba(185,130,82,.45),
                    rgba(90,56,39,.65)
                );

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10);
        }

        .nav-icon {
            width: 25px;

            text-align: center;

            font-size: 16px;
        }

        .sidebar-bottom {
            margin-top: auto;
        }

        .logout-link {
            border-top:
                1px solid rgba(255,255,255,.08);

            margin-top: 12px;

            padding-top: 18px !important;
        }

        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 255px;

            min-height: 100vh;

            padding: 38px 42px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 35px;
        }

        .topbar-kicker {
            margin-bottom: 6px;

            color: var(--caramel);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .18em;
            text-transform: uppercase;
        }

        .topbar h2 {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            color: var(--espresso);

            font-size: 36px;
            font-weight: 600;
        }

        .topbar p {
            margin-top: 7px;

            color: var(--muted);

            font-size: 13px;
        }

        .date-box {
            padding: 12px 17px;

            border:
                1px solid var(--border);

            border-radius: 13px;

            background:
                rgba(255,255,255,.60);

            color: var(--muted);

            font-size: 11px;
            font-weight: 700;

            box-shadow:
                0 8px 25px rgba(50,30,20,.04);
        }

        /* =========================
           STATISTICS
        ========================= */

        .stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 18px;

            margin-bottom: 25px;
        }

        .stat-card {
            position: relative;

            min-height: 155px;

            padding: 23px;

            overflow: hidden;

            border:
                1px solid rgba(255,255,255,.75);

            border-radius: 20px;

            background:
                rgba(255,255,255,.76);

            box-shadow: var(--shadow);

            backdrop-filter: blur(16px);

            transition: .2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);

            box-shadow:
                0 23px 48px rgba(49,30,20,.11);
        }

        .stat-card::after {
            content: "";

            position: absolute;

            width: 110px;
            height: 110px;

            right: -48px;
            bottom: -50px;

            border-radius: 50%;

            background:
                rgba(185,130,82,.09);
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 19px;
        }

        .stat-icon {
            width: 43px;
            height: 43px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            background:
                rgba(185,130,82,.11);

            color: var(--coffee);

            font-size: 19px;
        }

        .stat-label {
            color: var(--muted);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .11em;
            text-transform: uppercase;
        }

        .stat-number {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 32px;
            font-weight: 600;
        }

        /* =========================
           CONTENT CARD
        ========================= */

        .content-card {
            overflow: hidden;

            border:
                1px solid rgba(255,255,255,.75);

            border-radius: 22px;

            background:
                rgba(255,255,255,.80);

            box-shadow: var(--shadow);

            backdrop-filter: blur(16px);
        }

        .card-header {
            padding: 23px 25px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            border-bottom:
                1px solid var(--border);
        }

        .card-header h3 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 22px;
            font-weight: 600;
        }

        .view-all {
            padding: 9px 13px;

            border-radius: 9px;

            color: var(--coffee);

            background:
                rgba(185,130,82,.09);

            font-size: 11px;
            font-weight: 800;

            text-decoration: none;

            transition: .2s;
        }

        .view-all:hover {
            background:
                rgba(185,130,82,.16);
        }

        /* =========================
           TABLE
        ========================= */

        .table-wrapper {
            width: 100%;

            overflow-x: auto;
        }

        table {
            width: 100%;

            border-collapse: collapse;
        }

        th {
            padding: 15px 25px;

            background:
                rgba(248,242,233,.65);

            color: #9a887b;

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .13em;

            text-align: left;

            text-transform: uppercase;
        }

        td {
            padding: 17px 25px;

            border-top:
                1px solid var(--border);

            color: #5f5046;

            font-size: 12px;
        }

        tbody tr {
            transition: .15s;
        }

        tbody tr:hover {
            background:
                rgba(185,130,82,.035);
        }

        .order-id {
            color: var(--coffee);

            font-weight: 800;
        }

        .customer-name {
            color: var(--espresso);

            font-weight: 700;
        }

        .amount {
            color: var(--espresso);

            font-weight: 800;
        }

        .status {
            display: inline-flex;
            align-items: center;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .06em;

            text-transform: uppercase;
        }

        .status.completed {
            background: #e9f5ec;
            color: #397149;
        }

        .status.pending {
            background: #fff4df;
            color: #98691d;
        }

        .status.processing {
            background: #edf1fb;
            color: #52689a;
        }

        .status.cancelled {
            background: #fae9e6;
            color: #9b4b40;
        }

        .no-orders {
            padding: 55px 20px;

            color: var(--muted);

            text-align: center;

            font-size: 13px;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1100px) {

            .stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }

        @media (max-width: 800px) {

            .sidebar {
                position: static;

                width: 100%;
                height: auto;

                padding: 18px;
            }

            .sidebar-brand {
                padding: 8px 8px 18px;
            }

            .admin-profile {
                margin-bottom: 15px;
            }

            .sidebar-nav {
                display: grid;

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .sidebar-bottom {
                margin-top: 8px;
            }

            .main {
                margin-left: 0;

                padding: 28px 20px;
            }

            .topbar {
                align-items: flex-start;

                gap: 15px;
            }

            .date-box {
                display: none;
            }

        }

        @media (max-width: 560px) {

            .stats {
                grid-template-columns: 1fr;
            }

            .sidebar-nav {
                grid-template-columns: 1fr;
            }

            .topbar h2 {
                font-size: 30px;
            }

            th,
            td {
                padding-left: 15px;
                padding-right: 15px;
            }

        }

    </style>

</head>

<body>

    <!-- SIDEBAR -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <h1>Cafelia</h1>

            <p>Admin Management</p>

        </div>


        <div class="admin-profile">

            <div class="admin-avatar">

                <?php
                echo strtoupper(
                    substr($admin_name, 0, 1)
                );
                ?>

            </div>

            <div class="admin-info">

                <strong>
                    <?php
                    echo htmlspecialchars($admin_name);
                    ?>
                </strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>


        <nav class="sidebar-nav">

            <a
                href="dashboard.php"
                class="active"
            >
                <span class="nav-icon">⌂</span>
                Dashboard
            </a>

            <a href="products.php">
                <span class="nav-icon">☕</span>
                Products
            </a>

            <a href="orders.php">
                <span class="nav-icon">▣</span>
                Orders
            </a>

            <a href="customers.php">
                <span class="nav-icon">♙</span>
                Customers
            </a>

            <a href="reports.php">
                <span class="nav-icon">▤</span>
                Reports
            </a>

        </nav>


        <div class="sidebar-bottom">

            <nav class="sidebar-nav">

                <!-- IMPORTANT:
                     This only opens the customer website.
                     It does NOT destroy the admin session. -->

                <a href="../index.php">
                    <span class="nav-icon">↗</span>
                    View Website
                </a>


                <!-- Actual logout -->

                <a
                    href="../logout.php"
                    class="logout-link"
                >
                    <span class="nav-icon">⇥</span>
                    Logout
                </a>

            </nav>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main">

        <header class="topbar">

            <div>

                <div class="topbar-kicker">
                    Cafelia Management
                </div>

                <h2>
                    Dashboard
                </h2>

                <p>
                    Welcome back,
                    <strong>
                        <?php
                        echo htmlspecialchars($admin_name);
                        ?>
                    </strong>.
                    Here's what's happening today.
                </p>

            </div>


            <div class="date-box">

                <?php
                echo date("M d, Y");
                ?>

            </div>

        </header>


        <!-- STATISTICS -->

        <section class="stats">

            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Products
                    </span>

                    <div class="stat-icon">
                        ☕
                    </div>

                </div>

                <div class="stat-number">
                    <?php echo $total_products; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Customers
                    </span>

                    <div class="stat-icon">
                        ♙
                    </div>

                </div>

                <div class="stat-number">
                    <?php echo $total_customers; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Orders
                    </span>

                    <div class="stat-icon">
                        ▣
                    </div>

                </div>

                <div class="stat-number">
                    <?php echo $total_orders; ?>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-top">

                    <span class="stat-label">
                        Total Sales
                    </span>

                    <div class="stat-icon">
                        ₱
                    </div>

                </div>

                <div class="stat-number">
                    ₱<?php echo number_format($total_sales, 2); ?>
                </div>

            </div>

        </section>


        <!-- RECENT ORDERS -->

        <section class="content-card">

            <div class="card-header">

                <h3>
                    Recent Orders
                </h3>

                <a
                    href="orders.php"
                    class="view-all"
                >
                    View All
                </a>

            </div>


            <div class="table-wrapper">

                <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>

                    <table>

                        <thead>

                            <tr>

                                <th>
                                    Order #
                                </th>

                                <th>
                                    Customer
                                </th>

                                <th>
                                    Amount
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Date
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php while ($order = $recent_orders->fetch_assoc()): ?>

                                <?php

                                $status_class = strtolower(
                                    preg_replace(
                                        '/[^a-zA-Z0-9]+/',
                                        '-',
                                        $order["status"]
                                    )
                                );

                                ?>

                                <tr>

                                    <td class="order-id">
                                        #<?php
                                        echo (int) $order["id"];
                                        ?>
                                    </td>


                                    <td class="customer-name">
                                        <?php
                                        echo htmlspecialchars(
                                            $order["name"]
                                        );
                                        ?>
                                    </td>


                                    <td class="amount">
                                        ₱<?php
                                        echo number_format(
                                            (float) $order["total_amount"],
                                            2
                                        );
                                        ?>
                                    </td>


                                    <td>

                                        <span
                                            class="status <?php
                                                echo htmlspecialchars(
                                                    $status_class
                                                );
                                            ?>"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $order["status"]
                                            );
                                            ?>
                                        </span>

                                    </td>


                                    <td>

                                        <?php

                                        echo date(
                                            "M d, Y • h:i A",
                                            strtotime(
                                                $order["order_date"]
                                            )
                                        );

                                        ?>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                <?php else: ?>

                    <div class="no-orders">
                        No orders have been placed yet.
                    </div>

                <?php endif; ?>

            </div>

        </section>

    </main>

</body>

</html>