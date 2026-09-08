<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| ADMIN ACCESS
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ADMIN INFORMATION
|--------------------------------------------------------------------------
*/
$admin_name = $_SESSION["admin_name"] ?? "Administrator";
$admin_initial = strtoupper(
    substr(trim($admin_name !== "" ? $admin_name : "A"), 0, 1)
);

$active_admin_page = $active_admin_page
    ?? basename($_SERVER["PHP_SELF"], ".php");

/*
|--------------------------------------------------------------------------
| ACTIVE PAGE HELPERS
|--------------------------------------------------------------------------
*/
$products_pages = [
    "products",
    "add-product",
    "edit-product",
    "add-stock"
];

function admin_active(
    string $page,
    string $active_admin_page
): string {
    return $active_admin_page === $page ? "active" : "";
}

function admin_products_active(
    string $active_admin_page,
    array $products_pages
): string {
    return in_array(
        $active_admin_page,
        $products_pages,
        true
    ) ? "active" : "";
}
?>

<style>

/* =========================================================
   CAFELIA ADMIN SIDEBAR
========================================================= */

:root {
    --admin-sidebar-width: 260px;
    --admin-sidebar-bg: #281913;
    --admin-sidebar-card: rgba(255, 255, 255, .035);
    --admin-sidebar-border: rgba(255, 255, 255, .10);
    --admin-sidebar-text: rgba(255, 255, 255, .68);
    --admin-sidebar-white: #fffaf4;
    --admin-sidebar-accent: #c99a68;
}


/* ---------------------------------------------------------
   SIDEBAR
--------------------------------------------------------- */

.admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 9999;

    width: var(--admin-sidebar-width);
    height: 100vh;

    display: flex;
    flex-direction: column;

    padding: 27px 22px 24px;

    background:
        linear-gradient(
            180deg,
            #281913 0%,
            #241711 100%
        );

    border-right: 1px solid rgba(255, 255, 255, .08);

    box-shadow:
        12px 0 35px rgba(22, 12, 7, .08);

    overflow-y: auto;
    overflow-x: hidden;
}


/* ---------------------------------------------------------
   LOGO
--------------------------------------------------------- */

.admin-sidebar-brand {
    width: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    min-height: 128px;

    padding: 5px 0 18px;

    text-decoration: none;

    border-bottom: 1px solid rgba(255, 255, 255, .09);
}

.admin-sidebar-brand img {
    display: block;

    width: 175px;
    max-width: 100%;
    height: auto;

    object-fit: contain;
}


/* ---------------------------------------------------------
   ADMIN PROFILE
--------------------------------------------------------- */

.admin-sidebar-profile {
    display: flex;
    align-items: center;
    gap: 13px;

    margin: 28px 5px 29px;
    padding: 11px;

    border: 1px solid var(--admin-sidebar-border);
    border-radius: 16px;

    background: var(--admin-sidebar-card);
}

.admin-profile-avatar {
    width: 52px;
    height: 52px;

    flex: 0 0 52px;

    display: grid;
    place-items: center;

    border-radius: 50%;

    background:
        linear-gradient(
            145deg,
            #d4a16d,
            #9b6945
        );

    border: 1px solid rgba(255, 255, 255, .28);

    color: #fff;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 20px;
    font-weight: 700;

    box-shadow:
        inset 0 1px 2px rgba(255, 255, 255, .20),
        0 4px 12px rgba(0, 0, 0, .16);
}

.admin-profile-info {
    min-width: 0;
}

.admin-profile-name {
    overflow: hidden;

    color: var(--admin-sidebar-white);

    font-size: 13px;
    font-weight: 800;

    white-space: nowrap;
    text-overflow: ellipsis;
}

.admin-profile-role {
    margin-top: 4px;

    color: rgba(255, 255, 255, .40);

    font-size: 9px;
    font-weight: 800;

    letter-spacing: .11em;
    text-transform: uppercase;
}


/* ---------------------------------------------------------
   SECTION LABEL
--------------------------------------------------------- */

.admin-sidebar-label {
    margin: 0 13px 13px;

    color: rgba(255, 255, 255, .35);

    font-size: 10px;
    font-weight: 800;

    letter-spacing: .16em;
    text-transform: uppercase;
}


/* ---------------------------------------------------------
   NAVIGATION
--------------------------------------------------------- */

.admin-sidebar-nav {
    display: flex;
    flex-direction: column;

    gap: 8px;

    padding: 0 1px;
}

.admin-sidebar-nav a {
    min-height: 52px;

    display: flex;
    align-items: center;
    gap: 14px;

    padding: 0 13px;

    border: 1px solid transparent;
    border-radius: 13px;

    color: var(--admin-sidebar-text);

    font-size: 13px;
    font-weight: 800;

    text-decoration: none;

    transition:
        background .2s ease,
        color .2s ease,
        border-color .2s ease,
        transform .2s ease;
}

.admin-sidebar-nav a:hover {
    color: #fff;

    background:
        rgba(255, 255, 255, .045);

    transform: translateX(2px);
}

.admin-sidebar-nav a.active {
    color: #e7c398;

    background:
        rgba(201, 154, 104, .12);

    border-color:
        rgba(201, 154, 104, .12);

    box-shadow:
        inset 3px 0 0 #c99a68;
}

