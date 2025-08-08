<?php
// delete_invoice.php
require 'db.php';
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
$stmt->execute([$id]);
header('Location: index.php?deleted=1');
exit;
