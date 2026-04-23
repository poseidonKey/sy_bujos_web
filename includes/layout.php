<?php
/**
 * 레이아웃 템플릿 함수
 */

function renderHeader(string $title = 'BUJOS'): void {
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - BUJOS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { min-height: 100vh; background: #f5f5f5; }
        .sidebar { min-height: 100vh; background: #f8f9fa; }
        .content { padding: 2rem 0; }
        .card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .table { border-radius: 8px; overflow: hidden; }
        .table thead { font-weight: 600; }
        .badge { padding: 0.4em 0.8em; font-weight: 500; }
        .btn { border-radius: 8px; font-weight: 500; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/sy_bujos_web/">BUJOS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/sy_bujos_web/public/bujos/index.php">부조 목록</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sy_bujos_web/public/categories/index.php">카테고리</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/sy_bujos_web/public/statistics/index.php">통계</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container content">
<?php
}

function renderFooter(): void {
?>
    </main>
    <footer class="bg-dark text-light text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0">&copy; 2026 BUJOS Management System</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}
