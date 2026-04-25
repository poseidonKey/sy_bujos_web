<?php
/**
 * 부조 목록 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$bujosCollection = $db->collection('bujos');
$categoriesCollection = $db->collection('bujo_categories');

// 필터 처리
$categoryId = $_GET['category'] ?? null;
$sortBy = $_GET['sortBy'] ?? 'name';
$sortOrder = $_GET['sortOrder'] ?? 'asc';
$search = trim($_GET['search'] ?? '');
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;

if ($categoryId) {
    $allBujos = $bujosCollection->where(['categoryId' => $categoryId]);
} else {
    $allBujos = $bujosCollection->getAll();
}

// 이름 검색 필터
if ($search !== '') {
    $searchLower = mb_strtolower($search, 'UTF-8');
    $allBujos = array_filter($allBujos, function ($bujo) use ($searchLower) {
        $name = $bujo['name'] ?? '';
        if (is_array($name)) {
            $name = $name['stringValue'] ?? ($name[0] ?? '');
        }
        $name = (string)$name;
        return mb_strpos(mb_strtolower($name, 'UTF-8'), $searchLower, 0, 'UTF-8') !== false;
    });
    // array_filter preserves keys, reindex
    $allBujos = array_values($allBujos);
}

// 정렬 처리
usort($allBujos, function ($a, $b) use ($sortBy, $sortOrder) {
    $valueA = $a[$sortBy] ?? '';
    $valueB = $b[$sortBy] ?? '';

    // 금액은 숫자 비교
    if ($sortBy === 'account') {
        $valueA = (int)$valueA;
        $valueB = (int)$valueB;
    }

    // DDay 는 DateTime 객체 처리
    if ($sortBy === 'dDay') {
        $valueA = $valueA instanceof DateTime ? $valueA->format('Y-m-d') : (string)$valueA;
        $valueB = $valueB instanceof DateTime ? $valueB->format('Y-m-d') : (string)$valueB;
    }

    $result = strcmp((string)$valueA, (string)$valueB);
    return $sortOrder === 'desc' ? -$result : $result;
});

// isBujo 통계 계산
$receivedCount = 0;   // 받은 건수 (isBujo = true)
$givenCount = 0;      // 준 건수 (isBujo = false)
$receivedTotal = 0;   // 받은 금액 합계
$givenTotal = 0;      // 준 금액 합계
foreach ($allBujos as $bujo) {
    $isBujo = $bujo['isBujo'] ?? false;
    $account = is_array($bujo['account']) ? (int)($bujo['account']['integerValue'] ?? $bujo['account'][0] ?? 0) : (int)$bujo['account'];
    if ($isBujo) {
        $receivedCount++;
        $receivedTotal += $account;
    } else {
        $givenCount++;
        $givenTotal += $account;
    }
}
$balance = $receivedTotal - $givenTotal;  // 최종 잔액

// 페이지네이션
$totalItems = count($allBujos);
$totalPages = max(1, (int) ceil($totalItems / $perPage));
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $perPage;
$bujos = array_slice($allBujos, $offset, $perPage);

// 카테고리 목록
$categories = $categoriesCollection->getAll();

// categoryId 로 그룹핑
$categoriesById = [];
foreach ($categories as $cat) {
    $categoriesById[$cat['id']] = $cat;
}

renderHeader('부조 목록');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>부조 목록
            <span class="badge bg-primary">전체: <?= $totalItems ?>건</span>
        </h1>
        <div class="d-flex gap-2 flex-wrap">
            <span class="badge bg-success">받은 건수: <?= number_format($receivedCount) ?>건</span>
            <span class="badge bg-info">준 건수: <?= number_format($givenCount) ?>건</span>
            <span class="badge bg-success">받은 금액: ₩<?= number_format($receivedTotal) ?></span>
            <span class="badge bg-warning text-dark">준 금액: ₩<?= number_format($givenTotal) ?></span>
            <span class="badge <?= $balance >= 0 ? 'bg-primary' : 'bg-danger' ?>">최종 잔액: ₩<?= number_format($balance) ?></span>
        </div>
    </div>
</div>

<!-- 필터 -->
<form method="GET" class="row g-3 mb-4">
            <div class="col-auto">
                <div class="input-group"><input type="text" name="search" class="form-control" placeholder="이름 검색" value="<?= e($_GET['search'] ?? '') ?>"><button type="button" class="btn btn-outline-secondary" onclick="window.location='<?= strtok($_SERVER['REQUEST_URI'], '?') ?>'">초기화</button></div>
            </div>
    <div class="col-auto">
        <select name="category" class="form-select">
            <option value="">전체 카테고리</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= $categoryId === $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="sortBy" class="form-select">
            <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>이름</option>
            <option value="dDay" <?= $sortBy === 'dDay' ? 'selected' : '' ?>>DDay</option>
            <option value="account" <?= $sortBy === 'account' ? 'selected' : '' ?>>금액</option>
        </select>
    </div>
    <div class="col-auto">
        <select name="sortOrder" class="form-select">
            <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>오름차순</option>
            <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>내림차순</option>
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">정렬</button>
        <a href="/sy_bujos_web/public/bujos/create.php" class="btn btn-success">새 부조 추가</a>
    </div>
</form>

<!-- 목록 -->
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th style="width: 12.5%">
                    <a href="?sortBy=name&sortOrder=<?= $sortBy === 'name' && $sortOrder === 'asc' ? 'desc' : 'asc' ?><?= $categoryId ? '&category=' . urlencode($categoryId) : '' ?>" class="text-decoration-none text-white">
                        이름 <?= ($sortBy === 'name' ? ($sortOrder === 'asc' ? '↑' : '↓') : '') ?>
                    </a>
                </th>
                <th style="width: 12.5%">
                    <a href="?sortBy=account&sortOrder=<?= $sortBy === 'account' && $sortOrder === 'asc' ? 'desc' : 'asc' ?><?= $categoryId ? '&category=' . urlencode($categoryId) : '' ?>" class="text-decoration-none text-white">
                        금액 <?= ($sortBy === 'account' ? ($sortOrder === 'asc' ? '↑' : '↓') : '') ?>
                    </a>
                </th>
                <th style="width: 12.5%">
                    <a href="?sortBy=dDay&sortOrder=<?= $sortBy === 'dDay' && $sortOrder === 'asc' ? 'desc' : 'asc' ?><?= $categoryId ? '&category=' . urlencode($categoryId) : '' ?>" class="text-decoration-none text-white">
                        DDay <?= ($sortBy === 'dDay' ? ($sortOrder === 'asc' ? '↑' : '↓') : '') ?>
                    </a>
                </th>
                <th style="width: 12.5%">사유</th>
                <th style="width: 12.5%">카테고리</th>
                <th style="width: 12.5%">상태</th>
                <th style="width: 12.5%">비고</th>
                <th style="width: 12.5%">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bujos)): ?>
                <tr>
                    <td colspan="8" class="text-center">등록된 부조가 없습니다.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bujos as $bujo): ?>
                    <?php
                    // Firestore 데이터 안전 추출
                    $name = is_array($bujo['name']) ? ($bujo['name']['stringValue'] ?? $bujo['name'][0] ?? '') : e($bujo['name']);
                    $account = is_array($bujo['account']) ? (int)($bujo['account']['integerValue'] ?? $bujo['account'][0] ?? 0) : (int)$bujo['account'];
                    $reason = is_array($bujo['reason']) ? ($bujo['reason']['stringValue'] ?? $bujo['reason'][0] ?? '') : e($bujo['reason']);
                    $categoryId = isset($bujo['categoryId']) ? (is_array($bujo['categoryId']) ? ($bujo['categoryId']['stringValue'] ?? $bujo['categoryId'][0] ?? null) : $bujo['categoryId']) : null;
                    $isBujo = is_array($bujo['isBujo']) ? ($bujo['isBujo']['booleanValue'] ?? $bujo['isBujo'][0] ?? false) : $bujo['isBujo'];
                    $etc = is_array($bujo['etc']) ? ($bujo['etc']['stringValue'] ?? $bujo['etc'][0] ?? '') : e($bujo['etc'] ?? '');
                    $bujoId = is_array($bujo['id']) ? ($bujo['id']['stringValue'] ?? $bujo['id'][0] ?? '') : e($bujo['id']);

                    $dDayValue = $bujo['dDay'] ?? '';
                    if ($dDayValue instanceof DateTime) {
                        $dDayDisplay = $dDayValue->format('Y-m-d');
                    } elseif (is_array($dDayValue)) {
                        $dDayDisplay = $dDayValue['date'] ?? $dDayValue['stringValue'] ?? ($dDayValue[0] ?? '');
                    } else {
                        $dDayDisplay = e($dDayValue);
                    }
                    ?>
                    <tr>
                        <td><?= $name ?></td>
                        <td>₩<?= formatNumber($account) ?></td>
                        <td><?= $dDayDisplay ?></td>
                        <td><?= $reason ?></td>
                        <td><?= e($categoryId && isset($categoriesById[$categoryId]) ? $categoriesById[$categoryId]['name'] : '일반') ?></td>
                        <td><?= $isBujo ? '<span class="badge bg-success">부조함</span>' : '<span class="badge bg-secondary">받음</span>' ?></td>
                        <td><?= $etc ?></td>
                        <td>
                            <a href="/sy_bujos_web/public/bujos/edit.php?id=<?= $bujoId ?>" class="btn btn-sm btn-outline-primary">수정</a>
                            <a href="/sy_bujos_web/public/bujos/delete.php?id=<?= $bujoId ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('삭제하시겠습니까?')">삭제</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- 페이지네이션 -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <?php
        $pageGroup = ceil($page / 5);
        $totalGroups = ceil($totalPages / 5);
        $startPage = ($pageGroup - 1) * 5 + 1;
        $endPage = min($startPage + 4, $totalPages);

        // 이전 그룹 버튼 (<<)
        if ($pageGroup > 1):
            $prevGroupUrl = '?page=' . (($pageGroup - 1) * 5) . ($categoryId ? '&category=' . urlencode($categoryId) : '') . ($sortBy ? '&sortBy=' . urlencode($sortBy) : '') . ($sortOrder ? '&sortOrder=' . urlencode($sortOrder) : '');
        ?>
            <li class="page-item">
                <a class="page-link" href="<?= $prevGroupUrl ?>" aria-label="Previous Group">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- 이전 페이지 버튼 (<) -->
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <?php $prevUrl = '?page=' . ($page - 1) . ($categoryId ? '&category=' . urlencode($categoryId) : '') . ($sortBy ? '&sortBy=' . urlencode($sortBy) : '') . ($sortOrder ? '&sortOrder=' . urlencode($sortOrder) : ''); ?>
            <a class="page-link" href="<?= $prevUrl ?>" aria-label="Previous">
                <span aria-hidden="true">&lsaquo;</span>
            </a>
        </li>

        <!-- 페이지 번호 -->
        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <?php $pageNumUrl = '?page=' . $i . ($categoryId ? '&category=' . urlencode($categoryId) : '') . ($sortBy ? '&sortBy=' . urlencode($sortBy) : '') . ($sortOrder ? '&sortOrder=' . urlencode($sortOrder) : ''); ?>
                <a class="page-link" href="<?= $pageNumUrl ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <!-- 다음 페이지 버튼 (>) -->
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <?php $nextUrl = '?page=' . ($page + 1) . ($categoryId ? '&category=' . urlencode($categoryId) : '') . ($sortBy ? '&sortBy=' . urlencode($sortBy) : '') . ($sortOrder ? '&sortOrder=' . urlencode($sortOrder) : ''); ?>
            <a class="page-link" href="<?= $nextUrl ?>" aria-label="Next">
                <span aria-hidden="true">&rsaquo;</span>
            </a>
        </li>

        <!-- 다음 그룹 버튼 (>>) -->
        <?php if ($pageGroup < $totalGroups):
            $nextGroupUrl = '?page=' . ($pageGroup * 5 + 1) . ($categoryId ? '&category=' . urlencode($categoryId) : '') . ($sortBy ? '&sortBy=' . urlencode($sortBy) : '') . ($sortOrder ? '&sortOrder=' . urlencode($sortOrder) : '');
        ?>
            <li class="page-item">
                <a class="page-link" href="<?= $nextGroupUrl ?>" aria-label="Next Group">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php
renderFooter();
