<?php

session_start();

require_once "config/database.php";

/* =========================================
   ACCOUNT STATUS
========================================= */

$is_admin = isset($_SESSION["admin_id"]);
$is_customer = isset($_SESSION["user_id"]);

$account_name = "";

if ($is_admin) {
    $account_name = $_SESSION["admin_name"] ?? "Administrator";
} elseif ($is_customer) {
    $account_name = $_SESSION["user_name"] ?? "Customer";
}


/* =========================================
   GET 4 BEST SELLING COFFEE PRODUCTS
========================================= */

$sql = "SELECT *
        FROM products
        WHERE status = 'available'
        AND category = 'Coffee'
        ORDER BY id DESC
        LIMIT 4";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Query Error: " . mysqli_error($conn));
}

$best_selling = [];

while ($row = mysqli_fetch_assoc($result)) {
    $best_selling[] = $row;
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

    <title>Cafelia | More Than Coffee, It's an Experience</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

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

<?php
$active_page = "index";
include "navbar.php";
?>

<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="hero">

    <div class="hero-overlay"></div>


    <div class="hero-content">

        <p class="hero-small-text">
            More than coffee,
        </p>


        <h1>
            IT'S AN
            <br>
            <span>EXPERIENCE.</span>
        </h1>


        <p class="hero-description">
            At Cafelia every cup is crafted with passion
            to bring comfort and joy to your everyday.
        </p>


        <div class="hero-buttons">

            <a
                href="menu.php"
                class="hero-button primary"
            >
                EXPLORE OUR MENU
            </a>


            <a
                href="contact.php"
                class="hero-button secondary"
            >
                VISIT US
            </a>

        </div>

    </div>

</section>


<!-- =========================================================
     CAFELIA EXPERIENCE STRIP
========================================================= -->

<section class="experience-strip">

    <div class="experience-strip-container">


        <div class="experience-item">

            <span class="experience-icon">
                ☕
            </span>

            <div>

                <strong>
                    Freshly Crafted
                </strong>

                <small>
                    Made with care, every day
                </small>

            </div>

        </div>


        <div class="experience-divider"></div>


        <div class="experience-item">

            <span class="experience-icon">
                ✦
            </span>

            <div>

                <strong>
                    Quality Ingredients
                </strong>

                <small>
                    Thoughtfully selected for every cup
                </small>

            </div>

        </div>


        <div class="experience-divider"></div>


        <div class="experience-item">

            <span class="experience-icon">
                ♡
            </span>

            <div>

                <strong>
                    Made for Moments
                </strong>

                <small>
                    A warm place to slow down
                </small>

            </div>

        </div>


    </div>

</section>


<!-- =========================================================
     BEST SELLERS
========================================================= -->

<section class="featured-section">

    <div class="section-container">


        <div class="section-heading">

            <span>
                BEST SELLERS
            </span>

            <h2>
                Best Selling Coffee
            </h2>

            <p>
                Enjoy the coffee our customers love the most.
            </p>

        </div>


        <div class="products-grid">


            <?php if (!empty($best_selling)): ?>


                <?php foreach ($best_selling as $product): ?>

                    <?php

                    $product_image = !empty($product['image'])
                        ? basename($product['image'])
                        : '';

                    ?>


                    <div class="product-card">


                        <div class="product-image">

                            <?php if ($product_image): ?>

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

                                <div class="product-placeholder">
                                    ☕
                                </div>

                            <?php endif; ?>

                        </div>


                        <div class="product-info">

                            <span class="product-category">

                                <?php
                                    echo htmlspecialchars(
                                        $product['category']
                                    );
                                ?>

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


                            <div class="product-bottom">

                                <span class="product-price">

                                    ₱<?php
                                        echo number_format(
                                            (float)$product['price'],
                                            2
                                        );
                                    ?>

                                </span>


                                <a
                                    href="menu.php"
                                    class="product-button"
                                >
                                    Order Now
                                </a>

                            </div>

                        </div>

                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="no-products">

                    <p>
                        No coffee products available at the moment.
                    </p>

                </div>


            <?php endif; ?>


        </div>


        <div class="featured-button">

            <a href="menu.php">

                View Full Menu

                <span>
                    →
                </span>

            </a>

        </div>


    </div>

</section>


<!-- =========================================================
     MORE THAN JUST COFFEE
========================================================= -->

<section class="home-about-section">

    <div class="home-about-container">


        <div class="home-about-content">

            <span class="home-about-label">
                MORE THAN JUST COFFEE
            </span>


            <h2>
                More Than Just
                <span>Coffee</span>
            </h2>


            <p>
                Cafelia is a cozy coffee shop that serves
                premium coffee, delightful treats, and a
                place where good conversation happens.
            </p>


            <a
                href="about.php"
                class="about-button"
            >
                LEARN MORE
                <span>→</span>
            </a>

        </div>


        <div class="home-about-image">

            <img
                src="image/store front  img.png"
                alt="Cafelia Coffee Shop Front"
            >

        </div>


    </div>

</section>


<!-- =========================================================
     ABOUT CAFELIA STORY
========================================================= -->

<section class="about-story-section">

    <div class="about-story-container">


        <div class="about-story-content">

            <span class="section-label">
                ABOUT CAFELIA
            </span>


            <h2>
                About Cafelia
            </h2>


            <p>
                Cafelia was created from a simple love for
                good coffee and meaningful moments. What
                started from a passion for bringing people
                together over a perfectly brewed cup has
                grown into a place where every visit feels
                a little more special.
            </p>


            <p>
                From the aroma of freshly brewed coffee to
                the warmth of every conversation, Cafelia
                is made for slowing down, connecting, and
                enjoying the moment.
            </p>


            <p>
                We believe coffee is more than a drink. It is
                a part of the conversations we remember, the
                quiet mornings we treasure, and the little
                moments that make every day better.
            </p>


            <p>
                At Cafelia, every cup is prepared with care,
                every detail has a purpose, and every guest
                is welcomed like a friend.
            </p>


            <p class="about-tagline">
                Your cup. Your moment. Your Cafelia.
            </p>


            <a
                href="about.php"
                class="about-button"
            >
                MORE ABOUT US
                <span>→</span>
            </a>

        </div>


        <div class="about-story-image">

            <img
                src="image/about.png"
                alt="Cafelia Coffee Shop"
            >

        </div>


    </div>

</section>


<!-- =========================================================
     OUR VALUES / CUSTOMER TESTIMONIALS
========================================================= -->

<section class="values-section">

    <div class="values-container">


        <div class="values-intro">

            <span class="section-label">
                OUR VALUES
            </span>


            <h2>
                Our Loyal
                <span>Customers</span>
            </h2>


            <p>
                We’re grateful for our amazing customers
                who inspire us every day. Thank you for
                being part of Cafelia Family.
            </p>


            <div class="testimonial-navigation">

                <button
                    type="button"
                    class="testimonial-arrow"
                >
                    ←
                </button>


                <button
                    type="button"
                    class="testimonial-arrow"
                >
                    →
                </button>

            </div>

        </div>


        <div class="testimonials">


            <article class="testimonial-card">

                <div class="testimonial-quote">
                    “
                </div>


                <h3>
                    Cafelia is my everyday
                    coffee happiness!
                </h3>


                <p>
                    The coffee is amazing and the atmosphere
                    is so cozy. It’s my go-to place to relax,
                    work and catch up with friends. The staff
                    are always friendly and make me feel at home.
                </p>


                <strong>
                    JEN D.
                </strong>

            </article>


            <article class="testimonial-card">

                <div class="testimonial-quote">
                    “
                </div>


                <h3>
                    The best brews and the
                    best vibes in town!
                </h3>


                <p>
                    Every drink is crafted perfectly and
                    the place is just so inviting. Cafelia
                    never disappoints!
                </p>


                <strong>
                    MARK R.
                </strong>

            </article>


        </div>

    </div>

</section>


<!-- =========================================================
     JOIN THE CAFELIA FAMILY
========================================================= -->

<section class="join-section">

    <div class="join-container">


        <div class="join-image">

            <img
                src="image/about.png"
                alt="Join the Cafelia Family"
            >

        </div>


        <div class="join-content">

            <span class="section-label">
                CONTACT
            </span>


            <h2>
                We’d Love to
                <span>Meet YOU!</span>
            </h2>


            <p>
                If you’re passionate about coffee, people,
                and creating memorable moments, we’d love
                to hear from you.
            </p>


            <a
                href="contact.php"
                class="join-button"
            >
                APPLY NOW
                <span>→</span>
            </a>

        </div>

    </div>


    <div class="job-options">


        <div class="job-card">

            <span class="job-label">
                JOIN THE CAFELIA FAMILY
            </span>


            <h3>
                Join the Cafelia Family
            </h3>


            <p>
                Be part of a team that shares the same
                passion for coffee and creating meaningful
                connections.
            </p>


            <a href="contact.php">

                VIEW OPEN POSITIONS

                <span>
                    →
                </span>

            </a>

        </div>


        <div class="job-card">

            <span class="job-label">
                SEND YOUR APPLICATION
            </span>


            <h3>
                Send Your Application
            </h3>


            <p>
                Can’t find the right position today?
                Send us your resume and we’ll keep you
                in mind for future opportunities.
            </p>


            <a href="contact.php">

                SEND YOUR RESUME

                <span>
                    →
                </span>

            </a>

        </div>


    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="footer">

    <div class="footer-container">


        <div class="footer-brand">

            <a href="index.php" class="logo"><img src="image/logo.png" alt="Cafelia"></a>


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


            <?php if ($is_customer): ?>

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
n

            <?php elseif ($is_admin): ?>

                <a href="admin/dashboard.php">
                    ADMIN DASHBOARD
                </a>

                <a href="logout.php">
                    LOGOUT
                </a>


            <?php else: ?>

                <a href="login.php">
                    LOGIN
                </a>

                <a href="register.php">
                    REGISTER
                </a>

                <a href="cart.php">
                    MY CART
                </a>

            <?php endif; ?>

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