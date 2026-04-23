<?php
/**
 * 부조 생성 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$bujosCollection = $db->collection('bujos');
$categoriesCollection = $db->collection('bujo_categories');

$categories = $categoriesCollection->getAll();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => $_POST['name'] ?? '',
            'account' => (int)($_POST['account'] ?? 0),
            'dDay' => new DateTime($_POST['dDay'] ?? 'today'),
            'reason' => $_POST['reason'] ?? '',
            'etc' => $_POST['etc'] ?? '',
            'isBujo' => isset($_POST['isBujo']),
            'groupName' => $_POST['groupName'] ?? null,
            'categoryId' => $_POST['categoryId'] ?? '',
            'createdAt' => new DateTime(),
        ];

        // 검증
        if (empty($data['name']) || empty($data['categoryId'])) {
            throw new Exception('이름과 카테고리는 필수 항목입니다.');
        }

        $bujosCollection->add($data);
        setFlash('success', '부조가 등록되었습니다.');
        redirect('/sy_bujos_web/public/bujos/index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

renderHeader('부조 생성');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>새 부조 추가</h1>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">이름 *</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">금액 *</label>
        <input type="number" name="account" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">DDay *</label>
        <input type="date" name="dDay" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">카테고리 *</label>
        <select name="categoryId" class="form-select" required>
            <option value="">선택</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">사유 *</label>
        <input type="text" name="reason" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">그룹명</label>
        <input type="text" name="groupName" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">비고</label>
        <textarea name="etc" class="form-control" rows="2"></textarea>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="isBujo" class="form-check-input" value="1" checked>
            <label class="form-check-label">부조 완료 상태</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">등록</button>
        <a href="/sy_bujos_web/public/bujos/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<?php
renderFooter();
