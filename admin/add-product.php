<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

$error = "";

$name = "";
$description = "";
$price = "";
$stock = "";
$category = "Coffee";
$status = "available";

$MAX_STOCK = 5;

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $category = trim($_POST["category"] ?? "Coffee");
    $status = $_POST["status"] ?? "available";

    /*
    =========================================================
    BASIC VALIDATION
    =========================================================
    */

    if (
        $name === "" ||
        $price === "" ||
        $stock === "" ||
        $category === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (
        !is_numeric($price) ||
        (float)$price < 0
    ) {

        $error = "Please enter a valid price.";

    } elseif (
        filter_var($stock, FILTER_VALIDATE_INT) === false
    ) {

        $error = "Stock must be a whole number.";

    } elseif (
        (int)$stock < 0 ||
        (int)$stock > $MAX_STOCK
    ) {

        $error = "Stock must be between 0 and 5 only.";

    } elseif (
        !in_array(
            $category,
            ["Coffee", "Beverages", "Treats", "Food"],
            true
        )
    ) {

        $error = "Invalid product category.";

    } elseif (
        !in_array(
            $status,
            ["available", "unavailable"],
            true
        )
    ) {

        $error = "Invalid product status.";

    } elseif (
        !isset($_FILES["image"]) ||
        $_FILES["image"]["error"] === UPLOAD_ERR_NO_FILE
    ) {

        $error = "Please select a product image.";

    } elseif (
        $_FILES["image"]["error"] !== UPLOAD_ERR_OK
    ) {

        $error = "There was a problem uploading the product image.";

    } else {

        $file = $_FILES["image"];

        $extension = strtolower(
            pathinfo(
                $file["name"],
                PATHINFO_EXTENSION
            )
        );

        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];

        /*
        =========================================================
        IMAGE VALIDATION
        =========================================================
        */

        if (
            !in_array(
                $extension,
                $allowed_extensions,
                true
            )
        ) {

            $error =
                "Only JPG, JPEG, PNG, and WEBP images are allowed.";

        } elseif (
            $file["size"] > 5 * 1024 * 1024
        ) {

            $error =
                "The product image must be 5MB or smaller.";

        } else {

            $upload_dir = "../image/";

            if (
                !is_dir($upload_dir) &&
                !mkdir($upload_dir, 0755, true)
            ) {

                $error =
                    "The image upload folder could not be created.";

            } else {

                try {

                    $image_name =
                        "product_" .
                        bin2hex(random_bytes(8)) .
                        "." .
                        $extension;

                } catch (Throwable $e) {

                    $image_name =
                        "product_" .
                        uniqid() .
                        "." .
                        $extension;
                }

                $upload_path =
                    $upload_dir . $image_name;

                if (
                    !move_uploaded_file(
                        $file["tmp_name"],
                        $upload_path
                    )
                ) {

                    $error =
                        "The product image could not be saved.";

                } else {

                    $image = $image_name;

                    $price_value = (float)$price;
                    $stock_value = (int)$stock;

                    /*
                    =================================================
                    STOCK / STATUS CONSISTENCY

                    0 stock  = unavailable
                    1-5 stock = available
                    =================================================
                    */

                    $status_value =
                        $stock_value > 0
                            ? "available"
                            : "unavailable";

                    /*
                    =================================================
                    INSERT PRODUCT
                    =================================================
                    */

                    $stmt = $conn->prepare(
                        "INSERT INTO products
                        (
                            name,
                            description,
                            price,
                            stock,
                            category,
                            image,
                            status
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    if (!$stmt) {

                        @unlink($upload_path);

                        $error =
                            "Failed to prepare product creation: " .
                            $conn->error;

                    } else {

                        $stmt->bind_param(
                            "ssdisss",
                            $name,
                            $description,
                            $price_value,
                            $stock_value,
                            $category,
                            $image,
                            $status_value
                        );

                        if ($stmt->execute()) {

                            $stmt->close();

                            header(
                                "Location: products.php?success=created"
                            );

                            exit();
                        }

                        $error =
                            "Failed to add product: " .
                            $stmt->error;

                        $stmt->close();

                        @unlink($upload_path);
                    }
                }
            }
        }
    }
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

    <title>Add Product | Cafelia Admin</title>

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

html {
    scroll-behavior: smooth;
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
            circle at 82% 8%,
            rgba(216,163,109,.14),
            transparent 27%
        ),
        linear-gradient(
            135deg,
            #f8f3eb 0%,
            #eee2d4 100%
        );
}

/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    inset: 0 auto 0 0;

    width: 255px;

    padding: 30px 18px;

    display: flex;

    flex-direction: column;

    background:
        linear-gradient(
            160deg,
            var(--espresso),
            #3b2418
        );

    color: #fff;

    box-shadow:
        8px 0 35px
        rgba(36,21,15,.10);

    z-index: 20;
}

