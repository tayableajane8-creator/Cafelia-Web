<?php

session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

/* =========================
   DEFAULT VALUES
========================= */

$total_sales = 0;
$today_sales = 0;
$monthly_sales = 0;

$total_orders = 0;
$completed_orders = 0;
$pending_orders = 0;
$processing_orders = 0;
$cancelled_orders = 0;

$average_order = 0;

$recent_sales = null;

/* =========================
   TOTAL SALES
========================= */

$result = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE status = 'Completed'
");

if ($result) {
    $row = $result->fetch_assoc();
    $total_sales = (float) ($row["total"] ?? 0);
}

/* =========================
   TOTAL ORDERS
========================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
");

if ($result) {
    $row = $result->fetch_assoc();
    $total_orders = (int) ($row["total"] ?? 0);
}

/* =========================
   COMPLETED ORDERS
========================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'Completed'
");

if ($result) {
    $row = $result->fetch_assoc();
    $completed_orders = (int) ($row["total"] ?? 0);
}

/* =========================
   PENDING ORDERS
========================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'Pending'
");

if ($result) {
    $row = $result->fetch_assoc();
    $pending_orders = (int) ($row["total"] ?? 0);
}

/* =========================
   PROCESSING ORDERS
========================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'Processing'
");

if ($result) {
    $row = $result->fetch_assoc();
    $processing_orders = (int) ($row["total"] ?? 0);
}

/* =========================
   CANCELLED ORDERS
========================= */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'Cancelled'
");

if ($result) {
    $row = $result->fetch_assoc();
    $cancelled_orders = (int) ($row["total"] ?? 0);
}

/* =========================
   TODAY'S SALES
========================= */

$result = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE status = 'Completed'
    AND DATE(order_date) = CURDATE()
");

if ($result) {
    $row = $result->fetch_assoc();
    $today_sales = (float) ($row["total"] ?? 0);
}

/* =========================
   MONTHLY SALES
========================= */

