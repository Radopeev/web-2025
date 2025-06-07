<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['user_id'])) {
    $user = User::findById($_SESSION['user_id']);
    if (!empty($user) && !empty($user['profile_picture'])) {
        $username = !empty($user['username']) ? $user['username'] : 'Unknown User';
        ?>
        <div class="profile-pic-container">
            <img src="/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
            <span style="color: #e0e7ff; font-weight: 500;">
                <?php echo htmlspecialchars($username); ?>
            </span>
        </div>
        <?php
    }
}
?>