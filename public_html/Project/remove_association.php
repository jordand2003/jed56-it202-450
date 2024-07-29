<?php
require(__DIR__ . "/../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$product_id = se($_GET, "id", "", false);
if (empty($product_id)) {
    flash("Invalid product ID", "danger");
    die(header("Location: " . get_url("admin/all_user_associations.php")));
}

$user_id = get_user_id();

$db = getDB();
$query = "SELECT product_id FROM wishlists WHERE product_id = :product_id AND user_id = :user_id";
$stmt = $db->prepare($query);

try {
    $stmt->execute([":product_id" => $product_id, ":user_id" => $user_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        flash("Product not found", "warning");
        error_log("Product not found in wishlist for user_id: $user_id and product_id: $product_id");
        die(header("Location: " . get_url("admin/all_user_associations.php")));
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
    error_log(var_export($e->errorInfo, true));
    die(header("Location: " . get_url("admin/all_user_associations.php")));
}

$delete_query = "DELETE FROM wishlists WHERE product_id = :product_id AND user_id = :user_id";
$stmt = $db->prepare($delete_query);
try {
    $stmt->execute([":product_id" => $product_id, ":user_id" => $user_id]);
    flash("Product deleted from wishlist successfully", "success");
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
    error_log(var_export($e->errorInfo, true));
}

$redirect_url = get_url("admin/all_user_associations.php");

$previous_query_string = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) : '';
if ($previous_query_string) {
    $redirect_url .= '?' . $previous_query_string;
}

header("Location: " . $redirect_url);
exit();
?>
