<?php

declare(strict_types= 1);

$config = require __DIR__ . '/config.php';

$dsn = "mysql:host={$config->db->host};dbname={$config->db->dbname};charset={$config->db->charset}";
$pdo = new PDO($dsn,$config->db->user, $config->db->password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Basic Helper Functions

function e($string){
    return htmlspecialchars((string)$string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
$category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
$page = max(1, (int)($_GET['page']) ?? 1);
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Build Where Clause Safely
$where = "1=1";
$params = [];

if($search !== '') {
    $where .= ' AND (title LIKE :search OR description LIKE :search)';
    $params[':search'] = "%{$search}%";;
}

if($category !== "") {
    $where .= " AND category = :category";
    $params[":category"] = $category;
}

// Total Count
$totalstmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE {$where}");
$totalstmt->execute($params);
$total = (int)$totalstmt->fetchColumn();
$pages = (int)ceil($total / $per_page);

// Fetch Categories For Filtering Dropdown
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM articles WHERE category IS NOT NULL ORDER BY category");
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
// Fetch Articles
$sql = "SELECT * FROM articles WHERE {$where} ORDER BY published_date DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);

foreach($params as $key => $value){
    $stmt->bindValue($key, $value);
}

$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>NewsHub - Your Daily News Aggregator</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="style.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body>
        <div class="main-container">
            <div class="header-section">
                <div class="header-content">
                    <a href="index.php" class="d-block mb-5 text-decoration-none text-reset">
                        <h1 class="main-title fw-bold">
                            <i class="bi bi-newspaper me-3"></i>NewsHub
                        </h1>
                        <p class="main-subtitle">Stay Informed with the Latest News from Around the World!!!</p>
                    </a>
                </div>
            </div>

            <div class="search-section">
                <form action="index.php" method="GET" class="search-form">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0" style="border: 2px solid var(--border-color); border-right: none; border-radius: 12px 0 0 12px;">
                                    <i class="bi bi-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search articles..." value="<?= e($search) ?>" style="border-radius: 0 12px 12px 0;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= e($cat) ?>" <?= $cat === $category ? 'selected' : '' ?>>
                                        <?= e(ucfirst($cat)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-search w-100">
                                <i class="bi bi-funnel me-2"></i>Filter News
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="content-section">

                <?php if(empty($articles)): ?>
                    <div class="no-results">
                        <i class="bi bi-newspaper"></i>
                        <h3>No Articles Found</h3>
                        <p>Try Adjusting Your Search Criteria or Browse All Categories!!! </p>
                        <a href="index.php" class="btn btn-search mt-3">
                            <i class="bi bi-arrow-clockwise me-2"></i>Reset Filters
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Results Summary -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="results-info">
                            <h5 class="mb-1">
                                <i class="bi bi-collection me-2 text-primary"></i>
                                <?= $total ?> Article<?= $total !== 1 ? 's' : '' ?> Found
                            </h5>
                            <?php if($search || $category): ?>
                                <small class="text-muted">
                                    <?php if($search): ?>
                                        Search: "<strong><?= e($search) ?></strong>"
                                    <?php endif; ?>
                                    <?php if($search && $category): ?> • <?php endif; ?>
                                    <?php if($category): ?>
                                        Category: <strong><?= e(ucfirst($category)) ?></strong>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        <div class="view-options">
                            <small class="text-muted">Page <?= $page ?> of <?= $pages ?></small>
                        </div>
                    </div>

                    <!-- Articles Grid -->
                    <div class="row g-4">
                        <?php foreach($articles as $article): ?>
                            <div class="col-lg-4 col-md-6">
                                <article class="card news-card h-100">
                                    <?php if($article['image_url']): ?>
                                        <div class="position-relative overflow-hidden">
                                            <img src="<?= e($article['image_url']) ?>"
                                                 class="card-img-top"
                                                 alt="<?= e($article['title']) ?>"
                                                 loading="lazy"
                                                 onerror="this.style.display='none'">
                                            <?php if($article['category']): ?>
                                                <span class="position-absolute top-0 start-0 m-3 category-badge">
                                                    <?= e(ucfirst($article['category'])) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <div class="card-meta">
                                            <i class="bi bi-calendar3"></i>
                                            <span><?= e(date('M j, Y', strtotime($article['published_date']))) ?></span>
                                            <span class="ms-auto">
                                                <i class="bi bi-clock"></i>
                                                <?= e(date('g:i A', strtotime($article['published_date']))) ?>
                                            </span>
                                        </div>
                                        <h5 class="card-title">
                                            <a href="view.php?id=<?= e((int)$article['id']) ?>">
                                                <?= e($article['title']) ?>
                                            </a>
                                        </h5>
                                        <p class="card-text flex-grow-1">
                                            <?= e(substr($article['description'] ?? '', 0, 120)) ?>
                                            <?= strlen($article['description'] ?? '') > 120 ? '...' : '' ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <a href="<?= e($article['url']) ?>" target="_blank" class="btn btn-read-more btn-sm">
                                                <i class="bi bi-arrow-up-right me-1"></i>Read Full Article
                                            </a>
                                            <small class="text-muted">
                                                <i class="bi bi-eye"></i>
                                            </small>
                                        </div>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Enhanced Pagination -->
                    <?php if($pages > 1): ?>
                        <nav aria-label="News pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php
                                $start = max(1, $page - 2);
                                $end = min($pages, $page + 2);

                                if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=1">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=<?= $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if($end < $pages): ?>
                                    <?php if($end < $pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=<?= $pages ?>"><?= $pages ?></a>
                                    </li>
                                <?php endif; ?>

                                <?php if($page < $pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?search=<?= urlencode($search) ?>&category=<?= urlencode($category) ?>&page=<?= $page + 1 ?>" aria-label="Next">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>

