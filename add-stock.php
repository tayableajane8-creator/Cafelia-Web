<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

$admin_name = $_SESSION["admin_name"] ?? "Administrator";
$error = "";
$product_id = (int)($_GET["id"] ?? $_POST["product_id"] ?? 0);

if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, stock, image, status FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit();
}

$current_stock = max(0, (int)$product["stock"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $add_stock = filter_var($_POST["add_stock"] ?? null, FILTER_VALIDATE_INT);

    if ($add_stock === false || $add_stock < 1) {
        $error = "Please enter a stock quantity of at least 1.";
    } else {
        $stmt = $conn->prepare("UPDATE products SET stock = stock + ?, status = 'available' WHERE id = ?");

        if ($stmt) {
            $stmt->bind_param("ii", $add_stock, $product_id);

            if ($stmt->execute()) {
                $stmt->close();
                header("Location: products.php?success=stock_added");
                exit();
            }

            $error = "Unable to add stock: " . $stmt->error;
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }

    $current_stock = max(0, $current_stock);
}

function e_stock(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

$active_admin_page = "add-stock";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Stock | Cafelia Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{min-height:100vh;background:linear-gradient(135deg,#f8f3eb,#eee2d4);color:#302118;font-family:"DM Sans",Arial,sans-serif}
.admin-page-content{min-height:100vh;padding:42px}
.stock-page{max-width:760px;margin:0 auto}
.eyebrow{color:#b98252;font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;margin-bottom:7px}
h1{font-family:"Playfair Display",Georgia,serif;font-size:42px;color:#24150f}
.lead{margin-top:8px;color:#87766a;font-size:13px}
.card{margin-top:28px;background:rgba(255,255,255,.88);border:1px solid rgba(80,50,34,.10);border-radius:20px;box-shadow:0 18px 45px rgba(49,30,20,.08);overflow:hidden}
.product{display:flex;align-items:center;gap:16px;padding:24px;border-bottom:1px solid #eee3d8}
.product img,.placeholder{width:74px;height:74px;object-fit:cover;border-radius:14px;background:#f2e8dc;display:grid;place-items:center;color:#5a3827;font-size:25px}
.product strong{display:block;font-family:"Playfair Display",Georgia,serif;font-size:21px;color:#321d14}
.product span{display:block;margin-top:5px;color:#87766a;font-size:12px}
.form{padding:26px 24px}
.current{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;padding:15px 16px;background:#faf5ee;border:1px solid #eadfd3;border-radius:12px}
.current span{color:#87766a;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.current strong{color:#5a3827;font-family:"Playfair Display",serif;font-size:25px}
label{display:block;margin-bottom:8px;color:#4a3326;font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
input{width:100%;height:48px;padding:0 14px;border:1px solid #dfd1c4;border-radius:11px;background:#fffdfa;outline:none;color:#302118;font-size:14px}
input:focus{border-color:#b98252;box-shadow:0 0 0 3px rgba(185,130,82,.10)}
small{display:block;margin-top:7px;color:#87766a;font-size:10px}
.error{margin:0 24px;padding:12px 14px;border-radius:10px;background:#fbefee;border:1px solid #eccdca;color:#9c4945;font-size:12px;font-weight:700}
.actions{display:flex;gap:10px;margin-top:22px}.btn{min-height:45px;padding:0 18px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;text-decoration:none;font-size:11px;font-weight:800;letter-spacing:.08em}.back{border:1px solid #dfd1c4;color:#5a3827;background:#fff}.save{border:0;background:#321d14;color:#fff;cursor:pointer}.save:hover{background:#5a3827}
@media(max-width:700px){.admin-page-content{padding:28px 18px}.product{padding:20px}.form{padding:22px 20px}h1{font-size:34px}.actions{flex-direction:column}.btn{width:100%}}
</style>
</head>
<body>
<?php include "sidebar.php"; ?>
<main class="admin-page-content">
<section class="stock-page">
<div class="eyebrow">Inventory Management</div>
<h1>Add Stock</h1>
<p class="lead">Increase the available inventory without changing the product details.</p>
<div class="card">
<div class="product">
<?php $image = basename((string)($product["image"] ?? "")); ?>
<?php if ($image !== ""): ?><img src="../image/<?php echo e_stock($image); ?>" alt="<?php echo e_stock($product["name"]); ?>"><?php else: ?><div class="placeholder">☕</div><?php endif; ?>
<div><strong><?php echo e_stock($product["name"]); ?></strong><span><?php echo e_stock($product["status"]); ?> product</span></div>
</div>
<?php if ($error !== ""): ?><div class="error"><?php echo e_stock($error); ?></div><?php endif; ?>
<form method="POST" class="form">
<input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
<div class="current"><span>Current Stock</span><strong><?php echo $current_stock; ?></strong></div>
<label for="add_stock">Quantity to Add</label>
<input type="number" id="add_stock" name="add_stock" min="1" step="1" value="1" required autofocus>
<small>This amount will be added to the current stock. Editing the stock directly is disabled on the Edit Product page.</small>
<div class="actions"><a class="btn back" href="products.php">Cancel</a><button class="btn save" type="submit">Add Stock</button></div>
</form>
</div>
</section>
</main>
</body>
</html>
