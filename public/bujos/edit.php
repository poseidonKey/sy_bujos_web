<?php
/**
 * 부조 수정 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$bujosCollection = $db->collection('bujos');
$categoriesCollection = $db->collection('bujo_categories');

$id = $_GET['id'] ?? null;
$error = null;

if (!$id) {
    setFlash('error', '잘못된 접근입니다.');
    redirect('/sy_bujos_web/public/bujos/index.php');
}

$bujo = $bujosCollection->get($id);
if (!$bujo) {
    setFlash('error', '부조 정보를 찾을 수 없습니다.');
    redirect('/sy_bujos_web/public/bujos/index.php');
}

$categories = $categoriesCollection->getAll();

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
        ];

        if (empty($data['name']) || empty($data['categoryId'])) {
            throw new Exception('이름과 카테고리는 필수 항목입니다.');
        }

        $bujosCollection->update($id, $data);
        setFlash('success', '부조 정보가 수정되었습니다.');
        redirect('/sy_bujos_web/public/bujos/index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

renderHeader('부조 수정');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>부조 수정</h1>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">이름 *</label>
        <input type="text" name="name" class="form-control" value="<?= e($bujo['name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">금액 *</label>
        <input type="number" name="account" class="form-control" value="<?= e($bujo['account']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">DDay *</label>
        <input type="date" name="dDay" class="form-control"
               value="<?= $bujo['dDay'] instanceof DateTime ? $bujo['dDay']->format('Y-m-d') : '' ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">카테고리 *</label>
        <select name="categoryId" class="form-select" required>
            <option value="">선택</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= e($cat['id']) ?>" <?= $bujo['categoryId'] === $cat['id'] ? 'selected' : '' ?>>
                    <?= e($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">사유 *</label>
        <input type="text" name="reason" class="form-control" value="<?= e($bujo['reason']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">그룹명</label>
        <input type="text" name="groupName" class="form-control" value="<?= e($bujo['groupName'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label">비고</label>
        <textarea name="etc" class="form-control" rows="2"><?= e($bujo['etc'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="checkbox" name="isBujo" class="form-check-input" value="1" <?= $bujo['isBujo'] ? 'checked' : '' ?>>
            <label class="form-check-label">부조 완료 상태</label>
        </div>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">수정</button>
        <a href="/sy_bujos_web/public/bujos/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<?php
renderFooter();
