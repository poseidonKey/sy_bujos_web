<?php
/**
 * 카테고리 생성 페이지
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$categoriesCollection = $db->collection('bujo_categories');
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'createdAt' => new DateTime(),
        ];

        if (empty($data['name'])) {
            throw new Exception('이름은 필수 항목입니다.');
        }

        $categoriesCollection->add($data);
        setFlash('success', '카테고리가 등록되었습니다.');
        redirect('/sy_bujos_web/public/categories/index.php');
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

renderHeader('카테고리 생성');
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>새 카테고리 추가</h1>
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
        <label class="form-label">설명</label>
        <input type="text" name="description" class="form-control">
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">등록</button>
        <a href="/sy_bujos_web/public/categories/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<?php
renderFooter();