.admin-nav-icon {
    width: 39px;
    height: 39px;

    flex: 0 0 39px;

    display: grid;
    place-items: center;

    border-radius: 11px;

    background:
        rgba(255, 255, 255, .045);

    color: rgba(255, 255, 255, .68);

    font-size: 15px;
    line-height: 1;

    transition:
        background .2s ease,
        color .2s ease;
}

.admin-sidebar-nav a:hover .admin-nav-icon,
.admin-sidebar-nav a.active .admin-nav-icon {
    background:
        rgba(201, 154, 104, .14);

    color: #d7ad7d;
}

.admin-sidebar-nav a.active .admin-nav-icon {
    color: #e2bd91;
}


/* ---------------------------------------------------------
   BOTTOM LINKS
--------------------------------------------------------- */

.admin-sidebar-bottom {
    margin-top: auto;

    padding-top: 20px;

    border-top: 1px solid
        rgba(255, 255, 255, .09);

    display: flex;
    flex-direction: column;
    gap: 8px;
}

.admin-sidebar-bottom a {
    min-height: 50px;

    display: flex;
    align-items: center;
    gap: 14px;

    padding: 0 13px;

    border-radius: 13px;

    color: rgba(255, 255, 255, .58);

    font-size: 12px;
    font-weight: 800;

    text-decoration: none;

    transition:
        background .2s ease,
        color .2s ease;
}

.admin-sidebar-bottom a:hover {
    background: rgba(255, 255, 255, .045);
    color: #fff;
}

.admin-sidebar-bottom a.logout {
    color: #d79b91;
}

.admin-sidebar-bottom a.logout:hover {
    background: rgba(188, 83, 70, .08);
    color: #e4aaa1;
}


/* ---------------------------------------------------------
   MAIN CONTENT COMPATIBILITY
--------------------------------------------------------- */

.admin-page-content,
.main,
.main-content {
    margin-left: var(--admin-sidebar-width);
}


/* ---------------------------------------------------------
   SCROLLBAR
--------------------------------------------------------- */

.admin-sidebar::-webkit-scrollbar {
    width: 5px;
}

.admin-sidebar::-webkit-scrollbar-track {
    background: transparent;
}

.admin-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, .10);
    border-radius: 10px;
}


/* ---------------------------------------------------------
   RESPONSIVE
--------------------------------------------------------- */

@media (max-width: 900px) {

    :root {
        --admin-sidebar-width: 225px;
    }

    .admin-sidebar {
        width: var(--admin-sidebar-width);
        padding: 24px 17px 20px;
    }

    .admin-sidebar-brand {
        min-height: 110px;
    }

    .admin-sidebar-brand img {
        width: 150px;
    }

    .admin-sidebar-profile {
        margin-top: 22px;
        margin-bottom: 23px;
    }

    .admin-sidebar-nav a,
    .admin-sidebar-bottom a {
        min-height: 48px;
        font-size: 12px;
    }

    .admin-nav-icon {
        width: 36px;
        height: 36px;
        flex-basis: 36px;
    }

}


@media (max-width: 700px) {

    .admin-sidebar {
        position: relative;

        width: 100%;
        height: auto;

        min-height: auto;

        padding: 20px 15px;
    }

    .admin-sidebar-brand {
        min-height: 90px;
    }

    .admin-sidebar-brand img {
        width: 165px;
    }

    .admin-sidebar-profile {
        margin: 17px 3px;
    }

    .admin-sidebar-nav {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .admin-sidebar-label {
        margin-top: 12px;
    }

    .admin-sidebar-bottom {
        margin-top: 20px;
    }

    .admin-page-content,
    .main,
    .main-content {
        margin-left: 0 !important;
        width: 100%;
    }

}


@media (max-width: 450px) {

    .admin-sidebar-nav {
        grid-template-columns: 1fr;
    }

    .admin-sidebar-brand img {
        width: 145px;
    }

}

</style>


<aside class="admin-sidebar">

    <!-- =====================================================
         LOGO
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

        <div class="admin-profile-avatar">
            <?= htmlspecialchars(
                $admin_initial,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </div>

        <div class="admin-profile-info">

            <div class="admin-profile-name">
                <?= htmlspecialchars(
                    $admin_name,
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </div>

            <div class="admin-profile-role">
                Administrator
            </div>

        </div>

    </div>


    <!-- =====================================================
         MANAGEMENT
    ====================================================== -->

    <div class="admin-sidebar-label">
        Management
    </div>


    <nav class="admin-sidebar-nav">

        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="<?= admin_active(
                "dashboard",
                $active_admin_page
            ) ?>"
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
            class="<?= admin_products_active(
                $active_admin_page,
                $products_pages
            ) ?>"
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
            class="<?= admin_active(
                "orders",
                $active_admin_page
            ) ?>"
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
            class="<?= admin_active(
                "customers",
                $active_admin_page
            ) ?>"
        >

            <span class="admin-nav-icon">
                ♟
            </span>

            <span>
                Customers
            </span>

        </a>


        <!-- MESSAGES -->

        <a
            href="messages.php"
            class="<?= admin_active(
                "messages",
                $active_admin_page
            ) ?>"
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
            class="<?= admin_active(
                "reports",
                $active_admin_page
            ) ?>"
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
         BOTTOM LINKS
    ====================================================== -->

    <div class="admin-sidebar-bottom">

        <a href="../index.php">

            <span class="admin-nav-icon">
                ↗
            </span>

            <span>
                View Website
            </span>

        </a>


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
