<?php

session_start();

require_once __DIR__ . "/config/database.php";

/* =========================================================
   GET PRODUCTS
========================================================= */

$products = [];

$sql = "SELECT * FROM products
        WHERE status = 'available'
        ORDER BY category ASC, id DESC";

$result = $conn->query($sql);

if ($result === false) {
    die("Database Query Error: " . $conn->error);
}

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

/* =========================================================
   GET CATEGORIES
========================================================= */

$categories = [];

$category_sql = "SELECT DISTINCT category
                 FROM products
                 WHERE status = 'available'
                 ORDER BY category ASC";

$category_result = $conn->query($category_sql);

if ($category_result) {

    while ($category = $category_result->fetch_assoc()) {

        $categories[] = $category['category'];

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

    <title>Cafelia | Menu</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

<style>
/* =========================================================
   CAFELIA MENU — PROFESSIONAL POLISH
   ========================================================= */

.menu-hero {
    min-height: 560px;
    position: relative;
    display: flex;
    align-items: center;
    overflow: hidden;
}

.menu-hero-overlay {
    background:
        linear-gradient(90deg, rgba(31, 18, 11, .82) 0%, rgba(31, 18, 11, .56) 48%, rgba(31, 18, 11, .20) 100%);
}

.menu-hero-content {
    max-width: 760px;
    padding: 90px 6vw;
    position: relative;
    z-index: 2;
}

.menu-hero-content > span {
    letter-spacing: .24em;
    font-size: .78rem;
    font-weight: 700;
}

.menu-hero-content h1 {
    font-family: "Playfair Display", serif;
    font-size: clamp(3.3rem, 7vw, 6.5rem);
    line-height: .96;
    margin: 18px 0 22px;
    max-width: 720px;
}

.menu-hero-content h1 strong {
    font-weight: 500;
    font-style: italic;
}

.menu-hero-content p {
    max-width: 570px;
    font-size: 1.05rem;
    line-height: 1.8;
}

/* Text logo */
.logo {
    width: auto !important;
    text-decoration: none;
}

.logo-text {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.75rem;
    font-weight: 700;
    letter-spacing: .16em;
    color: #f8f1e8;
    line-height: 1;
    transition: opacity .2s ease, transform .2s ease;
}

.logo:hover .logo-text {
    opacity: .78;
    transform: translateY(-1px);
}

.footer-logo {
    text-decoration: none;
    display: inline-block;
}

.footer-logo-text {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: .16em;
    color: #f8f1e8;
}

/* Navbar actions */
.nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 22px;
}

.nav-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 0 15px;
    border-radius: 999px;
    border: 1px solid rgba(248,241,232,.24);
    color: #f8f1e8;
    text-decoration: none;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .11em;
    transition: .2s ease;
}

.nav-action:hover {
    background: rgba(248,241,232,.12);
    border-color: rgba(248,241,232,.48);
}

.nav-action-outline {
    background: #c89b6d;
    border-color: #c89b6d;
    color: #2d1a10;
}

.nav-action-outline:hover {
    background: #d8b892;
    border-color: #d8b892;
    color: #2d1a10;
}

/* Menu heading */
.menu-section {
    background: #f8f1e8;
    padding: 90px 0 100px;
}

.menu-heading {
    max-width: 760px;
    margin: 0 auto 48px;
    text-align: center;
}

.menu-heading h2 {
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2.4rem, 4vw, 4rem);
    color: #3b2115;
    margin: 10px 0 14px;
}

.menu-heading p {
    color: #75665b;
    line-height: 1.8;
    max-width: 600px;
    margin: auto;
}

.menu-heading-meta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    margin-top: 26px;
    color: #8b5e3c;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .18em;
}

.menu-rule {
    width: 42px;
    height: 1px;
    background: #c89b6d;
}

/* Category pills */
.menu-categories {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 50px;
}

.menu-category {
    min-width: 100px;
    padding: 12px 21px;
    border: 1px solid #d9c7b4;
    border-radius: 999px;
    background: transparent;
    color: #5d4535;
    font-size: .72rem;
    font-weight: 800;
    letter-spacing: .1em;
    cursor: pointer;
    transition: .22s ease;
}

.menu-category:hover {
    border-color: #8b5e3c;
    color: #3b2115;
    transform: translateY(-1px);
}

