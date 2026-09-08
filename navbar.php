<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_admin = isset($_SESSION["admin_id"]);
$is_customer = isset($_SESSION["user_id"]);

$account_name = "";

if ($is_admin) {
    $account_name = $_SESSION["admin_name"] ?? "Administrator";
} elseif ($is_customer) {
    $account_name = $_SESSION["user_name"] ?? "Customer";
}

$account_initial = strtoupper(substr(trim($account_name), 0, 1));

$active_page = $active_page ?? basename($_SERVER["PHP_SELF"], ".php");

$cart_count = 0;

if ($is_customer && isset($conn) && $conn instanceof mysqli) {
    $cart_stmt = $conn->prepare(
        "SELECT COALESCE(SUM(quantity), 0) AS cart_count
         FROM cart
         WHERE user_id = ?"
    );

    if ($cart_stmt) {
        $cart_stmt->bind_param("i", $_SESSION["user_id"]);
        $cart_stmt->execute();

        $cart_result = $cart_stmt->get_result();

        if ($cart_result) {
            $cart_row = $cart_result->fetch_assoc();
            $cart_count = (int)($cart_row["cart_count"] ?? 0);
        }

        $cart_stmt->close();
    }
}

?>

<style>

/* =========================================================
   CAFELIA MASTER NAVBAR
   ========================================================= */

.navbar {
    position: sticky;
    top: 0;
    z-index: 9999;
    width: 100%;

    background: #281913;

    border-bottom: 1px solid rgba(255,255,255,.10);

    box-shadow: none;

    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
}


/* =========================================================
   NAV CONTAINER
   ========================================================= */

.navbar .nav-container {
    width: min(1630px, calc(100% - 48px));
    max-width: 1630px;

    min-height: 106px;

    margin: 0 auto;
    padding: 0;

    display: flex;
    align-items: center;
    justify-content: space-between;
}


/* =========================================================
   LOGO
   ========================================================= */

.navbar .logo {
    width: auto;
    min-width: 145px;

    margin-right: auto;

    display: inline-flex;
    align-items: center;

    text-decoration: none;
}

.navbar .logo img {
    display: block;

    width: 160px;
    height: auto;

    object-fit: contain;
}

.navbar .logo-text {
    display: inline-block;

    color: #f5eee5;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 38px;
    font-weight: 700;

    line-height: 1;

    letter-spacing: 3px;

    white-space: nowrap;
}

.navbar .logo:hover .logo-text {
    color: #d8b892;
}


/* =========================================================
   NAVIGATION
   ========================================================= */

.navbar .nav-menu {
    display: flex;
    align-items: center;

    gap: 38px;

    margin-left: auto;
    margin-right: 62px;
}

.navbar .nav-menu a {
    position: relative;

    padding: 39px 0 22px;

    color: rgba(255,255,255,.78);

    background: transparent;
    border: 0;

    font-family:
        "DM Sans",
        Arial,
        sans-serif;

    font-size: 13px;
    font-weight: 800;

    letter-spacing: 1.25px;
    line-height: 1;

    text-decoration: none;
    text-transform: uppercase;

    transition: .2s ease;
}

.navbar .nav-menu a:hover {
    color: #ffffff;
}

.navbar .nav-menu a.active {
    color: #c99a68;
}

.navbar .nav-menu a.active::after {
    content: "";

    position: absolute;

    left: 0;
    right: 0;
    bottom: 0;

    height: 2px;

    background: #c99a68;
}


/* =========================================================
   RIGHT SIDE
   ========================================================= */

.homepage-nav-actions {
    display: flex;
    align-items: center;

    gap: 14px;

    flex-shrink: 0;
}


/* =========================================================
   CART
   ========================================================= */

.homepage-cart {
    position: relative;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 11px;

    min-width: 118px;
    height: 58px;

    padding: 0 20px;

    border: 1px solid rgba(255,255,255,.19);
    border-radius: 30px;

    background: rgba(255,255,255,.025);

    color: #f5eee5;

    text-decoration: none;

    font-size: 12px;
    font-weight: 800;

    letter-spacing: 1.4px;

    transition: .2s ease;
}

