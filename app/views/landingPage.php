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

$projects = Project::getAllProjects(); // or Project::searchProjects($searchQuery)
require_once 'app/views/landingPage.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - All Projects</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Light gray background */
        }
    </style>
</head>
<body">
<div>
    <header>
        <div">
            <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
            <a href="/logout">
                Logout
            </a>
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
                                <span class="text-xs font-medium px-2.5 py-0.5 rounded-full
                                    <?php
                                switch (htmlspecialchars($project['status'])) {
                                    case 'Active': echo 'bg-green-100 text-green-800'; break;
                                    case 'In Progress': echo 'bg-yellow-100 text-yellow-800'; break;
                                    case 'Completed': echo 'bg-blue-100 text-blue-800'; break;
                                    case 'Beta': echo 'bg-purple-100 text-purple-800'; break;
                                    default: echo 'bg-gray-100 text-gray-800'; break;
                                }
                                ?>">
                                    <?php echo htmlspecialchars($project['status']); ?>
                                </span>
                            <a href="/project_details?id=<?php echo htmlspecialchars($project['id']); ?>" class="text-blue-600 hover:underline text-sm font-medium">
                                View Details &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> Your App Name. All rights reserved.
</footer>
</body>
</html>
