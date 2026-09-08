<?php
/* =========================================================
   CAFELIA ADMIN SHARED SIDEBAR
   ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   ADMIN ACCESS
   ========================================================= */

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   ADMIN ACCOUNT
   ========================================================= */

$admin_name = $_SESSION["admin_name"] ?? "Administrator";

$admin_initial = strtoupper(
    substr(
        trim($admin_name ?: "A"),
        0,
        1
    )
);


/* =========================================================
   CURRENT ADMIN PAGE
   ========================================================= */

$active_admin_page = $active_admin_page
    ?? basename($_SERVER["PHP_SELF"], ".php");

?>

<style>

/* =========================================================
   CAFELIA ADMIN SIDEBAR
   ========================================================= */

:root {
    --admin-sidebar-width: 260px;

    --admin-dark: #281913;
    --admin-dark-2: #342019;

    --admin-cream: #f7f0e7;
    --admin-muted: #a99889;

    --admin-gold: #c99a68;
    --admin-gold-light: #d8b28b;

    --admin-border: rgba(255,255,255,.09);
}


/* =========================================================
   SIDEBAR
   ========================================================= */

.admin-sidebar {
    position: fixed;

    top: 0;
    left: 0;
    bottom: 0;

    width: var(--admin-sidebar-width);

    display: flex;
    flex-direction: column;

    box-sizing: border-box;

    padding: 28px 18px 20px;

    background:
        linear-gradient(
            180deg,
            #281913 0%,
            #241711 100%
        );

    border-right:
        1px solid
        rgba(255,255,255,.08);

    color: #fff;

    z-index: 9999;

    overflow-y: auto;

    scrollbar-width: thin;
}


/* =========================================================
   MAIN LOGO
   ========================================================= */

.admin-sidebar-brand {
    display: flex;

    align-items: center;
    justify-content: center;

    width: 100%;

    padding: 8px 8px 25px;

    box-sizing: border-box;

    text-decoration: none;

    border-bottom:
        1px solid
        var(--admin-border);
}


/*
|--------------------------------------------------------------------------
| logo.png is the PRIMARY ADMIN LOGO
|--------------------------------------------------------------------------
*/

.admin-sidebar-brand img {
    display: block;

    width: 175px;
    max-width: 100%;
    height: auto;

    object-fit: contain;

    transition:
        transform .25s ease,
        opacity .25s ease;
}

.admin-sidebar-brand:hover img {
    transform: scale(1.025);

    opacity: .95;
}


/* =========================================================
   ADMIN PROFILE
   ========================================================= */

.admin-sidebar-profile {
    display: flex;

    align-items: center;

    gap: 11px;

    margin: 22px 4px 25px;

    padding: 13px 12px;

    border:
        1px solid
        rgba(255,255,255,.08);

    border-radius: 14px;

    background:
        rgba(255,255,255,.035);
}


.admin-sidebar-avatar {
    width: 42px;
    height: 42px;

    display: grid;

    place-items: center;

    flex-shrink: 0;

    border-radius: 50%;

    background:
        linear-gradient(
            145deg,
            #d4a16d,
            #9b6945
        );

    border:
        1px solid
        rgba(255,255,255,.25);

    color: #fff;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 16px;

    font-weight: 700;
}


.admin-sidebar-profile-info {
    min-width: 0;
}


.admin-sidebar-profile-info strong {
    display: block;

    overflow: hidden;

    color: #fff;

    font-size: 12px;

    font-weight: 800;

    white-space: nowrap;

    text-overflow: ellipsis;
}


.admin-sidebar-profile-info span {
    display: block;

    margin-top: 3px;

    color:
        rgba(255,255,255,.43);

    font-size: 9px;

    font-weight: 700;

    letter-spacing: 1px;

    text-transform: uppercase;
}


/* =========================================================
   SECTION LABEL
   ========================================================= */

