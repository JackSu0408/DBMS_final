<?php
// logout.php - Handles user logout by clearing session data
session_start();

// Log the logout attempt for debugging
error_log("Logout attempt - Session ID: " . session_id());
error_log("User ID before logout: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session cookie, also delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session
session_destroy();

// Log successful logout
error_log("Session destroyed successfully");

// Redirect to index/login page
header("Location: index.html");
exit();
?>
