<?php

declare(strict_types= 1);

$config = require __DIR__ . '/config.php';

$dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
$pdo = new PDO($dsn,$config->db->user, $config->db->password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

function e($string){
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = :id');
$stmt->execute([':id'=> $id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$article){
    http_response_code(404);
    exit('Article Not Found!!');
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($article['title']) ?> - NewsHub</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="main-container">
            <!-- Header Section -->
            <div class="header-section">
                <div class="header-content">
                    <div class="d-flex justify-content-between gap-3 align-items-center mb-3">
                        <div class="d-flex gap-3 align-items-center">
                            <button class="btn btn-outline-light btn-sm" onclick="window.print()">
                                <i class="bi bi-printer"></i>
                            </button>
                            <button class="btn btn-outline-light btn-sm" onclick="navigator.share ? navigator.share({title: document.title, url: window.location.href}) : copyToClipboard(window.location.href)">
                                <i class="bi bi-share"></i>
                            </button>
                        </div>
                        <a href="index.php" class="btn btn-back">
                            <i class="bi bi-arrow-left me-2"></i>Back to News
                        </a>
                    </div>
                    <h1 class="main-title text-center">
                        <i class="bi bi-newspaper me-3"></i>Article View
                    </h1>
                </div>
            </div>

            <!-- Article Content -->
            <div class="content-section">
                <article class="article-content">
                    <!-- Article Header -->
                    <div class="article-header">
                        <h1 class="article-title"><?= e($article['title']) ?></h1>
                        <div class="article-meta">
                            <span>
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= e(date('F j, Y', strtotime($article['published_date']))) ?>
                            </span>
                            <span>
                                <i class="bi bi-clock me-1"></i>
                                <?= e(date('g:i A', strtotime($article['published_date']))) ?>
                            </span>
                            <?php if($article['category']): ?>
                                <span class="category-badge">
                                    <?= e(ucfirst($article['category'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Article Image -->
                    <?php if($article['image_url']): ?>
                        <div class="text-center mb-4">
                            <img src="<?= e($article['image_url']) ?>"
                                 class="article-image img-fluid"
                                 alt="<?= e($article['title']) ?>"
                                 loading="lazy"
                                 onerror="this.style.display='none'">
                        </div>
                    <?php endif; ?>

                    <!-- Article Description -->
                    <div class="article-content">
                        <?= nl2br(e($article['description'])) ?>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center mt-4 pt-4 border-top">
                        <a href="<?= e($article['url']) ?>"
                           target="_blank"
                           class="btn btn-search btn-lg">
                            <i class="bi bi-arrow-up-right me-2"></i>Read Full Article
                        </a>
                        <a href="index.php" class="btn btn-outline-secondary btn-lg">
                            <i class="bi bi-collection me-2"></i>Browse More News
                        </a>
                    </div>

                    <!-- Article Footer -->
                    <div class="mt-5 pt-4 border-top text-center">
                        <p class="text-muted mb-2">
                            <i class="bi bi-info-circle me-1"></i>
                            This article was aggregated from external sources
                        </p>
                        <small class="text-muted">
                            Published on <?= e(date('F j, Y \a\t g:i A', strtotime($article['published_date']))) ?>
                        </small>
                    </div>
                </article>
            </div>
        </div>

        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    // Show a simple alert or toast notification
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success alert-dismissible';
                    toast.innerHTML = '<i class="bi bi-check-circle me-2"></i>Link copied to clipboard!';
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                });
            }
        </script>
    </body>
</html>
