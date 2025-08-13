<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $serial_no = $_POST['serial_no'];
    $date = $_POST['date'];
    $invoice_type = $_POST['invoice_type'];
    $scenario_id = $_POST['scenario_id'];
    $customer_id = $_POST['customer_id'];
    $discount = floatval($_POST['discount']);
    $tax = floatval($_POST['tax']);
    $fbr_invoice_no = $_POST['fbr_invoice_no'];
    $po_no = $_POST['po_no'];
    $terms_of_payment = $_POST['terms_of_payment'];
    $created_at = $_POST['created_at'] ?? date('Y-m-d H:i:s');

    // Calculate totals
    $gross_total = array_sum(array_map('floatval', $_POST['excl_tax_amt']));
    $after_discount = $gross_total - ($gross_total * $discount / 100);
    $grand_total = $after_discount + ($after_discount * $tax / 100);

    $pdo->beginTransaction();
    try {
        // Insert into invoices table
        $stmt = $pdo->prepare("
            INSERT INTO invoices (
                serial_no, date, invoice_type, scenario_id,
                customer_id, discount, tax, gross_total, grand_total,
                fbr_invoice_no, po_no, terms_of_payment, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $serial_no,
            $date,
            $invoice_type,
            $scenario_id,
            $customer_id,
            $discount,
            $tax,
            $gross_total,
            $grand_total,
            $fbr_invoice_no,
            $po_no,
            $terms_of_payment,
            $created_at
        ]);

        $invoice_id = $pdo->lastInsertId();

        // Prepare invoice items insert
        $item_stmt = $pdo->prepare("
            INSERT INTO invoice_items (
                invoice_id, item_code, hs_code, item_name, qty,
                unit, rate, disc_perc, discount, excl_tax_amt, amount
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($_POST['item_name'] as $i => $itemName) {
            // Skip if all main fields are empty
            if (
                empty(trim($itemName)) &&
                empty(trim($_POST['item_code'][$i])) &&
                empty(trim($_POST['hs_code'][$i]))
            ) {
                continue;
            }

            $item_stmt->execute([
                $invoice_id,
                !empty(trim($_POST['item_code'][$i])) ? $_POST['item_code'][$i] : null, // Nullable item_code
                $_POST['hs_code'][$i],
                $itemName,
                $_POST['qty'][$i],
                $_POST['unit'][$i],
                $_POST['rate'][$i],
                $_POST['disc_perc'][$i],
                $_POST['discount'][$i],
                $_POST['excl_tax_amt'][$i],
                $_POST['amount'][$i]
            ]);
        }

        $pdo->commit();
        header("Location: index.php?success=1");
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}