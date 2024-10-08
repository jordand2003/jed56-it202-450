<?php
require(__DIR__ . "/../../partials/nav.php");
reset_session();
// jed56 07/09/2024
?>
<form onsubmit="return validate(this)" method="POST">
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" required />
    </div>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" required maxlength="30" />
    </div>
    <div>
        <label for="pw">Password</label>
        <input type="password" id="pw" name="password" required minlength="8" />
    </div>
    <div>
        <label for="confirm">Confirm</label>
        <input type="password" name="confirm" required minlength="8" />
    </div>
    <input type="submit" value="Register" />
</form>
<script src="helpers.js"></script>
<script>
    function validate(form) {
        //TODO 1: implement JavaScript validation
        //ensure it returns false for an error and true for success
        //jed56 7/10/24
            const email = form.email.value.trim();
            const username = form.username.value.trim();
            const password = form.password.value.trim();
            const confirm = form.confirm.value.trim();

            if (email === "") {
                flash("Email must not be empty");
                return false;
            }
            const emailPattern = /\S+@\S+\.\S+/;
            if (!emailPattern.test(email)) {
                flash("Invalid email address");
                return false;
            }
            const usernamePattern = /^[a-zA-Z0-9_-]{3,16}$/;
            if (!usernamePattern.test(username)) {
                flash("Username must only contain 3-16 characters a-z, 0-9, _, or -");
                return false;
            }
            if (password === "") {
                flash("Password must not be empty");
                return false;
            }
            if (password.length < 8) {
                flash("Password too short");
                return false;
            }
            if (confirm === "") {
                flash("Confirm password must not be empty");
                return false;
            }
            if (password !== confirm) {
                flash("Passwords must match");
                return false;
            }
        return true;
    }
</script>
<?php
//TODO 2: add PHP Code
// jed56 07/09/2024
if (isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirm"]) && isset($_POST["username"])) {
    $email = se($_POST, "email", "", false);
    $password = se($_POST, "password", "", false);
    $confirm = se($_POST, "confirm", "", false);
    $username = se($_POST, "username", "", false);
    //TODO 3
    $hasError = false;
    if (empty($email)) {
        flash("Email must not be empty", "danger");
        $hasError = true;
    }
    //sanitize
    $email = sanitize_email($email);
    //validate
    if (!is_valid_email($email)) {
        flash("Invalid email address", "danger");
        $hasError = true;
    }
    if (!is_valid_username($username)) {
        flash("Username must only contain 3-16 characters a-z, 0-9, _, or -", "danger");
        $hasError = true;
    }
    if (empty($password)) {
        flash("password must not be empty", "danger");
        $hasError = true;
    }
    if (empty($confirm)) {
        flash("Confirm password must not be empty", "danger");
        $hasError = true;
    }
    if (!is_valid_password($password)) {
        flash("Password too short", "danger");
        $hasError = true;
    }
    if (
        strlen($password) > 0 && $password !== $confirm
    ) {
        flash("Passwords must match", "danger");
        $hasError = true;
    }
    if (!$hasError) {
        //TODO 4
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO Users (email, password, username) VALUES(:email, :password, :username)");
        try {
            $stmt->execute([":email" => $email, ":password" => $hash, ":username" => $username]);
            flash("Successfully registered!", "success");
        } catch (Exception $e) {
            users_check_duplicate($e->errorInfo);
        }
    }
}
?>
<?php
require(__DIR__ . "/../../partials/flash.php");
?>