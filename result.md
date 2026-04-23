# Firebase Bujo Management Web App - 구현 완료 보고서

## ✅ 1 단계 완료: 기본 구성 설정

### 1.1 프로젝트 구조
```
sy_bujos_web/
├── config/
│   ├── database.php          # DB 설정 (Firestore REST API)
│   └── firestore.php         # Firestore 클라이언트 (REST 기반)
├── includes/
│   ├── functions.php         # 유틸리티 함수
│   └── layout.php            # 레이아웃 템플릿
├── public/
│   ├── index.php             # 대시보드
│   ├── bujos/                # 부조 관리
│   │   ├── index.php         # 목록
│   │   ├── create.php        # 생성
│   │   ├── edit.php          # 수정
│   │   └── delete.php        # 삭제
│   ├── categories/           # 카테고리 관리
│   │   ├── index.php         # 목록
│   │   ├── create.php        # 생성
│   │   ├── edit.php          # 수정
│   │   └── delete.php        # 삭제
│   └── statistics/           # 통계
│       └── index.php         # 카테고리별 통계
├── vendor/                   # Composer dependencies
├── composer.json
├── .env                      # Firebase 설정 (gitignore)
└── .env.example              # 환경 변수 샘플
```

### 1.2 Firebase 설정 완료
- **프로젝트 ID**: `sy-bujo-fb`
- **서비스 계정 키**: `config/firebase-key.json`
- **Firestore REST API** 기반 구현 (gRPC 없이 동작)

---

## ✅ 2 단계 완료: 페이지 구현

### 2.1 대시보드 (`public/index.php`)
- 전체 부조 건수 표시
- 부조 완료 건수 및 금액 표시
- 카테고리 수 표시
- 빠른 링크 제공

### 2.2 부조 관리 (`public/bujos/`)
| 페이지 | 기능 |
|--------|------|
| `index.php` | 목록 조회, 카테고리 필터 |
| `create.php` | 새 부조 추가 |
| `edit.php` | 부조 정보 수정 |
| `delete.php` | 부조 삭제 |

### 2.3 카테고리 관리 (`public/categories/`)
| 페이지 | 기능 |
|--------|------|
| `index.php` | 카테고리 목록 |
| `create.php` | 새 카테고리 추가 |
| `edit.php` | 카테고리 수정 |
| `delete.php` | 카테고리 삭제 |

### 2.4 통계 (`public/statistics/index.php`)
- 카테고리별 건수 및 금액 집계
- 부조 완료/대기 상태별 통계
- 전체 합계 및 비중 표시

---

## 🔧 기술 스택

| 항목 | 내용 |
|------|------|
| 언어 | PHP 8.x |
| 데이터베이스 | Firebase Firestore (REST API) |
| UI 프레임워크 | Bootstrap 5.3.2 |
| 의존성 관리 | Composer |
| 서버 | MAMP |

---

## 🚀 실행 방법

1. **MAMP 서버 시작**

2. **브라우저에서 접근**
   ```
   http://localhost:8888/sy_bujos_web/
   ```

3. **각 페이지**
   - 대시보드: `http://localhost:8888/sy_bujos_web/`
   - 부조 목록: `http://localhost:8888/sy_bujos_web/public/bujos/index.php`
   - 카테고리: `http://localhost:8888/sy_bujos_web/public/categories/index.php`
   - 통계: `http://localhost:8888/sy_bujos_web/public/statistics/index.php`

---

## 📝 보안 주의사항

- `.env` 파일은 Git 에 커밋되지 않습니다 (`.gitignore` 적용)
- `config/firebase-key.json` 도 Git 에서 제외됩니다
- 서비스 계정 키는 절대 공개 저장소에 업로드하지 마세요

---

## 📄 데이터 모델

### `bujo_categories` 컬렉션
| 필드 | 타입 | 설명 |
|------|------|------|
| `name` | string | 카테고리 이름 |
| `description` | string | 설명 |
| `createdAt` | timestamp | 생성일시 |

