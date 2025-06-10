<?php include __DIR__ . '/../partials/header.php'; ?>

<link rel="stylesheet" href="/public/styles/auth_styles.css">

<div class="auth-container">
    <h2>Login</h2>
    <form method="POST">
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="/register">Register</a></p>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>