<?php
// This file (app/views/landingPage.php) will now receive all necessary variables
// directly from the LandingPageController. No need for data fetching or pagination
// calculations here.

// Expected variables from LandingPageController:
// $username
// $searchQuery
// $projects (already paginated and filtered)
// $pagination (containing currentPage, totalPages, totalProjects, itemsPerPage)

// Ensure variables are set, though they should be coming from the controller.
// This is a defensive check, good practice if you can't guarantee the controller
// will always set them (though it should in a well-structured app).
if (!isset($username)) {
    $username = 'Guest';
}
if (!isset($searchQuery)) {
    $searchQuery = '';
}
if (!isset($projects)) {
    $projects = [];
}
if (!isset($pagination)) {
    $pagination = [
        'currentPage' => 1,
        'totalPages' => 1,
        'totalProjects' => 0,
        'itemsPerPage' => 5,
        'searchQuery' => ''
    ];
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>

    <div>
        <header>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            </div>
        </header>

        <section>
            <form action="/landingPage" method="GET">
                <input
                        type="search"
                        name="search"
                        placeholder="Search projects by name or description..."
                        value="<?php echo htmlspecialchars($searchQuery); ?>">
                <button
                        type="submit">
                    Search Projects
                </button>
            </form>
        </section>

        <section>
            <h2>All Projects</h2>

            <?php if (empty($projects)): ?>
                <div>
                    <p>
                        <?php if (!empty($searchQuery)): ?>
                            No projects found matching "<?php echo htmlspecialchars($searchQuery); ?>". Please try a different search term.
                        <?php else: ?>
                            No projects available at this time.
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <div>
                    <?php foreach ($projects as $project): ?>
                        <div>
                            <div>
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p><?php echo htmlspecialchars($project['description']); ?></p>
                            </div>
                            <div>
                                <a href="/project/details?id=<?php echo htmlspecialchars($project['id']); ?>" class="text-blue-600 hover:underline text-sm font-medium">
                                    View Details &rarr;
                                </a>
                            </div>
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

<?php include __DIR__ . '/partials/footer.php'; ?>