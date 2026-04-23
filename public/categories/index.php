<?php
/**
 * 카테고리 목록 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$categoriesCollection = $db->collection('bujo_categories');
$categories = $categoriesCollection->getAll();

// 메시지 표시
$successMsg = getFlash('success');
$errorMsg = getFlash('error');

renderHeader('카테고리 관리');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>카테고리 관리</h1>
    </div>
</div>

<?php if ($successMsg): ?>
    <div class="alert alert-success"><?= e($successMsg) ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert alert-danger"><?= e($errorMsg) ?></div>
<?php endif; ?>

<div class="mb-4">
    <a href="/sy_bujos_web/public/categories/create.php" class="btn btn-success">새 카테고리 추가</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>이름</th>
                <th>설명</th>
                <th>생성일</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-center">등록된 카테고리가 없습니다.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?= e($cat['name']) ?></td>
                        <td><?= e($cat['description'] ?? '') ?></td>
                        <td><?= $cat['createdAt'] instanceof DateTime ? $cat['createdAt']->format('Y-m-d H:i') : '' ?></td>
                        <td>
                            <a href="/sy_bujos_web/public/categories/edit.php?id=<?= e($cat['id']) ?>" class="btn btn-sm btn-outline-primary">수정</a>
                            <a href="/sy_bujos_web/public/categories/delete.php?id=<?= e($cat['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('삭제하시겠습니까?')">삭제</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
renderFooter();
