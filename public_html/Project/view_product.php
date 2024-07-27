<?php
require(__DIR__ . "/../../partials/nav.php");

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
$query = "SELECT product_id, product_name, current_price, original_price, discount_percentage, image_url, created, modified FROM products WHERE product_id = :product_id";
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
//jed56 7-26-2024
?>

<h1>Product Details</h1>
<?php if ($product): ?>
    <div>
        <img src="<?php se($product, 'image_url'); ?>" alt="Product Image" width="100" height="100">
        <p><strong>Product ID:</strong> <?php se($product, 'product_id'); ?></p>
        <p><strong>Product Name:</strong> <?php se($product, 'product_name'); ?></p>
        <p><strong>Current Price:</strong> $<?php se($product, 'current_price'); ?></p>
        <p><strong>Original Price:</strong> $<?php se($product, 'original_price'); ?></p>
        <p><strong>Discount Percentage:</strong> <?php se($product, 'discount_percentage'); ?>%</p>
        <p><strong>Created:</strong> <?php se($product, 'created'); ?></p>
        <p><strong>Modified:</strong> <?php se($product, 'modified'); ?></p>
    </div>
    <div>
        <?php if ($is_admin): ?>
            <a href="<?php echo get_url('admin/edit_product.php?id=' . $product['product_id']); ?>">Edit</a>
            <a href="<?php echo get_url('admin/delete_product.php?id=' . $product['product_id']); ?>">Delete</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p>Product details not available</p>
<?php endif; ?>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
//jed56 7-26-2024
?>
