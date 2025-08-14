<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $serial_no = $_POST['serial_no'];
    $date = $_POST['date'];
    $invoice_type = $_POST['invoice_type'];
    $scenario_id = $_POST['scenario_id'];
    $customer_id = $_POST['customer_id'];
    $fbr_invoice_no = $_POST['fbr_invoice_no'];
    $po_no = $_POST['po_no'];
    $terms_of_payment = $_POST['terms_of_payment'];
    $created_at = $_POST['created_at'];
    $discount = floatval($_POST['discount']);
    $tax = floatval($_POST['tax']);

    $gross_total = array_sum(array_map('floatval', $_POST['excl_tax_amt']));
    $after_discount = $gross_total - ($gross_total * $discount / 100);
    $grand_total = $after_discount + ($after_discount * $tax / 100);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE invoices SET 
            serial_no = ?, 
            date = ?, 
            invoice_type = ?, 
            scenario_id = ?, 
            customer_id = ?, 
            discount = ?, 
            tax = ?, 
            gross_total = ?, 
            grand_total = ?, 
            fbr_invoice_no = ?, 
            po_no = ?, 
            terms_of_payment = ?, 
            created_at = ?
            WHERE id = ?");

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
            $created_at,
            $id
        ]);

        $pdo->prepare("DELETE FROM invoice_items WHERE invoice_id = ?")->execute([$id]);

        $item_stmt = $pdo->prepare("INSERT INTO invoice_items 
                (invoice_id, item_code, hs_code, item_name, qty, unit, rate, disc_perc, discount, excl_tax_amt, amount) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($_POST['hs_code'] as $i => $hs_code) {
            $item_code = isset($_POST['item_code'][$i]) && trim($_POST['item_code'][$i]) !== ''
                ? $_POST['item_code'][$i]
                : 'default';

            $item_stmt->execute([
                $id,
                $item_code,
                $hs_code,
                $_POST['item_name'][$i],
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
    header("Location: index.php");
    exit;
}
