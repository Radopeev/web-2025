<?php include __DIR__ . '/partials/header.php'; ?>

<h2>My Profile</h2>

<div>
    <p>Username: <?php echo htmlspecialchars($username); ?></p>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    <p>Registration Date: <?php echo htmlspecialchars($created_at); ?></p>
</div>

<button onclick="openEditProfileModal()">Edit Profile</button>

<div id="editProfileModal" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border:1px solid black;">
    <form action="/update_profile" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="New Password"><br>

        <button type="submit">Save Changes</button>
        <button type="button" onclick="closeEditProfileModal()">Cancel</button>
    </form>
</div>

<script>
function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'block';
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}
</script>

<h3>My Projects</h3>

<ul>
    <?php foreach ($projects as $project): ?>
        <li>
            <?php echo htmlspecialchars($project['title']); ?>
            <br>
            &emsp;<?php echo htmlspecialchars($project['description']); ?>
        </li>
    <?php endforeach; ?>
</ul>

<div>
    <?php if ($page > 1): ?>
        <a href="/profile?page=<?php echo $page - 1; ?>">Previous</a>
    <?php endif; ?>

    <?php if ($page < $totalPages): ?>
        <a href="/profile?page=<?php echo $page + 1; ?>">Next</a>
    <?php endif; ?>
</div>

<form action="/upload_profile_picture" method="POST" enctype="multipart/form-data">
    <label for="profile_picture">Upload Profile Picture:</label>
    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
    <button type="submit">Upload</button>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>