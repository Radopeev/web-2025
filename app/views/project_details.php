<?php
require_once APP_ROOT . 'app/models/Project.php';

if (!isset($_GET['id'])) {
    echo "Project ID is missing.";
    exit;
}

$projectId = (int)$_GET['id'];
$project = Project::getProjectById($projectId);
$files = Project::getFilesByProjectId($projectId);
$instruments = Project::getInstrumentsByProjectId($projectId);

if (!$project) {
    echo "Project not found.";
    exit;
}
?>

<?php include __DIR__ . '/partials/header.php'; ?>

<div>
    <h2>Проект: <?php echo htmlspecialchars($project['title']); ?></h2>
    <p><strong>Описание:</strong> <?php echo htmlspecialchars($project['description']); ?></p>
    <p><strong>Дата на създаване:</strong> <?php echo htmlspecialchars($project['created_at']); ?></p>
</div>

<h3>Файлове</h3>
<ul>
    <?php if (empty($files)): ?>
        <li>Няма качени файлове за този проект.</li>
    <?php else: ?>
        <?php foreach ($files as $file): ?>
            <li>
                <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $file['original_name'])): ?>
                    <img src="/public/uploads/sources/<?php echo htmlspecialchars($file['file_path']); ?>" alt="<?php echo htmlspecialchars($file['original_name']); ?>" style="max-width: 200px; max-height: 200px;">
                <?php else: ?>
                    <a href="/<?php echo htmlspecialchars($file['file_path']); ?>" target="_blank">
                        <?php echo htmlspecialchars($file['original_name']); ?>
                    </a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<h3>Инструменти</h3>
<ul>
    <?php if (empty($instruments)): ?>
        <li>Няма добавени инструменти за този проект.</li>
    <?php else: ?>
        <?php foreach ($instruments as $instrument): ?>
            <li>
                <strong><?php echo htmlspecialchars($instrument['name']); ?></strong> (<?php echo htmlspecialchars($instrument['type']); ?>):
                <?php echo htmlspecialchars($instrument['description']); ?>
                <a href="<?php echo htmlspecialchars($instrument['access_link']); ?>" target="_blank">Достъп</a>
            </li>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>

<?php include __DIR__ . '/partials/footer.php'; ?>