<?php
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

<link rel="stylesheet" href="/public/styles/landing_page_styles.css">

<main class="main-container">
    <section class="welcome-section">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p class="welcome-desc">Browse and discover projects, or upload your own.</p>
    </section>

    <section class="search-section">
        <form action="/landingPage" method="GET" class="search-form">
            <input type="search" name="search" placeholder="Search projects by name or description..."
                value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input">
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
                        No projects found matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>". Please try
                        a different search term.
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
                    <?php endforeach; ?>
                </div>

                ---
                <?php if ($pagination['totalPages'] > 1): ?>
                    <nav aria-label="Projects Pagination">
                        <ul style="display: flex; list-style: none; padding: 0;">
                            <?php if ($pagination['currentPage'] > 1): ?>
                                <li style="margin-right: 10px;">
                                    <a href="/landingPage?page=<?php echo $pagination['currentPage'] - 1; ?><?php echo !empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : ''; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                                <li style="margin-right: 10px;">
                                    <a href="/landingPage?page=<?php echo $i; ?><?php echo !empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : ''; ?>"
                                        <?php if ($i === $pagination['currentPage']) echo 'style="font-weight: bold; text-decoration: underline;"'; ?>>
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['currentPage'] < $pagination['totalPages']): ?>
                                <li>
                                    <a href="/landingPage?page=<?php echo $pagination['currentPage'] + 1; ?><?php echo !empty($pagination['searchQuery']) ? '&search=' . urlencode($pagination['searchQuery']) : ''; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php endif; ?>
        </section>
    </div>
    =======
    <link rel="stylesheet" href="/public/styles/landing_page_styles.css">

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
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>