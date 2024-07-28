<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = get_user_id();
    $product_id = se($_POST, 'product_id', '', false);

    $db = getDB();

    $checkStmt = $db->prepare("SELECT id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id");
    $checkStmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        flash("Product is already in your wishlist", "warning");
    } else {
        $stmt = $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (:user_id, :product_id)");
        try {
            $stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
            flash("Added to wishlist", "success");
        } catch (PDOException $e) {
            flash("Failed to add to wishlist: " . $e->getMessage(), "danger");
            error_log("Error adding to wishlist: " . var_export($e->errorInfo, true));
        }
    }

    header("Location: " . get_url("list_data.php"));
    exit();
}
?>

<form method="POST" action="">
    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>" />
    <input type="submit" value="Add to Wishlist" />
</form>