.admin-sidebar-label {
    margin: 0 11px 9px;

    color:
        rgba(255,255,255,.34);

    font-size: 9px;

    font-weight: 800;

    letter-spacing: 1.7px;

    text-transform: uppercase;
}


/* =========================================================
   NAVIGATION
   ========================================================= */

.admin-sidebar-nav {
    display: flex;

    flex-direction: column;

    gap: 5px;
}


.admin-sidebar-nav a {
    position: relative;

    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 48px;

    padding: 0 13px;

    box-sizing: border-box;

    border-radius: 12px;

    color:
        rgba(255,255,255,.66);

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

    letter-spacing: .25px;

    transition:
        background .2s ease,
        color .2s ease,
        transform .2s ease;
}


.admin-sidebar-nav a:hover {
    background:
        rgba(255,255,255,.055);

    color: #fff;

    transform: translateX(2px);
}


/* =========================================================
   NAV ICON
   ========================================================= */

.admin-nav-icon {
    width: 31px;
    height: 31px;

    display: grid;

    place-items: center;

    flex-shrink: 0;

    border-radius: 9px;

    background:
        rgba(255,255,255,.045);

    color:
        rgba(255,255,255,.58);

    font-size: 14px;

    transition: .2s ease;
}


.admin-sidebar-nav a:hover .admin-nav-icon {
    background:
        rgba(201,154,104,.12);

    color:
        var(--admin-gold-light);
}


/* =========================================================
   ACTIVE NAVIGATION
   ========================================================= */

.admin-sidebar-nav a.active {
    background:
        linear-gradient(
            90deg,
            rgba(201,154,104,.18),
            rgba(201,154,104,.07)
        );

    color:
        #e1bc91;

    box-shadow:
        inset 0 0 0 1px
        rgba(201,154,104,.08);
}


.admin-sidebar-nav a.active::before {
    content: "";

    position: absolute;

    left: 0;
    top: 9px;
    bottom: 9px;

    width: 3px;

    border-radius: 3px;

    background:
        var(--admin-gold);
}


.admin-sidebar-nav a.active .admin-nav-icon {
    background:
        rgba(201,154,104,.16);

    color:
        var(--admin-gold-light);
}


/* =========================================================
   SIDEBAR BOTTOM
   ========================================================= */

.admin-sidebar-bottom {
    margin-top: auto;

    padding-top: 22px;

    border-top:
        1px solid
        var(--admin-border);
}


.admin-sidebar-bottom a {
    display: flex;

    align-items: center;

    gap: 12px;

    min-height: 46px;

    padding: 0 13px;

    box-sizing: border-box;

    border-radius: 12px;

    color:
        rgba(255,255,255,.60);

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;

    transition: .2s ease;
}


.admin-sidebar-bottom a:hover {
    background:
        rgba(255,255,255,.055);

    color: #fff;
}


.admin-sidebar-bottom .logout {
    color:
        #dba89d;
}


.admin-sidebar-bottom .logout:hover {
    background:
        rgba(169,71,55,.12);

    color:
        #efbcb2;
}


/* =========================================================
   MAIN CONTENT OFFSET
   ========================================================= */

.admin-page-content {
    margin-left:
        var(--admin-sidebar-width);

    min-height: 100vh;

    box-sizing: border-box;
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 900px) {

    :root {
        --admin-sidebar-width: 220px;
    }

    .admin-sidebar {
        width:
            var(--admin-sidebar-width);

        padding:
            22px 14px 16px;
    }

    .admin-sidebar-brand img {
        width: 150px;
    }

    .admin-sidebar-nav a {
        font-size: 11px;
    }
}


