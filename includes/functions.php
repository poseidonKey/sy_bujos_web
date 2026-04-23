<?php
/**
 * 유틸리티 함수 모음
 */

/**
 * HTML 이스케이프
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * 날짜 포맷팅
 */
function formatDate($timestamp): string {
    if ($timestamp instanceof \Google\Cloud\Firestore\Timestamp) {
        return $timestamp->get()->format('Y-m-d H:i');
    }
    return date('Y-m-d H:i', $timestamp);
}

/**
 * 금액 포맷팅
 */
function formatNumber(int $number): string {
    return number_format($number);
}

/**
 * 리디렉션
 */
function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

/**
 * 세션 메시지 설정
 */
function setFlash(string $key, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'][$key] = $message;
}

/**
 * 세션 메시지 가져오기
 */
function getFlash(string $key): ?string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $message = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $message;
}