.menu-category.active {
    background: #3b2115;
    border-color: #3b2115;
    color: #fff;
    box-shadow: 0 8px 20px rgba(59,33,21,.16);
}

/* Product grid */
.menu-products {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 24px;
}

.menu-product-card {
    background: #fff;
    border: 1px solid #eadfd3;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(59,33,21,.07);
    transition: transform .25s ease, box-shadow .25s ease;
}

.menu-product-card:hover {
    transform: translateY(-7px);
    box-shadow: 0 20px 42px rgba(59,33,21,.13);
}

.menu-product-image {
    height: 280px;
    position: relative;
    overflow: hidden;
    background: #ead9c6;
}

.menu-product-image img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
    transition: transform .45s ease;
}

.menu-product-card:hover .menu-product-image img {
    transform: scale(1.045);
}

.menu-product-category {
    position: absolute;
    top: 16px;
    left: 16px;
    bottom: auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: fit-content !important;
    height: auto !important;
    min-width: 0 !important;
    max-width: max-content;
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, .28);
    border-radius: 10px;
    background: rgba(59, 33, 21, .52) !important;
    color: rgba(255, 255, 255, .96) !important;
    font-size: .62rem;
    font-weight: 800;
    line-height: 1;
    letter-spacing: .11em;
    white-space: nowrap;
    writing-mode: horizontal-tb !important;
    transform: none !important;
    backdrop-filter: blur(10px) saturate(125%) !important;
    -webkit-backdrop-filter: blur(10px) saturate(125%) !important;
    box-shadow: 0 5px 18px rgba(0, 0, 0, .16), inset 0 1px 0 rgba(255,255,255,.12);
}

.menu-product-category::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    background: linear-gradient(135deg, rgba(255,255,255,.10), transparent 55%);
    pointer-events: none;
}

.menu-product-info {
    padding: 22px;
}

.menu-product-kicker {
    display: block;
    margin-bottom: 7px;
    color: #a17a57;
    font-size: .61rem;
    font-weight: 800;
    letter-spacing: .16em;
}

.menu-product-info h3 {
    margin: 0 0 9px;
    color: #3b2115;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.45rem;
}

.menu-product-info p {
    color: #75665b;
    font-size: .88rem;
    line-height: 1.65;
    min-height: 48px;
    margin: 0;
}

.menu-product-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #eee3d8;
}

.menu-product-price {
    color: #3b2115;
    font-size: 1.08rem;
    font-weight: 800;
    white-space: nowrap;
}

.menu-add-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 13px;
    border-radius: 999px;
    background: #3b2115;
    color: #fff;
    text-decoration: none;
    font-size: .65rem;
    font-weight: 800;
    letter-spacing: .08em;
    transition: .2s ease;
}

.menu-add-button span {
    font-size: 1rem;
    line-height: 1;
}

.menu-add-button:hover {
    background: #6b4126;
    transform: translateY(-1px);
}

/* Highlights */
.menu-highlights {
    background: #3b2115;
    color: #fff;
    padding: 26px 0;
}

.menu-highlights-container {
    width: min(1160px, calc(100% - 40px));
    margin: auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

.menu-highlight {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 8px 18px;
    border-right: 1px solid rgba(255,255,255,.14);
}

.menu-highlight:last-child {
    border-right: 0;
}

.menu-highlight-icon {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    flex: 0 0 38px;
    border: 1px solid rgba(216,184,146,.55);
    border-radius: 50%;
    color: #d8b892;
}

.menu-highlight strong,
.menu-highlight small {
    display: block;
}

.menu-highlight strong {
    font-size: .82rem;
    letter-spacing: .04em;
}

.menu-highlight small {
    margin-top: 3px;
    color: rgba(255,255,255,.62);
    font-size: .72rem;
}

/* CTA */
.menu-cta {
    min-height: 430px;
    display: grid;
    place-items: center;
    text-align: center;
    background:
        linear-gradient(rgba(31,18,11,.78), rgba(31,18,11,.78)),
        url("image/hero.png") center/cover no-repeat;
}

.menu-cta-content {
    max-width: 700px;
    padding: 70px 20px;
}

.menu-cta-content h2 {
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2.4rem, 5vw, 4.4rem);
    line-height: 1;
    color: #fff;
    margin: 15px 0 18px;
}