### `bujos` 컬렉션
| 필드 | 타입 | 설명 |
|------|------|------|
| `name` | string | 부조 이름 |
| `account` | int64 | 금액 |
| `dDay` | timestamp | D-Day |
| `reason` | string | 사유 |
| `etc` | string | 비고 |
| `isBujo` | boolean | 부조 완료 여부 |
| `groupName` | string/null | 그룹명 |
| `categoryId` | string | 카테고리 ID |
| `createdAt` | timestamp | 생성일시 |

---

## 다음 단계 (선택 사항)

1. 검색 및 고급 필터 기능 추가
2. 페이지네이션 구현
3. 차트/그래프 시각화 추가 (Chart.js 등)
4. 사용자 인증 시스템
5. 데이터 내보내기 (Excel, CSV)

---

## 기존 result.md 의 구현 가이드는 참조용으로 유지

### 1.1 디렉토리 구조
```
bujo-web/
├── config/
│   └── firebase.php          # Firebase 설정
├── includes/
│   ├── header.php            # 공통 헤더
│   ├── footer.php            # 공통 푸터
│   └── functions.php         # 유틸리티 함수
├── src/
│   ├── BujoRepository.php    # Bujos CRUD 로직
│   └── CategoryRepository.php # Categories CRUD 로직
├── public/
│   ├── index.php             # 대시보드
│   ├── bujos/
│   │   ├── index.php         # Bujo 목록
│   │   ├── create.php        # Bujo 생성
│   │   ├── edit.php          # Bujo 수정
│   │   └── delete.php        # Bujo 삭제
│   ├── categories/
│   │   ├── index.php         # 카테고리 목록
│   │   ├── create.php        # 카테고리 생성
│   │   ├── edit.php          # 카테고리 수정
│   │   └── delete.php        # 카테고리 삭제
│   └── stats/
│       └── index.php         # 카테고리별 통계
├── vendor/                   # Composer dependencies
├── composer.json
└── .env                      # Firebase 인증키 (gitignore)
```

### 1.2 Composer 설치
```bash
composer init --name=bujo/web --require="google/cloud-firestore:^1.43"
composer require vlucas/phpdotenv
```

### 1.3 Firebase 설정 (`config/firebase.php`)
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Firestore\FirestoreClient;

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../config/serviceAccount.json');

$firestore = new FirestoreClient([
    'projectId' => getenv('FIREBASE_PROJECT_ID') ?: 'your-project-id'
]);

return $firestore;
```

---

## 2 단계: 데이터 모델 구현

### 2.1 BujoRepository (`src/BujoRepository.php`)
```php
<?php
class BujoRepository {
    private $db;
    private $collection;

    public function __construct($firestore) {
        $this->db = $firestore;
        $this->collection = $this->db->collection('bujos');
    }

    // Create
    public function create(array $data): string {
        $doc = $this->collection->add([
            'name' => $data['name'],
            'account' => (int)$data['account'],
            'dDay' => $this->db->timestamp(new \DateTime($data['dDay'])),
            'reason' => $data['reason'] ?? '',
            'etc' => $data['etc'] ?? '',
            'isBujo' => $data['isBujo'] ?? false,
            'groupName' => $data['groupName'] ?? null,
            'categoryId' => $data['categoryId'],
            'createdAt' => $this->db->timestamp(new \DateTime())
        ]);
        return $doc->id();
    }

    // Read - 단일 문서
    public function find(string $id): ?array {
        $doc = $this->collection->document($id)->snapshot();
        return $doc->exists() ? $doc->data() : null;
    }

    // Read - 목록 (페이지네이션)
    public function findAll(int $limit = 20, ?string $categoryId = null): array {
        $query = $this->collection->orderBy('createdAt', 'desc');
        
        if ($categoryId) {
            $query = $query->where('categoryId', '=', $categoryId);
        }
        
        $docs = $query->limit($limit)->documents();
        $result = [];
        
        foreach ($docs as $doc) {
            $result[$doc->id()] = $doc->data();
        }
        
        return $result;
    }

