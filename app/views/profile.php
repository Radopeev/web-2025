<?php include __DIR__ . '/partials/header.php'; ?> 
 
<?php 
if (!isset($page)) $page = 1; 
if (!isset($totalPages)) $totalPages = 1; 
?> 
 
<link rel="stylesheet" href="/public/styles/profile_page_styles.css"> 
 
<main class="profile-main"> 
    <section class="profile-section"> 
        <h2>My Profile</h2> 
        <div class="profile-info"> 
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username ?? ''); ?></p> 
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email ?? ''); ?></p> 
            <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($created_at ?? 'Unknown'); ?></p> 
        </div>  
        <button class="edit-profile-btn" onclick="openEditProfileModal()">Edit Profile</button>  
    </section>  
  
    <div id="editProfileModal" class="modal">  
        <div class="modal-content">  
            <form action="/update_profile" method="POST">  
                <label for="username">Username:</label>  
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>  
  
                <label for="email">Email:</label>  
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>  
  
                <label for="password">Password:</label>  
                <input type="password" id="password" name="password" placeholder="New Password">  
  
                <div class="modal-actions">  
                    <button type="submit" class="save-btn">Save Changes</button>   
                    <button type="button" class="cancel-btn" onclick="closeEditProfileModal()">Cancel</button>  
                </div>  
            </form>  
        </div>  
    </div>  
  
    <section class="projects-section">  
        <h3>My Projects</h3>  
        <ul class="projects-list">  
            <?php foreach ($projects as $project): ?>  
                <li class="project-item">  
                    <div>  
                        <span class="project-title"><?php echo htmlspecialchars($project['title']); ?></span>  
                        <span class="project-desc"><?php echo htmlspecialchars($project['description']); ?></span>  
                    </div>   
                    <form action="/delete_project" method="POST" class="delete-form">   
                        <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">  
                        <button type="submit" class="delete-btn">Delete</button>  
                    </form>  
                </li>  
            <?php endforeach; ?>  
        </ul>  
        <div class="pagination">  
            <?php if ($page > 1): ?>  
                <a href="/profile?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>  
            <?php endif; ?>  
            <?php if ($page < $totalPages): ?>  
                <a href="/profile?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>   
            <?php endif; ?>   
        </div>   
    </section>   
   
    <section class="profile-picture-section">   
        <form action="/upload_profile_picture" method="POST" enctype="multipart/form-data" class="profile-picture-form">  
            <label for="profile_picture">Upload Profile Picture:</label>  
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>  
            <button type="submit" class="upload-btn">Upload</button>  
        </form>  
    </section>  
</main>  
  
<script>  
function openEditProfileModal() {  
    document.getElementById('editProfileModal').style.display = 'flex';  
}  
function closeEditProfileModal() {  
    document.getElementById('editProfileModal').style.display = 'none';  
}  
</script> 
 
<?php include __DIR__ . '/partials/footer.php'; ?> 