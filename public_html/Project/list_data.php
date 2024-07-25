<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$is_admin = has_role("Admin");

$query = "SELECT product_id, product_name, current_price, original_price, discount_percentage, image_url FROM products WHERE 1=1";
$params = [];

if (isset($_GET["product_name"]) && !empty($_GET["product_name"])) {
    $search = se($_GET, "product_name", "", false);
    $query .= " AND product_name LIKE :product_name";
    $params[":product_name"] = "%$search%";
}

if (isset($_GET["min_discount"]) && is_numeric($_GET["min_discount"])) {
    $min_discount = se($_GET, "min_discount", "", false);
    $query .= " AND discount_percentage >= :min_discount";
    $params[":min_discount"] = $min_discount;
}

if (isset($_GET["max_discount"]) && is_numeric($_GET["max_discount"])) {
    $max_discount = se($_GET, "max_discount", "", false);
    $query .= " AND discount_percentage <= :max_discount";
    $params[":max_discount"] = $max_discount;
}

if (isset($_GET["min_price"]) && is_numeric($_GET["min_price"])) {
    $min_price = se($_GET, "min_price", "", false);
    $query .= " AND current_price >= :min_price";
    $params[":min_price"] = $min_price;
}

if (isset($_GET["max_price"]) && is_numeric($_GET["max_price"])) {
    $max_price = se($_GET, "max_price", "", false);
    $query .= " AND current_price <= :max_price";
    $params[":max_price"] = $max_price;
}

$sort_by = se($_GET, "sort_by", "modified", false);
$sort_order = se($_GET, "sort_order", "DESC", false);
$query .= " ORDER BY $sort_by $sort_order";

$limit = se($_GET, "limit", 10, false);
if (!is_numeric($limit) || $limit < 1 || $limit > 100) {
    $limit = 10;
}
$query .= " LIMIT $limit";

$db = getDB();
$stmt = $db->prepare($query);
$products = [];
try {
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        $products = $results;
    } else {
        flash("No results available", "warning");
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}
?>

<h1>List Products</h1>
<form method="GET">
    <input type="search" name="product_name" placeholder="Product Name" value="<?php echo se($_GET, 'product_name', '', false); ?>" />
    <input type="number" name="min_discount" placeholder="Min Discount" step="0.01" value="<?php echo se($_GET, 'min_discount', '', false); ?>" />
    <input type="number" name="max_discount" placeholder="Max Discount" step="0.01" value="<?php echo se($_GET, 'max_discount', '', false); ?>" />
    <input type="number" name="min_price" placeholder="Min Price" step="0.01" value="<?php echo se($_GET, 'min_price', '', false); ?>" />
    <input type="number" name="max_price" placeholder="Max Price" step="0.01" value="<?php echo se($_GET, 'max_price', '', false); ?>" />
    <select name="sort_by">
        <option value="product_name">Product Name</option>
        <option value="current_price">Current Price</option>
        <option value="original_price">Original Price</option>
        <option value="discount_percentage">Discount Percentage</option>
        <option value="modified">Modified</option>
    </select>
    <select name="sort_order">
        <option value="ASC">Ascending</option>
        <option value="DESC">Descending</option>
    </select>
    <input type="number" name="limit" placeholder="Limit" min="1" max="100" value="<?php echo se($_GET, 'limit', 10, false); ?>" />
    <input type="submit" value="Filter" />
</form>
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Product ID</th>
            <th>Product Name</th>
            <th>Current Price</th>
            <th>Original Price</th>
            <th>Discount Percentage</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($products)) : ?>
            <tr>
                <td colspan="7">No results available</td>
            </tr>
        <?php else : ?>
            <?php foreach ($products as $product) : ?>
                <tr>
                    <td><img src="<?php se($product, "image_url"); ?>" alt="Product Image" width="50" height="50"></td>
                    <td><?php se($product, "product_id"); ?></td>
                    <td><?php se($product, "product_name"); ?></td>
                    <td><?php se($product, "current_price"); ?></td>
                    <td><?php se($product, "original_price"); ?></td>
                    <td><?php se($product, "discount_percentage"); ?></td>
                    <td>
                        <a href="<?php echo get_url('view_product.php?id=' . $product['product_id']); ?>">View</a>
                        <?php if ($is_admin): ?>
                            <a href="<?php echo get_url('admin/edit_product.php?id=' . $product['product_id']); ?>">Edit</a>
                            <a href="<?php echo get_url('admin/delete_product.php?id=' . $product['product_id']); ?>">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>