@media (max-width: 700px) {

    .admin-sidebar {
        position: relative;

        width: 100%;

        height: auto;

        min-height: auto;

        padding: 18px;

        border-right: none;

        border-bottom:
            1px solid
            rgba(255,255,255,.08);
    }

    .admin-sidebar-brand {
        padding-bottom: 18px;
    }

    .admin-sidebar-brand img {
        width: 165px;
    }

    .admin-sidebar-profile {
        margin:
            15px 0 17px;
    }

    .admin-sidebar-label {
        margin-top: 5px;
    }

    .admin-sidebar-nav {
        display: grid;

        grid-template-columns:
            repeat(2, 1fr);

        gap: 7px;
    }

    .admin-sidebar-bottom {
        margin-top: 12px;

        padding-top: 12px;

        display: grid;

        grid-template-columns:
            repeat(2, 1fr);

        gap: 7px;
    }

    .admin-page-content {
        margin-left: 0;
    }
}


@media (max-width: 450px) {

    .admin-sidebar-brand img {
        width: 145px;
    }

    .admin-sidebar-nav {
        grid-template-columns: 1fr;
    }

    .admin-sidebar-bottom {
        grid-template-columns: 1fr;
    }
}

</style>


<!-- =========================================================
     CAFELIA ADMIN SIDEBAR
========================================================= -->

<aside class="admin-sidebar">


    <!-- =====================================================
         MAIN LOGO
    ====================================================== -->

    <a
        href="dashboard.php"
        class="admin-sidebar-brand"
        aria-label="Cafelia Admin Dashboard"
    >

        <img
            src="../image/logo.png"
            alt="Cafelia Logo"
        >

    </a>


    <!-- =====================================================
         ADMIN PROFILE
    ====================================================== -->

    <div class="admin-sidebar-profile">

        <div class="admin-sidebar-avatar">

            <?php
            echo htmlspecialchars(
                $admin_initial
            );
            ?>

        </div>


        <div class="admin-sidebar-profile-info">

            <strong>

                <?php
                echo htmlspecialchars(
                    $admin_name
                );
                ?>

            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>


    <!-- =====================================================
         MAIN MENU
    ====================================================== -->

    <div class="admin-sidebar-label">
        Management
    </div>


    <nav
        class="admin-sidebar-nav"
        aria-label="Admin navigation"
    >


        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="<?php
                echo $active_admin_page === 'dashboard'
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ⌂
            </span>

            <span>
                Dashboard
            </span>

        </a>


        <!-- PRODUCTS -->

        <a
            href="products.php"
            class="<?php
                echo in_array(
                    $active_admin_page,
                    [
                        'products',
                        'add-product',
                        'edit-product'
                    ],
                    true
                )
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ☕
            </span>

            <span>
                Products
            </span>

        </a>


        <!-- ORDERS -->

        <a
            href="orders.php"
            class="<?php
                echo $active_admin_page === 'orders'
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ▣
            </span>

            <span>
                Orders
            </span>

        </a>


        <!-- CUSTOMERS -->

        <a
            href="customers.php"
            class="<?php
                echo $active_admin_page === 'customers'
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ♙
            </span>

            <span>
                Customers
            </span>

        </a>


        <!-- MESSAGES -->

        <a
            href="messages.php"
            class="<?php
                echo $active_admin_page === 'messages'
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ✉
            </span>

            <span>
                Messages
            </span>

        </a>


        <!-- REPORTS -->

        <a
            href="reports.php"
            class="<?php
                echo $active_admin_page === 'reports'
                    ? 'active'
                    : '';
            ?>"
        >

            <span class="admin-nav-icon">
                ▤
            </span>

            <span>
                Reports
            </span>

        </a>

    </nav>


    <!-- =====================================================
         BOTTOM MENU
    ====================================================== -->

    <div class="admin-sidebar-bottom">


        <!-- VIEW WEBSITE -->

        <a href="../index.php">

            <span class="admin-nav-icon">
                ↗
            </span>

            <span>
                View Website
            </span>

        </a>


        <!-- LOGOUT -->

        <a
            href="../logout.php"
            class="logout"
        >

            <span class="admin-nav-icon">
                ⇥
            </span>

            <span>
                Logout
            </span>

        </a>

    </div>

</aside>