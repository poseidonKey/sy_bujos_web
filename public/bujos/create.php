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

// bulk add results
$bulkSuccessCount = 0;
$bulkFailCount = 0;
$bulkErrors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1) 여러 데이터 일괄 추가
        if (isset($_POST['bulkMode']) && $_POST['bulkMode'] === '1') {
            $bulkText = trim($_POST['bulkText'] ?? '');
            if ($bulkText === '') {
                throw new Exception('일괄 추가할 데이터가 없습니다. textarea에 내용을 입력해 주세요.');
            }

            // sample format:
            // name|account|dDay|reason|categoryId|groupName|etc|isBujo
            // 필수: name, account, dDay, reason, isBujo
            // 옵션: categoryId, groupName, etc (빈 값 허용)
            $lines = preg_split("/\r\n|\r|\n/", $bulkText);
            $lineNo = 0;

            foreach ($lines as $rawLine) {
                $lineNo++;

                $line = trim($rawLine);
                if ($line === '') continue;

                // 컬럼 분리 (필드 내부에 | 문자가 있는 경우는 미지원)
                $parts = array_map('trim', explode('|', $line));

                // 최소 컬럼: 8개(비어도 빈 컬럼은 존재한다고 가정해야 함)
                if (count($parts) < 8) {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: 컬럼 수가 부족합니다. (기대: 8개: name|account|dDay|reason|categoryId|groupName|etc|isBujo)";
                    continue;
                }

                [$name, $accountStr, $dDayStr, $reason, $categoryId, $groupName, $etc, $isBujoStr] = array_slice($parts, 0, 8);

                // 필수 검증
                if ($name === '') {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: 이름(name)은 필수입니다.";
                    continue;
                }
                if ($accountStr === '' || !is_numeric($accountStr)) {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: 금액(account)은 숫자여야 합니다.";
                    continue;
                }
                if ($dDayStr === '') {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: DDay(dDay)는 필수입니다.";
                    continue;
                }
                try {
                    $dDay = new DateTime($dDayStr);
                } catch (Exception $e) {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: DDay 값이 올바르지 않습니다. ({$dDayStr})";
                    continue;
                }
                if ($reason === '') {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: 사유(reason)는 필수입니다.";
                    continue;
                }

                $account = (int)$accountStr;
                $categoryId = ($categoryId === '' ? '일반' : $categoryId);
                $groupName = ($groupName === '' ? null : $groupName);
                $etc = $etc ?? '';
                $etc = ($etc === '' ? '' : $etc);

                $isBujoNorm = mb_strtolower(trim($isBujoStr));
                $isBujo = in_array($isBujoNorm, ['1', 'true', '부조함', 'yes', 'y'], true);

                $data = [
                    'name' => $name,
                    'account' => $account,
                    'dDay' => $dDay,
                    'reason' => $reason,
                    'etc' => $etc,
                    'isBujo' => $isBujo,
                    'groupName' => $groupName,
                    'categoryId' => $categoryId,
                    'createdAt' => new DateTime(),
                ];

                try {
                    $bujosCollection->add($data);
                    $bulkSuccessCount++;
                } catch (Exception $e) {
                    $bulkFailCount++;
                    $bulkErrors[] = "라인 {$lineNo}: 저장 실패 - " . $e->getMessage();
                }
            }

            if ($bulkSuccessCount > 0) {
                setFlash('success', "일괄 추가 완료: 성공 {$bulkSuccessCount}건, 실패 {$bulkFailCount}건");
            } else {
                setFlash('danger', "일괄 추가 실패: 성공 0건, 실패 {$bulkFailCount}건");
            }

            $error = null;
        } else {
            // 2) 기존 단건 추가
            $data = [
                'name' => $_POST['name'] ?? '',
                'account' => (int)($_POST['account'] ?? 0),
                'dDay' => new DateTime($_POST['dDay'] ?? 'today'),
                'reason' => $_POST['reason'] ?? '',
                'etc' => $_POST['etc'] ?? '',
                'isBujo' => !isset($_POST['isBujo']), // 기존 로직 유지
                'groupName' => $_POST['groupName'] ?? null,
                'categoryId' => $_POST['categoryId'] ?: '일반',
                'createdAt' => new DateTime(),
            ];

            // 검증
            if (empty($data['name'])) {
                throw new Exception('이름은 필수 항목입니다.');
            }

            $bujosCollection->add($data);
            setFlash('success', '부조가 등록되었습니다.');
            redirect('/sy_bujos_web/public/bujos/index.php');
        }
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
    <div class="col-12 mb-2">
        <h3 class="h5 mb-2">단건 추가</h3>
    </div>

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
        <input type="date" name="dDay" class="form-control" value="<?= date('Y-m-d') ?>" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">카테고리</label>
        <select name="categoryId" class="form-select">
            <option value="">일반</option>
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
            <label class="form-check-label">부조함</label>
        </div>
    </div>
    <div class="col-12 mb-4">
        <button type="submit" class="btn btn-success">등록</button>
        <a href="/sy_bujos_web/public/bujos/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<hr class="my-4"/>