.sidebar-brand {

    padding:
        10px 14px 30px;
}

.sidebar-brand h1 {

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 31px;

    font-weight: 600;

    letter-spacing: .04em;
}

.sidebar-brand p {

    margin-top: 5px;

    color:
        rgba(255,255,255,.45);

    font-size: 10px;

    font-weight: 800;

    letter-spacing: .16em;

    text-transform: uppercase;
}

.admin-profile {

    margin:
        0 6px 28px;

    padding: 14px;

    display: flex;

    align-items: center;

    gap: 12px;

    border:
        1px solid
        rgba(255,255,255,.10);

    border-radius: 16px;

    background:
        rgba(255,255,255,.06);

    backdrop-filter: blur(12px);
}

.admin-avatar {

    width: 40px;
    height: 40px;

    flex: 0 0 40px;

    display: grid;

    place-items: center;

    border-radius: 12px;

    background:
        linear-gradient(
            135deg,
            var(--gold),
            var(--caramel)
        );

    color: #fff;

    font-weight: 800;
}

.admin-info {
    min-width: 0;
}

.admin-info strong {

    display: block;

    overflow: hidden;

    color: #fff;

    font-size: 13px;

    white-space: nowrap;

    text-overflow: ellipsis;
}

.admin-info span {

    display: block;

    margin-top: 3px;

    color:
        rgba(255,255,255,.42);

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

    color:
        rgba(255,255,255,.62);

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    transition: .2s ease;
}

.sidebar-nav a:hover {

    color: #fff;

    background:
        rgba(255,255,255,.07);

    transform:
        translateX(2px);
}

.sidebar-nav a.active {

    color: #fff;

    background:
        linear-gradient(
            135deg,
            rgba(185,130,82,.45),
            rgba(90,56,39,.65)
        );

    box-shadow:
        inset 0 1px 0
        rgba(255,255,255,.10);
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

    margin-top: 12px;

    padding-top: 18px !important;

    border-top:
        1px solid
        rgba(255,255,255,.08);
}

/* =========================================================
   MAIN
========================================================= */

.main {

    min-height: 100vh;

    margin-left: 255px;

    padding:
        40px 42px 60px;
}

.topbar {

    max-width: 1180px;

    margin:
        0 auto 28px;

    display: flex;

    align-items: flex-end;

    justify-content: space-between;

    gap: 24px;
}

.topbar-kicker {

    margin-bottom: 7px;

    color: var(--caramel);

    font-size: 10px;

    font-weight: 800;

    letter-spacing: .18em;

    text-transform: uppercase;
}

.topbar h2 {

    color: var(--espresso);

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 38px;

    font-weight: 600;

    line-height: 1.08;
}

.topbar p {

    margin-top: 8px;

    color: var(--muted);

    font-size: 13px;
}

.back-link {

    flex-shrink: 0;

    min-height: 44px;

    padding: 0 18px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border:
        1px solid
        rgba(80,50,34,.13);

    border-radius: 12px;

    background:
        rgba(255,255,255,.76);

    color: var(--coffee);

    font-size: 12px;

    font-weight: 800;

    text-decoration: none;

    box-shadow:
        0 8px 20px
        rgba(49,30,20,.04);

    transition: .2s ease;
}

.back-link:hover {

    background: #fff;

    border-color:
        rgba(185,130,82,.38);

    transform:
        translateY(-1px);
}

/* =========================================================
   CARD
========================================================= */

.add-card {

    width: 100%;

    max-width: 1180px;

    margin: 0 auto;

    overflow: hidden;

    border:
        1px solid
        var(--border);

    border-radius: 22px;

    background:
        rgba(255,253,249,.94);

    box-shadow:
        var(--shadow);

    backdrop-filter:
        blur(14px);
}

