<?php
require(__DIR__ . "/../../../partials/nav.php");

if (!has_role("Admin")) {
    flash("You don't have permission to view this page", "warning");
    die(header("Location: " . get_url("home.php")));
}
?>

<h1>Fetch API Data</h1>
<form method="POST" action="<?php echo get_url("admin/fetch_api_data_action.php"); ?>">
    <input type="submit" value="Fetch Data from API" />
</form>

<?php
require_once(__DIR__ . "/../../../partials/flash.php");
?>
