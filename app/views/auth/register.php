<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/styles/auth_styles.css">

<div class="auth-container">
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="<?php echo BASE_PATH; ?>/login">Login</a></p>
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>