.card-heading {

    padding:
        27px 32px 24px;

    border-bottom:
        1px solid
        var(--border);

    background:
        linear-gradient(
            135deg,
            rgba(248,242,233,.92),
            rgba(255,253,249,.98)
        );
}

.card-heading span {

    display: block;

    margin-bottom: 7px;

    color: var(--caramel);

    font-size: 10px;

    font-weight: 800;

    letter-spacing: .16em;

    text-transform: uppercase;
}

.card-heading h3 {

    color: var(--espresso);

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 25px;

    font-weight: 600;
}

.error-message {

    margin:
        22px 32px 0;

    padding:
        13px 16px;

    border:
        1px solid
        rgba(169,71,55,.16);

    border-radius: 12px;

    background: #fff0ed;

    color: #a94737;

    font-size: 13px;

    font-weight: 600;
}

/* =========================================================
   FORM
========================================================= */

.admin-form {

    padding:
        30px 32px 34px;

    display: grid;

    grid-template-columns:
        1fr 1fr;

    column-gap: 22px;

    row-gap: 0;
}

.form-group {

    min-width: 0;

    display: flex;

    flex-direction: column;

    gap: 8px;

    margin-bottom: 21px;
}

.form-group label {

    color: var(--text);

    font-size: 12px;

    font-weight: 800;

    letter-spacing: .04em;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {

    width: 100%;

    min-height: 47px;

    padding:
        12px 14px;

    border:
        1px solid
        rgba(80,50,34,.14);

    border-radius: 11px;

    outline: none;

    background: #fff;

    color: var(--text);

    font:
        14px
        "DM Sans",
        Arial,
        sans-serif;

    transition: .2s ease;
}

.form-group textarea {

    min-height: 125px;

    resize: vertical;

    line-height: 1.55;
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {

    border-color:
        rgba(185,130,82,.34);
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color:
        var(--caramel);

    background:
        #fffdf9;

    box-shadow:
        0 0 0 4px
        rgba(185,130,82,.10);
}

.form-group select {
    cursor: pointer;
}

.form-group small {

    color: var(--muted);

    font-size: 11px;

    line-height: 1.5;
}

/* FULL WIDTH */

.form-group:nth-child(1),
.form-group:nth-child(2),
.form-group:nth-child(7) {

    grid-column: 1 / -1;
}

/* =========================================================
   STOCK FIELD
========================================================= */

.stock-field {

    position: relative;
}

.stock-limit-note {

    display: flex;

    align-items: center;

    gap: 6px;

    color:
        var(--caramel) !important;

    font-weight: 700;
}

.stock-limit-note strong {
    color: var(--coffee);
}

/* =========================================================
   IMAGE
========================================================= */

.file-upload {
    margin-top: 2px;
}

.file-upload input[type="file"] {

    width: 100%;

    min-height: 52px;

    padding: 8px;

    border:
        1px dashed
        rgba(90,56,39,.28);

    border-radius: 11px;

    background:
        #faf6f0;

    color: var(--muted);

    font:
        12px
        "DM Sans",
        Arial,
        sans-serif;

    cursor: pointer;

    transition: .2s ease;
}

.file-upload input[type="file"]:hover {

    border-color:
        rgba(185,130,82,.55);

    background:
        #f8f1e8;
}

.file-upload input[type="file"]::file-selector-button {

    margin-right: 10px;

    padding:
        10px 15px;

    border: 0;

    border-radius: 8px;

    background:
        var(--coffee);

    color: #fff;

    font:
        11px
        "DM Sans",
        Arial,
        sans-serif;

    font-weight: 800;

    cursor: pointer;
}

#imagePreview {

    display: none;

    margin-top: 14px;

    padding: 10px;

    width: fit-content;

    border:
        1px solid
        var(--border);

    border-radius: 14px;

    background: #fff;
}

#previewImage {

    display: block;

    width: 150px;

    height: 150px;

    object-fit: cover;

    border-radius: 11px;

    border:
        1px solid
        rgba(36,21,15,.12);

    box-shadow:
        0 10px 22px
        rgba(36,21,15,.10);
}

/* =========================================================
   ACTIONS
========================================================= */

