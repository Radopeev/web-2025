<?php include __DIR__ . '/partials/header.php'; ?>

<?php
if (!isset($projects)) $projects = [];
if (!isset($page)) $page = 1;
if (!isset($totalPages)) $totalPages = 1;
?>

<link rel="stylesheet" href="<?php echo BASE_PATH; ?>/public/styles/profile_page_styles.css">

<main class="profile-main">
    <section class="profile-section">
        <h2>My Profile</h2>
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username ?? ''); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email ?? ''); ?></p>
        </div>
        <button class="edit-profile-btn" onclick="openEditProfileModal()">Edit Profile</button>
    </section>

    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <form action="<?php echo BASE_PATH; ?>/update_profile" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="New Password">
                </div>

                <div class="form-group">
                    <label for="profile_picture">Profile Picture:</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </div>

                <div class="modal-actions">
                    <button type="submit" class="save-btn">Save Changes</button>
                    <button type="button" class="cancel-btn" onclick="closeEditProfileModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <section class="projects-section">
        <h3>My Projects</h3>
        <?php
        $projectsPerPage = 5;
        $totalProjects = count($projects);
        $totalPages = ceil($totalProjects / $projectsPerPage);

        $startIndex = ($page - 1) * $projectsPerPage;
        $projectsToShow = array_slice($projects, $startIndex, $projectsPerPage);
        ?>

        <?php if (empty($projects)): ?>
            <p>You have no projects yet. <a href="<?php echo BASE_PATH; ?>/upload">Create your first project</a>.</p>
        <?php else: ?>
            <ul class="projects-list">
                <?php foreach ($projectsToShow as $project): ?>
                    <li class="project-item">
                        <div>
                            <a class="project-title" href="<?php echo BASE_PATH; ?>/project/details?id=<?php echo htmlspecialchars($project['id']); ?>">
                                <?php echo htmlspecialchars($project['title']); ?>
                            </a>
                            <span class="project-desc"><?php echo htmlspecialchars($project['description']); ?></span>
                        </div>
                        <form action="<?php echo BASE_PATH; ?>/delete_project" method="POST" class="delete-form">
                            <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="<?php echo BASE_PATH; ?>/profile?page=<?php echo $page - 1; ?>" class="pagination-btn">Previous</a>
                <?php endif; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo BASE_PATH; ?>/profile?page=<?php echo $page + 1; ?>" class="pagination-btn">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="<?php echo BASE_PATH; ?>/public/js/profile_scripts.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>