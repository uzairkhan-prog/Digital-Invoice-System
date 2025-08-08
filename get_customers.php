<?php
require 'db.php';

$stmt = $pdo->query("SELECT id, name, code FROM customers ORDER BY name ASC");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
header('Content-Type: application/json');
echo json_encode($customers);