    // Update
    public function update(string $id, array $data): void {
        $updateData = [];
        
        $fields = ['name', 'account', 'dDay', 'reason', 'etc', 'isBujo', 'groupName', 'categoryId'];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $field === 'dDay' 
                    ? $this->db->timestamp(new \DateTime($data[$field]))
                    : ($field === 'account' ? (int)$data[$field] : $data[$field]);
            }
        }
        
        $this->collection->document($id)->update($updateData);
    }

    // Delete
    public function delete(string $id): void {
        $this->collection->document($id)->delete();
    }

    // 통계: 카테고리별 건수 및 금액 합계
    public function getStatsByCategory(): array {
        $docs = $this->collection->documents();
        $stats = [];
        
        foreach ($docs as $doc) {
            $data = $doc->data();
            $catId = $data['categoryId'] ?? 'unknown';
            
            if (!isset($stats[$catId])) {
                $stats[$catId] = ['count' => 0, 'totalAmount' => 0];
            }
            
            $stats[$catId]['count']++;
            $stats[$catId]['totalAmount'] += (int)($data['account'] ?? 0);
        }
        
        return $stats;
    }
}
```

### 2.2 CategoryRepository (`src/CategoryRepository.php`)
```php
<?php
class CategoryRepository {
    private $db;
    private $collection;

    public function __construct($firestore) {
        $this->db = $firestore;
        $this->collection = $this->db->collection('bujo_categories');
    }

    public function create(array $data): string {
        $doc = $this->collection->add([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'createdAt' => $this->db->timestamp(new \DateTime())
        ]);
        return $doc->id();
    }

    public function find(string $id): ?array {
        $doc = $this->collection->document($id)->snapshot();
        return $doc->exists() ? $doc->data() : null;
    }

    public function findAll(): array {
        $docs = $this->collection->orderBy('createdAt', 'desc')->documents();
        $result = [];
        
        foreach ($docs as $doc) {
            $result[$doc->id()] = $doc->data();
        }
        
        return $result;
    }

