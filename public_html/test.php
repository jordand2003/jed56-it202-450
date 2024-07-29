<?php

$product_id = se($_GET, "id", "", false);
if (empty($product_id)) {
    flash("Invalid product ID", "danger");
    die(header("Location: " . get_url("wishlist.php")));
}

$user_id = get_user_id();

$db = getDB();

$query = "SELECT product_id FROM wishlists WHERE user_id = :user_id AND product_id = :product_id";
$stmt = $db->prepare($query);
try {
    $stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
    $wishlist_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$wishlist_item) {
        flash("Product not found in wishlist", "warning");
        error_log("Product ID: $product_id not found in wishlist for User ID: $user_id");
        die(header("Location: " . get_url("wishlist.php")));
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
    die(header("Location: " . get_url("wishlist.php")));
}

$delete_query = "DELETE FROM wishlists WHERE user_id = :user_id AND product_id = :product_id";
$stmt = $db->prepare($delete_query);
try {
    $stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
    flash("Product removed from wishlist successfully", "success");
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}

?>