<?php
/**
 * 부조 삭제 처리
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $db = db();
    $bujosCollection = $db->collection('bujos');
    $bujosCollection->delete($id);
}

setFlash('success', '부조가 삭제되었습니다.');
redirect('/sy_bujos_web/public/bujos/index.php');
