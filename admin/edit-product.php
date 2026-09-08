<?php

session_start();

require_once "../config/database.php";

/* =========================================================
   ADMIN ACCESS
========================================================= */

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

/* =========================================================
   HELPER
========================================================= */

function e($value): string
{
    return htmlspecialchars(
        (string)($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}

/* =========================================================
   CHECK PRODUCT ID
========================================================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET["id"];

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

/* =========================================================
   GET PRODUCT
========================================================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM products
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

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

/* =========================================================
   VARIABLES
========================================================= */

$error = "";

/*
 * If stock is exactly 0, this page becomes a RESTOCK page.
 *
 * If stock is greater than 0, it remains a normal
 * Edit Product page.
 */

$restock_mode = ((int)$product["stock"] === 0);

/* =========================================================
   POST ACTION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "update";


    /* =====================================================
       RESTOCK
    ====================================================== */

    if ($action === "restock") {

        /*
         * IMPORTANT:
         *
         * We do NOT accept stock from the browser.
         *
         * The server adds exactly 5 units.
         *
         * The WHERE stock = 0 protects against accidentally
         * restocking a product that was already changed.
         */

        $restock = $conn->prepare(
            "UPDATE products
             SET
                stock = stock + 5,
                status = 'available'
             WHERE id = ?
             AND stock = 0"
        );

        if (!$restock) {

            $error =
                "Failed to prepare restock: "
                . $conn->error;

        } else {

            $restock->bind_param(
                "i",
                $product_id
            );

            if ($restock->execute()) {

                if ($restock->affected_rows === 1) {

                    $restock->close();

                    header(
                        "Location: products.php?restocked=1"
                    );

                    exit();

                } else {

                    /*
                     * Someone may have already restocked
                     * this product.
                     */

                    $error =
                        "This product is no longer out of stock.";

                }

            } else {

                $error =
                    "Failed to restock product: "
                    . $restock->error;

            }

            $restock->close();

        }


        /*
         * Refresh product information after an error.
         */

        $refresh = $conn->prepare(
            "SELECT *
             FROM products
             WHERE id = ?
             LIMIT 1"
        );

        if ($refresh) {

            $refresh->bind_param(
                "i",
                $product_id
            );

            $refresh->execute();

            $refresh_result = $refresh->get_result();

            if ($refresh_result->num_rows === 1) {

                $product = $refresh_result->fetch_assoc();

            }

            $refresh->close();

        }

        $restock_mode =
            ((int)$product["stock"] === 0);

    }


    /* =====================================================
       NORMAL EDIT
    ====================================================== */

    elseif ($action === "update") {

        /*
         * NOTICE:
         *
         * There is NO $_POST["stock"] here.
         *
         * Stock cannot be changed through normal editing.
         */

        $name = trim(
            $_POST["name"] ?? ""
        );

        $description = trim(
            $_POST["description"] ?? ""
        );

        $price = trim(
            $_POST["price"] ?? ""
        );

        $category = trim(
            $_POST["category"] ?? ""
        );

        $status_input = strtolower(
            trim(
                (string)(
                    $_POST["status"] ?? "available"
                )
            )
        );


        /*
         * Normalize status.
         */

        if (
            in_array(
                $status_input,
                ["active", "available"],
                true
            )
        ) {

            $status = "available";

        } elseif (
            in_array(
                $status_input,
                ["inactive", "unavailable"],
                true
            )
        ) {

            $status = "unavailable";

        } else {

            $status = "";

        }


        /* =================================================
           VALIDATION
        ================================================== */

        if (
            $name === "" ||
            $price === "" ||
            $category === ""
        ) {

            $error =
                "Please fill in all required fields.";

        } elseif (
            !is_numeric($price) ||
            (float)$price < 0
        ) {

            $error =
                "Please enter a valid price.";

        } elseif ($status === "") {

            $error =
                "Invalid product status.";

        } else {


            /* =============================================
               KEEP CURRENT IMAGE
            ============================================== */

            $image =
                $product["image"] ?? "";


            /* =============================================
               NEW IMAGE UPLOAD
            ============================================== */

            if (
                isset($_FILES["image"]) &&
                $_FILES["image"]["error"]
                    !== UPLOAD_ERR_NO_FILE
            ) {

                if (
                    $_FILES["image"]["error"]
                    !== UPLOAD_ERR_OK
                ) {

                    $error =
                        "There was a problem uploading the new image.";

                } else {

                    $file =
                        $_FILES["image"];

                    $extension =
                        strtolower(
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
                        $file["size"] >
                        5 * 1024 * 1024
                    ) {

                        $error =
                            "The product image must be 5MB or smaller.";

                    } else {

                        $upload_dir =
                            "../image/";


                        if (
                            !is_dir($upload_dir) &&
                            !mkdir(
                                $upload_dir,
                                0755,
                                true
                            )
                        ) {

                            $error =
                                "The image upload folder could not be created.";

                        } else {

                            try {

                                $new_file_name =
                                    "product_" .
                                    bin2hex(
                                        random_bytes(8)
                                    ) .
                                    "." .
                                    $extension;

                            } catch (Throwable $e) {

                                $new_file_name =
                                    "product_" .
                                    uniqid() .
                                    "." .
                                    $extension;

                            }


                            $upload_path =
                                $upload_dir .
                                $new_file_name;


                            if (
                                move_uploaded_file(
                                    $file["tmp_name"],
                                    $upload_path
                                )
                            ) {

                                /*
                                 * Store only the filename.
                                 *
                                 * Products page adds ../image/
                                 * when displaying it.
                                 */

                                $image =
                                    $new_file_name;


                                /*
                                 * Delete old image if it exists.
                                 */

                                $old_image =
                                    str_replace(
                                        "\\",
                                        "/",
                                        (string)(
                                            $product["image"] ?? ""
                                        )
                                    );

                                $old_image_name =
                                    basename(
                                        $old_image
                                    );


                                if (
                                    $old_image_name !== "" &&
                                    $old_image_name !==
                                        $new_file_name &&
                                    is_file(
                                        "../image/" .
                                        $old_image_name
                                    )
                                ) {

                                    @unlink(
                                        "../image/" .
                                        $old_image_name
                                    );

                                }

                            } else {

                                $error =
                                    "The new product image could not be saved.";

                            }

                        }

                    }

                }

            }


            /* =============================================
               UPDATE PRODUCT
               IMPORTANT:
               STOCK IS NOT INCLUDED.
            ============================================== */

            if ($error === "") {

                $update = $conn->prepare(
                    "UPDATE products
                     SET
                        name = ?,
                        description = ?,
                        price = ?,
                        category = ?,
                        image = ?,
                        status = ?
                     WHERE id = ?"
                );


                if (!$update) {

                    $error =
                        "Failed to prepare product update: "
                        . $conn->error;

                } else {

                    $price_value =
                        (float)$price;


                    $update->bind_param(
                        "sdssssi",
                        $name,
                        $description,
                        $price_value,
                        $category,
                        $image,
                        $status,
                        $product_id
                    );


                    if ($update->execute()) {

                        $update->close();

                        header(
                            "Location: products.php?updated=1"
                        );

                        exit();

                    } else {

                        $error =
                            "Failed to update product: "
                            . $update->error;

                    }


                    $update->close();

                }

            }

        }


        /*
         * Keep form values if validation fails.
         */

        $product["name"] =
            $name;

        $product["description"] =
            $description;

        $product["price"] =
            $price;

        $product["category"] =
            $category;

        $product["status"] =
            $status;

        /*
         * Stock remains whatever is actually in the database.
         */

    }

}


/* =========================================================
   CURRENT STOCK
========================================================= */

$current_stock =
    (int)($product["stock"] ?? 0);

$restock_mode =
    ($current_stock === 0);


/* =========================================================
   IMAGE PATH
========================================================= */

$current_image = str_replace(
    "\\",
    "/",
    (string)($product["image"] ?? "")
);

$current_image = preg_replace(
    '#^(?:\.\./)?(?:image/|images/)#i',
    "",
    $current_image
);

$current_image =
    basename($current_image);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php
        echo $restock_mode
            ? "Restock Product"
            : "Edit Product";
        ?>
        | Cafelia Admin
    </title>

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

            --border:
                rgba(80, 50, 34, .10);

            --green: #397149;
            --green-bg: #e9f5ec;

            --red: #9b4b40;
            --red-bg: #fae9e6;

            --shadow:
                0 18px 45px
                rgba(49, 30, 20, .08);
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

            color:
                var(--text);

            background:

                radial-gradient(
                    circle at 80% 10%,
                    rgba(216,163,109,.13),
                    transparent 25%
                ),

                linear-gradient(
                    135deg,
                    #f8f3eb,
                    #eee2d4
                );

        }

        /* =====================================================
           SIDEBAR
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

            background:
                linear-gradient(
                    160deg,
                    var(--espresso),
                    #3b2418
                );

            color: white;

            box-shadow:
                8px 0 35px
                rgba(36,21,15,.10);

            z-index: 10;

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

            letter-spacing: .05em;

            color: #fff;

        }

        .sidebar-brand p {

            margin-top: 6px;

            color:
                rgba(255,255,255,.45);

            font-size: 10px;

            font-weight: 700;

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

            backdrop-filter:
                blur(12px);

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

            color: white;

            background:
                rgba(255,255,255,.07);

            transform:
                translateX(2px);

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
                inset
                0 1px 0
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

            border-top:
                1px solid
                rgba(255,255,255,.08);

            margin-top: 12px;

            padding-top: 18px !important;

        }

        /* =====================================================
           MAIN
        ===================================================== */

        .main {

            margin-left: 255px;

            min-height: 100vh;

            padding:
                38px 42px 55px;

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

            color:
                var(--caramel);

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

            color:
                var(--espresso);

            font-size: 36px;

            font-weight: 600;

            line-height: 1.1;

        }

        .topbar p {

            margin-top: 7px;

            color:
                var(--muted);

            font-size: 13px;

        }

        .back-link {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 44px;

            padding: 0 18px;

            border:
                1px solid
                rgba(80,50,34,.13);

            border-radius: 12px;

            background:
                rgba(255,255,255,.72);

            color:
                var(--coffee);

            font-size: 12px;

            font-weight: 800;

            text-decoration: none;

            transition: .2s ease;

        }

        .back-link:hover {

            background: white;

            border-color:
                rgba(185,130,82,.38);

            transform:
                translateY(-1px);

        }

        /* =====================================================
           CARD
        ===================================================== */

        .edit-card {

            width: 100%;

            max-width: 1120px;

            margin: 0 auto;

            overflow: hidden;

            border:
                1px solid var(--border);

            border-radius: 20px;

            background:
                rgba(255,253,249,.96);

            box-shadow:
                var(--shadow);

        }

        .card-heading {

            padding:
                28px 32px 24px;

            border-bottom:
                1px solid var(--border);

            background:
                linear-gradient(
                    135deg,
                    rgba(248,242,233,.92),
                    rgba(255,253,249,.98)
                );

        }

        .card-heading span {

            display: block;

            margin-bottom: 6px;

            color:
                var(--caramel);

            font-size: 10px;

            font-weight: 800;

            letter-spacing: .17em;

            text-transform: uppercase;

        }

        .card-heading h3 {

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            color:
                var(--espresso);

            font-size: 26px;

            font-weight: 600;

        }

        .error-message {

            margin:
                22px 32px 0;

            padding:
                13px 16px;

            border:
                1px solid
                rgba(169,71,55,.15);

            border-radius: 12px;

            background:
                #fbe8e5;

            color:
                #a94737;

            font-size: 13px;

            font-weight: 600;

        }

        .success-message {

            margin:
                22px 32px 0;

            padding:
                15px 17px;

            border:
                1px solid
                rgba(57,113,73,.14);

            border-radius: 12px;

            background:
                var(--green-bg);

            color:
                var(--green);

            font-size: 13px;

            font-weight: 700;

        }

        /* =====================================================
           RESTOCK BOX
        ===================================================== */

        .restock-box {

            margin:
                30px 32px;

            padding:
                28px;

            border:
                1px solid
                rgba(57,113,73,.15);

            border-radius: 17px;

            background:
                linear-gradient(
                    135deg,
                    #f1f8f2,
                    #fbfdfb
                );

        }

        .restock-icon {

            width: 54px;

            height: 54px;

            display: grid;

            place-items: center;

            margin-bottom: 16px;

            border-radius: 15px;

            background:
                var(--green-bg);

            color:
                var(--green);

            font-size: 24px;

            font-weight: 800;

        }

        .restock-box h4 {

            color:
                var(--espresso);

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 24px;

            font-weight: 600;

        }

        .restock-box p {

            max-width: 620px;

            margin-top: 8px;

            color:
                var(--muted);

            font-size: 13px;

            line-height: 1.6;

        }

        .restock-current {

            margin-top: 20px;

            display: flex;

            gap: 12px;

            flex-wrap: wrap;

        }

        .stock-box {

            min-width: 140px;

            padding:
                13px 16px;

            border:
                1px solid
                rgba(57,113,73,.12);

            border-radius: 11px;

            background:
                white;

        }

        .stock-box span {

            display: block;

            color:
                var(--muted);

            font-size: 9px;

            font-weight: 800;

            letter-spacing: .08em;

            text-transform: uppercase;

        }

        .stock-box strong {

            display: block;

            margin-top: 4px;

            color:
                var(--green);

            font-size: 20px;

        }

        /* =====================================================
           FORM
        ===================================================== */

        .admin-form {

            width: 100%;

            max-width: 920px;

            padding:
                30px 32px 34px;

        }

        .form-group {

            display: flex;

            flex-direction: column;

            gap: 8px;

            margin-bottom: 21px;

        }

        .form-group label,
        .current-image label {

            color:
                var(--text);

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

            background: #fff;

            color:
                var(--text);

            font-family:
                "DM Sans",
                Arial,
                sans-serif;

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

            border-color:
                var(--caramel);

            background:
                #fffdf9;

            box-shadow:
                0 0 0 4px
                rgba(185,130,82,.10);

        }

        .form-group select {

            cursor:
                pointer;

        }

        .form-group small {

            color:
                var(--muted);

            font-size: 11px;

            line-height: 1.5;

        }

        /* =====================================================
           IMAGE
        ===================================================== */

        .current-image {

            width:
                fit-content;

            max-width: 100%;

            margin:
                4px 0 24px;

            padding:
                17px;

            border:
                1px solid var(--border);

            border-radius: 14px;

            background:
                linear-gradient(
                    135deg,
                    rgba(248,242,233,.72),
                    rgba(255,253,249,.95)
                );

        }

        .current-image label {

            display:
                block;

            margin-bottom:
                12px;

        }

        .current-image img {

            display:
                block;

            width:
                150px;

            height:
                150px;

            object-fit:
                cover;

            border-radius:
                14px;

            border:
                1px solid
                rgba(36,21,15,.12);

            box-shadow:
                0 12px 25px
                rgba(36,21,15,.12);

        }

        .file-upload {

            margin-top:
                0;

        }

        .file-upload input[type="file"] {

            width:
                100%;

            min-height:
                52px;

            padding:
                8px;

            border:
                1px dashed
                rgba(90,56,39,.24);

            border-radius:
                11px;

            background:
                #faf6f0;

            color:
                var(--muted);

            font-family:
                inherit;

            font-size:
                12px;

            cursor:
                pointer;

        }

        .file-upload input[type="file"]::file-selector-button {

            margin-right:
                10px;

            padding:
                10px 15px;

            border:
                0;

            border-radius:
                8px;

            background:
                var(--coffee);

            color:
                white;

            font-family:
                inherit;

            font-size:
                11px;

            font-weight:
                800;

            cursor:
                pointer;

        }

        #imagePreview {

            display:
                none;

            margin-top:
                14px;

        }

        #previewImage {

            display:
                block;

            width:
                150px;

            height:
                150px;

            object-fit:
                cover;

            border-radius:
                14px;

            border:
                1px solid
                rgba(36,21,15,.12);

            box-shadow:
                0 12px 25px
                rgba(36,21,15,.10);

        }

        /* =====================================================
           FORM ACTIONS
        ===================================================== */

        .form-actions {

            display:
                flex;

            justify-content:
                flex-end;

            align-items:
                center;

            gap:
                10px;

            margin-top:
                30px;

            padding-top:
                25px;

            border-top:
                1px solid var(--border);

        }

        .btn-secondary,
        .btn-primary,
        .btn-restock {

            min-height:
                45px;

            padding:
                0 20px;

            display:
                inline-flex;

            align-items:
                center;

            justify-content:
                center;

            border-radius:
                11px;

            font-family:
                inherit;

            font-size:
                12px;

            font-weight:
                800;

            text-decoration:
                none;

            cursor:
                pointer;

            transition:
                .2s ease;

        }

        .btn-secondary {

            border:
                1px solid
                rgba(80,50,34,.13);

            background:
                white;

            color:
                var(--coffee);

        }

        .btn-primary {

            border:
                1px solid
                var(--caramel);

            background:
                linear-gradient(
                    135deg,
                    var(--coffee),
                    var(--caramel)
                );

            color:
                white;

            box-shadow:
                0 9px 22px
                rgba(90,56,39,.16);

        }

        .btn-restock {

            border:
                1px solid
                rgba(57,113,73,.22);

            background:
                linear-gradient(
                    135deg,
                    #397149,
                    #5d946b
                );

            color:
                white;

            box-shadow:
                0 9px 22px
                rgba(57,113,73,.15);

        }

        .btn-secondary:hover,
        .btn-primary:hover,
        .btn-restock:hover {

            transform:
                translateY(-2px);

        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 900px) {

            .sidebar {

                position:
                    relative;

                width:
                    100%;

                height:
                    auto;

                padding:
                    20px 18px;

            }

            .sidebar-brand {

                padding-bottom:
                    20px;

            }

            .admin-profile {

                margin-bottom:
                    18px;

            }

            .sidebar-nav {

                display:
                    grid;

                grid-template-columns:
                    repeat(2, 1fr);

            }

            .sidebar-bottom {

                margin-top:
                    12px;

            }

            .main {

                margin-left:
                    0;

                padding:
                    28px 20px 45px;

            }

            .topbar {

                align-items:
                    flex-start;

                flex-direction:
                    column;

            }

        }

        @media (max-width: 560px) {

            .main {

                padding:
                    22px 14px 40px;

            }

            .sidebar-nav {

                grid-template-columns:
                    1fr;

            }

            .topbar h2 {

                font-size:
                    31px;

            }

            .edit-card {

                border-radius:
                    16px;

            }

            .card-heading {

                padding:
                    24px 20px 21px;

            }

            .admin-form {

                padding:
                    24px 20px 28px;

            }

            .error-message,
            .success-message {

                margin-left:
                    20px;

                margin-right:
                    20px;

            }

            .restock-box {

                margin:
                    24px 20px;

                padding:
                    22px;

            }

            .form-actions {

                flex-direction:
                    column-reverse;

                align-items:
                    stretch;

            }

            .form-actions
            .btn-secondary,
            .form-actions
            .btn-primary,
            .form-actions
            .btn-restock,
            .back-link {

                width:
                    100%;

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

        <h1>
            Cafelia
        </h1>

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
                <?php echo e($admin_name); ?>
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
     MAIN CONTENT
========================================================= -->

<main class="main">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <header class="topbar">

        <div>

            <div class="topbar-kicker">
                Cafelia Management
            </div>


            <h2>

                <?php

                echo $restock_mode
                    ? "Restock Product"
                    : "Edit Product";

                ?>

            </h2>


            <p>

                <?php

                if ($restock_mode) {

                    echo "Add 5 units to this product's inventory.";

                } else {

                    echo "Update the product details without changing its stock.";

                }

                ?>

            </p>

        </div>


        <a
            href="products.php"
            class="back-link"
        >
            ← Back to Products
        </a>

    </header>


    <!-- =====================================================
         CARD
    ====================================================== -->

    <section class="edit-card">


        <div class="card-heading">

            <div>

                <span>

                    <?php

                    echo $restock_mode
                        ? "RESTOCK INVENTORY"
                        : "UPDATE MENU ITEM";

                    ?>

                </span>


                <h3>

                    <?php

                    echo e(
                        $product["name"]
                    );

                    ?>

                </h3>

            </div>

        </div>


        <!-- ERROR -->

        <?php if ($error !== ""): ?>

            <div class="error-message">

                <?php echo e($error); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             RESTOCK MODE
        ================================================== -->

        <?php if ($restock_mode): ?>


            <div class="restock-box">

                <div class="restock-icon">
                    +
                </div>


                <h4>
                    Product is out of stock
                </h4>


                <p>

                    This product currently has
                    <strong>0 units</strong>
                    available.

                    Clicking the button below will automatically
                    add exactly <strong>5 units</strong> and make
                    the product available again.

                </p>


                <div class="restock-current">


                    <div class="stock-box">

                        <span>
                            Current Stock
                        </span>

                        <strong>
                            <?php echo $current_stock; ?>
                        </strong>

                    </div>


                    <div class="stock-box">

                        <span>
                            After Restock
                        </span>

                        <strong>
                            5
                        </strong>

                    </div>

                </div>


                <form
                    method="POST"
                    action="edit-product.php?id=<?php echo $product_id; ?>"
                    class="form-actions"
                >

                    <input
                        type="hidden"
                        name="action"
                        value="restock"
                    >


                    <a
                        href="products.php"
                        class="btn-secondary"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn-restock"
                        onclick="return confirm('Restock this product with 5 units?');"
                    >
                        + Restock 5 Units
                    </button>

                </form>

            </div>


        <?php else: ?>


            <!-- =================================================
                 NORMAL EDIT FORM
            ================================================== -->

            <form
                action="edit-product.php?id=<?php echo $product_id; ?>"
                method="POST"
                enctype="multipart/form-data"
                class="admin-form"
            >

                <input
                    type="hidden"
                    name="action"
                    value="update"
                >


                <!-- =========================================
                     PRODUCT NAME
                ========================================== -->

                <div class="form-group">

                    <label for="name">
                        Product Name *
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo e($product["name"]); ?>"
                        required
                    >

                </div>


                <!-- =========================================
                     DESCRIPTION
                ========================================== -->

                <div class="form-group">

                    <label for="description">
                        Description
                    </label>


                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                    ><?php
                    echo e(
                        $product["description"] ?? ""
                    );
                    ?></textarea>

                </div>


                <!-- =========================================
                     PRICE
                ========================================== -->

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
                        value="<?php echo e($product["price"]); ?>"
                        required
                    >

                </div>


                <!-- =========================================
                     CATEGORY
                ========================================== -->

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


                <!-- =========================================
                     STATUS
                ========================================== -->

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

                            echo in_array(
                                strtolower(
                                    trim(
                                        (string)$product["status"]
                                    )
                                ),
                                [
                                    "active",
                                    "available"
                                ],
                                true
                            )
                                ? "selected"
                                : "";

                            ?>
                        >
                            Available
                        </option>


                        <option
                            value="unavailable"
                            <?php

                            echo in_array(
                                strtolower(
                                    trim(
                                        (string)$product["status"]
                                    )
                                ),
                                [
                                    "inactive",
                                    "unavailable"
                                ],
                                true
                            )
                                ? "selected"
                                : "";

                            ?>
                        >
                            Unavailable
                        </option>

                    </select>


                    <small>

                        Product stock is managed separately.
                        Editing this page will not change the
                        current stock quantity.

                    </small>

                </div>


                <!-- =========================================
                     CURRENT IMAGE
                ========================================== -->

                <?php if ($current_image !== ""): ?>

                    <div class="current-image">

                        <label>
                            Current Image
                        </label>


                        <img
                            src="../image/<?php echo e($current_image); ?>"
                            alt="<?php echo e($product["name"]); ?>"
                            onerror="this.style.display='none';"
                        >

                    </div>

                <?php endif; ?>


                <!-- =========================================
                     NEW IMAGE
                ========================================== -->

                <div class="form-group file-upload">

                    <label for="image">
                        Replace Product Image
                    </label>


                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <small>

                        JPG, JPEG, PNG, or WEBP.
                        Maximum file size: 5MB.

                    </small>


                    <div id="imagePreview">

                        <img
                            id="previewImage"
                            src=""
                            alt="New image preview"
                        >

                    </div>

                </div>


                <!-- =========================================
                     ACTIONS
                ========================================== -->

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


        <?php endif; ?>


    </section>

</main>


<!-- =========================================================
     IMAGE PREVIEW
========================================================= -->

<script>

const imageInput =
    document.getElementById("image");

const imagePreview =
    document.getElementById("imagePreview");

const previewImage =
    document.getElementById("previewImage");


if (imageInput) {

    imageInput.addEventListener(
        "change",
        function () {

            const file =
                this.files[0];

            if (!file) {

                imagePreview.style.display =
                    "none";

                previewImage.src =
                    "";

                return;

            }


            const allowedTypes = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    "Please select a JPG, JPEG, PNG, or WEBP image."
                );

                this.value =
                    "";

                imagePreview.style.display =
                    "none";

                return;

            }


            if (
                file.size >
                5 * 1024 * 1024
            ) {

                alert(
                    "The image must be 5MB or smaller."
                );

                this.value =
                    "";

                imagePreview.style.display =
                    "none";

                return;

            }


            const reader =
                new FileReader();


            reader.onload =
                function (event) {

                    previewImage.src =
                        event.target.result;

                    imagePreview.style.display =
                        "block";

                };


            reader.readAsDataURL(file);

        }
    );

}

</script>

</body>

</html>