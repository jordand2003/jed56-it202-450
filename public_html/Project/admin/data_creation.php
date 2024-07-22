<?php
//note we need to go up 1 more directory
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}
if (isset($_POST["product_id"]) && isset($_POST["product_name"]) && isset($_POST["current_price"]) && isset($_POST["original_price"]) && isset($_POST["discount_percentage"])) {
    $productid = se($_POST, "product_id", "", false);
    $name = se($_POST, "product_name", "", false);
    $currentprice = se($_POST, "current_price", "", false);
    $originalprice = se($_POST, "original_price", "", false);
    $discount = se($_POST, "discount_percentage", "", false);
    $image = se($_POST, "image_url", "", false);

    if (empty($productid)) {
        flash("Product ID is required", "warning");
    } else if (empty($name)) {
        flash("Name is required", "warning");
    } else if (empty($currentprice)) {
        flash("Current price is required", "warning");
    } else if (empty($originalprice)) {
        flash("Original price is required", "warning");
    } else if (empty($discount)) {
        flash("Discount is required", "warning");
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO products (product_id, product_name, current_price, original_price, discount_percentage, image_url) VALUES(:product_id, :product_name, :current_price, :original_price, :discount_percentage, :image_url)");
        try {
            $stmt->execute([
                ":product_id" => $productid,
                ":product_name" => $name,
                ":current_price" => $currentprice,
                ":original_price" => $originalprice,
                ":discount_percentage" => $discount,
                ":image_url" => $image
            ]);
            flash("Successfully created product $name!", "success");
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                flash("A product with this ID already exists, please try another", "warning");
            } else {
                flash("Unknown error occurred, please try again", "danger");
                error_log(var_export($e->errorInfo, true));
            }
        }
    }
}
?>

<h1>Add Product</h1>
<form method="POST">
    <div>
        <label for="product_id">Product ID</label>
        <input id="product_id" name="product_id" required />
    </div>
    <div>
        <label for="product_name">Name</label>
        <input id="product_name" name="product_name" required />
    </div>
    <div>
        <label for="current_price">Current Price</label>
        <input id="current_price" name="current_price" type="number" step="0.01" required />
    </div>
    <div>
        <label for="original_price">Original Price</label>
        <input id="original_price" name="original_price" type="number" step="0.01" required />
    </div>
    <div>
        <label for="discount_percentage">Discount Percentage</label>
        <input id="discount_percentage" name="discount_percentage" type="number" step="0.01" required />
    </div>
    <div>
        <label for="image_url">Image URL</label>
        <input id="image_url" name="image_url" />
    </div>
    <input type="submit" value="Add Product" />
</form>


<?php
//note we need to go up 1 more directory
require_once(__DIR__ . "/../../../partials/flash.php");
?>