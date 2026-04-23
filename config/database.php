<?php
/**
 * 데이터베이스 설정 - Firestore REST API 사용
 */

// .env 파일 로드
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Firestore 클라이언트 로드
require_once __DIR__ . '/firestore.php';
