<?php

session_start();
require_once "config/database.php";

/* =========================================================
   CUSTOMER AUTHENTICATION
========================================================= */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$account_name = $_SESSION["user_name"] ?? "Customer";

/* =========================================================
   CART COUNT
========================================================= */

$cart_count = 0;

$cart_stmt = $conn->prepare(
    "SELECT COALESCE(SUM(quantity), 0) AS total
     FROM cart
     WHERE user_id = ?"
);

if ($cart_stmt) {
    $cart_stmt->bind_param("i", $user_id);
    $cart_stmt->execute();

    $cart_result = $cart_stmt->get_result();
    $cart_data = $cart_result->fetch_assoc();

    $cart_count = (int) ($cart_data["total"] ?? 0);

    $cart_stmt->close();
}

/* =========================================================
   GET CUSTOMER ORDERS
========================================================= */

$stmt = $conn->prepare(
    "SELECT
        id,
        customer_name,
        total_amount,
        payment_method,
        status,
        order_date
     FROM orders
     WHERE user_id = ?
     ORDER BY order_date DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$orders_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Orders | Cafelia</title>

    <link rel="stylesheet" href="css/style.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <style>

        /* =====================================================
           CAFELIA CURRENT SHARED NAVBAR
           Matches the current homepage / menu design
        ===================================================== */

        .navbar {
            position: sticky !important;
            top: 0 !important;
            z-index: 9999 !important;
            width: 100% !important;

            background: #281913 !important;

            border-bottom:
                1px solid rgba(255,255,255,.10) !important;

            box-shadow: none !important;

            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .navbar .nav-container {
            width: 100% !important;
            max-width: 1650px !important;
            min-height: 100px !important;

            margin: 0 auto !important;
            padding: 0 34px !important;

            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
        }

        .navbar .logo {
            width: auto !important;
            min-width: 145px !important;

            margin-right: auto !important;

            display: inline-flex !important;
            align-items: center !important;

            text-decoration: none !important;
        }

        .navbar .logo-text {
            display: inline-block !important;

            color: #f5eee5 !important;

            font-family:
                "Playfair Display",
                Georgia,
                serif !important;

            font-size: 38px !important;
            font-weight: 700 !important;

            line-height: 1 !important;
            letter-spacing: 3px !important;

            white-space: nowrap !important;
        }

        .navbar .logo:hover .logo-text {
            color: #d8b892 !important;
        }

        .navbar .nav-menu {
            display: flex !important;
            align-items: center !important;

            gap: 38px !important;

            margin-left: auto !important;
            margin-right: 62px !important;
        }

        .navbar .nav-menu a {
            position: relative !important;

            padding: 39px 0 22px !important;

            color: rgba(255,255,255,.78) !important;

            background: transparent !important;
            border: 0 !important;

            font-family: "DM Sans", Arial, sans-serif !important;
            font-size: 13px !important;
            font-weight: 800 !important;

            letter-spacing: 1.25px !important;
            line-height: 1 !important;

            text-decoration: none !important;
            text-transform: uppercase !important;

            transition: .2s ease !important;
        }

        .navbar .nav-menu a:hover {
            color: #ffffff !important;
        }

        .navbar .nav-menu a.active {
            color: #c99a68 !important;
        }

        .navbar .nav-menu a.active::after {
            content: "";

            position: absolute;

            left: 0;
            right: 0;
            bottom: 0;

            height: 2px;

            background: #c99a68;

            transform: none !important;
        }

        /* =====================================================
           CART
        ===================================================== */

        .homepage-nav-actions {
            display: flex !important;
            align-items: center !important;

            gap: 14px !important;

            flex-shrink: 0 !important;
        }

        .homepage-cart {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;

            gap: 11px !important;

            min-width: 118px !important;
            height: 58px !important;

            padding: 0 20px !important;

            border: 1px solid rgba(255,255,255,.19) !important;
            border-radius: 30px !important;

            background: rgba(255,255,255,.025) !important;

            color: #f5eee5 !important;

            text-decoration: none !important;

            font-family: "DM Sans", Arial, sans-serif !important;
            font-size: 12px !important;
            font-weight: 800 !important;

            letter-spacing: 1.4px !important;

            transition: .2s ease !important;
        }

        .homepage-cart:hover {
            background: rgba(255,255,255,.08) !important;
            border-color: rgba(255,255,255,.28) !important;

            transform: translateY(-1px);
        }

        .homepage-cart-icon {
            font-size: 18px !important;
            line-height: 1 !important;
            color: #d8b28b !important;
        }

        .cart-count {
            min-width: 19px;
            height: 19px;

            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 0 5px;

            border-radius: 999px;

            background: #c99a68;
            color: #24150f;

            font-size: 10px;
            font-weight: 900;
        }

        /* =====================================================
           ACCOUNT
        ===================================================== */

        .account-menu {
            position: relative !important;
        }

        .account-trigger {
            height: 58px !important;

            display: flex !important;
            align-items: center !important;

            gap: 10px !important;

            padding: 5px 16px 5px 7px !important;

            border: 1px solid rgba(255,255,255,.19) !important;
            border-radius: 30px !important;

            background: rgba(255,255,255,.025) !important;

            color: #fff !important;

            font-family: inherit !important;

            cursor: pointer !important;

            transition: .2s ease !important;
        }

        .account-trigger:hover {
            background: rgba(255,255,255,.08) !important;
            border-color: rgba(255,255,255,.28) !important;
        }

        .profile-avatar,
        .dropdown-avatar {
            display: grid !important;
            place-items: center !important;

            border-radius: 50% !important;

            background:
                linear-gradient(
                    145deg,
                    #d4a16d,
                    #9b6945
                ) !important;

            border: 1px solid rgba(255,255,255,.28) !important;

            color: #fff !important;

            font-family:
                "Playfair Display",
                Georgia,
                serif !important;

            font-weight: 700 !important;
        }

        .profile-avatar {
            width: 46px !important;
            height: 46px !important;

            font-size: 17px !important;

            box-shadow:
                inset 0 1px 2px rgba(255,255,255,.20),
                0 3px 10px rgba(0,0,0,.15);
        }

        .profile-name {
            max-width: 120px !important;

            overflow: hidden !important;

            color: #f5eee5 !important;

            font-size: 12px !important;
            font-weight: 800 !important;

            white-space: nowrap !important;
            text-overflow: ellipsis !important;
        }

        .account-dropdown {
            position: absolute !important;

            top: calc(100% + 12px) !important;
            right: 0 !important;

            width: 245px !important;

            padding: 10px !important;

            border: 1px solid rgba(255,255,255,.13) !important;
            border-radius: 17px !important;

            background: rgba(45,27,20,.98) !important;

            box-shadow:
                0 20px 55px rgba(0,0,0,.30) !important;

            opacity: 0 !important;
            visibility: hidden !important;

            transform:
                translateY(-8px)
                scale(.98) !important;

            transition: .2s ease !important;

            z-index: 99999 !important;

            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .account-menu.open .account-dropdown {
            opacity: 1 !important;
            visibility: visible !important;

            transform:
                translateY(0)
                scale(1) !important;
        }

        .dropdown-profile {
            display: flex;
            align-items: center;

            gap: 11px;

            padding: 10px 9px 13px;
        }

        .dropdown-avatar {
            width: 40px;
            height: 40px;

            flex-shrink: 0;

            font-size: 13px;
        }

        .dropdown-profile-info {
            min-width: 0;
        }

        .dropdown-profile-info strong {
            display: block;

            color: #fffaf3;

            font-size: 13px;

            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dropdown-profile-info span {
            display: block;

            margin-top: 3px;

            color: rgba(255,255,255,.48);

            font-size: 10px;

            letter-spacing: .05em;
        }

        .dropdown-divider {
            height: 1px;

            margin: 0 4px 7px;

            background: rgba(255,255,255,.08);
        }

        .dropdown-item {
            display: flex;
            align-items: center;

            gap: 10px;

            width: 100%;

            padding: 11px 9px;

            border-radius: 10px;

            color: rgba(255,255,255,.82) !important;

            text-decoration: none !important;

            font-size: 12px;

            transition: .2s ease;
        }

        .dropdown-item:hover {
            background: rgba(255,255,255,.07);

            color: #fff !important;
        }

        .dropdown-item-icon {
            width: 22px;

            text-align: center;

            color: #d8b28b;
        }

        .dropdown-item-text {
            flex: 1;
        }

        .dropdown-logout {
            display: flex;

            align-items: center;

            gap: 10px;

            padding: 11px 9px;

            border-radius: 10px;

            color: #e8b7a7 !important;

            text-decoration: none !important;

            font-size: 12px;

            transition: .2s ease;
        }

        .dropdown-logout:hover {
            background: rgba(169,71,55,.12);

            color: #f2c5b7 !important;
        }

        /* =====================================================
           PAGE
        ===================================================== */

        body {
            margin: 0 !important;

            background:
                linear-gradient(
                    180deg,
                    #f8f3ec 0%,
                    #f1e7da 100%
                ) !important;

            color: #2d1d15 !important;

            font-family:
                "DM Sans",
                Arial,
                sans-serif !important;
        }

        .orders-hero {
            position: relative;

            padding: 90px 7% 125px;

            background:
                radial-gradient(
                    circle at 80% 25%,
                    rgba(216,163,109,.18),
                    transparent 28%
                ),
                linear-gradient(
                    135deg,
                    #2d1b14,
                    #4a2a1d
                );

            color: #fffaf3;

            overflow: hidden;
        }

        .orders-hero::before {
            content: "";

            position: absolute;

            width: 330px;
            height: 330px;

            right: -110px;
            top: -140px;

            border:
                1px solid
                rgba(255,255,255,.08);

            border-radius: 50%;
        }

        .orders-hero::after {
            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            left: -120px;
            bottom: -130px;

            border:
                1px solid
                rgba(216,163,109,.13);

            border-radius: 50%;
        }

        .orders-hero-inner {
            position: relative;

            z-index: 2;

            max-width: 1250px;

            margin: 0 auto;
        }

        .orders-kicker {
            margin-bottom: 13px;

            color: #d8a36d;

            font-size: 11px;
            font-weight: 800;

            letter-spacing: .24em;

            text-transform: uppercase;
        }

        .orders-hero h1 {
            margin: 0;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size:
                clamp(42px, 6vw, 68px);

            line-height: 1;

            font-weight: 600;

            letter-spacing: -.035em;
        }

        .orders-hero h1 span {
            color: #d8a36d;
        }

        .orders-hero p {
            max-width: 580px;

            margin: 20px 0 0;

            color: rgba(248,242,233,.72);

            font-size: 14px;
            line-height: 1.8;
        }

        /* =====================================================
           ORDERS CONTENT
        ===================================================== */

        .orders-section {
            width: min(1250px, calc(100% - 40px));

            margin: -52px auto 90px;

            position: relative;

            z-index: 3;
        }

        .success-message {
            margin-bottom: 20px;

            padding: 15px 18px;

            border:
                1px solid
                rgba(57,115,75,.18);

            border-radius: 14px;

            background: #eef7f0;

            color: #39734b;

            font-size: 13px;
            font-weight: 700;

            box-shadow:
                0 12px 30px rgba(36,21,15,.06);
        }

        .order-card {
            margin-bottom: 22px;

            background: rgba(255,253,249,.94);

            border:
                1px solid
                rgba(91,58,39,.12);

            border-radius: 20px;

            overflow: hidden;

            box-shadow:
                0 18px 45px
                rgba(36,21,15,.08);

            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .order-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 25px 28px 18px;
        }

        .order-header > div:first-child span {
            display: block;

            margin-bottom: 5px;

            color: #9a887b;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .14em;

            text-transform: uppercase;
        }

        .order-header > div:first-child strong {
            color: #2d1d15;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 24px;
        }

        .order-status {
            flex-shrink: 0;
        }

        .status {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-width: 105px;

            padding: 9px 16px;

            border-radius: 999px;

            font-size: 10px;
            font-weight: 900;

            letter-spacing: .11em;

            text-transform: uppercase;
        }

        .status.pending {
            background: #f2e4d3;
            border: 1px solid #e6ccb0;
            color: #8a5e3d;
        }

        .status.processing {
            background: #eee5d5;
            border: 1px solid #ddc6a6;
            color: #795d3f;
        }

        .status.completed {
            background: #e8f1e8;
            border: 1px solid #c9ddcb;
            color: #47704e;
        }

        .status.cancelled {
            background: #f8e9e5;
            border: 1px solid #e8c4bc;
            color: #9b4e40;
        }

        .order-date {
            margin: 0 28px;

            padding:
                14px 0;

            border-top:
                1px solid
                rgba(91,58,39,.11);

            border-bottom:
                1px solid
                rgba(91,58,39,.11);

            color: #8b786a;

            font-size: 12px;
        }

        .order-date span {
            font-weight: 700;
        }

        .order-items {
            padding: 4px 28px;
        }

        .order-item {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            padding: 20px 0;

            border-bottom:
                1px solid
                rgba(91,58,39,.09);
        }

        .order-item:last-child {
            border-bottom: 0;
        }

        .order-item > div:first-child {
            min-width: 0;
        }

        .order-item strong {
            color: #332119;
        }

        .order-item > div:first-child strong {
            display: inline-block;

            margin-right: 8px;

            font-size: 14px;
        }

        .order-item > div:first-child span {
            color: #88766a;

            font-size: 13px;
        }

        .order-item > strong {
            flex-shrink: 0;

            font-size: 14px;
        }

        .order-footer {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 25px;

            padding: 21px 28px;

            background:
                rgba(248,242,233,.72);
        }

        .order-footer > div {
            display: flex;

            align-items: baseline;

            gap: 6px;
        }

        .order-footer span {
            color: #88766a;

            font-size: 13px;
        }

        .order-footer strong {
            color: #38251b;

            font-size: 14px;
        }

        .order-total {
            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 22px !important;
        }

        /* =====================================================
           EMPTY STATE
        ===================================================== */

        .empty-orders {
            padding: 75px 30px;

            text-align: center;

            background:
                rgba(255,253,249,.94);

            border:
                1px solid
                rgba(91,58,39,.12);

            border-radius: 20px;

            box-shadow:
                0 18px 45px
                rgba(36,21,15,.08);
        }

        .empty-order-icon {
            width: 78px;
            height: 78px;

            margin: 0 auto 20px;

            display: grid;
            place-items: center;

            border-radius: 50%;

            background: #f0e2d1;

            border:
                1px solid
                #e3cbb1;

            font-size: 30px;
        }

        .empty-orders h2 {
            margin-bottom: 9px;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 32px;
            font-weight: 600;
        }

        .empty-orders p {
            margin-bottom: 24px;

            color: #806f63;

            font-size: 14px;
        }

        .btn-primary {
            display: inline-flex;

            align-items: center;
            justify-content: center;

            min-height: 48px;

            padding: 0 22px;

            border-radius: 999px;

            background: #2d1b14;

            color: #fffaf3 !important;

            text-decoration: none !important;

            font-size: 11px;
            font-weight: 800;

            letter-spacing: .13em;

            text-transform: uppercase;

            transition: .2s ease;
        }

        .btn-primary:hover {
            background: #5a3827;

            transform: translateY(-2px);
        }

        /* =====================================================
           CURRENT CAFELIA FOOTER
        ===================================================== */

        .footer {
            padding: 72px 7% 0 !important;

            background: #24150f !important;

            color: #f8f1e8 !important;
        }

        .footer-container {
            width: min(1380px, 100%);

            margin: 0 auto;

            display: grid;

            grid-template-columns:
                1.35fr
                1fr
                1fr
                1fr;

            gap: 60px;

            padding-bottom: 58px;
        }

        .footer-brand {
            max-width: 320px;
        }

        .footer-logo {
            display: inline-block;

            text-decoration: none;
        }

        .footer-logo-text {
            color: #f8f1e8;

            font-family:
                "Playfair Display",
                Georgia,
                serif;

            font-size: 30px;
            font-weight: 700;

            letter-spacing: 3px;
        }

        .footer-brand > p {
            margin-top: 15px;

            color: rgba(248,241,232,.58);

            font-size: 13px;

            line-height: 1.7;
        }

        .footer-contact {
            margin-top: 24px;
        }

        .footer-contact p {
            margin: 5px 0;

            color: rgba(248,241,232,.48);

            font-size: 11px;
        }

        .footer-column h3 {
            margin: 0 0 18px;

            color: #d8a36d;

            font-size: 10px;
            font-weight: 800;

            letter-spacing: .20em;
        }

        .footer-column a {
            display: block;

            width: fit-content;

            margin-bottom: 11px;

            color: rgba(248,241,232,.60) !important;

            text-decoration: none !important;

            font-size: 11px;
            font-weight: 600;

            letter-spacing: .06em;

            transition: .2s ease;
        }

        .footer-column a:hover {
            color: #fffaf3 !important;

            transform: translateX(2px);
        }

        .footer-bottom {
            width: min(1380px, 100%);

            margin: 0 auto;

            padding: 22px 0;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            border-top:
                1px solid
                rgba(255,255,255,.09);
        }

        .footer-bottom p {
            margin: 0;

            color: rgba(248,241,232,.38);

            font-size: 10px;

            letter-spacing: .06em;
        }

        .footer-legal {
            display: flex;

            gap: 20px;
        }

        .footer-legal a {
            color: rgba(248,241,232,.38) !important;

            text-decoration: none !important;

            font-size: 10px;

            transition: .2s ease;
        }

        .footer-legal a:hover {
            color: #d8a36d !important;
        }

        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 1100px) {

            .navbar .nav-menu {
                gap: 24px !important;
                margin-right: 30px !important;
            }

            .navbar .nav-menu a {
                font-size: 11px !important;
            }

            .footer-container {
                grid-template-columns:
                    1.3fr
                    1fr
                    1fr;
            }
        }

        @media (max-width: 850px) {

            .navbar .nav-container {
                min-height: 82px !important;
                padding: 15px 24px !important;

                flex-wrap: wrap;
            }

            .navbar .logo-text {
                font-size: 30px !important;
            }

            .navbar .nav-menu {
                order: 3;

                width: 100%;

                margin: 10px 0 0 !important;

                justify-content: center;

                gap: 24px !important;
            }

            .navbar .nav-menu a {
                padding: 8px 0 !important;
            }

            .navbar .nav-menu a.active::after {
                bottom: 0;
            }

            .orders-hero {
                padding: 75px 6% 105px;
            }

            .orders-section {
                width: min(100% - 28px, 900px);

                margin-top: -45px;
            }

            .footer-container {
                grid-template-columns: 1fr 1fr;

                gap: 40px;
            }

            .footer-brand {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 600px) {

            .navbar .nav-container {
                padding: 14px 16px !important;
            }

            .navbar .logo-text {
                font-size: 27px !important;
                letter-spacing: 2px !important;
            }

            .homepage-cart {
                min-width: auto !important;

                width: 48px;
                height: 48px !important;

                padding: 0 !important;
            }

            .homepage-cart span:nth-child(2) {
                display: none;
            }

            .homepage-cart-icon {
                font-size: 17px !important;
            }

            .account-trigger {
                width: 48px !important;
                height: 48px !important;

                padding: 3px !important;

                justify-content: center !important;
            }

            .profile-avatar {
                width: 40px !important;
                height: 40px !important;
            }

            .profile-name {
                display: none !important;
            }

            .navbar .nav-menu {
                gap: 15px !important;
            }

            .navbar .nav-menu a {
                font-size: 9px !important;
                letter-spacing: .8px !important;
            }

            .orders-hero {
                padding: 60px 22px 92px;
            }

            .orders-hero h1 {
                font-size: 42px;
            }

            .orders-section {
                width: calc(100% - 18px);

                margin-top: -38px;

                margin-bottom: 55px;
            }

            .order-header {
                align-items: flex-start;

                padding: 21px 18px 15px;
            }

            .order-header > div:first-child strong {
                font-size: 21px;
            }

            .status {
                min-width: 88px;

                padding: 8px 12px;

                font-size: 9px;
            }

            .order-date {
                margin: 0 18px;
            }

            .order-items {
                padding: 3px 18px;
            }

            .order-item {
                gap: 12px;

                padding: 17px 0;
            }

            .order-item > div:first-child strong {
                display: block;

                margin: 0 0 4px;

                font-size: 13px;
            }

            .order-item > div:first-child span {
                font-size: 12px;
            }

            .order-item > strong {
                font-size: 13px;
            }

            .order-footer {
                align-items: flex-start;

                flex-direction: column;

                padding: 18px;
            }

            .order-footer > div {
                width: 100%;

                justify-content: space-between;
            }

            .footer {
                padding: 55px 7% 0 !important;
            }

            .footer-container {
                grid-template-columns: 1fr;

                gap: 30px;

                padding-bottom: 42px;
            }

            .footer-brand {
                grid-column: auto;
            }

            .footer-bottom {
                align-items: flex-start;

                flex-direction: column;
            }
        }

    </style>

</head>

<body>

    <!-- =====================================================
         CURRENT CAFELIA NAVBAR
    ====================================================== -->

    <header class="navbar">

        <div class="nav-container">

            <a
                href="index.php"
                class="logo"
                aria-label="Cafelia Home"
            >
                <span class="logo-text">
                    CAFELIA
                </span>
            </a>


            <nav class="nav-menu">

                <a href="index.php">
                    HOME
                </a>

                <a href="menu.php">
                    MENU
                </a>

                <a href="about.php">
                    ABOUT US
                </a>

                <a href="join_team.php">
                    JOIN OUR TEAM
                </a>

                <a href="contact.php">
                    CONTACT
                </a>

            </nav>


            <div class="homepage-nav-actions">

                <a
                    href="cart.php"
                    class="homepage-cart"
                >
                    <span class="homepage-cart-icon">
                        🛒
                    </span>

                    <span>
                        CART
                    </span>

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
                            echo htmlspecialchars(
                                strtoupper(
                                    substr(
                                        $account_name,
                                        0,
                                        1
                                    )
                                )
                            );
                            ?>
                        </span>

                        <span class="profile-name">
                            <?php
                            echo htmlspecialchars(
                                $account_name
                            );
                            ?>
                        </span>

                    </button>


                    <div class="account-dropdown">

                        <div class="dropdown-profile">

                            <div class="dropdown-avatar">
                                <?php
                                echo htmlspecialchars(
                                    strtoupper(
                                        substr(
                                            $account_name,
                                            0,
                                            1
                                        )
                                    )
                                );
                                ?>
                            </div>

                            <div class="dropdown-profile-info">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $account_name
                                    );
                                    ?>
                                </strong>

                                <span>
                                    Cafelia Customer
                                </span>

                            </div>

                        </div>


                        <div class="dropdown-divider"></div>


                        <a
                            href="profile.php"
                            class="dropdown-item"
                        >
                            <span class="dropdown-item-icon">
                                ◎
                            </span>

                            <span class="dropdown-item-text">
                                My Profile
                            </span>
                        </a>


                        <a
                            href="orders.php"
                            class="dropdown-item"
                        >
                            <span class="dropdown-item-icon">
                                ▤
                            </span>

                            <span class="dropdown-item-text">
                                My Orders
                            </span>
                        </a>


                        <a
                            href="cart.php"
                            class="dropdown-item"
                        >
                            <span class="dropdown-item-icon">
                                🛒
                            </span>

                            <span class="dropdown-item-text">
                                My Cart
                            </span>
                        </a>


                        <a
                            href="logout.php"
                            class="dropdown-logout"
                        >
                            <span class="dropdown-item-icon">
                                ↪
                            </span>

                            <span class="dropdown-item-text">
                                Log Out
                            </span>
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </header>


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="orders-hero">

        <div class="orders-hero-inner">

            <p class="orders-kicker">
                YOUR CAFELIA ACCOUNT
            </p>

            <h1>
                My <span>Orders.</span>
            </h1>

            <p>
                Keep track of your Cafelia purchases,
                order status, payment details, and
                everything you have enjoyed from our menu.
            </p>

        </div>

    </section>


    <!-- =====================================================
         ORDERS
    ====================================================== -->

    <section class="orders-section">

        <?php if (isset($_GET["success"])): ?>

            <div class="success-message">
                Your order has been placed successfully! ☕
            </div>

        <?php endif; ?>


        <?php if ($orders_result->num_rows > 0): ?>

            <?php while ($order = $orders_result->fetch_assoc()): ?>

                <article class="order-card">

                    <div class="order-header">

                        <div>

                            <span>
                                Order Number
                            </span>

                            <strong>
                                #<?php echo (int) $order["id"]; ?>
                            </strong>

                        </div>


                        <div class="order-status">

                            <?php
                            $status_class = strtolower(
                                str_replace(
                                    " ",
                                    "-",
                                    $order["status"]
                                )
                            );
                            ?>

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

                        </div>

                    </div>


                    <div class="order-date">

                        <span>
                            Date:
                        </span>

                        <?php
                        echo date(
                            "F d, Y h:i A",
                            strtotime(
                                $order["order_date"]
                            )
                        );
                        ?>

                    </div>


                    <div class="order-items">

                        <?php

                        $order_id = (int) $order["id"];

                        $item_stmt = $conn->prepare(
                            "SELECT
                                product_name,
                                price,
                                quantity,
                                subtotal
                             FROM order_items
                             WHERE order_id = ?
                             ORDER BY id ASC"
                        );

                        $item_stmt->bind_param(
                            "i",
                            $order_id
                        );

                        $item_stmt->execute();

                        $items_result =
                            $item_stmt->get_result();

                        ?>

                        <?php while (
                            $item =
                            $items_result->fetch_assoc()
                        ): ?>

                            <div class="order-item">

                                <div>

                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $item["product_name"]
                                        );
                                        ?>
                                    </strong>

                                    <span>
                                        ₱<?php
                                        echo number_format(
                                            (float) $item["price"],
                                            2
                                        );
                                        ?>

                                        ×

                                        <?php
                                        echo (int)
                                            $item["quantity"];
                                        ?>
                                    </span>

                                </div>


                                <strong>
                                    ₱<?php
                                    echo number_format(
                                        (float) $item["subtotal"],
                                        2
                                    );
                                    ?>
                                </strong>

                            </div>

                        <?php endwhile; ?>

                        <?php $item_stmt->close(); ?>

                    </div>


                    <div class="order-footer">

                        <div>

                            <span>
                                Payment
                            </span>

                            <strong>
                                <?php
                                echo htmlspecialchars(
                                    $order["payment_method"]
                                );
                                ?>
                            </strong>

                        </div>


                        <div>

                            <span>
                                Total
                            </span>

                            <strong class="order-total">
                                ₱<?php
                                echo number_format(
                                    (float) $order["total_amount"],
                                    2
                                );
                                ?>
                            </strong>

                        </div>

                    </div>

                </article>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-orders">

                <div
                    class="empty-order-icon"
                    aria-hidden="true"
                >
                    ☕
                </div>

                <h2>
                    No orders yet
                </h2>

                <p>
                    You haven't placed an order yet.
                    Your Cafelia orders will appear here.
                </p>

                <a
                    href="menu.php"
                    class="btn-primary"
                >
                    Browse Menu
                </a>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         CURRENT CAFELIA FOOTER
    ====================================================== -->

    <footer class="footer">

        <div class="footer-container">


            <div class="footer-brand">

                <a
                    href="index.php"
                    class="footer-logo"
                    aria-label="Cafelia Home"
                >
                    <span class="footer-logo-text">
                        CAFELIA
                    </span>
                </a>

                <p>
                    More than coffee, it’s an experience.
                </p>

                <div class="footer-contact">

                    <p>
                        09971868624
                    </p>

                    <p>
                        Cafelia@gmail.com
                    </p>

                </div>

            </div>


            <div class="footer-column">

                <h3>
                    MENU
                </h3>

                <a href="menu.php">
                    DRINKS
                </a>

                <a href="menu.php">
                    HOT FOOD
                </a>

                <a href="menu.php">
                    TREATS
                </a>

                <a href="menu.php">
                    FEATURED BEVERAGES
                </a>

            </div>


            <div class="footer-column">

                <h3>
                    QUICKLINKS
                </h3>

                <a href="index.php">
                    HOME
                </a>

                <a href="menu.php">
                    MENU
                </a>

                <a href="about.php">
                    ABOUT US
                </a>

                <a href="join_team.php">
                    JOIN OUR TEAM
                </a>

                <a href="contact.php">
                    CONTACT
                </a>

            </div>


            <div class="footer-column">

                <h3>
                    ACCOUNT
                </h3>

                <a href="profile.php">
                    MY PROFILE
                </a>

                <a href="orders.php">
                    MY ORDERS
                </a>

                <a href="cart.php">
                    MY CART
                </a>

                <a href="logout.php">
                    LOGOUT
                </a>

            </div>


        </div>


        <div class="footer-bottom">

            <p>
                © <?php echo date("Y"); ?>
                Cafelia.
                All Right Reserved.
            </p>

            <div class="footer-legal">

                <a href="#">
                    Privacy Policy
                </a>

                <a href="#">
                    Terms of Service
                </a>

            </div>

        </div>

    </footer>


    <script>

        function toggleAccountMenu(button) {

            const menu =
                button.closest(".account-menu");

            document
                .querySelectorAll(".account-menu.open")
                .forEach(function (item) {

                    if (item !== menu) {

                        item.classList.remove("open");

                        const trigger =
                            item.querySelector(
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

                        if (
                            !menu.contains(
                                event.target
                            )
                        ) {

                            menu.classList.remove(
                                "open"
                            );

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
                        .querySelectorAll(
                            ".account-menu.open"
                        )
                        .forEach(function (menu) {

                            menu.classList.remove(
                                "open"
                            );

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

    </script>

</body>

</html>
