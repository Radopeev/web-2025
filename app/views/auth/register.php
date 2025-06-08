<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- register.php -->
<link rel="stylesheet" href="/public/styles/auth_styles.css">

<div class="auth-container">
    <h2>Register</h2>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required />
        <input type="email" name="email" placeholder="Email" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="/login">Login</a></p>
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../partials/footer.php'; ?>