$result = $conn->query("
    SELECT COALESCE(SUM(total_amount), 0) AS total
    FROM orders
    WHERE status = 'Completed'
    AND MONTH(order_date) = MONTH(CURDATE())
    AND YEAR(order_date) = YEAR(CURDATE())
");

if ($result) {
    $row = $result->fetch_assoc();
    $monthly_sales = (float) ($row["total"] ?? 0);
}

/* =========================
   AVERAGE ORDER
========================= */

if ($completed_orders > 0) {
    $average_order = $total_sales / $completed_orders;
}

/* =========================
   RECENT COMPLETED SALES
========================= */

$recent_sales = $conn->query("
    SELECT
        orders.id,
        orders.total_amount,
        orders.order_date,
        users.name
    FROM orders
    INNER JOIN users
        ON orders.user_id = users.id
    WHERE orders.status = 'Completed'
    ORDER BY orders.order_date DESC
    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reports | Cafelia Admin</title>

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
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --espresso: #2b1710;
            --espresso-dark: #1c0e09;
            --coffee: #5a3425;
            --caramel: #b9824f;

            --cream: #f7f1e8;
            --cream-light: #fcfaf7;

            --text: #30211b;
            --muted: #8c7d72;

            --border: #e9ddd1;

            --green: #3e7554;
            --green-bg: #e9f4ec;

            --yellow: #a36b24;
            --yellow-bg: #fff3dc;

            --purple: #67549b;
            --purple-bg: #eeeafb;

            --red: #a64b47;
            --red-bg: #f9e9e7;

            --shadow: 0 16px 40px rgba(43, 23, 16, .07);
        }


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {
            min-height: 100vh;
            background: #f5f0e9;
            color: var(--text);
            font-family: "DM Sans", sans-serif;
        }


        a {
            text-decoration: none;
            color: inherit;
        }


        /* =========================
           SIDEBAR
        ========================= */

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;

            width: 245px;

            padding: 28px 17px;

            background:
                linear-gradient(
                    180deg,
                    var(--espresso-dark),
                    var(--espresso)
                );

            color: white;

            z-index: 100;
        }


        .brand {
            padding: 7px 14px 27px;

            border-bottom:
                1px solid
                rgba(255,255,255,.09);
        }


        .brand h2 {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 29px;
            font-weight: 700;
        }


        .brand p {
            margin-top: 4px;

            color:
                rgba(255,255,255,.43);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .14em;
            text-transform: uppercase;
        }


        .admin-label {
            display: flex;
            align-items: center;
            gap: 9px;

            margin:
                22px
                10px
                13px;

            color:
                rgba(255,255,255,.42);

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .14em;
            text-transform: uppercase;
        }


        .admin-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #94c59d;
        }


        .nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }


        .nav a {
            display: flex;
            align-items: center;
            gap: 12px;

            min-height: 47px;

            padding: 0 13px;

            border:
                1px solid
                transparent;

            border-radius: 12px;

            color:
                rgba(255,255,255,.62);

            font-size: 12px;
            font-weight: 600;

            transition:
                background .2s ease,
                color .2s ease,
                transform .2s ease;
        }


        .nav a:hover {
            background:
                rgba(255,255,255,.07);

            color: white;

            transform: translateX(2px);
        }


        .nav a.active {
            background:
                rgba(255,255,255,.11);

            border-color:
                rgba(255,255,255,.06);

            color: white;
        }


        .nav-icon {
            width: 29px;
            height: 29px;

            display: grid;
            place-items: center;

            border-radius: 9px;

            background:
                rgba(255,255,255,.06);

            font-size: 13px;
        }


        .nav a.active .nav-icon {
            background: var(--caramel);
        }


        .nav-divider {
            height: 1px;

            margin:
                10px
                10px
                12px;

            background:
                rgba(255,255,255,.08);
        }


        /* =========================
           MAIN
        ========================= */

        .main {
            margin-left: 245px;

            min-height: 100vh;

            padding:
                34px
                38px
                55px;
        }


        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 20px;

            margin-bottom: 27px;
        }


        .eyebrow {
            margin-bottom: 7px;

            color: var(--caramel);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .16em;
            text-transform: uppercase;
        }


        .page-header h1 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 40px;
            line-height: 1.1;
        }


        .page-header p {
            margin-top: 8px;

            color: var(--muted);

            font-size: 12px;
        }


        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 8px 13px 8px 8px;

            background:
                rgba(255,255,255,.78);

            border:
                1px solid
                var(--border);

            border-radius: 14px;

            box-shadow:
                0 8px 24px
                rgba(43,23,16,.04);
        }


        .admin-avatar {
            width: 36px;
            height: 36px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            background: var(--espresso);
            color: white;

            font-size: 12px;
            font-weight: 700;
        }


        .admin-user small {
            display: block;

            margin-bottom: 2px;

            color: var(--muted);

            font-size: 9px;
        }


        .admin-user strong {
            font-size: 11px;
        }


        /* =========================
           REVENUE CARDS
        ========================= */

        .revenue-grid {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 20px;
        }


        .revenue-card {
            position: relative;

            overflow: hidden;

            padding: 21px;

            background:
                rgba(255,255,255,.86);

            border:
                1px solid
                var(--border);

            border-radius: 17px;

            box-shadow: var(--shadow);
        }


        .revenue-card::after {
            content: "";

            position: absolute;

            right: -30px;
            bottom: -35px;

            width: 100px;
            height: 100px;

            border-radius: 50%;

            background:
                rgba(185,130,79,.07);
        }


        .revenue-icon {
            width: 38px;
            height: 38px;

            display: grid;
            place-items: center;

            margin-bottom: 15px;

            border-radius: 11px;

            background: var(--cream);

            color: var(--coffee);

            font-size: 15px;
            font-weight: 800;
        }


        .revenue-card h2 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 25px;
        }


        .revenue-card p {
            margin-top: 5px;

            color: var(--muted);

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .08em;
            text-transform: uppercase;
        }


        .revenue-card.green .revenue-icon {
            background: var(--green-bg);
            color: var(--green);
        }


        .revenue-card.purple .revenue-icon {
            background: var(--purple-bg);
            color: var(--purple);
        }


        .revenue-card.yellow .revenue-icon {
            background: var(--yellow-bg);
            color: var(--yellow);
        }


        /* =========================
           ANALYTICS
        ========================= */

        .analytics-grid {
            display: grid;

            grid-template-columns:
                1fr
                1.35fr;

            gap: 20px;

            margin-bottom: 20px;
        }


        .card {
            overflow: hidden;

            background:
                rgba(255,255,255,.88);

            border:
                1px solid
                var(--border);

            border-radius: 19px;

            box-shadow: var(--shadow);
        }


        .card-header {
            padding:
                21px
                23px;

            border-bottom:
                1px solid
                var(--border);
        }


        .card-header h2 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 20px;
        }


        .card-header p {
            margin-top: 4px;

            color: var(--muted);

            font-size: 10px;
        }


        /* STATUS */

        .status-list {
            padding:
                7px
                23px
                19px;
        }


        .status-row {
            display: flex;
            align-items: center;
            gap: 11px;

            padding: 14px 0;

            border-bottom:
                1px solid
                #f0e8df;
        }


        .status-row:last-child {
            border-bottom: 0;
        }


        .status-icon {
            width: 35px;
            height: 35px;

            flex: 0 0 35px;

            display: grid;
            place-items: center;

            border-radius: 10px;

            background: var(--cream);

            font-size: 13px;
        }


        .status-info {
            flex: 1;
        }


        .status-top {
            display: flex;
            justify-content: space-between;

            margin-bottom: 6px;
        }


        .status-name {
            color: var(--espresso);

            font-size: 11px;
            font-weight: 700;
        }


        .status-number {
            color: var(--espresso);

            font-size: 11px;
            font-weight: 800;
        }


        .status-track {
            height: 5px;

            overflow: hidden;

            border-radius: 20px;

            background:
                #eee7df;
        }


        .status-fill {
            height: 100%;

            border-radius: inherit;
        }


        .pending {
            background: var(--yellow);
        }


        .processing {
            background: var(--purple);
        }


        .completed {
            background: var(--green);
        }


        .cancelled {
            background: var(--red);
        }


        /* PERFORMANCE */

        .performance {
            padding: 21px 23px;
        }


        .performance-row {
            margin-bottom: 22px;
        }


        .performance-row:last-child {
            margin-bottom: 0;
        }


        .performance-top {
            display: flex;
            align-items: center;
            justify-content: space-between;

            margin-bottom: 8px;
        }


        .performance-top span:first-child {
            color: var(--muted);

            font-size: 10px;
            font-weight: 700;
        }


        .performance-top span:last-child {
            color: var(--espresso);

            font-size: 11px;
            font-weight: 800;
        }


        .performance-track {
            height: 7px;

            overflow: hidden;

            border-radius: 20px;

            background:
                #eee7df;
        }


        .performance-fill {
            height: 100%;

            border-radius: inherit;
        }


        .performance-total {
            width: 100%;

            background: var(--caramel);
        }


        .performance-today {
            width: <?php
                if ($total_sales > 0) {
                    echo min(
                        100,
                        ($today_sales / $total_sales) * 100
                    );
                } else {
                    echo 0;
                }
            ?>;

            background: var(--green);
        }


        .performance-month {
            width: <?php
                if ($total_sales > 0) {
                    echo min(
                        100,
                        ($monthly_sales / $total_sales) * 100
                    );
                } else {
                    echo 0;
                }
            ?>;

            background: var(--purple);
        }


        .performance-average {
            width: <?php
                if ($total_sales > 0) {
                    echo min(
                        100,
                        ($average_order / $total_sales) * 100
                    );
                } else {
                    echo 0;
                }
            ?>;

            background: var(--yellow);
        }


        /* =========================
           RECENT SALES
        ========================= */

        .recent-card {
            margin-bottom: 0;
        }


        .table-wrap {
            overflow-x: auto;
        }


        table {
            width: 100%;

            min-width: 650px;

            border-collapse: collapse;
        }


        thead {
            background: #faf6f0;
        }


        th {
            padding:
                14px
                21px;

            border-bottom:
                1px solid
                var(--border);

            color: #9b8b7f;

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .10em;

            text-align: left;

            text-transform: uppercase;
        }


        td {
            padding:
                16px
                21px;

            border-bottom:
                1px solid
                #f0e8df;

            font-size: 11px;
        }


        tbody tr {
            transition:
                background .18s ease;
        }


        tbody tr:hover {
            background:
                #fdfaf6;
        }


        tbody tr:last-child td {
            border-bottom: 0;
        }


        .order-id {
            display: inline-flex;

            padding:
                6px
                9px;

            border-radius: 8px;

            background:
                #f2e8dc;

            color: var(--espresso);

            font-size: 10px;
            font-weight: 800;
        }


        .customer-name {
            color: var(--espresso);

            font-weight: 700;
        }


        .sale-amount {
            color: var(--green);

            font-size: 12px;
            font-weight: 800;
        }


        .sale-date {
            color: var(--muted);

            font-size: 10px;
        }


        /* EMPTY */

        .empty {
            padding:
                60px
                20px;

            text-align: center;
        }


        .empty-icon {
            width: 56px;
            height: 56px;

            display: grid;
            place-items: center;

            margin:
                0
                auto
                14px;

            border-radius: 16px;

            background: var(--cream);

            color: var(--coffee);

            font-size: 20px;
        }


        .empty h3 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 19px;
        }


        .empty p {
            margin-top: 5px;

            color: var(--muted);

            font-size: 11px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 1150px) {

            .revenue-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .analytics-grid {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 850px) {

            .sidebar {
                position: static;

                width: 100%;
                height: auto;
            }


            .nav {
                display: grid;

                grid-template-columns:
                    repeat(3, 1fr);
            }


            .nav-divider {
                display: none;
            }


            .main {
                margin-left: 0;

                padding:
                    25px
                    20px
                    40px;
            }

        }


        @media (max-width: 650px) {

            .revenue-grid {
                grid-template-columns: 1fr;
            }


            .page-header {
                flex-direction: column;
            }


            .admin-user {
                width: 100%;
            }


            .nav {
                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media (max-width: 430px) {

            .main {
                padding:
                    20px
                    14px
                    35px;
            }


            .page-header h1 {
                font-size: 34px;
            }


            .card-header {
                padding: 18px;
            }


            .status-list,
            .performance {
                padding-left: 18px;
                padding-right: 18px;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     SIDEBAR
========================= -->

<aside class="sidebar">

    <div class="brand">

        <h2>
            Cafelia
        </h2>

        <p>
            Management System
        </p>

    </div>


    <div class="admin-label">

        <span class="admin-dot"></span>

        Admin Panel

    </div>


    <nav class="nav">


        <a href="dashboard.php">

            <span class="nav-icon">
                ⌂
            </span>

            Dashboard

        </a>


        <a href="products.php">

            <span class="nav-icon">
                ☕
            </span>

            Products

        </a>


        <a href="orders.php">

            <span class="nav-icon">
                ▤
            </span>

            Orders

        </a>


        <a href="customers.php">

            <span class="nav-icon">
                ♙
            </span>

            Customers

        </a>


        <a
            href="reports.php"
            class="active"
        >

            <span class="nav-icon">
                ▥
            </span>

            Reports

        </a>


        <div class="nav-divider"></div>


        <a href="../index.php">

            <span class="nav-icon">
                ↗
            </span>

            View Website

        </a>


        <a href="../index.php">

            <span class="nav-icon">
                ⇥
            </span>

            Logout

        </a>


    </nav>

</aside>


<!-- =========================
     MAIN
========================= -->

<main class="main">


    <!-- HEADER -->

    <header class="page-header">


        <div>

            <div class="eyebrow">
                Cafelia Administration
            </div>

            <h1>
                Reports
            </h1>

            <p>
                Monitor sales, revenue, and order performance.
            </p>

        </div>


        <div class="admin-user">


            <div class="admin-avatar">

                <?php
                echo htmlspecialchars(
                    strtoupper(
                        substr(
                            $admin_name,
                            0,
                            1
                        )
                    )
                );
                ?>

            </div>


            <div>

                <small>
                    Signed in as
                </small>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $admin_name
                    );
                    ?>
                </strong>

            </div>


        </div>


    </header>


    <!-- REVENUE -->

    <section class="revenue-grid">


        <div class="revenue-card">

            <div class="revenue-icon">
                ₱
            </div>

            <h2>
                ₱<?php
                echo number_format(
                    $total_sales,
                    2
                );
                ?>
            </h2>

            <p>
                Total Sales
            </p>

        </div>


        <div class="revenue-card green">

            <div class="revenue-icon">
                ◷
            </div>

            <h2>
                ₱<?php
                echo number_format(
                    $today_sales,
                    2
                );
                ?>
            </h2>

            <p>
                Today's Sales
            </p>

        </div>


        <div class="revenue-card purple">

            <div class="revenue-icon">
                ↗
            </div>

            <h2>
                ₱<?php
                echo number_format(
                    $monthly_sales,
                    2
                );
                ?>
            </h2>

            <p>
                This Month
            </p>

        </div>


        <div class="revenue-card yellow">

            <div class="revenue-icon">
                #
            </div>

            <h2>
                <?php
                echo $total_orders;
                ?>
            </h2>

            <p>
                Total Orders
            </p>

        </div>


    </section>


    <!-- ANALYTICS -->

    <section class="analytics-grid">


        <!-- ORDER STATUS -->

        <div class="card">


            <div class="card-header">

                <h2>
                    Order Status
                </h2>

                <p>
                    Current status of all customer orders.
                </p>

            </div>


            <div class="status-list">


                <!-- PENDING -->

                <div class="status-row">

                    <div class="status-icon">
                        ⏳
                    </div>


                    <div class="status-info">

                        <div class="status-top">

                            <span class="status-name">
                                Pending
                            </span>

                            <span class="status-number">
                                <?php
                                echo $pending_orders;
                                ?>
                            </span>

                        </div>


                        <div class="status-track">

                            <div
                                class="status-fill pending"
                                style="width:
                                <?php
                                echo $total_orders > 0
                                    ? round(
                                        ($pending_orders / $total_orders) * 100
                                    )
                                    : 0;
                                ?>%;"
                            ></div>

                        </div>

                    </div>

                </div>


                <!-- PROCESSING -->

                <div class="status-row">

                    <div class="status-icon">
                        ⚙
                    </div>


                    <div class="status-info">

                        <div class="status-top">

                            <span class="status-name">
                                Processing
                            </span>

                            <span class="status-number">
                                <?php
                                echo $processing_orders;
                                ?>
                            </span>

                        </div>


                        <div class="status-track">

                            <div
                                class="status-fill processing"
                                style="width:
                                <?php
                                echo $total_orders > 0
                                    ? round(
                                        ($processing_orders / $total_orders) * 100
                                    )
                                    : 0;
                                ?>%;"
                            ></div>

                        </div>

                    </div>

                </div>


                <!-- COMPLETED -->

                <div class="status-row">

                    <div class="status-icon">
                        ✓
                    </div>


                    <div class="status-info">

                        <div class="status-top">

                            <span class="status-name">
                                Completed
                            </span>

                            <span class="status-number">
                                <?php
                                echo $completed_orders;
                                ?>
                            </span>

                        </div>


                        <div class="status-track">

                            <div
                                class="status-fill completed"
                                style="width:
                                <?php
                                echo $total_orders > 0
                                    ? round(
                                        ($completed_orders / $total_orders) * 100
                                    )
                                    : 0;
                                ?>%;"
                            ></div>

                        </div>

                    </div>

                </div>


                <!-- CANCELLED -->

                <div class="status-row">

                    <div class="status-icon">
                        ×
                    </div>


                    <div class="status-info">

                        <div class="status-top">

                            <span class="status-name">
                                Cancelled
                            </span>

                            <span class="status-number">
                                <?php
                                echo $cancelled_orders;
                                ?>
                            </span>

                        </div>


                        <div class="status-track">

                            <div
                                class="status-fill cancelled"
                                style="width:
                                <?php
                                echo $total_orders > 0
                                    ? round(
                                        ($cancelled_orders / $total_orders) * 100
                                    )
                                    : 0;
                                ?>%;"
                            ></div>

                        </div>

                    </div>

                </div>


            </div>

        </div>


        <!-- SALES PERFORMANCE -->

        <div class="card">


            <div class="card-header">

                <h2>
                    Sales Performance
                </h2>

                <p>
                    Overview of your current revenue metrics.
                </p>

            </div>


            <div class="performance">


                <div class="performance-row">

                    <div class="performance-top">

                        <span>
                            Total Completed Sales
                        </span>

                        <span>
                            ₱<?php
                            echo number_format(
                                $total_sales,
                                2
                            );
                            ?>
                        </span>

                    </div>


                    <div class="performance-track">

                        <div
                            class="performance-fill performance-total"
                        ></div>

                    </div>

                </div>


                <div class="performance-row">

                    <div class="performance-top">

                        <span>
                            Today's Sales
                        </span>

                        <span>
                            ₱<?php
                            echo number_format(
                                $today_sales,
                                2
                            );
                            ?>
                        </span>

                    </div>


                    <div class="performance-track">

                        <div
                            class="performance-fill performance-today"
                            style="width:
                            <?php
                            echo $total_sales > 0
                                ? min(
                                    100,
                                    ($today_sales / $total_sales) * 100
                                )
                                : 0;
                            ?>%;"
                        ></div>

                    </div>

                </div>


                <div class="performance-row">

                    <div class="performance-top">

                        <span>
                            Monthly Sales
                        </span>

                        <span>
                            ₱<?php
                            echo number_format(
                                $monthly_sales,
                                2
                            );
                            ?>
                        </span>

                    </div>


                    <div class="performance-track">

                        <div
                            class="performance-fill performance-month"
                            style="width:
                            <?php
                            echo $total_sales > 0
                                ? min(
                                    100,
                                    ($monthly_sales / $total_sales) * 100
                                )
                                : 0;
                            ?>%;"
                        ></div>

                    </div>

                </div>


                <div class="performance-row">

                    <div class="performance-top">

                        <span>
                            Average Completed Order
                        </span>

                        <span>
                            ₱<?php
                            echo number_format(
                                $average_order,
                                2
                            );
                            ?>
                        </span>

                    </div>


                    <div class="performance-track">

                        <div
                            class="performance-fill performance-average"
                            style="width:
                            <?php
                            echo $total_sales > 0
                                ? min(
                                    100,
                                    ($average_order / $total_sales) * 100
                                )
                                : 0;
                            ?>%;"
                        ></div>

                    </div>

                </div>


            </div>

        </div>


    </section>


    <!-- RECENT SALES -->

    <section class="card recent-card">


        <div class="card-header">

            <h2>
                Recent Completed Sales
            </h2>

            <p>
                Latest successfully completed customer orders.
            </p>

        </div>


        <div class="table-wrap">


            <?php if ($recent_sales && $recent_sales->num_rows > 0): ?>


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
                                Amount
                            </th>

                            <th>
                                Date
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($sale = $recent_sales->fetch_assoc()): ?>


                        <tr>


                            <td>

                                <span class="order-id">

                                    #<?php
                                    echo (int) $sale["id"];
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="customer-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $sale["name"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="sale-amount">

                                    ₱<?php
                                    echo number_format(
                                        (float) $sale["total_amount"],
                                        2
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <span class="sale-date">

                                    <?php
                                    echo date(
                                        "M d, Y • h:i A",
                                        strtotime(
                                            $sale["order_date"]
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>


            <?php else: ?>


                <div class="empty">

                    <div class="empty-icon">
                        ₱
                    </div>

                    <h3>
                        No completed sales yet
                    </h3>

                    <p>
                        Completed customer orders will appear here.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </section>


</main>

</body>
</html>