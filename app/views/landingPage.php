<?php
require_once dirname(__DIR__, 2) . '/config/global.php';
global $PATHS;
if (!isset($username)) {
    $username = 'Guest';
}
if (!isset($projects) || !is_array($projects)) {
    $projects = [];
}
if (!isset($searchQuery)) {
    $searchQuery = '';
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>

<link rel="stylesheet" href="<?= $PATHS['url_root'] ?? '/' ?>public/styles/landing_page_styles.css">

<main class="main-container">
    <section class="welcome-section">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p class="welcome-desc">Browse and discover projects, or upload your own.</p>
    </section>

    <section class="search-section">
        <form action="/landingPage" method="GET" class="search-form">
            <input
                    type="search"
                    name="search"
                    placeholder="Search projects by name or description..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    class="search-input"
            >
            <button type="submit" class="search-btn">
                Search Projects
            </button>
        </form>
    </section>

    <section class="projects-section">
        <h2>All Projects</h2>

        <?php if (empty($projects)): ?>
            <div class="no-projects">
                <p>
                    <?php if (!empty($searchQuery)): ?>
                        No projects found matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>". Please try a different search term.
                    <?php else: ?>
                        No projects available at this time.
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-info">
                            <h3>
                                <?php
                                $title = htmlspecialchars($project['title'] ?? '');
                                if (!empty($searchQuery)) {
                                    echo preg_replace(
                                        '/' . preg_quote($searchQuery, '/') . '/i',
                                        '<mark>$0</mark>',
                                        $title
                                    );
                                } else {
                                    echo $title;
                                }
                                ?>
                            </h3>
                            <p>
                                <?php
                                $desc = htmlspecialchars($project['description'] ?? '');
                                if (!empty($searchQuery)) {
                                    echo preg_replace(
                                        '/' . preg_quote($searchQuery, '/') . '/i',
                                        '<mark>$0</mark>',
                                        $desc
                                    );
                                } else {
                                    echo $desc;
                                }
                                ?>
                            </p>
                        </div>
                        <div class="project-actions">
                            <a href="/project/details?id=<?php echo htmlspecialchars($project['id']); ?>" class="details-link">
                                View Details &rarr;
                            </a>
                            <?php
                            $isFavorited = isset($_SESSION['user_id']) ? Project::isFavorited($_SESSION['user_id'], $project['id']) : false;
                            ?>
                            <form action="/project/<?php echo $isFavorited ? 'unstar' : 'star'; ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="project_id" value="<?php echo htmlspecialchars($project['id']); ?>">
                                <button type="submit" class="star-btn" title="<?php echo $isFavorited ? 'Remove from favorites' : 'Add to favorites'; ?>">
                                    <?php echo $isFavorited ? '★' : '☆'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>