.form-actions {

    grid-column: 1 / -1;

    margin-top: 8px;

    padding-top: 24px;

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 10px;

    border-top:
        1px solid
        var(--border);
}

.btn-secondary,
.btn-primary {

    min-height: 45px;

    padding:
        0 20px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    font:
        12px
        "DM Sans",
        Arial,
        sans-serif;

    font-weight: 800;

    text-decoration: none;

    cursor: pointer;

    transition: .2s ease;
}

.btn-secondary {

    border:
        1px solid
        rgba(80,50,34,.13);

    background: #fff;

    color: var(--coffee);
}

.btn-primary {

    border:
        1px solid
        var(--coffee);

    background:
        linear-gradient(
            135deg,
            var(--coffee),
            var(--caramel)
        );

    color: #fff;

    box-shadow:
        0 9px 22px
        rgba(90,56,39,.16);
}

.btn-secondary:hover,
.btn-primary:hover {

    transform:
        translateY(-2px);
}

.btn-secondary:active,
.btn-primary:active {

    transform:
        translateY(0);
}

/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 950px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        padding: 20px 18px;
    }

    .sidebar-brand {
        padding-bottom: 20px;
    }

    .admin-profile {
        margin-bottom: 18px;
    }

    .sidebar-nav {

        display: grid;

        grid-template-columns:
            repeat(2, 1fr);
    }

    .sidebar-bottom {
        margin-top: 12px;
    }

    .main {

        margin-left: 0;

        padding:
            30px 22px 45px;
    }
}

@media (max-width: 720px) {

    .topbar {

        align-items: flex-start;

        flex-direction: column;
    }

    .topbar h2 {
        font-size: 33px;
    }

    .back-link {
        width: 100%;
    }

    .admin-form {

        grid-template-columns: 1fr;

        padding:
            26px 22px 30px;
    }

    .form-group:nth-child(1),
    .form-group:nth-child(2),
    .form-group:nth-child(7),
    .form-actions {

        grid-column: auto;
    }

    .form-actions {

        flex-direction: column-reverse;

        align-items: stretch;
    }

    .btn-secondary,
    .btn-primary {

        width: 100%;
    }
}

