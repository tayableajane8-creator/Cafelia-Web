<?php

session_start();
require_once "../config/database.php";

/* ADMIN ACCESS */
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

/* SEARCH + FILTER */
$search = trim($_GET["search"] ?? "");
$category = trim($_GET["category"] ?? "");

/* GET CATEGORIES */
$categories = [];

$category_result = $conn->query(
    "SELECT DISTINCT category
     FROM products
     WHERE category IS NOT NULL
     AND category != ''
     ORDER BY category ASC"
);

if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row["category"];
    }
}

/* GET PRODUCTS */
$sql = "
    SELECT
        id,
        name,
        description,
        category,
        price,
        stock,
        status,
        image
    FROM products
    WHERE 1=1
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= "
        AND (
            name LIKE ?
            OR description LIKE ?
            OR category LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "sss";
}

if ($category !== "") {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$products = $stmt->get_result();

/* COUNTS */
$total_products = 0;
$active_products = 0;
$low_stock = 0;
$out_of_stock = 0;

$count_result = $conn->query(
    "SELECT
        COUNT(*) AS total,
        SUM(LOWER(status) IN ('active', 'available')) AS active,
        SUM(stock > 0 AND stock <= 5) AS low_stock,
        SUM(stock <= 0) AS out_stock
     FROM products"
);

if ($count_result) {
    $counts = $count_result->fetch_assoc();

    $total_products = (int)$counts["total"];
    $active_products = (int)$counts["active"];
    $low_stock = (int)$counts["low_stock"];
    $out_of_stock = (int)$counts["out_stock"];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Products | Cafelia Admin</title>

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

            --green: #397149;
            --green-bg: #e9f5ec;

            --orange: #98691d;
            --orange-bg: #fff4df;

            --red: #9b4b40;
            --red-bg: #fae9e6;

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
                    circle at 85% 5%,
                    rgba(216,163,109,.14),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    #f8f3eb,
                    #eee2d4
                );
        }

        /* SIDEBAR */

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
                8px 0 35px rgba(36,21,15,.10);

            z-index: 10;
        }

        .brand {
            padding: 10px 14px 30px;
        }

        .brand h1 {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 31px;

            letter-spacing: .05em;
        }

        .brand p {
            margin-top: 6px;

            color: rgba(255,255,255,.43);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .15em;

            text-transform: uppercase;
        }

        .admin-box {
            margin: 0 6px 25px;

            padding: 13px;

            display: flex;
            align-items: center;

            gap: 11px;

            border:
                1px solid rgba(255,255,255,.09);

            border-radius: 15px;

            background:
                rgba(255,255,255,.06);

            backdrop-filter: blur(12px);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;

            display: grid;
            place-items: center;

            flex-shrink: 0;

            border-radius: 12px;

            background:
                linear-gradient(
                    135deg,
                    var(--gold),
                    var(--caramel)
                );

            font-weight: 800;
        }

        .admin-info {
            min-width: 0;
        }

        .admin-info strong {
            display: block;

            overflow: hidden;

            color: white;

            font-size: 12px;

            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .admin-info span {
            display: block;

            margin-top: 3px;

            color: rgba(255,255,255,.40);

            font-size: 9px;
            font-weight: 700;

            letter-spacing: .08em;

            text-transform: uppercase;
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

            padding: 13px 15px;

            border-radius: 12px;

            color: rgba(255,255,255,.60);

            font-size: 13px;
            font-weight: 600;

            text-decoration: none;

            transition: .2s ease;
        }

        .nav a:hover {
            color: white;

            background:
                rgba(255,255,255,.07);

            transform: translateX(2px);
        }

        .nav a.active {
            color: white;

            background:
                linear-gradient(
                    135deg,
                    rgba(185,130,82,.46),
                    rgba(90,56,39,.62)
                );

            box-shadow:
                inset 0 1px 0 rgba(255,255,255,.10);
        }

        .nav-icon {
            width: 23px;

            text-align: center;

            font-size: 15px;
        }

        .sidebar-bottom {
            margin-top: auto;
        }

        .logout {
            margin-top: 12px;

            padding-top: 18px !important;

            border-top:
                1px solid rgba(255,255,255,.08);

        }

        /* MAIN */

        .main {
            margin-left: 255px;

            min-height: 100vh;

            padding: 38px 42px;
        }

        .header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;

            gap: 20px;

            margin-bottom: 30px;
        }

        .eyebrow {
            margin-bottom: 7px;

            color: var(--caramel);

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .18em;

            text-transform: uppercase;
        }

        .header h2 {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            color: var(--espresso);

            font-size: 38px;

            font-weight: 600;
        }

        .header p {
            margin-top: 7px;

            color: var(--muted);

            font-size: 13px;
        }

        .add-button {
            display: inline-flex;
            align-items: center;

            gap: 8px;

            padding: 13px 17px;

            border-radius: 12px;

            background:
                var(--espresso);

            color: white;

            font-size: 11px;
            font-weight: 800;

            letter-spacing: .08em;

            text-decoration: none;

            box-shadow:
                0 10px 25px rgba(36,21,15,.13);

            transition: .2s ease;
        }

        .add-button:hover {
            background: var(--coffee);

            transform: translateY(-2px);
        }

        /* MINI STATS */

        .mini-stats {
            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 15px;

            margin-bottom: 22px;
        }

        .mini-card {
            padding: 18px 20px;

            display: flex;
            align-items: center;

            gap: 13px;

            border:
                1px solid rgba(255,255,255,.75);

            border-radius: 17px;

            background:
                rgba(255,255,255,.76);

            box-shadow: var(--shadow);

            backdrop-filter: blur(14px);
        }

        .mini-icon {
            width: 40px;
            height: 40px;

            display: grid;
            place-items: center;

            flex-shrink: 0;

            border-radius: 12px;

            background:
                rgba(185,130,82,.11);

            color: var(--coffee);

            font-size: 17px;
        }

        .mini-card strong {
            display: block;

            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 22px;
        }

        .mini-card span {
            display: block;

            margin-top: 2px;

            color: var(--muted);

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;
        }

        /* PRODUCT PANEL */

        .panel {
            overflow: hidden;

            border:
                1px solid rgba(255,255,255,.75);

            border-radius: 22px;

            background:
                rgba(255,255,255,.80);

            box-shadow: var(--shadow);

            backdrop-filter: blur(16px);
        }

        .panel-top {
            padding: 21px 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;

            gap: 20px;

            border-bottom:
                1px solid var(--border);
        }

        .panel-title h3 {
            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 22px;

            font-weight: 600;
        }

        .panel-title p {
            margin-top: 4px;

            color: var(--muted);

            font-size: 11px;
        }

        /* FILTERS */

        .filters {
            display: flex;
            align-items: center;

            gap: 9px;

            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            width: 220px;
            height: 40px;

            padding: 0 13px 0 37px;

            border:
                1px solid var(--border);

            border-radius: 10px;

            outline: none;

            background:
                rgba(255,255,255,.82);

            color: var(--text);

            font-family: inherit;

            font-size: 11px;
        }

        .search-box input:focus {
            border-color: var(--caramel);

            box-shadow:
                0 0 0 3px rgba(185,130,82,.10);
        }

        .search-icon {
            position: absolute;

            left: 13px;
            top: 50%;

            transform: translateY(-50%);

            color: var(--muted);

            font-size: 13px;
        }

        .filter-select {
            height: 40px;

            padding: 0 30px 0 12px;

            border:
                1px solid var(--border);

            border-radius: 10px;

            outline: none;

            background:
                rgba(255,255,255,.82);

            color: var(--text);

            font-family: inherit;

            font-size: 11px;

            cursor: pointer;
        }

        .filter-button {
            height: 40px;

            padding: 0 13px;

            border: 0;

            border-radius: 10px;

            background:
                var(--espresso);

            color: white;

            font-family: inherit;

            font-size: 10px;
            font-weight: 800;

            cursor: pointer;
        }

        .clear-button {
            height: 40px;

            padding: 0 12px;

            display: inline-flex;
            align-items: center;

            border-radius: 10px;

            color: var(--coffee);

            background:
                rgba(185,130,82,.09);

            font-size: 10px;
            font-weight: 800;

            text-decoration: none;
        }

        /* TABLE */

        .table-wrap {
            width: 100%;

            overflow-x: auto;
        }

        table {
            width: 100%;

            min-width: 950px;

            border-collapse: collapse;
        }

        th {
            padding: 14px 18px;

            background:
                rgba(248,242,233,.65);

            color: #9a887b;

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .12em;

            text-align: left;

            text-transform: uppercase;

            white-space: nowrap;
        }

        td {
            padding: 15px 18px;

            border-top:
                1px solid var(--border);

            vertical-align: middle;

            font-size: 12px;
        }

        tbody tr {
            transition: .15s ease;
        }

        tbody tr:hover {
            background:
                rgba(185,130,82,.035);
        }

        .product-image {
            width: 62px;
            height: 62px;

            object-fit: cover;

            display: block;

            border-radius: 12px;

            border:
                1px solid rgba(80,50,34,.10);

            background:
                #f3e9dc;

            box-shadow:
                0 5px 15px rgba(50,30,20,.07);
        }

        .no-image {
            width: 62px;
            height: 62px;

            display: grid;
            place-items: center;

            border-radius: 12px;

            background:
                #eee3d6;

            color: #aa9686;

            font-size: 21px;
        }

        .product-name {
            min-width: 190px;
        }

        .product-name strong {
            display: block;

            color: var(--espresso);

            font-size: 13px;
        }

        .product-name small {
            display: block;

            max-width: 250px;

            margin-top: 4px;

            overflow: hidden;

            color: var(--muted);

            font-size: 10px;

            line-height: 1.45;

            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .category {
            display: inline-flex;

            padding: 6px 9px;

            border-radius: 8px;

            background:
                rgba(185,130,82,.09);

            color: var(--coffee);

            font-size: 9px;
            font-weight: 800;

            letter-spacing: .05em;
        }

        .price {
            color: var(--espresso);

            font-weight: 800;

            white-space: nowrap;
        }

        .stock {
            font-weight: 800;

            white-space: nowrap;
        }

        .stock.good {
            color: var(--green);
        }

        .stock.low {
            color: var(--orange);
        }

        .stock.out {
            color: var(--red);
        }

        .stock-note {
            display: block;

            margin-top: 3px;

            font-size: 9px;

            font-weight: 600;
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

        .status.active {
            background: var(--green-bg);

            color: var(--green);
        }

        .status.inactive {
            background: var(--red-bg);

            color: var(--red);
        }

        .actions {
            display: flex;

            gap: 7px;
        }

        .action {
            padding: 7px 10px;

            border-radius: 8px;

            font-size: 10px;
            font-weight: 800;

            text-decoration: none;

            transition: .2s ease;
        }

        .edit {
            background:
                rgba(185,130,82,.11);

            color: var(--coffee);
        }

        .delete {
            background:
                var(--red-bg);

            color: var(--red);
        }

        .action:hover {
            transform: translateY(-1px);
        }

        .empty {
            padding: 65px 20px !important;

            color: var(--muted);

            text-align: center;
        }

        .empty-icon {
            margin-bottom: 12px;

            font-size: 30px;

            opacity: .55;
        }

        .empty strong {
            display: block;

            color: var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 19px;
        }

        .empty p {
            margin-top: 5px;

            font-size: 11px;
        }

        /* MOBILE */

        @media (max-width: 1050px) {

            .sidebar {
                width: 220px;
            }

            .main {
                margin-left: 220px;

                padding: 30px;
            }

            .mini-stats {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .panel-top {
                align-items: flex-start;

                flex-direction: column;
            }

        }

        @media (max-width: 760px) {

            .sidebar {
                position: static;

                width: 100%;
                height: auto;

                padding: 18px;
            }

            .brand {
                padding: 5px 8px 18px;
            }

            .admin-box {
                margin-bottom: 15px;
            }

            .nav {
                display: grid;

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .sidebar-bottom {
                margin-top: 8px;
            }

            .main {
                margin-left: 0;

                padding: 25px 18px;
            }

            .header {
                align-items: flex-start;

                flex-direction: column;
            }

            .header h2 {
                font-size: 32px;
            }

            .add-button {
                width: 100%;

                justify-content: center;
            }

        }

        @media (max-width: 500px) {

            .mini-stats {
                grid-template-columns: 1fr;
            }

            .nav {
                grid-template-columns: 1fr;
            }

            .filters {
                width: 100%;
            }

            .search-box,
            .search-box input,
            .filter-select,
            .filter-button,
            .clear-button {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<!-- SIDEBAR -->

<aside class="sidebar">

    <div class="brand">

        <h1>Cafelia</h1>

        <p>Admin Management</p>

    </div>


    <div class="admin-box">

        <div class="admin-avatar">

            <?php
            echo strtoupper(
                substr(
                    $_SESSION["admin_name"] ?? "A",
                    0,
                    1
                )
            );
            ?>

        </div>

        <div class="admin-info">

            <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION["admin_name"] ?? "Administrator"
                );
                ?>
            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>


    <nav class="nav">

        <a href="dashboard.php">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a
            href="products.php"
            class="active"
        >
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

        <nav class="nav">

            <a href="../index.php">
                <span class="nav-icon">↗</span>
                View Website
            </a>

            <a
                href="../logout.php"
                class="logout"
            >
                <span class="nav-icon">⇥</span>
                Logout
            </a>

        </nav>

    </div>

</aside>


<!-- MAIN -->

<main class="main">

    <header class="header">

        <div>

            <div class="eyebrow">
                Cafelia Management
            </div>

            <h2>
                Products
            </h2>

            <p>
                Manage your coffee products, pricing, inventory,
                and availability.
            </p>

        </div>


        <a
            href="add-product.php"
            class="add-button"
        >
            <span>＋</span>
            Add Product
        </a>

    </header>


    <!-- MINI STATS -->

    <section class="mini-stats">

        <div class="mini-card">

            <div class="mini-icon">
                ☕
            </div>

            <div>

                <strong>
                    <?php echo $total_products; ?>
                </strong>

                <span>
                    Total Products
                </span>

            </div>

        </div>


        <div class="mini-card">

            <div class="mini-icon">
                ✓
            </div>

            <div>

                <strong>
                    <?php echo $active_products; ?>
                </strong>

                <span>
                    Active Products
                </span>

            </div>

        </div>


        <div class="mini-card">

            <div class="mini-icon">
                !
            </div>

            <div>

                <strong>
                    <?php echo $low_stock; ?>
                </strong>

                <span>
                    Low Stock
                </span>

            </div>

        </div>


        <div class="mini-card">

            <div class="mini-icon">
                ×
            </div>

            <div>

                <strong>
                    <?php echo $out_of_stock; ?>
                </strong>

                <span>
                    Out of Stock
                </span>

            </div>

        </div>

    </section>


    <!-- PRODUCT PANEL -->

    <section class="panel">

        <div class="panel-top">

            <div class="panel-title">

                <h3>
                    Product Catalog
                </h3>

                <p>
                    <?php echo $products->num_rows; ?>
                    product(s) currently displayed
                </p>

            </div>


            <form
                method="GET"
                class="filters"
            >

                <div class="search-box">

                    <span class="search-icon">
                        ⌕
                    </span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search products..."
                        value="<?php
                            echo htmlspecialchars($search);
                        ?>"
                    >

                </div>


                <select
                    name="category"
                    class="filter-select"
                >

                    <option value="">
                        All Categories
                    </option>

                    <?php foreach ($categories as $item_category): ?>

                        <option
                            value="<?php
                                echo htmlspecialchars(
                                    $item_category
                                );
                            ?>"
                            <?php
                            echo $category === $item_category
                                ? "selected"
                                : "";
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $item_category
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>


                <button
                    type="submit"
                    class="filter-button"
                >
                    Filter
                </button>


                <?php if ($search !== "" || $category !== ""): ?>

                    <a
                        href="products.php"
                        class="clear-button"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>

        </div>


        <!-- TABLE -->

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>

                        <th>
                            Image
                        </th>

                        <th>
                            Product
                        </th>

                        <th>
                            Category
                        </th>

                        <th>
                            Price
                        </th>

                        <th>
                            Stock
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($products->num_rows > 0): ?>

                        <?php while ($product = $products->fetch_assoc()): ?>

                            <?php

                            $stock = (int)$product["stock"];

                            if ($stock <= 0) {

                                $stock_class = "out";
                                $stock_note = "Out of stock";

                            } elseif ($stock <= 5) {

                                $stock_class = "low";
                                $stock_note = "Low stock";

                            } else {

                                $stock_class = "good";
                                $stock_note = "Available";

                            }

                            $image = trim(
                                $product["image"] ?? ""
                            );

                            ?>

                            <tr>

                                <!-- IMAGE -->

                                <td>

                                    <?php if ($image !== ""): ?>

                                        <img
                                            src="../image/<?php
                                                echo htmlspecialchars(
                                                    $image
                                                );
                                            ?>"
                                            alt="<?php
                                                echo htmlspecialchars(
                                                    $product["name"]
                                                );
                                            ?>"
                                            class="product-image"
                                            onerror="this.style.display='none';this.nextElementSibling.style.display='grid';"
                                        >

                                        <div
                                            class="no-image"
                                            style="display:none;"
                                        >
                                            ☕
                                        </div>

                                    <?php else: ?>

                                        <div class="no-image">
                                            ☕
                                        </div>

                                    <?php endif; ?>

                                </td>


                                <!-- PRODUCT -->

                                <td>

                                    <div class="product-name">

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $product["name"]
                                            );
                                            ?>
                                        </strong>

                                        <small>
                                            <?php
                                            echo htmlspecialchars(
                                                $product["description"]
                                            );
                                            ?>
                                        </small>

                                    </div>

                                </td>


                                <!-- CATEGORY -->

                                <td>

                                    <span class="category">

                                        <?php
                                        echo htmlspecialchars(
                                            $product["category"]
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- PRICE -->

                                <td class="price">

                                    ₱<?php
                                    echo number_format(
                                        (float)$product["price"],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- STOCK -->

                                <td>

                                    <div
                                        class="stock <?php
                                            echo $stock_class;
                                        ?>"
                                    >

                                        <?php
                                        echo $stock;
                                        ?>

                                        <span class="stock-note">
                                            <?php
                                            echo $stock_note;
                                            ?>
                                        </span>

                                    </div>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if (
                                        in_array(
                                            strtolower(trim((string)$product["status"])),
                                            ["active", "available"],
                                            true
                                        )
                                    ): ?>

                                        <span class="status active">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span class="status inactive">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <div class="actions">

                                        <a
                                            href="edit-product.php?id=<?php
                                                echo (int)$product["id"];
                                            ?>"
                                            class="action edit"
                                        >
                                            Edit
                                        </a>


                                        <a
                                            href="delete-product.php?id=<?php
                                                echo (int)$product["id"];
                                            ?>"
                                            class="action delete"
                                            onclick="return confirm('Are you sure you want to delete this product?');"
                                        >
                                            Delete
                                        </a>

                                    </div>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="empty"
                            >

                                <div class="empty-icon">
                                    ☕
                                </div>

                                <strong>
                                    No products found
                                </strong>

                                <p>
                                    Try changing your search or category filter.
                                </p>

                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

</body>
</html>