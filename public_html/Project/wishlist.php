<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$user_id = get_user_id();
$db = getDB();

$stmt = $db->prepare("SELECT p.* FROM products p JOIN wishlists w ON p.product_id = w.product_id WHERE w.user_id = :user_id");
$stmt->execute([":user_id" => $user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h1>My Wishlist</h1>
<table>
    <thead>
        <th>Image</th>
        <th>Product ID</th>
        <th>Product Name</th>
        <th>Current Price</th>
        <th>Original Price</th>
        <th>Discount</th>
        <th>Actions</th>
    </thead>
    <tbody>
        <?php foreach ($wishlist_items as $item) : ?>
            <tr>
                <td><img src="<?php echo se($item, 'image_url'); ?>" alt="Product Image" width="50" height="50"></td>
                <td><?php echo se($item, 'product_id'); ?></td>
                <td><?php echo se($item, 'product_name'); ?></td>
                <td><?php echo se($item, 'current_price'); ?></td>
                <td><?php echo se($item, 'original_price'); ?></td>
                <td><?php echo se($item, 'discount_percentage'); ?>%</td>
                <td>
                    <a href="remove_from_wishlist.php?id=<?php echo se($item, 'id'); ?>">Remove</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
