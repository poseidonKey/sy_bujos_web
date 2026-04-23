<?php
/**
 * 카테고리 삭제 처리
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $db = db();
    $categoriesCollection = $db->collection('bujo_categories');
    $categoriesCollection->delete($id);
}

setFlash('success', '카테고리가 삭제되었습니다.');
redirect('/sy_bujos_web/public/categories/index.php');
