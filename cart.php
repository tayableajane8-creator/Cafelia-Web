<?php

session_start();

require_once "config/database.php";


// ==========================================
// CHECK CUSTOMER LOGIN
// ==========================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = (int) $_SESSION["user_id"];


// ==========================================
// CART RULES
// ==========================================

const MAX_ORDER_QUANTITY = 6;

$cart_message = "";
$cart_message_type = "";

// ==========================================
// ADD TO CART
// ==========================================

$is_add_request =
    ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add") ||
    ($_SERVER["REQUEST_METHOD"] === "GET" && ($_GET["action"] ?? "") === "add");

if ($is_add_request) {

    $product_id = (int) ($_POST["product_id"] ?? $_GET["product_id"] ?? 0);
    $quantity = (int) ($_POST["quantity"] ?? $_GET["quantity"] ?? 1);
    $quantity = max(1, min($quantity, MAX_ORDER_QUANTITY));

    if ($product_id > 0) {
        $stmt = $conn->prepare(
            "SELECT id, stock
             FROM products
             WHERE id = ?
               AND status = 'available'
             LIMIT 1"
        );

        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();

            if ($product) {
                $stock = max(0, (int) $product["stock"]);

                $stmt = $conn->prepare(
                    "SELECT id, quantity
                     FROM cart
                     WHERE user_id = ?
                       AND product_id = ?
                     LIMIT 1"
                );

                if ($stmt) {
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $existing = $result->fetch_assoc();
                    $stmt->close();

                    $current_quantity = $existing ? (int) $existing["quantity"] : 0;
                    $allowed_max = min(MAX_ORDER_QUANTITY, $stock);
                    $new_quantity = $current_quantity + $quantity;

                    if ($stock <= 0) {
                        header("Location: cart.php?cart_error=out_of_stock");
                        exit();
                    }

                    if ($new_quantity > $allowed_max) {
                        header("Location: cart.php?cart_error=limit&max=" . $allowed_max);
                        exit();
                    }

                    if ($existing) {
                        $stmt = $conn->prepare(
                            "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?"
                        );
                        if ($stmt) {
                            $stmt->bind_param("iii", $new_quantity, $existing["id"], $user_id);
                            $stmt->execute();
                            $stmt->close();
                        }
                    } else {
                        $stmt = $conn->prepare(
                            "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)"
                        );
                        if ($stmt) {
                            $stmt->bind_param("iii", $user_id, $product_id, $quantity);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }

    header("Location: cart.php");
    exit();
}

// ==========================================
// UPDATE CART
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "update") {

    if (isset($_POST["quantities"]) && is_array($_POST["quantities"])) {

        foreach ($_POST["quantities"] as $product_id => $quantity) {
            $product_id = (int) $product_id;
            $quantity = (int) $quantity;

            if ($product_id <= 0) continue;

            if ($quantity <= 0) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                if ($stmt) {
                    $stmt->bind_param("ii", $user_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
                continue;
            }

            $stock_stmt = $conn->prepare("SELECT stock FROM products WHERE id = ? AND status = 'available' LIMIT 1");
            if (!$stock_stmt) continue;

            $stock_stmt->bind_param("i", $product_id);
            $stock_stmt->execute();
            $stock_result = $stock_stmt->get_result();
            $stock_row = $stock_result->fetch_assoc();
            $stock_stmt->close();

            if (!$stock_row) {
                $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                if ($delete) {
                    $delete->bind_param("ii", $user_id, $product_id);
                    $delete->execute();
                    $delete->close();
                }
                continue;
            }

            $max_allowed = min(MAX_ORDER_QUANTITY, max(0, (int)$stock_row["stock"]));
            $quantity = min($quantity, $max_allowed);

            if ($quantity <= 0) {
                $delete = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                if ($delete) {
                    $delete->bind_param("ii", $user_id, $product_id);
                    $delete->execute();
                    $delete->close();
                }
            } else {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                if ($stmt) {
                    $stmt->bind_param("iii", $quantity, $user_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }

    header("Location: cart.php");
    exit();
}

// ==========================================
// REMOVE ITEM
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "remove") {

    $product_id = (int) ($_POST["product_id"] ?? 0);

    if ($product_id > 0) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        if ($stmt) {
            $stmt->bind_param("ii", $user_id, $product_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: cart.php");
    exit();
}

// ==========================================
// GET CART PRODUCTS FROM DATABASE
// ==========================================

$cart_products = [];
$grand_total = 0;

$stmt = $conn->prepare(
    "SELECT
        cart.id AS cart_id,
        cart.product_id,
        cart.quantity,
        products.name,
        products.description,
        products.price,
        products.stock,
        products.image,
        products.category
     FROM cart
     INNER JOIN products
        ON cart.product_id = products.id
     WHERE cart.user_id = ?
     AND products.status = 'available'
     ORDER BY cart.id DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($product = $result->fetch_assoc()) {

    $product["quantity"] = (int) $product["quantity"];
    $product["price"] = (float) $product["price"];
    $product["subtotal"] = $product["price"] * $product["quantity"];

    $grand_total += $product["subtotal"];
    $cart_products[] = $product;

}

$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart | Cafelia</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

<style>
:root{
    --espresso:#24150f;
    --espresso-2:#321d14;
    --coffee:#5a3827;
    --caramel:#b98252;
    --gold:#d8a36d;
    --cream:#f8f2e9;
    --cream-2:#eee2d3;
    --paper:#fffdf9;
    --muted:#7c6b5e;
    --line:rgba(72,45,31,.12);
    --shadow:0 22px 65px rgba(36,21,15,.10);
}

*{box-sizing:border-box;margin:0;padding:0}

body{
    min-height:100vh;
    font-family:"DM Sans",Arial,sans-serif;
    color:var(--espresso);
    background:
        radial-gradient(circle at 7% 10%,rgba(216,163,109,.13),transparent 27%),
        radial-gradient(circle at 94% 80%,rgba(185,130,82,.10),transparent 29%),
        linear-gradient(145deg,#fcf8f2,#f1e7da 55%,#ecdecd);
}

button,input{font:inherit}
a{text-decoration:none}

/* ================= NAVBAR ================= */

.navbar{
    position:sticky;
    top:0;
    z-index:9999;
    min-height:88px;
    background:rgba(36,21,15,.975);
    border-bottom:1px solid rgba(255,255,255,.08);
    box-shadow:0 8px 28px rgba(0,0,0,.08);
}

.nav-container{
    width:min(calc(100% - 72px),1500px);
    min-height:88px;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:30px;
}

.logo{
    margin-right:auto;
    flex:0 0 auto;
}

.logo-text{
    color:#f8ecdf;
    font-family:"Playfair Display",Georgia,serif;
    font-size:30px;
    font-weight:700;
    letter-spacing:.13em;
}

.nav-right{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:clamp(20px,2.5vw,38px);
}

.nav-menu{
    display:flex;
    align-items:center;
    gap:clamp(20px,2.5vw,38px);
}

.nav-menu a{
    position:relative;
    padding:34px 0 31px;
    color:rgba(255,250,243,.74);
    font-size:10px;
    font-weight:800;
    letter-spacing:.13em;
    text-transform:uppercase;
    transition:.2s ease;
}

.nav-menu a:hover,
.nav-menu a.active{color:#fffaf3}

.nav-menu a::after{
    content:"";
    position:absolute;
    left:0;right:0;bottom:24px;
    height:1px;
    background:var(--gold);
    transform:scaleX(0);
    transition:.2s ease;
}

.nav-menu a:hover::after,
.nav-menu a.active::after{transform:scaleX(1)}

.account-menu{position:relative}

.account-trigger{
    position:relative;
    min-height:46px;
    padding:5px 15px 5px 6px;
    display:inline-flex;
    align-items:center;
    gap:10px;
    border:1px solid rgba(255,255,255,.16);
    border-radius:999px;
    background:linear-gradient(135deg,rgba(255,255,255,.105),rgba(255,255,255,.035));
    color:#fffaf3;
    cursor:pointer;
    backdrop-filter:blur(20px) saturate(140%);
    -webkit-backdrop-filter:blur(20px) saturate(140%);
    box-shadow:0 8px 26px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.12);
    transition:.22s ease;
}

.account-trigger:hover,
.account-trigger[aria-expanded="true"]{
    border-color:rgba(216,163,109,.42);
    background:linear-gradient(135deg,rgba(255,255,255,.15),rgba(216,163,109,.07));
    box-shadow:0 12px 32px rgba(0,0,0,.21),0 0 0 4px rgba(216,163,109,.045);
    transform:translateY(-1px);
}

.profile-avatar,.dropdown-avatar{
    display:grid;
    place-items:center;
    flex:0 0 auto;
    border-radius:50%;
    color:#fffaf3;
    background:linear-gradient(145deg,#c99968,#9c623a);
    border:1px solid rgba(255,255,255,.30);
    box-shadow:0 4px 12px rgba(0,0,0,.20),inset 0 1px 0 rgba(255,255,255,.25);
    font-family:"Playfair Display",Georgia,serif;
    font-weight:700;
}

.profile-avatar{width:36px;height:36px;font-size:14px}

.profile-name{
    max-width:150px;
    color:rgba(255,250,243,.94);
    font-size:11px;
    font-weight:700;
    letter-spacing:.02em;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.account-dropdown{
    position:absolute;
    top:calc(100% + 14px);
    right:0;
    width:305px;
    padding:10px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.19);
    border-radius:20px;
    background:linear-gradient(145deg,rgba(61,37,26,.84),rgba(29,18,13,.94));
    backdrop-filter:blur(26px) saturate(145%);
    -webkit-backdrop-filter:blur(26px) saturate(145%);
    box-shadow:0 26px 65px rgba(0,0,0,.34),inset 0 1px 0 rgba(255,255,255,.12);
    opacity:0;
    visibility:hidden;
    transform:translateY(-8px) scale(.975);
    transform-origin:top right;
    pointer-events:none;
    transition:.2s ease;
}

.account-menu.open .account-dropdown{
    opacity:1;
    visibility:visible;
    transform:none;
    pointer-events:auto;
}

.dropdown-profile{
    display:flex;
    align-items:center;
    gap:12px;
    padding:10px 10px 14px;
}

.dropdown-avatar{width:44px;height:44px;font-size:17px}
.dropdown-profile div{min-width:0}
.dropdown-profile strong{
    display:block;color:#fffaf3;font-size:13px;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.dropdown-profile small{
    display:block;margin-top:4px;color:rgba(255,250,243,.52);
    font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}

.dropdown-divider{
    height:1px;margin:3px 5px 7px;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.12),transparent);
}

.dropdown-item{
    display:flex;align-items:center;gap:11px;
    padding:10px;margin:2px 0;
    border:1px solid transparent;border-radius:13px;
    color:#fffaf3;transition:.18s ease;
}

.dropdown-item:hover{
    background:rgba(255,255,255,.075);
    border-color:rgba(255,255,255,.06);
    transform:translateX(2px);
}

.dropdown-icon{
    width:34px;height:34px;display:grid;place-items:center;flex:0 0 auto;
    border:1px solid rgba(216,163,109,.20);
    border-radius:10px;background:rgba(216,163,109,.085);
    color:#e0b17d;
}

.dropdown-item strong{display:block;font-size:11px}
.dropdown-item small{display:block;margin-top:3px;color:rgba(255,250,243,.45);font-size:9px}

.dropdown-logout{
    display:flex;align-items:center;gap:8px;padding:10px;
    color:rgba(255,225,217,.80);
    font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
}
.dropdown-logout:hover{color:#ffd8cf}

/* ================= HERO ================= */

.cart-hero{
    position:relative;
    overflow:hidden;
    padding:78px 24px 72px;
    background:linear-gradient(125deg,rgba(36,21,15,.99),rgba(77,46,32,.95));
    color:var(--cream);
}

.cart-hero::before,
.cart-hero::after{
    content:"";
    position:absolute;
    border:1px solid rgba(216,163,109,.15);
    border-radius:50%;
    pointer-events:none;
}

.cart-hero::before{width:430px;height:430px;right:-190px;top:-250px}
.cart-hero::after{width:280px;height:280px;left:-155px;bottom:-185px}

.cart-hero-inner{
    position:relative;z-index:1;
    width:min(1180px,100%);margin:auto;
}

.cart-kicker{
    color:var(--gold);
    font-size:10px;font-weight:800;letter-spacing:.25em;
    text-transform:uppercase;margin-bottom:14px;
}

.cart-hero h1{
    font-family:"Playfair Display",Georgia,serif;
    font-size:clamp(44px,6vw,68px);
    line-height:.98;font-weight:600;letter-spacing:-.035em;
}

.cart-hero p{
    max-width:540px;margin-top:17px;
    color:rgba(248,242,233,.67);
    font-size:14px;line-height:1.8;
}

/* ================= CART ================= */

.cart-section{
    width:min(1180px,calc(100% - 48px));
    margin:auto;
    padding:58px 0 90px;
}

.cart-layout{
    display:grid;
    grid-template-columns:minmax(0,1.55fr) minmax(310px,.75fr);
    gap:28px;
    align-items:start;
}

.cart-panel{
    padding:26px;
    border:1px solid rgba(72,45,31,.10);
    border-radius:22px;
    background:rgba(255,253,249,.84);
    box-shadow:var(--shadow);
}

.cart-panel-header{
    display:flex;align-items:end;justify-content:space-between;
    gap:20px;padding-bottom:18px;border-bottom:1px solid var(--line);
}

.cart-panel-header h2,
.summary-panel h2{
    font-family:"Playfair Display",Georgia,serif;
    font-size:25px;font-weight:600;
}

.cart-count{
    color:var(--muted);
    font-size:10px;font-weight:700;letter-spacing:.10em;text-transform:uppercase;
}

/* ITEM */

.cart-item{
    display:grid;
    grid-template-columns:104px minmax(0,1fr) 105px 125px 42px;
    align-items:center;
    gap:20px;
    padding:22px 0;
    border-bottom:1px solid var(--line);
}

.cart-item:last-child{border-bottom:0}

.cart-image{
    width:104px;height:104px;overflow:hidden;
    border-radius:16px;
    background:var(--cream-2);
    border:1px solid rgba(72,45,31,.08);
}

.cart-image img{
    width:100%;height:100%;display:block;object-fit:cover;
}

.no-image{
    width:100%;height:100%;display:grid;place-items:center;
    color:var(--coffee);font-size:30px;
}

.cart-details{min-width:0}

.product-category{
    display:inline-flex;
    padding:5px 9px;
    border:1px solid rgba(185,130,82,.20);
    border-radius:999px;
    background:rgba(185,130,82,.07);
    color:#96613c;
    font-size:8px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;
}

.cart-details h3{
    margin-top:10px;
    font-family:"Playfair Display",Georgia,serif;
    font-size:21px;line-height:1.15;font-weight:600;
}

.cart-details p{
    margin-top:7px;color:var(--muted);font-size:12px;
}

.stock-available{
    display:block;
    margin-top:6px;
    color:#9a806c;
    font-size:10px;
    font-weight:700;
}

.cart-alert{
    width:min(calc(100% - 48px),1100px);
    margin:20px auto 0;
    padding:13px 16px;
    border:1px solid #ead2b4;
    border-radius:12px;
    background:#fff6e8;
    color:#8c5e2c;
    font-size:12px;
    font-weight:700;
}


.quantity-label{
    display:block;margin-bottom:7px;color:var(--muted);
    font-size:9px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
}

.quantity-control{
    display:flex;align-items:center;
    width:105px;height:39px;overflow:hidden;
    border:1px solid rgba(72,45,31,.15);
    border-radius:10px;background:#fffefa;
}

.quantity-control button{
    width:31px;height:100%;border:0;background:transparent;
    color:var(--coffee);cursor:pointer;font-size:17px;
}

.quantity-control button:hover{background:rgba(185,130,82,.10)}

.quantity-control input{
    width:43px;height:100%;border:0;outline:0;background:transparent;
    color:var(--espresso);text-align:center;font-size:12px;font-weight:700;
}

.quantity-control input::-webkit-outer-spin-button,
.quantity-control input::-webkit-inner-spin-button{
    -webkit-appearance:none;margin:0;
}

.quantity-control input[type=number]{-moz-appearance:textfield}

.item-subtotal{text-align:right}

.item-subtotal span{
    display:block;margin-bottom:5px;color:var(--muted);
    font-size:9px;text-transform:uppercase;letter-spacing:.10em;
}

.item-subtotal strong{
    font-family:"Playfair Display",Georgia,serif;
    font-size:19px;font-weight:600;white-space:nowrap;
}

.remove-button{
    width:36px;height:36px;margin-left:auto;
    display:grid;place-items:center;
    border:1px solid rgba(169,71,55,.12);
    border-radius:10px;background:rgba(169,71,55,.035);
    color:#a94737;cursor:pointer;font-size:19px;
    transition:.18s ease;
}

.remove-button:hover{
    background:rgba(169,71,55,.10);
    border-color:rgba(169,71,55,.22);
    transform:translateY(-1px);
}

.cart-update{
    display:flex;justify-content:flex-end;padding-top:18px;
}

.update-button{
    min-height:43px;padding:0 18px;
    border:1px solid rgba(72,45,31,.14);
    border-radius:10px;background:transparent;color:var(--coffee);
    font-size:10px;font-weight:800;letter-spacing:.11em;text-transform:uppercase;
    cursor:pointer;transition:.2s ease;
}

.update-button:hover{background:var(--cream);border-color:rgba(72,45,31,.24)}

/* ================= SUMMARY ================= */

.summary-panel{
    position:sticky;top:112px;
    padding:28px;
    border:1px solid rgba(255,255,255,.72);
    border-radius:22px;
    background:linear-gradient(145deg,rgba(255,253,249,.90),rgba(247,239,228,.80));
    box-shadow:var(--shadow);
    backdrop-filter:blur(16px);
    -webkit-backdrop-filter:blur(16px);
}

.summary-panel h2{
    padding-bottom:20px;border-bottom:1px solid var(--line);
}

.summary-row{
    display:flex;justify-content:space-between;
    padding:14px 0;color:var(--muted);font-size:12px;
}

.summary-row strong{color:var(--espresso)}

.summary-divider{height:1px;margin:7px 0;background:var(--line)}

.summary-total{
    display:flex;align-items:center;justify-content:space-between;
    padding:18px 0 22px;
}

.summary-total span{font-size:13px;font-weight:700}

.summary-total strong{
    font-family:"Playfair Display",Georgia,serif;
    font-size:27px;font-weight:600;
}

.checkout-button{
    width:100%;min-height:54px;
    display:flex;align-items:center;justify-content:center;
    border-radius:12px;background:var(--espresso);color:#fffaf3;
    font-size:10px;font-weight:800;letter-spacing:.15em;text-transform:uppercase;
    box-shadow:0 12px 25px rgba(36,21,15,.16);
    transition:.22s ease;
}

.checkout-button:hover{
    background:var(--coffee);
    transform:translateY(-2px);
}

.continue-shopping{
    display:block;width:fit-content;margin:18px auto 0;
    color:var(--muted);font-size:11px;font-weight:700;
}

.continue-shopping:hover{color:var(--coffee)}

.summary-note{
    display:flex;gap:8px;
    margin-top:22px;padding-top:17px;
    border-top:1px solid var(--line);
    color:#968578;font-size:10px;line-height:1.6;
}

.summary-note span{color:var(--caramel)}

/* ================= EMPTY ================= */

.empty-cart{
    max-width:650px;margin:20px auto 40px;padding:72px 35px;
    text-align:center;
    border:1px solid rgba(255,255,255,.72);
    border-radius:25px;
    background:rgba(255,253,249,.80);
    box-shadow:var(--shadow);
    backdrop-filter:blur(14px);
}

.empty-cart-icon{
    width:72px;height:72px;margin:0 auto 22px;
    display:grid;place-items:center;
    border:1px solid rgba(185,130,82,.20);
    border-radius:50%;background:rgba(185,130,82,.07);
    font-size:27px;
}

.empty-cart h2{
    font-family:"Playfair Display",Georgia,serif;
    font-size:31px;font-weight:600;
}

.empty-cart p{
    max-width:390px;margin:11px auto 25px;
    color:var(--muted);font-size:13px;line-height:1.7;
}

.browse-button{
    display:inline-flex;align-items:center;justify-content:center;
    min-height:50px;padding:0 22px;border-radius:11px;
    background:var(--espresso);color:#fffaf3;
    font-size:10px;font-weight:800;letter-spacing:.13em;text-transform:uppercase;
    transition:.2s ease;
}

.browse-button:hover{background:var(--coffee);transform:translateY(-1px)}

/* ================= FOOTER ================= */

.footer{
    background:var(--espresso);color:var(--cream);
    padding:55px 24px 24px;
}

.footer-content{
    width:min(1180px,100%);margin:auto;
    display:grid;grid-template-columns:1.4fr 1fr 1fr;gap:45px;
}

.footer h3{
    font-family:"Playfair Display",Georgia,serif;font-size:25px;
}

.footer h4{
    margin-bottom:16px;color:var(--gold);
    font-size:10px;letter-spacing:.15em;
}

.footer p{
    max-width:310px;margin-top:10px;
    color:rgba(248,242,233,.56);font-size:11px;line-height:1.7;
}

.footer a{
    display:block;width:fit-content;margin:9px 0;
    color:rgba(248,242,233,.60);font-size:11px;
}

.footer a:hover{color:var(--gold)}

.footer-bottom{
    width:min(1180px,100%);margin:40px auto 0;
    padding-top:18px;border-top:1px solid rgba(255,255,255,.09);
}

.footer-bottom p{margin:0;max-width:none;font-size:10px}

/* ================= RESPONSIVE ================= */

@media(max-width:1050px){
    .nav-container{width:min(calc(100% - 42px),1500px)}
    .nav-menu{gap:18px}
    .nav-menu a{font-size:9px}
    .cart-item{
        grid-template-columns:90px minmax(0,1fr) 95px 105px 42px;
        gap:14px;
    }
    .cart-image{width:90px;height:90px}
}

@media(max-width:900px){
    .nav-container{min-height:74px;width:calc(100% - 30px)}
    .nav-menu a:nth-child(4){display:none}
    .nav-menu{gap:14px}
    .nav-menu a{padding:28px 0 25px}
    .nav-menu a::after{bottom:19px}
    .profile-name{max-width:95px;font-size:10px}
    .cart-layout{grid-template-columns:1fr}
    .summary-panel{position:static}
    .footer-content{grid-template-columns:1fr 1fr}
}

@media(max-width:700px){
    .cart-hero{padding:62px 22px}
    .cart-section{width:min(calc(100% - 28px),600px);padding-top:35px}
    .cart-panel{padding:18px}
    .cart-item{
        grid-template-columns:76px minmax(0,1fr) 34px;
        gap:13px;align-items:start;
    }
    .cart-image{width:76px;height:76px}
    .cart-details h3{font-size:18px}
    .cart-quantity{grid-column:2}
    .item-subtotal{grid-column:2;text-align:left;margin-top:-3px}
    .remove-button{grid-column:3;grid-row:1}
    .cart-update{justify-content:stretch}
    .update-button{width:100%}
    .footer-content{grid-template-columns:1fr;gap:28px}
}

@media(max-width:520px){
    .nav-container{width:calc(100% - 20px);gap:8px}
    .logo-text{font-size:24px}
    .nav-menu{gap:8px}
    .nav-menu a{font-size:8px;letter-spacing:.07em}
    .nav-menu a:nth-child(3){display:none}
    .profile-name{display:none}
    .account-trigger{
        width:43px;min-height:43px;padding:4px;justify-content:center
    }
    .profile-avatar{width:33px;height:33px}
    .account-dropdown{right:-4px;width:min(300px,calc(100vw - 20px))}
    .cart-panel-header{align-items:flex-start;flex-direction:column;gap:5px}
    .summary-panel{padding:22px}
}

@media(prefers-reduced-motion:reduce){
    *{transition:none!important;scroll-behavior:auto!important}
}
</style>
</head>

<body>

<header class="navbar">
    <div class="nav-container">

        <a href="index.php" class="logo" aria-label="Cafelia Home">
            <span class="logo-text">CAFELIA</span>
        </a>

        <div class="nav-right">

            <nav class="nav-menu">
                <a href="index.php">HOME</a>
                <a href="menu.php">MENU</a>
                <a href="about.php">ABOUT US</a>
                <a href="join_team.php">JOIN OUR TEAM</a>
                <a href="contact.php">CONTACT</a>
            </nav>

            <div class="account-menu">

                <button type="button"
                        class="account-trigger"
                        aria-label="Open account menu"
                        aria-expanded="false"
                        aria-haspopup="true"
                        onclick="toggleAccountMenu(this)">

                    <span class="profile-avatar" aria-hidden="true">
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
                            <small>Review your selected items</small>
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
            </div>
        </div>
    </div>
</header>


<section class="cart-hero">
    <div class="cart-hero-inner">
        <div class="cart-kicker">CAFELIA ORDER</div>
        <h1>Your Cart</h1>
        <p>
            Review your selected favorites, adjust your quantities,
            and continue when everything looks just right.
        </p>
    </div>
</section>


<?php if (isset($_GET["cart_error"])): ?>
    <div class="cart-alert">
        <?php if ($_GET["cart_error"] === "limit"): ?>
            You can order up to <?php echo MAX_ORDER_QUANTITY; ?> of one product, and never more than the available stock.
        <?php elseif ($_GET["cart_error"] === "out_of_stock"): ?>
            Sorry, this product is currently out of stock.
        <?php endif; ?>
    </div>
<?php endif; ?>

<section class="cart-section">

<?php if (!empty($cart_products)): ?>

    <?php
        $total_items = 0;

        foreach ($cart_products as $cart_product) {
            $total_items += (int) $cart_product["quantity"];
        }
    ?>

    <div class="cart-layout">

        <div class="cart-panel">

            <div class="cart-panel-header">
                <h2>Selected Items</h2>

                <span class="cart-count">
                    <?php echo $total_items; ?>
                    <?php echo $total_items === 1 ? "item" : "items"; ?>
                </span>
            </div>


            <form action="cart.php" method="POST" id="cart-form">

                <input type="hidden" name="action" value="update">

                <?php foreach ($cart_products as $product): ?>

                    <?php
                        $cart_image = !empty($product["image"])
                            ? basename($product["image"])
                            : "";
                    ?>

                    <article class="cart-item">

                        <div class="cart-image">
                            <?php if ($cart_image): ?>
                                <img
                                    src="image/<?php echo htmlspecialchars($cart_image); ?>"
                                    alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                >
                            <?php else: ?>
                                <div class="no-image">☕</div>
                            <?php endif; ?>
                        </div>


                        <div class="cart-details">

                            <span class="product-category">
                                <?php echo htmlspecialchars($product["category"]); ?>
                            </span>

                            <h3>
                                <?php echo htmlspecialchars($product["name"]); ?>
                            </h3>

                            <p>
                                ₱<?php echo number_format($product["price"], 2); ?> each
                            </p>

                            <small class="stock-available">
                                <?php echo (int)$product["stock"]; ?> available · max <?php echo MAX_ORDER_QUANTITY; ?> per product
                            </small>

                        </div>


                        <div class="cart-quantity">

                            <span class="quantity-label">Quantity</span>

                            <div class="quantity-control">

                                <button
                                    type="button"
                                    aria-label="Decrease quantity"
                                    onclick="changeQuantity(<?php echo (int) $product['product_id']; ?>,-1,<?php echo min(MAX_ORDER_QUANTITY, (int)$product['stock']); ?>)"
                                >−</button>

                                <input
                                    type="number"
                                    id="quantity-<?php echo (int) $product['product_id']; ?>"
                                    name="quantities[<?php echo (int) $product['product_id']; ?>]"
                                    value="<?php echo (int) $product["quantity"]; ?>"
                                    min="1"
                                    max="<?php echo min(MAX_ORDER_QUANTITY, (int)$product["stock"]); ?>"
                                    oninput="validateQuantityInput(this)"
                                    aria-label="Quantity"
                                >

                                <button
                                    type="button"
                                    aria-label="Increase quantity"
                                    onclick="changeQuantity(<?php echo (int) $product['product_id']; ?>,1,<?php echo min(MAX_ORDER_QUANTITY, (int)$product['stock']); ?>)"
                                >+</button>

                            </div>

                        </div>


                        <div class="item-subtotal">

                            <span>Subtotal</span>

                            <strong class="item-subtotal-value" data-price="<?php echo htmlspecialchars((string)$product["price"], ENT_QUOTES); ?>">
                                ₱<?php echo number_format($product["subtotal"], 2); ?>
                            </strong>

                        </div>


                        <button
                            type="submit"
                            class="remove-button"
                            name="action"
                            value="remove"
                            formaction="cart.php"
                            formmethod="POST"
                            onclick="return prepareRemove(this)"
                            aria-label="Remove item"
                            title="Remove item"
                        >
                            ×

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo (int) $product["product_id"]; ?>"
                            >
                        </button>

                    </article>

                <?php endforeach; ?>


                <div class="cart-update">
                    <button type="submit" class="update-button">
                        Update Cart
                    </button>
                </div>

            </form>

        </div>


        <aside class="summary-panel">

            <h2>Order Summary</h2>

            <div class="summary-row">
                <span>Items</span>
                <strong><?php echo $total_items; ?></strong>
            </div>

            <div class="summary-row">
                <span>Subtotal</span>
                <strong id="cartSubtotal">₱<?php echo number_format($grand_total, 2); ?></strong>
            </div>

            <div class="summary-row">
                <span>Delivery</span>
                <strong>₱0.00</strong>
            </div>

            <div class="summary-divider"></div>

            <div class="summary-total">
                <span>Total</span>
                <strong id="cartTotal">₱<?php echo number_format($grand_total, 2); ?></strong>
            </div>

            <a href="checkout.php" class="checkout-button">
                Proceed to Checkout
            </a>

            <a href="menu.php" class="continue-shopping">
                ← Continue Shopping
            </a>

            <div class="summary-note">
                <span>✦</span>
                <p>
                    Review your selected items before continuing
                    to checkout.
                </p>
            </div>

        </aside>

    </div>


<?php else: ?>

    <div class="empty-cart">

        <div class="empty-cart-icon">🛒</div>

        <h2>Your cart is empty</h2>

        <p>
            Looks like you haven't added anything to your cart yet.
            Discover something good from the Cafelia menu.
        </p>

        <a href="menu.php" class="browse-button">
            Browse Our Menu
        </a>

    </div>

<?php endif; ?>

</section>


<footer class="footer">

    <div class="footer-content">

        <div>
            <h3>CAFELIA</h3>
            <p>
                More than coffee, it’s an experience.
                Coffee, treats, and moments worth remembering.
            </p>
        </div>

        <div>
            <h4>QUICK LINKS</h4>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="about.php">About Us</a>
            <a href="contact.php">Contact</a>
        </div>

        <div>
            <h4>CUSTOMER</h4>
            <a href="orders.php">My Orders</a>
            <a href="cart.php">Cart</a>
            <a href="profile.php">Profile</a>
        </div>

    </div>

    <div class="footer-bottom">
        <p>
            © <?php echo date("Y"); ?> Cafelia. All Rights Reserved.
        </p>
    </div>

</footer>


<script>
const MAX_ORDER_QUANTITY = <?php echo MAX_ORDER_QUANTITY; ?>;

function toggleAccountMenu(button) {
    const menu = button.closest(".account-menu");
    const isOpen = menu.classList.toggle("open");
    button.setAttribute("aria-expanded", isOpen ? "true" : "false");
}

document.addEventListener("click", function(event) {

    document.querySelectorAll(".account-menu.open").forEach(function(menu) {

        if (!menu.contains(event.target)) {

            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");

            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        }
    });
});

document.addEventListener("keydown", function(event) {

    if (event.key === "Escape") {

        document.querySelectorAll(".account-menu.open").forEach(function(menu) {

            menu.classList.remove("open");

            const button = menu.querySelector(".account-trigger");

            if (button) {
                button.setAttribute("aria-expanded", "false");
            }
        });
    }
});

function changeQuantity(productId, amount, maxStock) {
    const input = document.getElementById("quantity-" + productId);
    if (!input) return;

    let quantity = parseInt(input.value, 10) || 1;
    const max = Math.max(1, Math.min(MAX_ORDER_QUANTITY, parseInt(maxStock, 10) || 1));

    quantity += amount;
    quantity = Math.max(1, Math.min(quantity, max));
    input.value = quantity;
    updateCartPreview();
}

function validateQuantityInput(input) {
    const max = Math.max(1, Math.min(MAX_ORDER_QUANTITY, parseInt(input.max, 10) || 1));
    let quantity = parseInt(input.value, 10) || 1;
    quantity = Math.max(1, Math.min(quantity, max));
    input.value = quantity;
    updateCartPreview();
}

function updateCartPreview() {
    let total = 0;
    document.querySelectorAll(".cart-item").forEach(function(item) {
        const input = item.querySelector('input[name^="quantities["]');
        const subtotal = item.querySelector(".item-subtotal-value");
        if (!input || !subtotal) return;
        const price = parseFloat(subtotal.dataset.price) || 0;
        const quantity = parseInt(input.value, 10) || 1;
        const line = price * quantity;
        total += line;
        subtotal.textContent = "₱" + line.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });

    const subtotalEl = document.getElementById("cartSubtotal");
    const totalEl = document.getElementById("cartTotal");
    const formatted = "₱" + total.toLocaleString("en-PH", {minimumFractionDigits: 2, maximumFractionDigits: 2});
    if (subtotalEl) subtotalEl.textContent = formatted;
    if (totalEl) totalEl.textContent = formatted;
}

document.addEventListener("DOMContentLoaded", updateCartPreview);

function prepareRemove(button) {

    const confirmed = confirm(
        "Remove this item from your Cafelia cart?"
    );

    if (!confirmed) {
        return false;
    }

    const form = button.closest("form");

    form.querySelectorAll(
        'input[name^="quantities["]'
    ).forEach(function(input) {
        input.disabled = true;
    });

    return true;
}
</script>

</body>
</html>
