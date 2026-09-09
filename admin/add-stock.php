<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: ../login.php");
    exit();
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

// Admin may add 1 to 5 stocks per replenishment.
$MIN_ADD = 1;
$MAX_ADD = 5;

$product_id = (int)($_GET["id"] ?? $_POST["product_id"] ?? 0);
if ($product_id <= 0) {
    header("Location: products.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, stock, status, image FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: products.php");
    exit();
}

$current_stock = (int)$product["stock"];
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $add_quantity = (int)($_POST["add_quantity"] ?? 0);

    if ($add_quantity < $MIN_ADD || $add_quantity > $MAX_ADD) {
        $error = "You can add a minimum of 1 and a maximum of 5 stocks.";
    } else {
        /*
         * Add the selected quantity to the CURRENT stock.
         * This works whether current stock is 0, 1, 2, 3, 4, or 5.
         * If the product was out of stock, it is automatically reactivated.
         */
        $update = $conn->prepare(
            "UPDATE products
             SET stock = stock + ?,
                 status = 'available'
             WHERE id = ?"
        );

        if ($update) {
            $update->bind_param("ii", $add_quantity, $product_id);

            if ($update->execute()) {
                if ($update->affected_rows > 0) {
                    $update->close();
                    header("Location: products.php?stock_added=1");
                    exit();
                }

                $error = "Stock was not added. Please try again.";
            } else {
                $error = "Unable to update the product stock: " . $update->error;
            }

            $update->close();
        } else {
            $error = "Unable to prepare the stock update: " . $conn->error;
        }
    }
}

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
        :root{--espresso:#24150f;--dark:#321d14;--caramel:#b98252;--gold:#d8a36d;--cream:#f7f1e8;--white:#fff;--text:#302118;--muted:#87766a;--border:rgba(80,50,34,.10);--green:#397149;--green-bg:#e9f5ec;--red:#9b4b40;--red-bg:#fae9e6}
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;font-family:"DM Sans",Arial,sans-serif;color:var(--text);background:radial-gradient(circle at 85% 5%,rgba(216,163,109,.14),transparent 25%),linear-gradient(135deg,#f8f3eb,#eee2d4)}
        .main{margin-left:260px;padding:44px 46px 60px;min-height:100vh}
        .topbar{max-width:900px;margin:0 auto 24px;display:flex;justify-content:space-between;align-items:flex-start;gap:20px}
        .eyebrow{color:var(--caramel);font-size:11px;font-weight:800;letter-spacing:2px;text-transform:uppercase;margin-bottom:8px}
        h1{font-family:"Playfair Display",Georgia,serif;font-size:42px;line-height:1.05;color:var(--espresso)}
        .subtitle{margin-top:10px;color:var(--muted);font-size:14px}
        .back-link{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 18px;border:1px solid var(--border);border-radius:12px;background:rgba(255,255,255,.72);color:#5a3827;text-decoration:none;font-size:12px;font-weight:800}
        .card{max-width:900px;margin:0 auto;background:rgba(255,255,255,.88);border:1px solid rgba(80,50,34,.08);border-radius:24px;box-shadow:0 18px 45px rgba(49,30,20,.08);overflow:hidden}
        .product-preview{display:flex;align-items:center;gap:20px;padding:28px 30px;border-bottom:1px solid var(--border);background:#fbf8f3}
        .product-image,.no-image{width:82px;height:82px;border-radius:16px}
        .product-image{object-fit:cover;background:#efe4d8}
        .no-image{display:grid;place-items:center;background:#efe4d8;font-size:28px}
        .product-name{font-size:18px;font-weight:800}.stock-display{margin-top:6px;color:var(--muted);font-size:13px}.stock-display strong{color:#5a3827}
        .form{padding:30px}.form-title{font-family:"Playfair Display",Georgia,serif;font-size:24px;margin-bottom:8px}.form-description{color:var(--muted);font-size:13px;margin-bottom:24px}
        .error{margin-bottom:20px;padding:13px 15px;border:1px solid #eccdca;border-radius:12px;background:var(--red-bg);color:var(--red);font-size:13px;font-weight:700}
        label{display:block;margin-bottom:8px;font-size:12px;font-weight:800;letter-spacing:.4px}
        input[type=number]{width:100%;height:54px;padding:0 16px;border:1px solid rgba(80,50,34,.14);border-radius:12px;background:#fff;color:var(--text);font-family:inherit;font-size:16px;font-weight:700;outline:none}
        input[type=number]:focus{border-color:var(--gold);box-shadow:0 0 0 4px rgba(216,163,109,.13)}
        .hint{display:block;margin-top:8px;color:var(--muted);font-size:11px}
        .result-preview{margin-top:18px;padding:14px 16px;border-radius:12px;background:var(--green-bg);color:var(--green);font-size:12px;font-weight:700}
        .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:26px}.btn{min-height:48px;padding:0 22px;border-radius:12px;font-family:inherit;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer}.btn-secondary{display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--border);background:#f8f4ee;color:#5a3827}.btn-primary{border:1px solid var(--espresso);background:var(--espresso);color:#fff}.btn-primary:hover{background:var(--dark)}
        @media(max-width:900px){.main{margin-left:0;padding:30px 20px 45px}}@media(max-width:600px){.topbar{flex-direction:column}.back-link{width:100%}h1{font-size:34px}.product-preview,.form{padding:22px}.actions{flex-direction:column}.btn{width:100%}}
    </style>
</head>
<body>
<?php $active_admin_page = "products"; include "sidebar.php"; ?>
<main class="main">
    <header class="topbar">
        <div><div class="eyebrow">Cafelia Admin</div><h1>Add Stock</h1><p class="subtitle">Replenish inventory for an existing product.</p></div>
        <a href="products.php" class="back-link">← Back to Products</a>
    </header>
    <section class="card">
        <div class="product-preview">
            <?php $image = trim($product["image"] ?? ""); ?>
            <?php if ($image !== ""): ?>
                <img src="../image/<?php echo e($image); ?>" alt="<?php echo e($product["name"]); ?>" class="product-image">
            <?php else: ?><div class="no-image">☕</div><?php endif; ?>
            <div><div class="product-name"><?php echo e($product["name"]); ?></div><div class="stock-display">Current stock: <strong><?php echo $current_stock; ?> unit(s)</strong></div></div>
        </div>
        <form method="POST" class="form">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <h2 class="form-title">Inventory Replenishment</h2>
            <p class="form-description">Enter how many units you want to add. If this product is out of stock, adding inventory will automatically make it available again.</p>
            <?php if ($error !== ""): ?><div class="error"><?php echo e($error); ?></div><?php endif; ?>
            <label for="add_quantity">Number of Stock to Add</label>
            <input type="number" id="add_quantity" name="add_quantity" min="1" max="5" step="1" value="1" required autofocus>
            <span class="hint">You can add 1 to 5 stocks at a time.</span>
            <div class="result-preview" id="resultPreview" style="display:none"></div>
            <div class="actions"><a href="products.php" class="btn btn-secondary">Cancel</a><button type="submit" class="btn btn-primary" id="addStockButton">＋ Add Stock</button></div>
        </form>
    </section>
</main>
<script>
const input=document.getElementById("add_quantity"), preview=document.getElementById("resultPreview"), currentStock=<?php echo $current_stock; ?>;
function updatePreview(){
    let q = Number(input.value || 0);
    if (q < 1) q = 0;
    if (q > 5) q = 5;
    if (q > 0) {
        preview.textContent = "New stock after adding: " + (currentStock + q) + " unit(s)";
        preview.style.display = "block";
    } else {
        preview.style.display = "none";
    }
}
input.addEventListener("input", updatePreview);
input.addEventListener("change", function(){
    let q = Number(this.value || 0);
    if (q < 1) this.value = 1;
    if (q > 5) this.value = 5;
    updatePreview();
});
input.addEventListener("input",updatePreview); updatePreview();
</script>
</body>
</html>
