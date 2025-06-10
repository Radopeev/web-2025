<?php
if (!isset($username)) {
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ProjectHub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/styles/main_styles.css">
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_PATH; ?>/public/favicon.svg">
</head>

<body>
    <header>
        <div style="display: flex; align-items: center;">
            <h1>ProjectHub</h1>
            <?php if (!empty($_SESSION['user_id'])): ?>
                <?php
                $user = User::findById($_SESSION['user_id']);
                error_log(print_r($user, true));
                if (!empty($user['profile_picture'])): ?>
                    <div class="profile-pic-container">
                        <img src="/<?php echo htmlspecialchars($user['profile_picture']);?>" alt="Profile Picture">
                        <span style="color: #e0e7ff; font-weight: 500;"><?php echo htmlspecialchars($username);error_log($username) ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <nav>
            <a href="<?php echo BASE_PATH; ?>/landingPage"
                class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/landingPage') ? 'active' : ''; ?>">Home</a>
            <?php if (!empty($username) && $username !== 'Guest'): ?>
                <a href="<?php echo BASE_PATH; ?>/upload" class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/upload') ? 'active' : ''; ?>">Upload
                    Project</a>
                <a href="<?php echo BASE_PATH; ?>/profile" class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/profile') ? 'active' : ''; ?>">My
                    Profile</a>
                <a href="<?php echo BASE_PATH; ?>/favorites" class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/favorites') ? 'active' : ''; ?>">Favorites</a>
                <a href="<?php echo BASE_PATH; ?>/logout" style="margin-left:20px;">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>/login" class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/login') ? 'active' : ''; ?>">Login</a>
                <a href="<?php echo BASE_PATH; ?>/register"
                    class="<?php echo ($_SERVER['REQUEST_URI'] === BASE_PATH . '/register') ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
        </nav>
    </header>