.menu-cta-content h2 strong {
    color: #d8b892;
    font-style: italic;
    font-weight: 500;
}

.menu-cta-content p {
    max-width: 560px;
    margin: auto;
    color: rgba(255,255,255,.75);
    line-height: 1.8;
}

@media (max-width: 1050px) {
    .menu-products {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .nav-actions {
        display: none;
    }
}

@media (max-width: 760px) {
    .menu-hero {
        min-height: 500px;
    }

    .menu-hero-content {
        padding: 80px 25px;
    }

    .menu-products {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .menu-highlights-container {
        grid-template-columns: 1fr;
    }

    .menu-highlight {
        border-right: 0;
        border-bottom: 1px solid rgba(255,255,255,.14);
        padding: 12px 5px;
    }

    .menu-highlight:last-child {
        border-bottom: 0;
    }
}

@media (max-width: 520px) {
    .logo-text {
        font-size: 1.45rem;
    }

    .menu-section {
        padding: 65px 0 75px;
    }

    .menu-products {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .menu-product-image {
        height: 300px;
    }

    .menu-categories {
        gap: 7px;
    }

    .menu-category {
        min-width: 0;
        padding: 10px 14px;
    }

    .menu-heading-meta {
        font-size: .58rem;
    }
}


/* =========================================================
   SHARED CAFELIA GLASSMORPHISM ACCOUNT NAVIGATION
   ========================================================= */
/* =========================================================
   CAFELIA — PREMIUM GLASSMORPHISM ACCOUNT NAVIGATION
   ========================================================= */

.navbar {
    position: sticky;
    top: 0;
    z-index: 9999;
    border-bottom: 1px solid rgba(255, 255, 255, .08);
}

.nav-container {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    width: 100%;
}

.nav-menu {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    margin-left: auto;
}

.nav-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-left: 22px;
    flex-shrink: 0;
}

.account-menu {
    position: relative;
}

.account-trigger {
    appearance: none;
    -webkit-appearance: none;
    position: relative;
    min-height: 48px;
    padding: 5px 12px 5px 6px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(255, 255, 255, .16);
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(255,255,255,.105), rgba(255,255,255,.035));
    color: #fffaf3;
    font-family: inherit;
    cursor: pointer;
    backdrop-filter: blur(20px) saturate(140%);
    -webkit-backdrop-filter: blur(20px) saturate(140%);
    box-shadow:
        0 8px 26px rgba(0,0,0,.16),
        inset 0 1px 0 rgba(255,255,255,.12);
    transition: background .25s ease, border-color .25s ease,
                box-shadow .25s ease, transform .25s ease;
}

.account-trigger::before {
    content: "";
    position: absolute;
    inset: 1px;
    border-radius: inherit;
    background: linear-gradient(120deg, rgba(255,255,255,.08), transparent 38%, transparent 72%, rgba(255,255,255,.025));
    pointer-events: none;
}

.account-trigger:hover,
.account-trigger[aria-expanded="true"] {
    background: linear-gradient(135deg, rgba(255,255,255,.15), rgba(216,163,109,.07));
    border-color: rgba(216,163,109,.42);
    box-shadow:
        0 12px 32px rgba(0,0,0,.21),
        0 0 0 4px rgba(216,163,109,.045),
        inset 0 1px 0 rgba(255,255,255,.16);
    transform: translateY(-1px);
}

.profile-avatar,
.dropdown-avatar {
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border-radius: 50%;
    color: #fffaf3;
    background: linear-gradient(145deg, #c99968 0%, #9c623a 100%);
    border: 1px solid rgba(255,255,255,.30);
    box-shadow:
        0 4px 12px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.25);
    font-family: "Playfair Display", Georgia, serif;
    font-weight: 700;
}

.profile-avatar {
    width: 36px;
    height: 36px;
    font-size: 14px;
}

.profile-name {
    max-width: 140px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: rgba(255,250,243,.94);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .025em;
}

.profile-chevron {
    width: 20px;
    height: 20px;
    display: grid;
    place-items: center;
    color: rgba(255,250,243,.56);
    font-size: 14px;
    line-height: 1;
    transition: transform .22s ease, color .22s ease;
}

.account-trigger[aria-expanded="true"] .profile-chevron {
    color: #d8a36d;
    transform: rotate(180deg);
}

.account-dropdown {
    position: absolute;
    top: calc(100% + 14px);
    right: 0;
    width: 305px;
    padding: 10px;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.19);
    border-radius: 20px;
    background: linear-gradient(145deg, rgba(61,37,26,.82), rgba(29,18,13,.93));
    backdrop-filter: blur(26px) saturate(145%);
    -webkit-backdrop-filter: blur(26px) saturate(145%);
    box-shadow:
        0 26px 65px rgba(0,0,0,.34),
        0 8px 25px rgba(0,0,0,.14),
        inset 0 1px 0 rgba(255,255,255,.12);
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px) scale(.975);
    transform-origin: top right;
    pointer-events: none;
    transition: opacity .22s ease, visibility .22s ease,
                transform .22s cubic-bezier(.2,.8,.2,1);
}

