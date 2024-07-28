<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$query = "SELECT product_id, product_name, current_price, original_price, discount_percentage, image_url, data_source FROM products";
$params = [];
$filters = [];
$sort = "modified desc";
$limit = 10;

if (isset($_POST["product_name"])) {
    $search = se($_POST, "product_name", "", false);
    if (!empty($search)) {
        $filters[] = "product_name LIKE :product_name";
        $params[":product_name"] = "%$search%";
    }
}
//jed56 7-26-2024

if (isset($_POST["min_price"]) && isset($_POST["max_price"])) {
    $min_price = se($_POST, "min_price", "", false);
    $max_price = se($_POST, "max_price", "", false);
    if (is_numeric($min_price) && is_numeric($max_price)) {
        $filters[] = "current_price BETWEEN :min_price AND :max_price";
        $params[":min_price"] = $min_price;
        $params[":max_price"] = $max_price;
    }
}

if (isset($_POST["sort_by"])) {
    $sort_by = se($_POST, "sort_by", "", false);
    switch ($sort_by) {
        case "price_asc":
            $sort = "current_price asc";
            break;
        case "price_desc":
            $sort = "current_price desc";
            break;
        case "discount_asc":
            $sort = "discount_percentage asc";
            break;
        case "discount_desc":
            $sort = "discount_percentage desc";
            break;
    }
}

if (isset($_POST["limit"])) {
    $limit = se($_POST, "limit", 10, false);
    if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
        $limit = 10;
    }
}

if (!empty($filters)) {
    $query .= " WHERE " . implode(" AND ", $filters);
}

$query .= " ORDER BY $sort LIMIT $limit"; 

$db = getDB();
$stmt = $db->prepare($query);

$products = [];
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $products = $results;
    } else {
        flash("No matches found", "warning");
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}
//jed56 7-26-2024
?>

<h1>List Products</h1>
<table>
    <thead>
        <th>Image</th>
        <th>Product ID</th>
        <th>Product Name</th>
        <th>Current Price</th>
        <th>Original Price</th>
        <th>Discount</th>
        <th>Data Source</th>
        <th>Actions</th>
    </thead>
    <tbody>
        <?php foreach ($products as $product) : ?>
            <tr>
                <td><img src="<?php se($product, 'image_url'); ?>" alt="Product Image" width="50" height="50"></td>
                <td><?php se($product, "product_id"); ?></td>
                <td><?php se($product, "product_name"); ?></td>
                <td><?php se($product, "current_price"); ?></td>
                <td><?php se($product, "original_price"); ?></td>
                <td><?php se($product, "discount_percentage"); ?>%</td>
                <td><?php se($product, "data_source"); ?></td>
                <td>
                    <a href="view_product.php?id=<?php se($product, 'product_id'); ?>">View</a>
                    <?php if (has_role("Admin")) : ?>
                        <a href="edit_product.php?id=<?php se($product, 'product_id'); ?>">Edit</a>
                        <a href="delete_product.php?id=<?php se($product, 'product_id'); ?>">Delete</a>
                    <?php endif; ?>
                    <form method="POST" action="add_to_wishlist.php">
                        <input type="hidden" name="product_id" value="<?php se($product, 'product_id'); ?>" />
                        <input type="submit" value="Add to Wishlist" />
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
function validateForm() {
    let productName = document.getElementById('product_name').value;
    let minPrice = document.getElementById('min_price').value;
    let maxPrice = document.getElementById('max_price').value;
    let limit = document.getElementById('limit').value;
    let errors = [];

    if (minPrice && (isNaN(minPrice) || minPrice < 0)) {
        errors.push("Min Price must be a non-negative number.");
    }

    if (maxPrice && (isNaN(maxPrice) || maxPrice < 0)) {
        errors.push("Max Price must be a non-negative number.");
    }

    if (limit && (isNaN(limit) || limit < 1 || limit > 100)) {
        errors.push("Limit must be a number between 1 and 100.");
    }

    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }

    return true;
}
</script>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>