.homepage-cart:hover {
    background: rgba(255,255,255,.08);

    border-color: rgba(255,255,255,.28);

    transform: translateY(-1px);
}

.homepage-cart-icon {
    font-size: 18px;
    line-height: 1;

    color: #d8b28b;
}


/* =========================================================
   CART COUNT
   ========================================================= */

.cart-count {
    position: absolute;

    top: -5px;
    right: -5px;

    min-width: 21px;
    height: 21px;

    padding: 0 5px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #c99a68;
    color: #281913;

    font-size: 10px;
    font-weight: 900;

    line-height: 1;
}


/* =========================================================
   ACCOUNT MENU
   ========================================================= */

.account-menu {
    position: relative;
}

.account-trigger {
    height: 58px;

    display: flex;
    align-items: center;

    gap: 10px;

    padding: 5px 16px 5px 7px;

    border: 1px solid rgba(255,255,255,.19);
    border-radius: 30px;

    background: rgba(255,255,255,.025);

    color: #ffffff;

    cursor: pointer;

    font-family:
        "DM Sans",
        Arial,
        sans-serif;

    transition: .2s ease;
}

.account-trigger:hover {
    background: rgba(255,255,255,.08);

    border-color: rgba(255,255,255,.28);
}

.profile-avatar {
    width: 46px;
    height: 46px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: linear-gradient(
        135deg,
        #d8b28b,
        #a96f3f
    );

    color: #fffaf3;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 19px;
    font-weight: 700;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.25),
        0 4px 14px rgba(0,0,0,.18);
}

.profile-name {
    max-width: 120px;

    overflow: hidden;

    white-space: nowrap;
    text-overflow: ellipsis;

    color: #f5eee5;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: .02em;
}


/* =========================================================
   ACCOUNT DROPDOWN
   ========================================================= */

.account-dropdown {
    position: absolute;

    top: calc(100% + 12px);
    right: 0;

    width: 290px;

    padding: 12px;

    border: 1px solid rgba(255,255,255,.14);
    border-radius: 18px;

    background:
        linear-gradient(
            145deg,
            rgba(54,31,23,.98),
            rgba(39,25,19,.98)
        );

    box-shadow:
        0 22px 55px rgba(0,0,0,.28),
        inset 0 1px 0 rgba(255,255,255,.08);

    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);

    opacity: 0;
    visibility: hidden;

    transform:
        translateY(-8px)
        scale(.98);

    transform-origin: top right;

    transition:
        opacity .18s ease,
        visibility .18s ease,
        transform .18s ease;
}

.account-menu.open .account-dropdown {
    opacity: 1;
    visibility: visible;

    transform:
        translateY(0)
        scale(1);
}


/* =========================================================
   DROPDOWN PROFILE
   ========================================================= */

.dropdown-profile {
    display: flex;
    align-items: center;

    gap: 12px;

    padding: 10px;
}

.dropdown-avatar {
    width: 45px;
    height: 45px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: linear-gradient(
        135deg,
        #d8b28b,
        #a96f3f
    );

    color: #fffaf3;

    font-family:
        "Playfair Display",
        Georgia,
        serif;

    font-size: 18px;
    font-weight: 700;
}

.dropdown-profile-info {
    min-width: 0;

    display: flex;
    flex-direction: column;

    gap: 4px;
}

.dropdown-profile-info strong {
    overflow: hidden;

    color: #fffaf3;

    font-size: 13px;
    font-weight: 800;

    white-space: nowrap;
    text-overflow: ellipsis;
}

.dropdown-profile-info span {
    color: rgba(255,255,255,.52);

    font-size: 10px;
    font-weight: 600;

    letter-spacing: .04em;
}


/* =========================================================
   DIVIDER
   ========================================================= */

.dropdown-divider {
    height: 1px;

    margin: 8px 4px;

    background: rgba(255,255,255,.10);
}


/* =========================================================
   DROPDOWN ITEMS
   ========================================================= */

.dropdown-item {
    position: relative;
    z-index: 1;

    display: flex;
    align-items: center;

    gap: 10px;

    padding: 11px 10px;

    border-radius: 11px;

    color: rgba(255,255,255,.82);

    text-decoration: none;

    font-size: 11px;
    font-weight: 700;

    transition:
        background .18s ease,
        color .18s ease;
}

