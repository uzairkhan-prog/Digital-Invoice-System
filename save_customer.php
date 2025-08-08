<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $cnic = $_POST['cnic'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? '';
    $strn = $_POST['strn'] ?? '';
    $ntn = $_POST['ntn'] ?? '';

    if (!$code || !$name || !$cnic || !$email) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO customers (code, name, cnic, email, phone, address, city, strn, ntn) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$code, $name, $cnic, $email, $phone, $address, $city, $strn, $ntn]);

    echo json_encode(['status' => 'success']);
}