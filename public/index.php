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
$bujosCount = $db->collection('bujos')->count();
// 카테고리 수
$categoriesCount = $db->collection('bujo_categories')->count();

?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">대시보드</h1>
    </div>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">전체 부조</h5>
                <p class="display-4"><?= $bujosCount ?></p>
                <a href="/sy_bujos_web/bujos/" class="btn btn-primary">보기</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">카테고리</h5>
                <p class="display-4"><?= $categoriesCount ?></p>
                <a href="/sy_bujos_web/categories/" class="btn btn-primary">보기</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">통계</h5>
                <p class="display-4">📊</p>
                <a href="/sy_bujos_web/statistics/" class="btn btn-primary">보기</a>
            </div>
        </div>
    </div>
</div>
<?php
renderFooter();