.account-dropdown::after {
    content: "";
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 100% 0%, rgba(216,163,109,.11), transparent 34%),
        linear-gradient(135deg, rgba(255,255,255,.035), transparent 40%);
    pointer-events: none;
}

.account-dropdown::before {
    content: "";
    position: absolute;
    top: -7px;
    right: 28px;
    width: 14px;
    height: 14px;
    border-left: 1px solid rgba(255,255,255,.18);
    border-top: 1px solid rgba(255,255,255,.18);
    background: rgba(55,33,23,.92);
    transform: rotate(45deg);
}

.account-menu.open .account-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0) scale(1);
    pointer-events: auto;
}

.dropdown-profile {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 10px 14px;
}

.dropdown-avatar {
    width: 44px;
    height: 44px;
    font-size: 17px;
}

.dropdown-profile div {
    min-width: 0;
}

.dropdown-profile strong {
    display: block;
    color: #fffaf3;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-profile small {
    display: block;
    margin-top: 4px;
    color: rgba(255,250,243,.52);
    font-size: 10px;
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dropdown-divider {
    position: relative;
    z-index: 1;
    height: 1px;
    margin: 3px 5px 7px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.12), transparent);
}

.dropdown-item {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 10px;
    margin: 2px 0;
    border: 1px solid transparent;
    border-radius: 13px;
    color: #fffaf3;
    text-decoration: none;
    transition: background .18s ease, border-color .18s ease, transform .18s ease;
}

.dropdown-item:hover {
    background: rgba(255,255,255,.075);
    border-color: rgba(255,255,255,.06);
    transform: translateX(2px);
}

.dropdown-icon {
    width: 34px;
    height: 34px;
    display: grid;
    place-items: center;
    flex: 0 0 auto;
    border: 1px solid rgba(216,163,109,.20);
    border-radius: 10px;
    background: rgba(216,163,109,.085);
    color: #e0b17d;
    font-size: 14px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.05);
}

.dropdown-item strong {
    display: block;
    color: rgba(255,250,243,.94);
    font-size: 11px;
    font-weight: 700;
}

.dropdown-item small {
    display: block;
    margin-top: 3px;
    color: rgba(255,250,243,.45);
    font-size: 9px;
    line-height: 1.4;
}

.dropdown-logout {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    border-radius: 11px;
    color: rgba(255,225,217,.80);
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
    transition: background .18s ease, color .18s ease;
}

.dropdown-logout:hover {
    background: rgba(169,71,55,.13);
    color: #ffd8cf;
}

.nav-cart {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 44px;
    padding: 0 17px;
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 999px;
    background: rgba(255,255,255,.055);
    color: rgba(255,250,243,.92);
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .14em;
    box-shadow:
        0 8px 22px rgba(0,0,0,.12),
        inset 0 1px 0 rgba(255,255,255,.10);
    backdrop-filter: blur(14px) saturate(135%);
    -webkit-backdrop-filter: blur(14px) saturate(135%);
    transition: all .22s ease;
}

.nav-cart:hover {
    border-color: rgba(216,163,109,.42);
    background: rgba(216,163,109,.10);
    color: #fffaf3;
    transform: translateY(-1px);
    box-shadow:
        0 11px 27px rgba(0,0,0,.18),
        inset 0 1px 0 rgba(255,255,255,.14);
}