@media (max-width: 520px) {

    .main {

        padding:
            22px 14px 38px;
    }

    .topbar h2 {
        font-size: 30px;
    }

    .card-heading {

        padding:
            23px 20px 20px;
    }

    .admin-form {

        padding:
            23px 20px 26px;
    }

    .error-message {

        margin-left: 20px;

        margin-right: 20px;
    }

    .sidebar-nav {

        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="sidebar">

    <div class="sidebar-brand">

        <h1>Cafelia</h1>

        <p>
            Admin Management
        </p>

    </div>

    <div class="admin-profile">

        <div class="admin-avatar">

            <?php
            echo strtoupper(
                substr(
                    $admin_name,
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
                    $admin_name,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>
            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>

    <nav class="sidebar-nav">

        <a href="dashboard.php">

            <span class="nav-icon">
                ⌂
            </span>

            Dashboard

        </a>

        <a
            href="products.php"
            class="active"
        >

            <span class="nav-icon">
                ☕
            </span>

            Products

        </a>

        <a href="orders.php">

            <span class="nav-icon">
                ▣
            </span>

            Orders

        </a>

        <a href="customers.php">

            <span class="nav-icon">
                ♙
            </span>

            Customers

        </a>

        <a href="reports.php">

            <span class="nav-icon">
                ▤
            </span>

            Reports

        </a>

    </nav>

    <div class="sidebar-bottom">

        <nav class="sidebar-nav">

            <a href="../index.php">

                <span class="nav-icon">
                    ↗
                </span>

                View Website

            </a>

            <a
                href="../logout.php"
                class="logout-link"
            >

                <span class="nav-icon">
                    ⇥
                </span>

                Logout

            </a>

        </nav>

    </div>

</aside>

<!-- =========================================================
     MAIN
========================================================= -->

<main class="main">

    <header class="topbar">

        <div>

            <div class="topbar-kicker">
                Cafelia Management
            </div>

            <h2>
                Add Product
            </h2>

            <p>
                Create a new menu item and save it directly to the products database.
            </p>

        </div>

        <a
            href="products.php"
            class="back-link"
        >
            ← Back to Products
        </a>

    </header>

    <section class="add-card">

        <div class="card-heading">

            <span>
                NEW MENU ITEM
            </span>

            <h3>
                Product Information
            </h3>

        </div>

        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    "UTF-8"
                );
                ?>

            </div>

        <?php endif; ?>

        <form
            method="POST"
            enctype="multipart/form-data"
            class="admin-form"
        >

            <!-- PRODUCT NAME -->

            <div class="form-group">

                <label for="name">
                    Product Name *
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php
                        echo htmlspecialchars(
                            $name,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    required
                >

            </div>

            <!-- DESCRIPTION -->

            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                ><?php
                    echo htmlspecialchars(
                        $description,
                        ENT_QUOTES,
                        "UTF-8"
                    );
                ?></textarea>

            </div>

            <!-- PRICE -->

            <div class="form-group">

                <label for="price">
                    Price *
                </label>

                <input
                    type="number"
                    id="price"
                    name="price"
                    step="0.01"
                    min="0"
                    value="<?php
                        echo htmlspecialchars(
                            $price,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    required
                >

            </div>

            <!-- STOCK -->

            <div class="form-group stock-field">

                <label for="stock">
                    Stock *
                </label>

                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    max="5"
                    step="1"
                    value="<?php
                        echo htmlspecialchars(
                            $stock,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                    required
                >

                <small class="stock-limit-note">

                    Maximum stock:
                    <strong>5 units</strong>

                </small>

            </div>

            <!-- CATEGORY -->

            <div class="form-group">

                <label for="category">
                    Category *
                </label>

                <select
                    id="category"
                    name="category"
                    required
                >

                    <?php
                    $categories = [
                        "Coffee",
                        "Beverages",
                        "Treats",
                        "Food"
                    ];
                    ?>

                    <?php foreach ($categories as $option): ?>

                        <option
                            value="<?php
                                echo htmlspecialchars(
                                    $option,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                            <?php
                            echo $category === $option
                                ? "selected"
                                : "";
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $option,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <!-- STATUS -->

            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option
                        value="available"
                        <?php
                        echo $status === "available"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Available
                    </option>

                    <option
                        value="unavailable"
                        <?php
                        echo $status === "unavailable"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Unavailable
                    </option>

                </select>

            </div>

            <!-- IMAGE -->

            <div class="form-group file-upload">

                <label for="image">
                    Product Image *
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <small>
                    JPG, JPEG, PNG, or WEBP.
                    Maximum 5MB.
                </small>

                <div id="imagePreview">

                    <img
                        id="previewImage"
                        src=""
                        alt="Product image preview"
                    >

                </div>

            </div>

            <!-- BUTTONS -->

            <div class="form-actions">

                <a
                    href="products.php"
                    class="btn-secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn-primary"
                >
                    Add Product
                </button>

            </div>

        </form>

    </section>

</main>

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const imageInput =
            document.getElementById("image");

        const previewBox =
            document.getElementById("imagePreview");

        const previewImage =
            document.getElementById("previewImage");

        const stockInput =
            document.getElementById("stock");


        /*
        =====================================================
        STOCK FRONT-END LIMIT
        =====================================================
        */

        stockInput.addEventListener(
            "input",
            function () {

                let value =
                    parseInt(this.value, 10);

                if (isNaN(value)) {
                    return;
                }

                if (value < 0) {
                    this.value = 0;
                }

                if (value > 5) {
                    this.value = 5;
                }
            }
        );


        /*
        =====================================================
        IMAGE PREVIEW
        =====================================================
        */

        imageInput.addEventListener(
            "change",
            function () {

                const file =
                    this.files &&
                    this.files[0];

                if (
                    !file ||
                    !file.type.startsWith("image/")
                ) {

                    previewBox.style.display =
                        "none";

                    previewImage.removeAttribute(
                        "src"
                    );

                    return;
                }

                const reader =
                    new FileReader();

                reader.onload =
                    function (event) {

                        previewImage.src =
                            event.target.result;

                        previewBox.style.display =
                            "block";
                    };

                reader.readAsDataURL(file);
            }
        );

    }
);

</script>

<script src="../js/admin.js"></script>

</body>
</html>