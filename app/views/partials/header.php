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
    <link rel="stylesheet" href="/public/styles/main_styles.css">
</head>
<body>
    <header>
        <div style="display: flex; align-items: center;">
            <h1>Your App Name</h1>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php
                $user = User::findById($_SESSION['user_id']);
                if (!empty($user['profile_picture'])): ?>
                    <div class="profile-pic-container">
                        <img src="/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                        <span style="color: #e0e7ff; font-weight: 500;"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <nav>
            <a href="/landingPage" class="<?php echo ($_SERVER['REQUEST_URI'] === '/landingPage') ? 'active' : ''; ?>">Home</a>
            <?php if (!empty($username) && $username !== 'Guest'): ?>
                <a href="/upload" class="<?php echo ($_SERVER['REQUEST_URI'] === '/upload') ? 'active' : ''; ?>">Upload Project</a>
                <a href="/profile" class="<?php echo ($_SERVER['REQUEST_URI'] === '/profile') ? 'active' : ''; ?>">My Profile</a>
                <a href="/logout" style="margin-left:20px;">Logout</a>
            <?php else: ?>
                <a href="/login" class="<?php echo ($_SERVER['REQUEST_URI'] === '/login') ? 'active' : ''; ?>">Login</a>
                <a href="/register" class="<?php echo ($_SERVER['REQUEST_URI'] === '/register') ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
        </nav>
    </header>