.dropdown-item:hover {
    background: rgba(255,255,255,.07);

    color: #ffffff;
}

.dropdown-item-icon {
    width: 25px;
    height: 25px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;

    background: rgba(216,178,139,.12);

    color: #d8b28b;

    font-size: 13px;
}

.dropdown-item-text {
    flex: 1;
}


/* =========================================================
   LOGOUT
   ========================================================= */

.dropdown-logout {
    color: rgba(255,225,217,.80);
}

.dropdown-logout:hover {
    background: rgba(169,71,55,.13);

    color: #ffd8cf;
}


/* =========================================================
   GUEST LOGIN
   ========================================================= */

.homepage-login {
    height: 58px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 0 22px;

    border: 1px solid rgba(255,255,255,.14);
    border-radius: 30px;

    background:
        linear-gradient(
            135deg,
            rgba(185,130,82,.96),
            rgba(145,88,51,.96)
        );

    color: #fffaf3;

    text-decoration: none;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1.3px;

    box-shadow:
        0 8px 22px rgba(0,0,0,.15),
        inset 0 1px 0 rgba(255,255,255,.18);

    transition: .2s ease;
}

.homepage-login:hover {
    transform: translateY(-1px);

    box-shadow:
        0 11px 27px rgba(0,0,0,.20),
        inset 0 1px 0 rgba(255,255,255,.20);
}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 1100px) {

    .navbar .nav-container {
        width: calc(100% - 32px);
    }

    .navbar .nav-menu {
        gap: 25px;
        margin-right: 30px;
    }

    .profile-name {
        max-width: 90px;
    }
}


@media (max-width: 900px) {

    .navbar .nav-container {
        flex-wrap: wrap;

        padding: 15px 0;

        min-height: auto;
    }

    .navbar .logo {
        order: 1;
    }

    .homepage-nav-actions {
        order: 2;
    }

    .navbar .nav-menu {
        order: 3;

        width: 100%;

        margin: 10px 0 0;

        justify-content: center;

        gap: 24px;
    }

    .navbar .nav-menu a {
        padding: 8px 0;
    }

    .navbar .nav-menu a.active::after {
        bottom: 0;
    }
}


@media (max-width: 600px) {

    .navbar .nav-container {
        width: calc(100% - 32px);

        padding: 15px 0;
    }

    .navbar .logo img {
        width: 125px;
    }

    .navbar .logo-text {
        font-size: 27px;
        letter-spacing: 2px;
    }

    .homepage-cart {
        min-width: 48px;

        width: 48px;
        height: 48px;

        padding: 0;
    }

    .homepage-cart span:not(.homepage-cart-icon):not(.cart-count) {
        display: none;
    }

    .homepage-cart-icon {
        font-size: 17px;
    }

    .account-trigger {
        width: 48px;
        height: 48px;

        padding: 3px;

        justify-content: center;
    }

    .profile-avatar {
        width: 40px;
        height: 40px;
    }

    .profile-name {
        display: none;
    }

    .homepage-login {
        height: 48px;

        padding: 0 14px;

        font-size: 10px;
    }

    .navbar .nav-menu {
        gap: 15px;
    }

    .navbar .nav-menu a {
        font-size: 9px;
        letter-spacing: .8px;
    }
}


@media (max-width: 480px) {

    .navbar .nav-menu {
        gap: 11px;
    }

    .navbar .nav-menu a {
        font-size: 8px;
        letter-spacing: .5px;
    }

    .account-dropdown {
        right: -5px;

        width: min(
            292px,
            calc(100vw - 24px)
        );
    }
}

</style>


