<?php
require 'db.php';
$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM hs_codes WHERE id = ?");
    $stmt->execute([$id]);
}
header("Location: hs_code.php");
exit;
