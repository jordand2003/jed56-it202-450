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
$query = "SELECT product_id, product_name, current_price, original_price, discount_percentage, image_url, data_source, created, modified FROM products WHERE product_id = :product_id";
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
    $data_source = se($_POST, "data_source", "manual", false);
    $errors = [];

    if (empty($name)) {
        $errors[] = "Product name is required";
    }
    if (!is_numeric($current_price) || $current_price <= 0) {
        $errors[] = "Current price must be a positive number";
    }
    if (!is_numeric($original_price) || $original_price <= 0) {
        $errors[] = "Original price must be a positive number";
    }
    if (!is_numeric($discount_percentage) || $discount_percentage < 0 || $discount_percentage > 100) {
        $errors[] = "Discount percentage must be between 0 and 100";
    }


    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE products SET product_name = :name, current_price = :current_price, original_price = :original_price, discount_percentage = :discount_percentage, image_url = :image_url, data_source = :data_source, modified = NOW() WHERE product_id = :product_id");
        try {
            $stmt->execute([
                ":name" => $name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url,
                ":data_source" => $data_source,
                ":product_id" => $product_id
            ]);
            flash("Successfully updated product $name!", "success");
        } catch (PDOException $e) {
            flash("An error occurred, please try again", "danger");
            error_log(var_export($e->errorInfo, true));
        }
    } else {
        foreach ($errors as $error) {
            flash($error, "danger");
        }
    }
}
//jed56 7-26-2024
?>

<h1>Edit Product</h1>
<form method="POST" onsubmit="return validateForm()">
    <div>
        <label for="product_id">Product ID</label>
        <input id="product_id" name="product_id" value="<?php se($product, 'product_id'); ?>" readonly />
    </div>
    <div>
        <label for="product_name">Product Name</label>
        <input id="product_name" name="product_name" value="<?php se($product, 'product_name'); ?>" required />
    </div>
    <div>
        <label for="current_price">Current Price</label>
        <input id="current_price" name="current_price" type="number" step="0.01" min="0.01" value="<?php se($product, 'current_price'); ?>" required />
    </div>
    <div>
        <label for="original_price">Original Price</label>
        <input id="original_price" name="original_price" type="number" step="0.01" min="0.01" value="<?php se($product, 'original_price'); ?>" required />
    </div>
    <div>
        <label for="discount_percentage">Discount Percentage</label>
        <input id="discount_percentage" name="discount_percentage" type="number" step="0.01" min="0" max="100" value="<?php se($product, 'discount_percentage'); ?>" required />
    </div>
    <div>
        <label for="image_url">Image URL</label>
        <input id="image_url" name="image_url" type="url" value="<?php se($product, 'image_url'); ?>" />
    </div>
    <div>
        <label for="data_source">Data Source</label>
        <input id="data_source" name="data_source" value="<?php se($product, 'data_source'); ?>" readonly />
    </div>
    <input type="submit" value="Update Product" />
</form>

<script>
function validateForm() {
    let product_name = document.getElementById('product_name').value;
    let current_price = document.getElementById('current_price').value;
    let original_price = document.getElementById('original_price').value;
    let discount_percentage = document.getElementById('discount_percentage').value;
    let image_url = document.getElementById('image_url').value;
    let errors = [];

    if (!product_name) {
        errors.push("Product name is required");
    }
    if (!current_price || isNaN(current_price) || current_price <= 0) {
        errors.push("Valid current price is required");
    }
    if (!original_price || isNaN(original_price) || original_price <= 0) {
        errors.push("Valid original price is required");
    }
    if (!discount_percentage || isNaN(discount_percentage) || discount_percentage < 0 || discount_percentage > 100) {
        errors.push("Valid discount percentage is required");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }

    return true;
}

function isValidUrl(urlString) {
    try {
        return Boolean(new URL(urlString));
    } catch (e) {
        return false;
    }
}
</script>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
