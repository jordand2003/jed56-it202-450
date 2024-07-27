<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!is_logged_in()) {
    flash("You must be logged in to view this page", "warning");
    die(header("Location: " . get_url("login.php")));
}

$is_admin = has_role("Admin");

$product_id = se($_GET, "id", "", false);
if (empty($product_id)) {
    flash("Invalid product ID", "danger");
    die(header("Location: " . get_url("list_data.php")));
}

//jed56 7-26-2024

$db = getDB();
$query = "SELECT product_id, product_name FROM products WHERE product_id = :product_id";
$stmt = $db->prepare($query);
$product = null;
try {
    $stmt->execute([":product_id" => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        flash("Product not found", "warning");
        die(header("Location: " . get_url("list_data.php")));
    }
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
    die(header("Location: " . get_url("list_data.php")));
}

if (!$is_admin) {
    flash("You do not have permission to delete this product", "danger");
    die(header("Location: " . get_url("list_data.php")));
}

$delete_query = "DELETE FROM products WHERE product_id = :product_id";
$stmt = $db->prepare($delete_query);
try {
    $stmt->execute([":product_id" => $product_id]);
    flash("Product deleted successfully", "success");
} catch (PDOException $e) {
    flash(var_export($e->errorInfo, true), "danger");
}

$redirect_url = get_url("list_data.php");

$previous_query_string = isset($_SERVER['HTTP_REFERER']) ? parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) : '';
if ($previous_query_string) {
    $redirect_url .= '?' . $previous_query_string;
}

header("Location: " . $redirect_url);
exit();
?>
