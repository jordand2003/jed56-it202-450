<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$is_admin = has_role("Admin");

$product_id = se($_GET, "id", "", false);
if (empty($product_id)) {
    flash("Invalid product ID", "danger");
    die(header("Location: " . get_url("list_data.php")));
}

$db = getDB();
$query = "SELECT product_id, product_name, current_price, original_price, discount_percentage, image_url FROM products WHERE product_id = :product_id";
$stmt = $db->prepare($query);
$product = null;
try {
    $stmt->execute([":product_id" => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        flash("Product not found", "warning");
        die(header("Location: " . get_url("list_data.php")));
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
    die(header("Location: " . get_url("list_data.php")));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = se($_POST, "product_name", "", false);
    $current_price = se($_POST, "current_price", "", false);
    $original_price = se($_POST, "original_price", "", false);
    $discount_percentage = se($_POST, "discount_percentage", "", false);
    $image_url = se($_POST, "image_url", "", false);
    $hasError = false;

    if (empty($name)) {
        flash("Product name is required", "danger");
        $hasError = true;
    }
    if (!is_numeric($current_price) || $current_price <= 0) {
        flash("Current price must be a positive number", "danger");
        $hasError = true;
    }
    if (!is_numeric($original_price) || $original_price <= 0) {
        flash("Original price must be a positive number", "danger");
        $hasError = true;
    }
    if (!is_numeric($discount_percentage) || $discount_percentage < 0 || $discount_percentage > 100) {
        flash("Discount percentage must be between 0 and 100", "danger");
        $hasError = true;
    }

    if (!$hasError) {
        $update_query = "UPDATE products SET product_name = :product_name, current_price = :current_price, original_price = :original_price, discount_percentage = :discount_percentage, image_url = :image_url WHERE product_id = :product_id";
        $stmt = $db->prepare($update_query);
        try {
            $stmt->execute([
                ":product_name" => $name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url,
                ":product_id" => $product_id
            ]);
            flash("Product updated successfully", "success");
            $product = array_merge($product, [
                'product_name' => $name,
                'current_price' => $current_price,
                'original_price' => $original_price,
                'discount_percentage' => $discount_percentage,
                'image_url' => $image_url
            ]);
        } catch (PDOException $e) {
            flash(var_export($e->errorInfo, true), "danger");
        }
    }
}
?>

<h1>Edit Product</h1>
<form method="POST">
    <div>
        <label for="product_name">Product Name</label>
        <input type="text" id="product_name" name="product_name" value="<?php se($product, 'product_name'); ?>" required />
    </div>
    <div>
        <label for="current_price">Current Price</label>
        <input type="number" step="0.01" id="current_price" name="current_price" value="<?php se($product, 'current_price'); ?>" required />
    </div>
    <div>
        <label for="original_price">Original Price</label>
        <input type="number" step="0.01" id="original_price" name="original_price" value="<?php se($product, 'original_price'); ?>" required />
    </div>
    <div>
        <label for="discount_percentage">Discount Percentage</label>
        <input type="number" step="0.01" id="discount_percentage" name="discount_percentage" value="<?php se($product, 'discount_percentage'); ?>" required />
    </div>
    <div>
        <label for="image_url">Image URL</label>
        <input type="url" id="image_url" name="image_url" value="<?php se($product, 'image_url'); ?>" />
    </div>
    <div>
        <input type="submit" value="Update Product" />
    </div>
</form>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>

