<?php include __DIR__ . '/../partials/header.php'; ?>

<h2>Login</h2>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required /><br>
    <input type="password" name="password" placeholder="Password" required /><br>
    <button type="submit">Login</button>
</form>
<p>Don't have an account? <a href="/register">Register</a></p>

<?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
