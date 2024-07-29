<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$limit = (int)se($_GET, "limit", 10, false);
$limit = max(1, min($limit, 100));

$sort_by = se($_GET, "sort_by", "created", false);
$sort_order = se($_GET, "sort_order", "desc", false);
$sort_order = ($sort_order === "asc") ? "asc" : "desc";

$username_filter = se($_GET, "username", "", false);

//jed56 7-28-2024

$db = getDB();
$query = "SELECT u.username, w.id AS wishlist_id, p.*, COUNT(*) OVER(PARTITION BY p.product_id) AS user_count 
          FROM wishlists w 
          JOIN Users u ON w.user_id = u.id 
          JOIN products p ON w.product_id = p.product_id";

$params = [];
if (!empty($username_filter)) {
    $query .= " WHERE u.username LIKE :username";
    $params[":username"] = "%$username_filter%";
}
$query .= " ORDER BY $sort_by $sort_order LIMIT :limit";
$stmt = $db->prepare($query);
$stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

//jed56 7-28-2024
$total_items = count($items);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    if (!empty($username_filter)) {
        $stmt = $db->prepare("DELETE w FROM wishlists w JOIN Users u ON w.user_id = u.id WHERE u.username LIKE :username");
        $stmt->execute([":username" => "%$username_filter%"]);
        flash("All matching associations removed", "success");
    } else {
        $stmt = $db->prepare("DELETE FROM wishlists");
        $stmt->execute();
        flash("All associations removed", "success");
    }
    header("Location: " . get_url("admin/all_user_associations.php"));
    exit();
}

//jed56 7-28-2024
?>

<h1>All User Associations (Total: <?php echo $total_items; ?>)</h1>

<form method="GET" action="">
    <label for="username">Username Filter:</label>
    <input type="text" name="username" id="username" value="<?php echo $username_filter; ?>" />

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

    <button type="submit">Apply</button>
</form>

<?php if (empty($items)): ?>
    <p>No results available</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Username</th>
                <th>Image</th>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Current Price</th>
                <th>Original Price</th>
                <th>Discount</th>
                <th>Data Source</th>
                <th>User Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><a href="../profile.php?id=<?php se($item, 'user_id'); ?>"><?php se($item, 'username'); ?></a></td>
                    <td><img src="<?php se($item, 'image_url'); ?>" alt="Product Image" width="50" height="50"></td>
                    <td><?php se($item, "product_id"); ?></td>
                    <td><?php se($item, "product_name"); ?></td>
                    <td><?php se($item, "current_price"); ?></td>
                    <td><?php se($item, "original_price"); ?></td>
                    <td><?php se($item, "discount_percentage"); ?>%</td>
                    <td><?php se($item, "data_source"); ?></td>
                    <td><?php se($item, "user_count"); ?></td>
                    <td>
                        <a href="../view_product.php?id=<?php se($item, 'product_id'); ?>">View</a>
                        <a href="../remove_association.php?id=<?php se($item, 'product_id'); ?>">Delete</a>
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
require(__DIR__ . "/../../../partials/flash.php");
?>
