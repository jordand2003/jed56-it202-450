<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

$limit = (int)se($_GET, "limit", 10, false);
$limit = max(1, min($limit, 100));
//jed56 7-28-2024

$sort_by = se($_GET, "sort_by", "created", false);
$sort_order = se($_GET, "sort_order", "desc", false);
$sort_order = ($sort_order === "asc") ? "asc" : "desc";

$search = se($_GET, "search", "", false);

$db = getDB();
$query = "SELECT p.* FROM products p LEFT JOIN wishlists w ON p.product_id = w.product_id WHERE w.product_id IS NULL";
$params = [];

if (!empty($search)) {
    $query .= " AND p.product_name LIKE :search";
    $params[":search"] = "%$search%";
}

$query .= " ORDER BY $sort_by $sort_order LIMIT :limit";
$stmt = $db->prepare($query);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_items = count($items);
?>

<h1>Unassociated Products (Total: <?php echo $total_items; ?>)</h1>

<form method="GET" action="">
    <label for="limit">Limit:</label>
    <input type="number" name="limit" id="limit" value="<?php echo $limit; ?>" min="1" max="100" />
    
    <label for="sort_by">Sort By:</label>
    <select name="sort_by" id="sort_by">
        <option value="created" <?php if ($sort_by === "created") echo "selected"; ?>>Created</option>
        <option value="product_name" <?php if ($sort_by === "product_name") echo "selected"; ?>>Product Name</option>
        <option value="current_price" <?php if ($sort_by === "current_price") echo "selected"; ?>>Price</option>
    </select>

    <label for="sort_order">Order:</label>
    <select name="sort_order" id="sort_order">
        <option value="asc" <?php if ($sort_order === "asc") echo "selected"; ?>>Ascending</option>
        <option value="desc" <?php if ($sort_order === "desc") echo "selected"; ?>>Descending</option>
    </select>

    <label for="search">Product Name:</label>
    <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search); ?>" />

    <button type="submit">Apply</button>
</form>

<?php if (empty($items)):     //jed56 7-29-2024
?>
    <p>No results available</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Current Price</th>
                <th>Original Price</th>
                <th>Discount</th>
                <th>Data Source</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><img src="<?php se($item, 'image_url'); ?>" alt="Product Image" width="50" height="50"></td>
                    <td><?php se($item, "product_id"); ?></td>
                    <td><?php se($item, "product_name"); ?></td>
                    <td><?php se($item, "current_price"); ?></td>
                    <td><?php se($item, "original_price"); ?></td>
                    <td><?php se($item, "discount_percentage"); ?>%</td>
                    <td><?php se($item, "data_source"); ?></td>
                    <td>
                        <a href="../view_product.php?id=<?php se($item, 'product_id'); ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
require(__DIR__ . "/../../../partials/flash.php");
?>