.cart-icon {
    font-size: 14px;
    line-height: 1;
    filter: saturate(.75);
}

.account-login-icon {
    min-width: 48px;
    padding: 5px 6px;
    text-decoration: none;
}

.account-login-icon .profile-avatar-guest {
    width: 36px;
    height: 36px;
    font-family: Arial, sans-serif;
    font-size: 19px;
    line-height: 1;
}

.account-login-icon:hover {
    text-decoration: none;
}

.nav-login {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 20px;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 999px;
    background: linear-gradient(135deg, rgba(185,130,82,.96), rgba(145,88,51,.96));
    color: #fffaf3;
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .14em;
    box-shadow:
        0 8px 22px rgba(0,0,0,.15),
        inset 0 1px 0 rgba(255,255,255,.18);
    transition: all .22s ease;
}

.nav-login:hover {
    transform: translateY(-1px);
    box-shadow:
        0 11px 27px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.20);
}

@media (max-width: 900px) {
    .nav-cart {
        min-height: 42px;
        padding: 0 12px;
        font-size: 9px;
    }


    .account-trigger {
        min-height: 43px;
        padding-right: 8px;
    }

    .profile-name {
        display: none;
    }

    .profile-chevron {
        margin-left: 1px;
    }

    .account-dropdown {
        right: -5px;
        width: min(305px, calc(100vw - 28px));
    }
}

@media (max-width: 700px) {
    .account-dropdown {
        top: calc(100% + 10px);
    }
}

@media (max-width: 480px) {
    .account-dropdown {
        width: min(292px, calc(100vw - 24px));
    }
}

/* Keep cart + account visible on the menu page. */
@media (max-width: 1050px) {
    .nav-actions {
        display: flex !important;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-left: auto;
        flex-shrink: 0;
    }
}

</style>

</head>

<body>

<!-- =========================================================
     NAVIGATION
========================================================= -->

<header class="navbar">

    <div class="nav-container">

        <a href="index.php" class="logo" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php">HOME</a>
            <a href="menu.php" class="active">MENU</a>
            <a href="about.php">ABOUT US</a>
            <a href="join_team.php">JOIN OUR TEAM</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <div class="nav-actions">

            <a href="cart.php" class="nav-cart" aria-label="Open shopping cart">
                <span class="cart-icon">🛒</span>
                <span>CART</span>
            </a>

            <div class="account-menu">

                <?php if (isset($_SESSION["user_id"])): ?>

                    <button type="button"
                            class="account-trigger"
                            aria-expanded="false"
                            aria-haspopup="true"
                            onclick="toggleAccountMenu(this)">
                        <span class="profile-avatar">
                            <?php
                                $nav_name = trim($_SESSION["user_name"] ?? "User");
                                echo htmlspecialchars(strtoupper(substr($nav_name, 0, 1)));
                            ?>
                        </span>

                        <span class="profile-name">
                            <?php echo htmlspecialchars($_SESSION["user_name"] ?? "Profile"); ?>
                        </span>
                    </button>

                    <div class="account-dropdown">

                        <div class="dropdown-profile">
                            <span class="dropdown-avatar">
                                <?php echo htmlspecialchars(strtoupper(substr($nav_name, 0, 1))); ?>
                            </span>

                            <div>
                                <strong><?php echo htmlspecialchars($nav_name); ?></strong>
                                <small>
                                    <?php echo htmlspecialchars($_SESSION["user_email"] ?? "Cafelia Member"); ?>
                                </small>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <a href="cart.php" class="dropdown-item">
                            <span class="dropdown-icon">🛒</span>
                            <span>
                                <strong>Cart</strong>
                                <small>View your selected items</small>
                            </span>
                        </a>

                        <a href="join_team.php" class="dropdown-item">
                            <span class="dropdown-icon">✦</span>
                            <span>
                                <strong>Join Our Team</strong>
                                <small>Explore opportunities at Cafelia</small>
                            </span>
                        </a>

                        <a href="profile.php" class="dropdown-item">
                            <span class="dropdown-icon">♙</span>
                            <span>
                                <strong>Profile</strong>
                                <small>Manage your Cafelia account</small>
                            </span>
                        </a>

                        <?php if (($_SESSION["user_role"] ?? "") === "admin"): ?>
                            <div class="dropdown-divider"></div>
                            <a href="admin/dashboard.php" class="dropdown-item">
                                <span class="dropdown-icon">⌘</span>
                                <span>
                                    <strong>Admin Dashboard</strong>
                                    <small>Manage Cafelia</small>
                                </span>
                            </a>
                        <?php endif; ?>

                        <div class="dropdown-divider"></div>

                        <a href="logout.php" class="dropdown-logout">
                            <span>↪</span>
                            Log Out
                        </a>

                    </div>

                <?php else: ?>

                    <a href="login.php"
                       class="account-trigger account-login-icon"
                       aria-label="Login to Cafelia">
                        <span class="profile-avatar profile-avatar-guest">♙</span>
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</header>

