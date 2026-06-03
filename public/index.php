<?php
/**
 * 메인 엔트리 포인트
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/layout.php';

renderHeader('대시보드');

$db = db();

// 전체 부조 수
$bujos = $db->collection('bujos')->getAll();
$bujosCount = count($bujos);
// 카테고리 수
$categories = $db->collection('bujo_categories')->getAll();
$categoriesCount = count($categories);

// 부조 완료 건수와 금액
$bujoCompletedCount = 0;
$bujoCompletedAmount = 0;
foreach ($bujos as $bujo) {
    if ($bujo['isBujo']) {
        $bujoCompletedCount++;
        $bujoCompletedAmount += (int)($bujo['account'] ?? 0);
    }
}

?>
<div class="row mb-4">
    <div class="col-12 d-flex align-items-center justify-content-between">
        <h1 class="mb-0">대시보드</h1>
        <div class="ms-auto">
            <a href="/sy_bujos_web/public/admin/backup.php" class="btn btn-primary">
                백업/복원
            </a>
        </div>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title">전체 부조</h5>
                <p class="display-4"><?= $bujosCount ?></p>
                <a href="/sy_bujos_web/public/bujos/index.php" class="btn btn-light">보기</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title">부조 완료</h5>
                <p class="display-4"><?= $bujoCompletedCount ?></p>
                <small>₩<?= formatNumber($bujoCompletedAmount) ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5 class="card-title">카테고리</h5>
                <p class="display-4"><?= $categoriesCount ?></p>
                <a href="/sy_bujos_web/public/categories/index.php" class="btn btn-light">보기</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5 class="card-title">통계</h5>
                <p class="display-4">📊</p>
                <a href="/sy_bujos_web/public/statistics/index.php" class="btn btn-dark">보기</a>
            </div>
        </div>
    </div>
</div>
<?php
renderFooter();
