<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM invoice_items WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo "success";
    } else {
        echo "fail";
    }
} else {
    echo "invalid";
}