    public function update(string $id, array $data): void {
        $this->collection->document($id)->update([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }

    public function delete(string $id): void {
        $this->collection->document($id)->delete();
    }
}
```

---

## 3 단계: 공통 컴포넌트

### 3.1 헤더 (`includes/header.php`)
```php
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Bujo Management' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="/index.php">Bujo Manager</a>
        <div class="navbar-nav">
            <a class="nav-link" href="/public/bujos/index.php">Bujo 목록</a>
            <a class="nav-link" href="/public/categories/index.php">카테고리</a>
            <a class="nav-link" href="/public/stats/index.php">통계</a>
        </div>
    </div>
</nav>
<div class="container">
```

### 3.2 푸터 (`includes/footer.php`)
```php
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## 4 단계: 페이지 구현

### 4.1 대시보드 (`public/index.php`)
```php
<?php
require_once __DIR__ . '/../config/firebase.php';
require_once __DIR__ . '/../src/BujoRepository.php';

$firestore = include __DIR__ . '/../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);

$stats = $bujoRepo->getStatsByCategory();
$totalCount = array_sum(array_column($stats, 'count'));
$totalAmount = array_sum(array_column($stats, 'totalAmount'));

$pageTitle = 'Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1>대시보드</h1>
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">총 Bujo 건수</h5>
                <p class="display-4"><?= $totalCount ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">총 금액</h5>
                <p class="display-4">₩<?= number_format($totalAmount) ?></p>
            </div>
        </div>
    </div>
</div>

<a href="/public/bujos/index.php" class="btn btn-primary">Bujo 목록 보기</a>
<a href="/public/stats/index.php" class="btn btn-secondary">통계 보기</a>

<?php include __DIR__ . '/../includes/footer.php'; ?>
```

### 4.2 Bujo 목록 (`public/bujos/index.php`)
```php
<?php
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../src/BujoRepository.php';
require_once __DIR__ . '/../../src/CategoryRepository.php';

$firestore = include __DIR__ . '/../../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);
$categoryRepo = new CategoryRepository($firestore);

$categoryId = $_GET['category'] ?? null;
$bujos = $bujoRepo->findAll(20, $categoryId);
$categories = $categoryRepo->findAll();

$pageTitle = 'Bujo 목록';
include __DIR__ . '/../../includes/header.php';
?>

<h1>Bujo 목록</h1>

<form method="GET" class="mb-3">
    <select name="category" class="form-select w-25 d-inline-block">
        <option value="">전체 카테고리</option>
        <?php foreach ($categories as $id => $cat): ?>
            <option value="<?= $id ?>" <?= $categoryId === $id ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline-primary">필터</button>
    <a href="/public/bujos/create.php" class="btn btn-primary">새 Bujo 추가</a>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>이름</th>
            <th>금액</th>
            <th>DDay</th>
            <th>사유</th>
            <th>카테고리</th>
            <th>상태</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bujos as $id => $bujo): ?>
        <tr>
            <td><?= htmlspecialchars($bujo['name']) ?></td>
            <td>₩<?= number_format($bujo['account']) ?></td>
            <td><?= (new \DateTime($bujo['dDay']->value()->getSeconds()))->format('Y-m-d') ?></td>
            <td><?= htmlspecialchars($bujo['reason']) ?></td>
            <td><?= htmlspecialchars($categories[$bujo['categoryId']]['name'] ?? 'N/A') ?></td>
            <td><?= $bujo['isBujo'] ? '✓' : '-' ?></td>
            <td>
                <a href="/public/bujos/edit.php?id=<?= $id ?>" class="btn btn-sm btn-outline-secondary">수정</a>
                <a href="/public/bujos/delete.php?id=<?= $id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('삭제하시겠습니까?')">삭제</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
```

### 4.3 Bujo 생성 (`public/bujos/create.php`)
```php
<?php
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../src/BujoRepository.php';
require_once __DIR__ . '/../../src/CategoryRepository.php';

$firestore = include __DIR__ . '/../../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);
$categoryRepo = new CategoryRepository($firestore);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bujoRepo->create($_POST);
    header('Location: /public/bujos/index.php');
    exit;
}

$categories = $categoryRepo->findAll();
$pageTitle = 'Bujo 생성';
include __DIR__ . '/../../includes/header.php';
?>

<h1>Bujo 생성</h1>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">이름</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">금액</label>
        <input type="number" name="account" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">DDay</label>
        <input type="date" name="dDay" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">사유</label>
        <input type="text" name="reason" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">비고</label>
        <textarea name="etc" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">그룹명</label>
        <input type="text" name="groupName" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">카테고리</label>
        <select name="categoryId" class="form-select" required>
            <?php foreach ($categories as $id => $cat): ?>
                <option value="<?= $id ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="isBujo" class="form-check-input" value="1" checked>
        <label class="form-check-label">Bujo 상태</label>
    </div>
    <button type="submit" class="btn btn-primary">저장</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
```

### 4.4 Bujo 수정 (`public/bujos/edit.php`)
```php
<?php
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../src/BujoRepository.php';
require_once __DIR__ . '/../../src/CategoryRepository.php';

$firestore = include __DIR__ . '/../../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);
$categoryRepo = new CategoryRepository($firestore);

$id = $_GET['id'] ?? null;
$bujo = $bujoRepo->find($id);

if (!$bujo) {
    die('Bujo 를 찾을 수 없습니다.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bujoRepo->update($id, $_POST);
    header('Location: /public/bujos/index.php');
    exit;
}

$categories = $categoryRepo->findAll();
$pageTitle = 'Bujo 수정';
include __DIR__ . '/../../includes/header.php';
?>

<h1>Bujo 수정</h1>
<form method="POST">
    <div class="mb-3">
        <label class="form-label">이름</label>
        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($bujo['name']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">금액</label>
        <input type="number" name="account" class="form-control" value="<?= $bujo['account'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">DDay</label>
        <input type="date" name="dDay" class="form-control" 
               value="<?= (new \DateTime($bujo['dDay']->value()->getSeconds()))->format('Y-m-d') ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">사유</label>
        <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($bujo['reason']) ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">비고</label>
        <textarea name="etc" class="form-control"><?= htmlspecialchars($bujo['etc']) ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">그룹명</label>
        <input type="text" name="groupName" class="form-control" value="<?= htmlspecialchars($bujo['groupName'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">카테고리</label>
        <select name="categoryId" class="form-select" required>
            <?php foreach ($categories as $catId => $cat): ?>
                <option value="<?= $catId ?>" <?= $bujo['categoryId'] === $catId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" name="isBujo" class="form-check-input" value="1" <?= $bujo['isBujo'] ? 'checked' : '' ?>>
        <label class="form-check-label">Bujo 상태</label>
    </div>
    <button type="submit" class="btn btn-primary">수정</button>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
```

### 4.5 Bujo 삭제 (`public/bujos/delete.php`)
```php
<?php
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../src/BujoRepository.php';

$firestore = include __DIR__ . '/../../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);

$id = $_GET['id'] ?? null;
if ($id) {
    $bujoRepo->delete($id);
}
header('Location: /public/bujos/index.php');
exit;
```

### 4.6 카테고리 관리 (`public/categories/index.php`, `create.php`, `edit.php`, `delete.php`)
Bujo 와 동일한 패턴으로 구현 (필드: name, description 만 차이)

---

## 5 단계: 통계 페이지 (`public/stats/index.php`)

```php
<?php
require_once __DIR__ . '/../../config/firebase.php';
require_once __DIR__ . '/../../src/BujoRepository.php';
require_once __DIR__ . '/../../src/CategoryRepository.php';

$firestore = include __DIR__ . '/../../config/firebase.php';
$bujoRepo = new BujoRepository($firestore);
$categoryRepo = new CategoryRepository($firestore);

$stats = $bujoRepo->getStatsByCategory();
$categories = $categoryRepo->findAll();

$pageTitle = '통계';
include __DIR__ . '/../../includes/header.php';
?>

<h1>카테고리별 통계</h1>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>카테고리</th>
            <th>설명</th>
            <th>건수</th>
            <th>총 금액</th>
            <th>비중</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $totalAmount = array_sum(array_column($stats, 'totalAmount'));
        foreach ($stats as $catId => $stat): 
            $cat = $categories[$catId] ?? ['name' => 'Unknown', 'description' => ''];
            $percent = $totalAmount > 0 ? ($stat['totalAmount'] / $totalAmount * 100) : 0;
        ?>
        <tr>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td><?= htmlspecialchars($cat['description']) ?></td>
            <td><?= $stat['count'] ?></td>
            <td>₩<?= number_format($stat['totalAmount']) ?></td>
            <td>
                <div class="progress" style="width: 200px;">
                    <div class="progress-bar" style="width: <?= $percent ?>%">
                        <?= number_format($percent, 1) ?>%
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">합계</th>
            <th><?= array_sum(array_column($stats, 'count')) ?></th>
            <th>₩<?= number_format($totalAmount) ?></th>
            <th>100%</th>
        </tr>
    </tfoot>
</table>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
```

---

## 6 단계: 실행 방법

1. **Composer 설치**
   ```bash
   composer install
   ```

2. **Firebase 인증 설정**
   - Firebase Console 에서 서비스 계정 키 다운로드
   - `config/serviceAccount.json` 에 저장
   - `.env` 파일에 `FIREBASE_PROJECT_ID` 설정

3. **PHP 내장 서버 실행**
   ```bash
   php -S localhost:8000 -t public
   ```

4. **브라우저에서 접근**
   ```
   http://localhost:8000
   ```

---

## 다음 단계

1. Bootstrap 대신 Tailwind CSS 등 다른 UI 프레임워크 적용 가능
2. Chart.js 등을 활용한 그래프 추가 가능
3. 인증/인가 시스템 추가 가능
4. 검색 및 고급 필터 기능 추가 가능
