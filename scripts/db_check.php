<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=mini_blog_notes;charset=utf8mb4','root','');
    $stmt = $pdo->query('SELECT post_id, title, is_public, post_status, user_id, created_at FROM tbl_post ORDER BY post_id');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $public = 0;
    foreach ($rows as $r) {
        printf("%s | %s | is_public=%s | post_status=%s | user_id=%s | %s\n", $r['post_id'], $r['title'], $r['is_public'], $r['post_status'], $r['user_id'], $r['created_at']);
        if ($r['is_public'] == 1) $public++;
    }
    printf("TOTAL=%d\nPUBLIC=%d\n", count($rows), $public);
} catch (Exception $e) {
    echo 'ERR: ' . $e->getMessage() . PHP_EOL;
}