<!-- =========================================================
     MENU HERO
========================================================= -->

<section class="menu-hero">

    <div class="menu-hero-overlay"></div>

    <div class="menu-hero-content">

        <span>
            CAFELIA MENU
        </span>

        <h1>
            Something
            <br>
            <strong>Good Is Brewing.</strong>
        </h1>

        <p>
            From freshly brewed coffee to delicious treats,
            find something made just for your moment.
        </p>

    </div>

</section>

<!-- =========================================================
     MENU SECTION
========================================================= -->

<section class="menu-section">

    <div class="menu-container">

        <!-- =================================================
             MENU HEADER
        ================================================== -->

        <div class="menu-heading">

            <span>
                OUR MENU
            </span>

            <h2>
                Choose Your Favorite
            </h2>

            <p>
                Explore our selection of carefully prepared
                drinks and treats, made for every kind of coffee moment.
            </p>

            <div class="menu-heading-meta">
                <span class="menu-rule"></span>
                <span><?php echo count($products); ?> ITEMS AVAILABLE</span>
                <span class="menu-rule"></span>
            </div>

        </div>

        <!-- =================================================
             CATEGORY FILTER
        ================================================== -->

        <div class="menu-categories">

            <button
                type="button"
                class="menu-category active"
                onclick="filterMenu('all', this)"
            >
                ALL
            </button>

            <?php foreach ($categories as $category): ?>

                <button
                    type="button"
                    class="menu-category"
                    onclick="filterMenu(
                        '<?php echo htmlspecialchars($category, ENT_QUOTES); ?>',
                        this
                    )"
                >

                    <?php
                    echo htmlspecialchars(
                        strtoupper($category)
                    );
                    ?>

                </button>

            <?php endforeach; ?>

        </div>

        <!-- =================================================
             PRODUCTS
        ================================================== -->

        <div class="menu-products">

            <?php if (!empty($products)): ?>

                <?php foreach ($products as $product): ?>

                    <?php

                    $product_image = !empty($product['image'])
                        ? basename($product['image'])
                        : '';

                    ?>

                    <article
                        class="menu-product-card"
                        data-category="<?php
                            echo htmlspecialchars(
                                $product['category'],
                                ENT_QUOTES
                            );
                        ?>"
                    >

                        <!-- IMAGE -->

                        <div class="menu-product-image">

                            <?php if ($product_image !== ''): ?>

                                <img
                                    src="image/<?php
                                        echo htmlspecialchars(
                                            $product_image
                                        );
                                    ?>"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $product['name']
                                        );
                                    ?>"
                                >

                            <?php else: ?>

                                <div class="menu-product-placeholder">
                                    ☕
                                </div>

                            <?php endif; ?>

                            <!-- CATEGORY -->

                            <span class="menu-product-category">

                                <?php
                                echo htmlspecialchars(
                                    $product['category']
                                );
                                ?>

                            </span>

                        </div>

                        <!-- PRODUCT INFORMATION -->

                        <div class="menu-product-info">

                            <span class="menu-product-kicker">
                                CAFELIA FAVORITE
                            </span>

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $product['name']
                                );
                                ?>

                            </h3>

                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $product['description'] ?? ''
                                );
                                ?>

                            </p>

                            <div class="menu-product-bottom">

                                <span class="menu-product-price">

                                    ₱<?php
                                    echo number_format(
                                        (float)$product['price'],
                                        2
                                    );
                                    ?>

                                </span>

                                <a
                                    href="cart.php?action=add&amp;product_id=<?php echo (int) $product['id']; ?>&amp;quantity=1"
                                    class="menu-add-button"
                                >
                                    ADD TO CART
                                    <span aria-hidden="true">+</span>
                                </a>

                            </div>

                        </div>

                    </article>

                <?php endforeach; ?>

            <?php else: ?>

                <div class="menu-empty">

                    <div class="menu-empty-icon">
                        ☕
                    </div>

                    <h3>
                        No Products Available
                    </h3>

                    <p>
                        Our menu is currently being prepared.
                        Please check back soon.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</section>

