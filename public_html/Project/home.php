<?php
require(__DIR__ . "/../../partials/nav.php");
?>
<h1>Home</h1>
<?php
//jed56 7/9/24
if (is_logged_in(true)) {
    error_log("Session data: " . var_export($_SESSION, true));
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>