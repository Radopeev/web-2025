<?php include __DIR__ . '/partials/header.php'; ?>

<h2>Моят профил</h2>

<div>
    <p>Потребителско име: <?php echo htmlspecialchars($username); ?></p>
    <p>Имейл: <?php echo htmlspecialchars($email); ?></p>
    <p>Дата на регистрация: <?php echo htmlspecialchars($created_at); ?></p>
</div>

<h3>Проекти</h3>
<ul>
    <?php foreach ($projects as $project): ?>
        <li>
            <a href="/project_details?id=<?php echo htmlspecialchars($project['id']); ?>">
                <?php echo htmlspecialchars($project['title']); ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php include __DIR__ . '/partials/footer.php'; ?>