<!-- =========================================================
     MENU HIGHLIGHTS
========================================================= -->

<section class="menu-highlights">
    <div class="menu-highlights-container">

        <div class="menu-highlight">
            <span class="menu-highlight-icon">✦</span>
            <div>
                <strong>Freshly Prepared</strong>
                <small>Made with care for every order</small>
            </div>
        </div>

        <div class="menu-highlight">
            <span class="menu-highlight-icon">☕</span>
            <div>
                <strong>Quality Coffee</strong>
                <small>Rich flavors in every cup</small>
            </div>
        </div>

        <div class="menu-highlight">
            <span class="menu-highlight-icon">♡</span>
            <div>
                <strong>Made for Moments</strong>
                <small>A warm place to slow down</small>
            </div>
        </div>

    </div>
</section>

<!-- =========================================================
     MENU CTA
========================================================= -->

<section class="menu-cta">

    <div class="menu-cta-container">

        <div class="menu-cta-content">

            <span>
                YOUR NEXT COFFEE MOMENT
            </span>

            <h2>
                Good Coffee.
                <br>
                <strong>Good Moments.</strong>
            </h2>

            <p>
                Whether you're grabbing a quick coffee or
                staying for a while, Cafelia is always ready
                to make your day a little better.
            </p>

            <a
                href="contact.php"
                class="menu-cta-button"
            >
                VISIT CAFELIA
                <span>→</span>
            </a>

        </div>

    </div>

</section>

<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="footer">

    <div class="footer-container">

        <!-- BRAND -->

        <div class="footer-brand">

            <a
                href="index.php"
                class="footer-logo"
                aria-label="Cafelia Home"
            >
                <span class="footer-logo-text">CAFELIA</span>
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

        <!-- MENU -->

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

        <!-- QUICK LINKS -->

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

            <a href="contact.php">
                JOIN OUR TEAM
            </a>

            <a href="contact.php">
                CONTACT
            </a>

        </div>

        <!-- APP -->

        <div class="footer-column footer-app">

            <h3>
                GET THE APP
            </h3>

            <a
                href="#"
                class="app-button"
            >
                GET IT ON

                <strong>
                    Google Play
                </strong>

            </a>

            <a
                href="#"
                class="app-button"
            >
                Download on the

                <strong>
                    App Store
                </strong>

            </a>

        </div>

    </div>

    <!-- FOOTER BOTTOM -->

    <div class="footer-bottom">

        <p>
            © <?php echo date("Y"); ?> Cafelia.
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

<!-- =========================================================
     MENU FILTER JAVASCRIPT
========================================================= -->

<script>

function filterMenu(category, button) {

    const products =
        document.querySelectorAll(
            ".menu-product-card"
        );

    const buttons =
        document.querySelectorAll(
            ".menu-category"
        );

    /* Remove active state */

    buttons.forEach(function(btn) {

        btn.classList.remove("active");

    });

    /* Add active state */

    button.classList.add("active");

    /* Filter products */

    products.forEach(function(product) {

        const productCategory =
            product.getAttribute(
                "data-category"
            );

        if (
            category === "all" ||
            productCategory === category
        ) {

            product.style.display = "";

        } else {

            product.style.display = "none";

        }

    });

}

</script>


<script>
function toggleAccountMenu(button) {
    const menu = button.closest(".account-menu");
    const isOpen = menu.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen ? "true" : "false");
}

document.addEventListener("click", function (event) {
    document.querySelectorAll(".account-menu.open").forEach(function (menu) {
        if (!menu.contains(event.target)) {
            menu.classList.remove("open");
            const button = menu.querySelector(".account-trigger");
            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        }
    });
});
</script>

</body>
</html>