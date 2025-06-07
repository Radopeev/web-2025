<?php
if (!isset($username)) {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your App Name</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/styles.css">
</head>
<body>
    <header>
        <h1>Your App Name</h1>
        <nav>
            <a href="/landingPage">Home</a>
            <?php if (!empty($username) && $username !== 'Guest'): ?>
                <a href="/upload">Upload Project</a>
                <a href="/profile">My Profile</a>
                <a href="/logout" style="float:right; margin-left:20px;">Logout</a>
            <?php else: ?>
                <a href="/login">Login</a>
                <a href="/register">Register</a>
            <?php endif; ?>
        </nav>
        <hr>
    </header>
