<?php
/**
 * 통계 페이지 - 카테고리별 통계
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$bujosCollection = $db->collection('bujos');
$categoriesCollection = $db->collection('bujo_categories');

// 전체 부조 데이터 가져오기
$bujos = $bujosCollection->getAll();
$categories = $categoriesCollection->getAll();

// 카테고리 ID 로 매핑
$categoriesById = [];
foreach ($categories as $cat) {
    $categoriesById[$cat['id']] = $cat;
}

// 카테고리별 통계 계산
$stats = [];
foreach ($bujos as $bujo) {
    $catId = $bujo['categoryId'] ?? 'unknown';

    if (!isset($stats[$catId])) {
        $stats[$catId] = [
            'count' => 0,
            'totalAmount' => 0,
            'bujoCount' => 0,  // isBujo=true 인 것만
            'bujoAmount' => 0,
        ];
    }

    $stats[$catId]['count']++;
    $stats[$catId]['totalAmount'] += (int)($bujo['account'] ?? 0);

    if ($bujo['isBujo']) {
        $stats[$catId]['bujoCount']++;
        $stats[$catId]['bujoAmount'] += (int)($bujo['account'] ?? 0);
    }
}

// 전체 합계
$totalCount = array_sum(array_column($stats, 'count'));
$totalAmount = array_sum(array_column($stats, 'totalAmount'));
$totalBujoCount = array_sum(array_column($stats, 'bujoCount'));
$totalBujoAmount = array_sum(array_column($stats, 'bujoAmount'));

renderHeader('통계');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>카테고리별 통계</h1>
    </div>
</div>

<!-- 전체 요약 -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title">전체 건수</h5>
                <p class="display-4"><?= $totalCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title">전체 금액</h5>
                <p class="display-5">₩<?= formatNumber($totalAmount) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5 class="card-title">부조 완료</h5>
                <p class="display-4"><?= $totalBujoCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5 class="card-title">부조 금액</h5>
                <p class="display-5">₩<?= formatNumber($totalBujoAmount) ?></p>
            </div>
        </div>
    </div>
</div>

<!-- 카테고리별 상세 통계 -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">카테고리별 상세 통계</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>카테고리</th>
                        <th>설명</th>
                        <th>건수</th>
                        <th>부조 완료</th>
                        <th>총 금액</th>
                        <th>부조 금액</th>
                        <th>비중</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($stats)): ?>
                        <tr>
                            <td colspan="7" class="text-center">통계 데이터가 없습니다.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($stats as $catId => $stat): ?>
                            <?php
                            $cat = $categoriesById[$catId] ?? ['name' => '미분류', 'description' => ''];
                            $percent = $totalAmount > 0 ? ($stat['totalAmount'] / $totalAmount * 100) : 0;
                            ?>
                            <tr>
                                <td><?= e($cat['name']) ?></td>
                                <td><?= e($cat['description'] ?? '') ?></td>
                                <td><?= $stat['count'] ?></td>
                                <td><?= $stat['bujoCount'] ?></td>
                                <td>₩<?= formatNumber($stat['totalAmount']) ?></td>
                                <td>₩<?= formatNumber($stat['bujoAmount']) ?></td>
                                <td>
                                    <div class="progress" style="width: 150px;">
                                        <div class="progress-bar" style="width: <?= number_format($percent, 1) ?>%">
                                            <?= number_format($percent, 1) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-dark">
                    <tr>
                        <th colspan="2">합계</th>
                        <th><?= $totalCount ?></th>
                        <th><?= $totalBujoCount ?></th>
                        <th>₩<?= formatNumber($totalAmount) ?></th>
                        <th>₩<?= formatNumber($totalBujoAmount) ?></th>
                        <th>100%</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php
renderFooter();
