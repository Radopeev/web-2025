<?php include __DIR__ . '/partials/header.php'; ?>

<link rel="stylesheet" href="/public/styles/favorites_page_styles.css">

<main class="favorites-main">
    <h2>My Favorite Projects</h2>
    <ul class="projects-list">
        <?php foreach ($projects as $project): ?>
            <li>
                <a href="/project/details?id=<?php echo htmlspecialchars($project['id']); ?>">
                    <?php echo htmlspecialchars($project['title']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
<?php include __DIR__ . '/partials/footer.php'; ?>