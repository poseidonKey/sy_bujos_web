<?php
/**
 * 백업/복원 페이지 - Replace only
 * 대상 컬렉션: bujos, bujo_categories
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/layout.php';

$db = db();
$bujosCollection = $db->collection('bujos');
$categoriesCollection = $db->collection('bujo_categories');

$successMsg = null;
$errorMsg = null;

function downloadJson(string $filename, $data): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$action = $_GET['action'] ?? ($_POST['action'] ?? null);

if ($action === 'backup') {
    try {
        $bujos = $bujosCollection->getAll();
        $categories = $categoriesCollection->getAll();

        $payload = [
            'schemaVersion' => 1,
            'exportedAt' => (new DateTime())->format(DateTime::ATOM),
            'collections' => [
                'bujos' => $bujos,
                'bujo_categories' => $categories,
            ],
        ];

        $filename = 'backup_' . date('Ymd_His') . '.json';
        downloadJson($filename, $payload);
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if ($action === 'restore') {
    // Replace with uploaded JSON
    $confirm = isset($_POST['confirm']) ? (string)$_POST['confirm'] : '';
    if ($confirm !== 'YES') {
        $errorMsg = 'Replace 복원은 확인 입력(YES)이 필요합니다.';
    } else {
        try {
            if (!isset($_FILES['backupFile']) || !is_uploaded_file($_FILES['backupFile']['tmp_name'])) {
                throw new Exception('업로드된 백업 파일이 없습니다.');
            }

            $raw = file_get_contents($_FILES['backupFile']['tmp_name']);
            $json = json_decode($raw, true);

            if (!is_array($json)) {
                throw new Exception('JSON 파싱에 실패했습니다.');
            }

            if (($json['schemaVersion'] ?? null) !== 1) {
                throw new Exception('지원하지 않는 백업 스키마 버전입니다.');
            }

            $collections = $json['collections'] ?? [];
            if (!is_array($collections)) {
                throw new Exception('collections 구조가 올바르지 않습니다.');
            }

            $bujos = $collections['bujos'] ?? [];
            $categories = $collections['bujo_categories'] ?? [];

            if (!is_array($bujos) || !is_array($categories)) {
                throw new Exception('bujos / bujo_categories 데이터 구조가 올바르지 않습니다.');
            }

            // Replace: 기존 전체 삭제
            $bujosCollection->deleteAll();
            $categoriesCollection->deleteAll();

            // Restore: id 유지 set
            $bujoOk = 0;
            $bujoFail = 0;

            foreach ($bujos as $doc) {
                $id = $doc['id'] ?? null;
                if (!$id) {
                    $bujoFail++;
                    continue;
                }

                // Firestore set()는 data 필드만 받음 (id는 제거)
                $data = $doc;
                unset($data['id']);

                try {
                    $bujosCollection->set((string)$id, $data);
                    $bujoOk++;
                } catch (Exception $e) {
                    $bujoFail++;
                }
            }

            $catOk = 0;
            $catFail = 0;

            foreach ($categories as $doc) {
                $id = $doc['id'] ?? null;
                if (!$id) {
                    $catFail++;
                    continue;
                }

                $data = $doc;
                unset($data['id']);

                try {
                    $categoriesCollection->set((string)$id, $data);
                    $catOk++;
                } catch (Exception $e) {
                    $catFail++;
                }
            }

            $successMsg = "복원 완료(Replace). bujos: 성공 {$bujoOk} / 실패 {$bujoFail}, bujo_categories: 성공 {$catOk} / 실패 {$catFail}";
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }
    }
}

renderHeader('백업/복원');

?>
<div class="row mb-4">
    <div class="col-12">
        <h1>백업 / 복원</h1>
        <div class="text-muted">
            대상 컬렉션: <code>bujos</code>, <code>bujo_categories</code><br>
            복원은 <b>Replace(덮어쓰기)</b>만 지원합니다. 기존 데이터가 삭제됩니다.
        </div>
    </div>
</div>

<?php if ($successMsg): ?>
    <div class="alert alert-success"><?= e($successMsg) ?></div>
<?php endif; ?>
<?php if ($errorMsg): ?>
    <div class="alert alert-danger"><?= e($errorMsg) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">백업 다운로드</div>
            <div class="card-body">
                <p class="mb-3">
                    Firestore의 전체 데이터를 JSON으로 다운로드합니다.
                </p>
                <a href="/sy_bujos_web/public/admin/backup.php?action=backup" class="btn btn-primary">
                    백업 JSON 다운로드
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">복원(Replace)</div>
            <div class="card-body">
                <p class="text-danger mb-3">
                    복원 실행 시 기존 데이터가 모두 삭제됩니다.
                </p>

                <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('정말로 Replace 복원을 실행할까요?');">
                    <input type="hidden" name="action" value="restore">

                    <div class="mb-3">
                        <label class="form-label">백업 JSON 파일</label>
                        <input type="file" name="backupFile" class="form-control" accept="application/json,.json" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">확인 입력 (YES)</label>
                        <input type="text" name="confirm" class="form-control" placeholder="YES" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">Replace 복원 실행</button>
                </form>

                <div class="form-text mt-2">
                    Replace 후 id(문서 id)는 백업에 포함된 원본 id를 유지하여 다시 생성합니다.
                </div>
            </div>
        </div>
    </div>
</div>

<?php
renderFooter();
?>