<form method="POST" class="row g-3">
    <input type="hidden" name="bulkMode" value="1">

    <div class="col-12 mb-2">
        <h3 class="h5 mb-2">여러 데이터 일괄 추가</h3>
        <div class="text-muted small">
            구분자: <code>|</code><br>
            필수: <b>name, account, dDay, reason, isBujo</b><br>
            옵션: <b>categoryId, groupName, etc</b> (빈 칸 허용)
        </div>
    </div>

    <div class="col-12 d-flex gap-2 mb-2">
        <button type="button" class="btn btn-outline-primary" onclick="downloadSampleTxt()">샘플 포맷 다운로드 (sample.txt)</button>
        <button type="button" class="btn btn-outline-secondary" onclick="setSampleToTextarea()">샘플을 textarea에 넣기</button>
    </div>

    <div class="col-12">
        <label class="form-label">데이터 입력 (한 줄 = 1건)</label>
        <textarea id="bulkText" name="bulkText" class="form-control" rows="8" placeholder="name|account|dDay|reason|categoryId|groupName|etc|isBujo"></textarea>
        <div class="form-text">
            예) <code>김철수|50000|2026-06-30|생일|일반|팀A|추가메모|1</code>
        </div>
    </div>

    <?php if ($bulkSuccessCount > 0 || $bulkFailCount > 0): ?>
        <div class="col-12">
            <div class="alert alert-info mb-0">
                일괄 추가 결과: 성공 <b><?= $bulkSuccessCount ?></b>건 / 실패 <b><?= $bulkFailCount ?></b>건
            </div>
        </div>

        <?php if (!empty($bulkErrors)): ?>
            <div class="col-12">
                <div class="alert alert-warning">
                    실패 상세(최대 5개):
                    <ul class="mb-0">
                        <?php foreach (array_slice($bulkErrors, 0, 5) as $msg): ?>
                            <li><?= e($msg) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="col-12 mb-4">
        <button type="submit" class="btn btn-success">일괄 추가</button>
        <a href="/sy_bujos_web/public/bujos/index.php" class="btn btn-secondary">취소</a>
    </div>
</form>

<script>
const SAMPLE_TXT = `name|account|dDay|reason|categoryId|groupName|etc|isBujo
홍길동|50000|2026-06-15|기념일|일반|팀A|메모|1
김영희|120000|2026-06-30|장례|일반||없음|0
`;

function downloadSampleTxt() {
    const blob = new Blob([SAMPLE_TXT], { type: 'text/plain;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sample.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function setSampleToTextarea() {
    const ta = document.getElementById('bulkText');
    if (ta) ta.value = SAMPLE_TXT;
}
</script>

<?php
renderFooter();
?>
