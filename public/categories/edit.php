<?php
/**
 * 카테고리 수정 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$categoriesCollection = $db->collection('bujo_categories');

$id = $_GET['id'] ?? null;
$error = null;

if (!$id) {
    setFlash('error', '잘못된 접근입니다.');
    redirect('/sy_bujos_web/public/categories/index.php');
}

$category = $categoriesCollection->get($id);
if (!$category) {
    setFlash('error', '카테고리 정보를 찾을 수 없습니다.');
    redirect('/sy_bujos_web/public/categories/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        if (empty($data['name'])) {
            throw new Exception('이름은 필수 항목입니다.');
        }

        $categoriesCollection->update($id, $data);
        setFlash('success', '카테고리 정보가 수정되었습니다.');
        redirect('/sy_bujos_web/public/categories/index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

renderHeader('카테고리 수정');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>카테고리 수정</h1>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<form method="POST" class="row g-3">
    <div class="col-md-6">
        <label class="form-label">이름 *</label>
        <input type="text" name="name" class="form-control" value="<?= e($category['name']) ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">설명</label>
        <input type="text" name="description" class="form-control" value="<?= e($category['description'] ?? '') ?>">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">수정</button>
        <a href="/sy_bujos_web/public/categories/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<?php
renderFooter();
