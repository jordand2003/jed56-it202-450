<?php
require_once(__DIR__ . "/../lib/functions.php");
//Note: this is to resolve cookie issues with port numbers
$domain = $_SERVER["HTTP_HOST"];
if (strpos($domain, ":")) {
    $domain = explode(":", $domain)[0];
}
$localWorks = true; //some people have issues with localhost for the cookie params
//if you're one of those people make this false

//this is an extra condition added to "resolve" the localhost issue for the session cookie
if (($localWorks && $domain == "localhost") || $domain != "localhost") {
    session_set_cookie_params([
        "lifetime" => 60 * 60,
        "path" => "$BASE_PATH",
        //"domain" => $_SERVER["HTTP_HOST"] || "localhost",
        "domain" => $domain,
        "secure" => true,
        "httponly" => true,
        "samesite" => "lax"
    ]);
}
session_start();


?>
<!-- include css and js files -->
<link rel="stylesheet" href="<?php echo get_url('styles.css'); ?>">
<script src="<?php echo get_url('helpers.js'); ?>"></script>
<nav>
    <ul>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php echo get_url('home.php'); ?>">Home</a></li>
            <li><a href="<?php echo get_url('profile.php'); ?>">Profile</a></li>
            <li><a href="<?php echo get_url('wishlist.php'); ?>">My Wishlist</a></li>
        <?php endif; ?>
        <?php if (!is_logged_in()) : ?>
            <li><a href="<?php echo get_url('login.php'); ?>">Login</a></li>
            <li><a href="<?php echo get_url('register.php'); ?>">Register</a></li>
        <?php endif; ?>
        <?php if (has_role("Admin")) : ?>
            <li><a href="<?php echo get_url('admin/create_role.php'); ?>">Create Role</a></li>
            <li><a href="<?php echo get_url('admin/list_roles.php'); ?>">List Roles</a></li>
            <li><a href="<?php echo get_url('admin/assign_roles.php'); ?>">Assign Roles</a></li>
            <li><a href="<?php echo get_url('admin/data_creation.php'); ?>">Add Product</a></li>
            <li><a href="<?php echo get_url('admin/fetch_api_data.php'); ?>">Fetch API Data</a></li>
            <li><a href="<?php echo get_url('admin/all_user_associations.php'); ?>">All User Associations</a></li>
            <li><a href="<?php echo get_url('admin/unassociated_data.php'); ?>">Unassociated Data</a></li>
            <li><a href="<?php echo get_url('admin/associate_entities.php'); ?>">Associate Entities</a></li>
        <?php endif; ?>
        <?php if (is_logged_in()) : ?>
            <li><a href="<?php echo get_url('list_data.php'); ?>">View Products</a></li>
            <li><a href="<?php echo get_url('logout.php'); ?>">Logout</a></li>
        <?php endif; ?>
    </ul>
</nav>