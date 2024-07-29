<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

//jed56 7-28-2024

$user_id = get_user_id();
$limit = (int)se($_GET, "limit", 10, false);
$limit = max(1, min($limit, 100));

$sort_by = se($_GET, "sort_by", "created", false);
$sort_order = se($_GET, "sort_order", "desc", false);
$sort_order = ($sort_order === "asc") ? "asc" : "desc";

$search = se($_GET, "search", "", false);

$db = getDB();
$query = "SELECT w.id AS wishlist_id, p.* FROM wishlists w JOIN products p ON w.product_id = p.product_id WHERE w.user_id = :user_id";
$params = [":user_id" => $user_id];

if (!empty($search)) {
    $query .= " AND p.product_name LIKE :search";
    $params[":search"] = "%$search%";
}

$query .= " ORDER BY $sort_by $sort_order LIMIT :limit";
$stmt = $db->prepare($query);
$stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
if (!empty($search)) {
    $stmt->bindValue(":search", "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
$stmt->execute();
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_items = count($wishlist_items);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $stmt = $db->prepare("DELETE FROM wishlists WHERE user_id = :user_id");
    $stmt->execute([":user_id" => $user_id]);
    flash("All items removed from your wishlist", "success");
    header("Location: " . get_url("wishlist.php"));
    exit();
}
?>

<h1>Wishlist (Total: <?php echo $total_items; ?>)</h1>

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

<?php if (empty($wishlist_items)): ?>
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
            <?php foreach ($wishlist_items as $item): ?>
                <tr>
                    <td><img src="<?php se($item, 'image_url'); ?>" alt="Product Image" width="50" height="50"></td>
                    <td><?php se($item, "product_id"); ?></td>
                    <td><?php se($item, "product_name"); ?></td>
                    <td><?php se($item, "current_price"); ?></td>
                    <td><?php se($item, "original_price"); ?></td>
                    <td><?php se($item, "discount_percentage"); ?>%</td>
                    <td><?php se($item, "data_source"); ?></td>
                    <td>
                        <a href="view_product.php?id=<?php se($item, 'product_id'); ?>">View</a>
                        <a href="remove_from_wishlist.php?id=<?php se($item, 'product_id'); ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<form method="POST" action="">
    <button type="submit" name="delete_all">Remove All Associations</button>
</form>

<?php
require(__DIR__ . "/../../partials/flash.php");
?>
