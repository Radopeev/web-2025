<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Register</h2>

<form method="POST">
    <input type="text" name="username" placeholder="Username" required /><br>
    <input type="email" name="email" placeholder="Email" required /><br>
    <input type="password" name="password" placeholder="Password" required /><br>
    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="/login">Login</a></p>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>