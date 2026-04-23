# BUJOS Management System

부조 관리 웹 애플리케이션 (Firebase Firestore 기반)

## 프로젝트 구조

```
sy_bujos_web/
├── config/          # 설정 파일
│   ├── database.php # DB 연결 설정
│   └── firestore.php # Firestore REST API 클라이언트
├── includes/        # 공통 함수 및 레이아웃
│   ├── functions.php
│   └── layout.php
├── public/          # 웹 루트 (MAMP 에서 접근)
│   └── index.php
├── src/             # 애플리케이션 코드
├── vendor/          # Composer 의존성
├── .env             # 환경 변수 (직접 생성)
├── .env.example     # 환경 변수 샘플
└── composer.json    # Composer 설정
```

## 설치 방법

### 1. Composer 의존성 설치

```bash
cd /Applications/MAMP/htdocs/sy_bujos_web
composer install
```

### 2. Firebase 설정

1. [Firebase Console](https://console.firebase.google.com/) 에서 프로젝트 생성
2. Firestore Database 활성화
3. 서비스 계정 키 다운로드 (JSON)
4. `.env` 파일에서 경로 및 프로젝트 ID 설정

```env
GOOGLE_APPLICATION_CREDENTIALS=/path/to/your-service-account-key.json
FIRESTORE_PROJECT_ID=your-project-id
```

### 3. MAMP 서버 시작

MAMP 앱에서 서버 시작 후 다음 URL 로 접근:

```
http://localhost:8888/sy_bujos_web
```

## 주요 기능

- **대시보드**: 전체 부조 및 카테고리 현황
- **부조 관리**: CRUD (생성, 조회, 수정, 삭제)
- **카테고리 관리**: 부조 카테고리 분류
- **통계**: 카테고리별 집계

## 기술 스택

- **Backend**: PHP 8.0+
- **Database**: Google Cloud Firestore (REST API)
- **HTTP Client**: Guzzle
- **Frontend**: Bootstrap 5

## 1 단계 완료 구성

- [x] 프로젝트 디렉토리 구조
- [x] Composer 의존성 설정
- [x] Firestore REST API 클라이언트
- [x] 기본 레이아웃 및 함수
- [x] 대시보드 페이지
