<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
    //jed56 7-25-2024
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = se($_POST, "product_id", "", false);
    $product_name = se($_POST, "product_name", "", false);
    $current_price = se($_POST, "current_price", "", false);
    $original_price = se($_POST, "original_price", "", false);
    $discount_percentage = se($_POST, "discount_percentage", "", false);
    $image_url = se($_POST, "image_url", "", false);
    $data_source = 'manual';
    // jed56 7-25-2024

    $errors = [];

    if (empty($product_id)) {
        $errors[] = "Product ID is required";
    }
    if (empty($product_name)) {
        $errors[] = "Product name is required";
    }
    if (empty($current_price) || !is_numeric($current_price) || $current_price <= 0) {
        $errors[] = "Valid current price is required";
    }
    if (empty($original_price) || !is_numeric($original_price) || $original_price <= 0) {
        $errors[] = "Valid original price is required";
    }
    if (empty($discount_percentage) || !is_numeric($discount_percentage) || $discount_percentage < 0 || $discount_percentage > 100) {
        $errors[] = "Valid discount percentage is required";
    }


    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO products (product_id, product_name, current_price, original_price, discount_percentage, image_url, data_source, created, modified) VALUES (:product_id, :product_name, :current_price, :original_price, :discount_percentage, :image_url, :data_source, NOW(), NOW())");
        try {
            $stmt->execute([
                ":product_id" => $product_id,
                ":product_name" => $product_name,
                ":current_price" => $current_price,
                ":original_price" => $original_price,
                ":discount_percentage" => $discount_percentage,
                ":image_url" => $image_url,
                ":data_source" => $data_source
            ]);
            flash("Successfully created product $product_name!", "success");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                flash("A product with this ID already exists, please try another", "warning");
            } else {
                flash("Unknown error occurred, please try again", "danger");
                error_log(var_export($e->errorInfo, true));
            }
        }
    } else {
        foreach ($errors as $error) {
            flash($error, "danger");
        }
    }
}
?>

<h1>Add Product</h1>
<form method="POST" onsubmit="return validateForm()">
    <div>
        <label for="product_id">Product ID</label>
        <input id="product_id" name="product_id" required />
    </div>
    <div>
        <label for="product_name">Product Name</label>
        <input id="product_name" name="product_name" required />
    </div>
    <div>
        <label for="current_price">Current Price</label>
        <input id="current_price" name="current_price" type="number" step="0.01" min="0.01" required />
    </div>
    <div>
        <label for="original_price">Original Price</label>
        <input id="original_price" name="original_price" type="number" step="0.01" min="0.01" required />
    </div>
    <div>
        <label for="discount_percentage">Discount Percentage</label>
        <input id="discount_percentage" name="discount_percentage" type="number" step="0.01" min="0" max="100" required />
    </div>
    <div>
        <label for="image_url">Image URL</label>
        <input id="image_url" name="image_url" type="url" />
    </div>
    <div>
        <label for="data_source">Data Source</label>
        <input id="data_source" name="data_source" value="manual" readonly />
    </div>
    <input type="submit" value="Add Product" />
</form>

<script>
function validateForm() {
    let product_id = document.getElementById('product_id').value;
    let product_name = document.getElementById('product_name').value;
    let current_price = document.getElementById('current_price').value;
    let original_price = document.getElementById('original_price').value;
    let discount_percentage = document.getElementById('discount_percentage').value;
    let image_url = document.getElementById('image_url').value;
    let errors = [];

    if (!product_id) {
        errors.push("Product ID is required");
    }
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
