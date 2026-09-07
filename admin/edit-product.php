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

$admin_name = $_SESSION["admin_name"] ?? "Administrator";


// ==========================================
// CHECK PRODUCT ID
// ==========================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: products.php");
    exit();

}

$product_id = (int) $_GET["id"];


// ==========================================
// GET PRODUCT
// ==========================================

$stmt = $conn->prepare(
    "SELECT *
     FROM products
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param(
    "i",
    $product_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: products.php");
    exit();

}


$product = $result->fetch_assoc();

$stmt->close();


// ==========================================
// VARIABLES
// ==========================================

$error = "";


// ==========================================
// UPDATE PRODUCT
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status_input = strtolower(trim((string)($_POST["status"] ?? "available")));
    $status = in_array($status_input, ["active", "available"], true)
        ? "available"
        : (in_array($status_input, ["inactive", "unavailable"], true) ? "unavailable" : "");

    // ======================================
    // VALIDATION
    // ======================================

    if ($name === "" || $price === "" || $stock === "" || $category === "") {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($price) || (float) $price < 0) {
        $error = "Please enter a valid price.";
    } elseif (!filter_var($stock, FILTER_VALIDATE_INT) && $stock !== "0") {
        $error = "Please enter a valid stock quantity.";
    } elseif ((int) $stock < 0) {
        $error = "Stock cannot be negative.";
    } elseif ($status === "") {
        $error = "Invalid product status.";
    } else {

        $image = $product["image"] ?? "";

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
                $error = "There was a problem uploading the new image.";
            } else {

                $file = $_FILES["image"];
                $extension = strtolower(
                    pathinfo($file["name"], PATHINFO_EXTENSION)
                );

                $allowed_extensions = ["jpg", "jpeg", "png", "webp"];

                if (!in_array($extension, $allowed_extensions, true)) {
                    $error = "Only JPG, JPEG, PNG, and WEBP images are allowed.";
                } elseif ($file["size"] > 5 * 1024 * 1024) {
                    $error = "The product image must be 5MB or smaller.";
                } else {

                    $upload_dir = "../image/";

                    if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                        $error = "The image upload folder could not be created.";
                    } else {

                        $new_file_name =
                            "product_" .
                            bin2hex(random_bytes(8)) .
                            "." .
                            $extension;

                        $upload_path = $upload_dir . $new_file_name;

                        if (move_uploaded_file($file["tmp_name"], $upload_path)) {

                            $image = "image/" . $new_file_name;

                            $old_image = str_replace("\\", "/", $product["image"] ?? "");
                            $old_image_name = basename($old_image);

                            if (
                                $old_image_name !== "" &&
                                $old_image_name !== $new_file_name &&
                                is_file("../image/" . $old_image_name)
                            ) {
                                @unlink("../image/" . $old_image_name);
                            }

                        } else {
                            $error = "The new product image could not be saved.";
                        }
                    }
                }
            }
        }

        if ($error === "") {

            $update = $conn->prepare(
                "UPDATE products
                 SET
                    name = ?,
                    description = ?,
                    price = ?,
                    stock = ?,
                    category = ?,
                    image = ?,
                    status = ?
                 WHERE id = ?"
            );

            if (!$update) {
                $error = "Failed to prepare product update: " . $conn->error;
            } else {

                $price_value = (float) $price;
                $stock_value = (int) $stock;

                $update->bind_param(
                    "ssdisssi",
                    $name,
                    $description,
                    $price_value,
                    $stock_value,
                    $category,
                    $image,
                    $status,
                    $product_id
                );

                if ($update->execute()) {
                    $update->close();

                    header("Location: products.php?success=updated");
                    exit();
                }

                $error = "Failed to update product: " . $update->error;
                $update->close();
            }
        }
    }

    $product["name"] = $name;
    $product["description"] = $description;
    $product["price"] = $price;
    $product["stock"] = $stock;
    $product["category"] = $category;
    $product["status"] = $status;
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

    <title>Edit Product | Cafelia Admin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

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
            --shadow: 0 18px 45px rgba(49, 30, 20, .08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: "DM Sans", Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 80% 10%, rgba(216,163,109,.13), transparent 25%),
                linear-gradient(135deg, #f8f3eb, #eee2d4);
        }

        /* =====================================================
           SIDEBAR — SAME CURRENT CAFELIA ADMIN DESIGN
        ===================================================== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 255px;
            height: 100vh;
            padding: 30px 18px;
            display: flex;
            flex-direction: column;
            background: linear-gradient(160deg, var(--espresso), #3b2418);
            color: white;
            box-shadow: 8px 0 35px rgba(36,21,15,.10);
            z-index: 10;
        }

        .sidebar-brand {
            padding: 10px 14px 30px;
        }

        .sidebar-brand h1 {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 31px;
            font-weight: 600;
            letter-spacing: .05em;
            color: #fff;
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
            border: 1px solid rgba(255,255,255,.10);
            border-radius: 16px;
            background: rgba(255,255,255,.06);
            backdrop-filter: blur(12px);
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--gold), var(--caramel));
            color: white;
            font-weight: 800;
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
            background: rgba(255,255,255,.07);
            transform: translateX(2px);
        }

        .sidebar-nav a.active {
            color: white;
            background: linear-gradient(135deg, rgba(185,130,82,.45), rgba(90,56,39,.65));
            box-shadow: inset 0 1px 0 rgba(255,255,255,.10);
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
            border-top: 1px solid rgba(255,255,255,.08);
            margin-top: 12px;
            padding-top: 18px !important;
        }

        /* =====================================================
           MAIN / HEADER — SAME ALIGNMENT AS CURRENT DASHBOARD
        ===================================================== */
        .main {
            margin-left: 255px;
            min-height: 100vh;
            padding: 38px 42px 55px;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            margin-bottom: 30px;
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
            font-family: "Playfair Display", Georgia, serif;
            color: var(--espresso);
            font-size: 36px;
            font-weight: 600;
            line-height: 1.1;
        }

        .topbar p {
            margin-top: 7px;
            color: var(--muted);
            font-size: 13px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border: 1px solid rgba(80,50,34,.13);
            border-radius: 12px;
            background: rgba(255,255,255,.72);
            color: var(--coffee);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            transition: .2s ease;
        }

        .back-link:hover {
            background: white;
            border-color: rgba(185,130,82,.38);
            transform: translateY(-1px);
        }

        /* =====================================================
           EDIT PRODUCT CARD
        ===================================================== */
        .edit-card {
            width: 100%;
            max-width: 1120px;
            margin: 0 auto;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: rgba(255,253,249,.96);
            box-shadow: var(--shadow);
        }

        .card-heading {
            padding: 28px 32px 24px;
            border-bottom: 1px solid var(--border);
            background: linear-gradient(135deg, rgba(248,242,233,.92), rgba(255,253,249,.98));
        }

        .card-heading span {
            display: block;
            margin-bottom: 6px;
            color: var(--caramel);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .card-heading h3 {
            font-family: "Playfair Display", Georgia, serif;
            color: var(--espresso);
            font-size: 26px;
            font-weight: 600;
        }

        .error-message {
            margin: 22px 32px 0;
            padding: 13px 16px;
            border: 1px solid rgba(169,71,55,.15);
            border-radius: 12px;
            background: #fbe8e5;
            color: #a94737;
            font-size: 13px;
            font-weight: 600;
        }

        .admin-form {
            width: 100%;
            max-width: 920px;
            padding: 30px 32px 34px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 21px;
        }

        .form-group label,
        .current-image label {
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
            padding: 12px 14px;
            border: 1px solid rgba(80,50,34,.14);
            border-radius: 11px;
            background: #fff;
            color: var(--text);
            font-family: "DM Sans", Arial, sans-serif;
            font-size: 14px;
            outline: none;
            transition: .2s ease;
        }

        .form-group textarea {
            min-height: 125px;
            resize: vertical;
            line-height: 1.55;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--caramel);
            background: #fffdf9;
            box-shadow: 0 0 0 4px rgba(185,130,82,.10);
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group small {
            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        /* Current image is deliberately separated from file upload */
        .current-image {
            width: fit-content;
            max-width: 100%;
            margin: 4px 0 24px;
            padding: 17px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(248,242,233,.72), rgba(255,253,249,.95));
        }

        .current-image label {
            display: block;
            margin-bottom: 12px;
        }

        .current-image img {
            display: block;
            width: 150px !important;
            height: 150px !important;
            object-fit: cover;
            border-radius: 14px !important;
            border: 1px solid rgba(36,21,15,.12) !important;
            box-shadow: 0 12px 25px rgba(36,21,15,.12) !important;
        }

        /* File upload stays BELOW the current image */
        .file-upload {
            margin-top: 0;
        }

        .file-upload input[type="file"] {
            width: 100%;
            min-height: 52px;
            padding: 8px;
            border: 1px dashed rgba(90,56,39,.24);
            border-radius: 11px;
            background: #faf6f0;
            color: var(--muted);
            font-family: inherit;
            font-size: 12px;
            cursor: pointer;
        }

        .file-upload input[type="file"]::file-selector-button {
            margin-right: 10px;
            padding: 10px 15px;
            border: 0;
            border-radius: 8px;
            background: var(--coffee);
            color: white;
            font-family: inherit;
            font-size: 11px;
            font-weight: 800;
            cursor: pointer;
        }

        #imagePreview {
            display: none;
            margin-top: 14px !important;
            padding: 0;
        }

        #previewImage {
            display: block;
            width: 150px !important;
            height: 150px !important;
            object-fit: cover;
            border-radius: 14px !important;
            border: 1px solid rgba(36,21,15,.12) !important;
            box-shadow: 0 12px 25px rgba(36,21,15,.10);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }

        .btn-secondary,
        .btn-primary {
            min-height: 45px;
            padding: 0 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: .2s ease;
        }

        .form-actions .btn-secondary {
            border: 1px solid rgba(80,50,34,.13);
            background: white;
            color: var(--coffee);
        }

        .form-actions .btn-primary {
            border: 1px solid var(--caramel);
            background: linear-gradient(135deg, var(--coffee), var(--caramel));
            color: white;
            box-shadow: 0 9px 22px rgba(90,56,39,.16);
        }

        .form-actions .btn-secondary:hover,
        .form-actions .btn-primary:hover {
            transform: translateY(-2px);
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */
        @media (max-width: 900px) {
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
                grid-template-columns: repeat(2, 1fr);
            }

            .sidebar-bottom {
                margin-top: 12px;
            }

            .main {
                margin-left: 0;
                padding: 28px 20px 45px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 560px) {
            .main {
                padding: 22px 14px 40px;
            }

            .sidebar-nav {
                grid-template-columns: 1fr;
            }

            .topbar h2 {
                font-size: 31px;
            }

            .edit-card {
                border-radius: 16px;
            }

            .card-heading {
                padding: 24px 20px 21px;
            }

            .admin-form {
                padding: 24px 20px 28px;
            }

            .error-message {
                margin-left: 20px;
                margin-right: 20px;
            }

            .form-actions {
                flex-direction: column-reverse;
                align-items: stretch;
            }

            .form-actions .btn-secondary,
            .form-actions .btn-primary,
            .back-link {
                width: 100%;
            }
        }
    </style>

</head>

<body>


<!-- ==========================================
     SIDEBAR
========================================== -->

<aside class="sidebar">

    <div class="sidebar-brand">
        <h1>Cafelia</h1>
        <p>Admin Management</p>
    </div>

    <div class="admin-profile">
        <div class="admin-avatar">
            <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
        </div>

        <div class="admin-info">
            <strong><?php echo htmlspecialchars($admin_name); ?></strong>
            <span>Administrator</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php">
            <span class="nav-icon">⌂</span>
            Dashboard
        </a>

        <a href="products.php" class="active">
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
            <a href="../index.php">
                <span class="nav-icon">↗</span>
                View Website
            </a>

            <a href="../logout.php" class="logout-link">
                <span class="nav-icon">⇥</span>
                Logout
            </a>
        </nav>
    </div>

</aside>


<!-- ==========================================
     MAIN CONTENT
========================================== -->

<main class="main">


    <header class="topbar">

        <div>

            <div class="topbar-kicker">
                Cafelia Management
            </div>

            <h2>Edit Product</h2>

            <p>
                Update the details, stock, and image for this menu item.
            </p>

        </div>


        <a
            href="products.php"
            class="back-link"
        >
            ← Back to Products
        </a>

    </header>


    <!-- ======================================
         FORM
    ======================================= -->

    <section class="edit-card">


        <div class="card-heading">

            <div>

                <span>
                    UPDATE MENU ITEM
                </span>

                <h3>
                    Product Information
                </h3>

            </div>

        </div>


        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>


        <form
            action="edit-product.php?id=<?php
                echo $product_id;
            ?>"
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
                            $product["name"]
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
                        $product["description"] ?? ""
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
                            $product["price"]
                        );
                    ?>"
                    required
                >

            </div>


            <!-- STOCK -->

            <div class="form-group">

                <label for="stock">
                    Stock *
                </label>

                <input
                    type="number"
                    id="stock"
                    name="stock"
                    min="0"
                    step="1"
                    value="<?php
                        echo htmlspecialchars(
                            $product["stock"] ?? 0
                        );
                    ?>"
                    required
                >

                <small>
                    Set the quantity currently available for this product.
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

                    <option
                        value="Coffee"
                        <?php
                        echo $product["category"] === "Coffee"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Coffee
                    </option>

                    <option
                        value="Beverages"
                        <?php
                        echo $product["category"] === "Beverages"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Beverages
                    </option>

                    <option
                        value="Treats"
                        <?php
                        echo $product["category"] === "Treats"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Treats
                    </option>

                    <option
                        value="Food"
                        <?php
                        echo $product["category"] === "Food"
                            ? "selected"
                            : "";
                        ?>
                    >
                        Food
                    </option>

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
                        echo in_array(strtolower(trim((string)$product["status"])), ["active", "available"], true)
                            ? "selected"
                            : "";
                        ?>
                    >
                        Available
                    </option>

                    <option
                        value="unavailable"
                        <?php
                        echo in_array(strtolower(trim((string)$product["status"])), ["inactive", "unavailable"], true)
                            ? "selected"
                            : "";
                        ?>
                    >
                        Unavailable
                    </option>

                </select>

            </div>


            <!-- CURRENT IMAGE -->

            <?php if (!empty($product["image"])): ?>

                <div class="current-image">

                    <label>
                        Current Image
                    </label>

                    <?php
                    $current_image = str_replace(
                        "\\",
                        "/",
                        $product["image"] ?? ""
                    );

                    $current_image = preg_replace(
                        '#^(?:\.\./)?(?:image/|images/)#i',
                        "",
                        $current_image
                    );

                    $current_image = basename($current_image);
                    ?>

                    <img
                        src="../image/<?php
                            echo htmlspecialchars($current_image);
                        ?>"
                        alt="<?php echo htmlspecialchars($product["name"]); ?>"
                    >

                </div>

            <?php endif; ?>


            <!-- NEW IMAGE -->

            <div class="form-group file-upload">

                <label for="image">
                    Change Product Image
                </label>

                <input
                    type="file"
                    id="image"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    Leave empty to keep the current image. Maximum 5MB.
                </small>

                <div
                    id="imagePreview"
                >
                    <img
                        id="previewImage"
                        src=""
                        alt="New product image preview"
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
                    Save Changes
                </button>

            </div>


        </form>


    </section>


</main>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById("image");
    const previewBox = document.getElementById("imagePreview");
    const previewImage = document.getElementById("previewImage");

    if (!imageInput || !previewBox || !previewImage) {
        return;
    }

    imageInput.addEventListener("change", function () {
        const file = this.files && this.files[0];

        if (!file || !file.type.startsWith("image/")) {
            previewBox.style.display = "none";
            previewImage.removeAttribute("src");
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            previewImage.src = event.target.result;
            previewBox.style.display = "block";
        };

        reader.readAsDataURL(file);
    });
});
</script>

<script src="../js/admin.js"></script>

</body>

</html>