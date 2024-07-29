<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}

$entities = [];
$users = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entity_query = se($_POST, 'entity_query', '', false);
    $user_query = se($_POST, 'user_query', '', false);

    $db = getDB();

    $entity_stmt = $db->prepare("SELECT * FROM products WHERE product_name LIKE :entity_query LIMIT 25");
    $entity_stmt->execute([":entity_query" => "%$entity_query%"]);
    $entities = $entity_stmt->fetchAll(PDO::FETCH_ASSOC);

    $user_stmt = $db->prepare("SELECT id, username FROM Users WHERE username LIKE :user_query LIMIT 25");
    $user_stmt->execute([":user_query" => "%$user_query%"]);
    $users = $user_stmt->fetchAll(PDO::FETCH_ASSOC);
    //jed56 7-29-2024
}

if (isset($_POST['associate'])) {
    $selected_entities = $_POST['selected_entities'] ?? [];
    $selected_users = $_POST['selected_users'] ?? [];

    $db = getDB();
    foreach ($selected_entities as $product_id) {
        foreach ($selected_users as $user_id) {
            $stmt = $db->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (:user_id, :product_id) ON DUPLICATE KEY UPDATE product_id = :product_id");
            $stmt->execute([":user_id" => $user_id, ":product_id" => $product_id]);
        }
    }
    flash("Associations updated", "success");
    header("Location: " . get_url("admin/associate_entities.php"));
    exit();
}

?>

<h1>Associate Products with Users</h1>
<form method="POST">
    <div>
        <label for="entity_query">Entity (Product Name):</label>
        <input type="text" id="entity_query" name="entity_query" value="<?php se($_POST, 'entity_query'); ?>" />
    </div>
    <div>
        <label for="user_query">Username:</label>
        <input type="text" id="user_query" name="user_query" value="<?php se($_POST, 'user_query'); ?>" />
    </div>
    <button type="submit">Search</button>
</form>

<?php if (!empty($entities) || !empty($users)): ?>
    <form method="POST">
        <input type="hidden" name="entity_query" value="<?php se($_POST, 'entity_query'); ?>" />
        <input type="hidden" name="user_query" value="<?php se($_POST, 'user_query'); ?>" />
        <table>
            <thead>
                <tr>
                    <th>Products</th>
                    <th>Users</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php foreach ($entities as $entity): ?>
                            <div>
                                <input type="checkbox" name="selected_entities[]" value="<?php se($entity, 'product_id'); ?>" />
                                <?php se($entity, 'product_name'); ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    <td>
                        <?php foreach ($users as $user): ?>
                            <div>
                                <input type="checkbox" name="selected_users[]" value="<?php se($user, 'id'); ?>" />
                                <?php se($user, 'username'); ?>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="submit" name="associate">Apply Associations</button>
    </form>
<?php else: //jed56 7-29-2024 ?>
    <p>No results available</p>
<?php endif; ?>

<?php
require(__DIR__ . "/../../../partials/flash.php");
?>