<header class="navbar">

    <div class="nav-container">


        <!-- =================================================
             LOGO
        ================================================== -->

        <a
            href="index.php"
            class="logo"
            aria-label="Cafelia Home"
        >

            <img
                src="image/logo.png"
                alt="Cafelia"
            >

        </a>


        <!-- =================================================
             NAVIGATION
        ================================================== -->

        <nav class="nav-menu">

            <a
                href="index.php"
                class="<?php echo $active_page === 'index' ? 'active' : ''; ?>"
            >
                HOME
            </a>

            <a
                href="menu.php"
                class="<?php echo $active_page === 'menu' ? 'active' : ''; ?>"
            >
                MENU
            </a>

            <a
                href="about.php"
                class="<?php echo $active_page === 'about' ? 'active' : ''; ?>"
            >
                ABOUT US
            </a>

             <a
                href="join_team.php"
                class="<?php echo $active_page === 'join_team' ? 'active' : ''; ?>"
            >
                JOIN OUR TEAM
            </a>

            <a
                href=" contact.php"
                class="<?php echo $active_page === 'contact' ? 'active' : ''; ?>"
            >
                CONTACT
            </a>

        </nav>


        <!-- =================================================
             RIGHT SIDE
        ================================================== -->

        <div class="homepage-nav-actions">


            <!-- CART -->

            <a
                href="cart.php"
                class="homepage-cart"
                aria-label="Shopping Cart"
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


            <!-- =================================================
                 LOGGED-IN ACCOUNT
            ================================================== -->

            <?php if ($is_admin || $is_customer): ?>

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
                                $account_initial,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>

                        <span class="profile-name">
                            <?php
                            echo htmlspecialchars(
                                $account_name,
                                ENT_QUOTES,
                                "UTF-8"
                            );
                            ?>
                        </span>

                    </button>


                    <!-- =================================================
                         ACCOUNT DROPDOWN
                    ================================================== -->

                    <div class="account-dropdown">

                        <div class="dropdown-profile">

                            <div class="dropdown-avatar">
                                <?php
                                echo htmlspecialchars(
                                    $account_initial,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                                ?>
                            </div>

                            <div class="dropdown-profile-info">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $account_name,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );
                                    ?>
                                </strong>

                                <span>
                                    <?php
                                    echo $is_admin
                                        ? "Administrator"
                                        : "Cafelia Customer";
                                    ?>
                                </span>

                            </div>

                        </div>


                        <div class="dropdown-divider"></div>


                        <?php if ($is_admin): ?>

                            <a
                                href="admin/dashboard.php"
                                class="dropdown-item"
                            >

                                <span class="dropdown-item-icon">
                                    ⚙
                                </span>

                                <span class="dropdown-item-text">
                                    Admin Dashboard
                                </span>

                            </a>

                        <?php else: ?>

                            <a
                                href="profile.php"
                                class="dropdown-item"
                            >

                                <span class="dropdown-item-icon">
                                    ◉
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
                                    ☰
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

                        <?php endif; ?>


                        <div class="dropdown-divider"></div>


                        <a
                            href="logout.php"
                            class="dropdown-item dropdown-logout"
                        >

                            <span class="dropdown-item-icon">
                                ↪
                            </span>

                            <span class="dropdown-item-text">
                                Logout
                            </span>

                        </a>

                    </div>

                </div>


            <?php else: ?>


                <!-- =================================================
                     GUEST LOGIN
                ================================================== -->

                <a
                    href="login.php"
                    class="homepage-login"
                >
                    LOGIN
                </a>

            <?php endif; ?>

        </div>

    </div>

</header>


<script>

/* =========================================================
   ACCOUNT DROPDOWN
   ========================================================= */

function toggleAccountMenu(button) {

    const menu = button.closest(".account-menu");

    document
        .querySelectorAll(".account-menu.open")
        .forEach(function(item) {

            if (item !== menu) {

                item.classList.remove("open");

                const trigger =
                    item.querySelector(".account-trigger");

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


/* =========================================================
   CLOSE WHEN CLICKING OUTSIDE
   ========================================================= */

document.addEventListener(
    "click",
    function(event) {

        document
            .querySelectorAll(".account-menu.open")
            .forEach(function(menu) {

                if (!menu.contains(event.target)) {

                    menu.classList.remove("open");

                    const trigger =
                        menu.querySelector(".account-trigger");

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


/* =========================================================
   ESCAPE KEY
   ========================================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (event.key === "Escape") {

            document
                .querySelectorAll(".account-menu.open")
                .forEach(function(menu) {

                    menu.classList.remove("open");

                    const trigger =
                        menu.querySelector(".account-trigger");

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