<?php
require 'db.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute([$id]);
$invoice = $stmt->fetch();
if (!$invoice) {
    header('Location: index.php');
    exit;
}

$itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

// Fetch customers for selection dropdown
$stmt = $pdo->query("SELECT id, code, name FROM customers ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch HS Code for selection dropdown
$stmt = $pdo->query("SELECT id, hs_code FROM hs_codes ORDER BY id");
$hs_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit Invoice #<?= htmlspecialchars($invoice['serial_no']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ===== Base Page Styling ===== */
        body {
            background-color: #f4f6f9;
            padding-top: 40px;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        /* ===== Form Container ===== */
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border-top: 4px solid #0d6efd;
        }

        .form-section h3 {
            font-weight: 700;
            font-size: 1.4rem;
            color: #0d6efd;
            border-bottom: 2px solid #f1f3f5;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        /* ===== Form Labels ===== */
        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }

        /* ===== Inputs & Selects ===== */
        input.form-control,
        select.form-select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.9rem;
            padding: 8px 10px;
            transition: all 0.2s ease-in-out;
        }

        input.form-control:focus,
        select.form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.15);
        }

        input[type="text"]:read-only,
        input[type="number"]:read-only {
            background-color: #f8f9fa;
            color: #555;
        }

        /* ===== Buttons ===== */
        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.2s ease-in-out;
        }

        .btn-success,
        .btn-primary,
        .btn-secondary {
            min-width: 120px;
        }

        .btn-outline-secondary {
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .btn-outline-secondary:hover {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        /* ===== Table Styling ===== */
        #itemsTable {
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.88rem;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        #itemsTable thead th {
            background: linear-gradient(90deg, #b2d8ff, #e0f0ff);
            color: #000000;
            font-weight: 600;
            /* letter-spacing: 0.5px; */
            border-bottom: 2px solid #496d92;
            text-align: center;
        }

        #itemsTable tbody tr {
            transition: background-color 0.15s ease-in-out;
        }

        #itemsTable tbody tr:hover {
            background-color: #f8f9fa;
        }

        #itemsTable td {
            padding: 10px;
            vertical-align: middle;
            border-color: #e9ecef;
            text-align: center;
        }

        #itemsTable input.form-control,
        #itemsTable select.form-select {
            border-radius: 6px;
            border: 1px solid #ced4da;
            font-size: 0.85rem;
            padding: 6px 8px;
        }

        #itemsTable input.form-control:focus,
        #itemsTable select.form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
        }

        /* ===== Add Row Button ===== */
        #addRowBtn {
            margin-top: 15px;
            margin-bottom: 20px;
            border-radius: 20px;
            padding: 6px 18px;
            font-weight: 500;
            transition: all 0.2s ease-in-out;
        }

        #addRowBtn:hover {
            background-color: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }

        /* ===== Remove Button ===== */
        .removeRow {
            border-radius: 50%;
            padding: 3px 8px;
            font-size: 12px;
            transition: all 0.2s ease-in-out;
        }

        .removeRow:hover {
            background-color: #dc3545;
            color: white;
        }

        /* ===== Totals Section ===== */
        #totalAmount,
        #grandTotal {
            font-weight: bold;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        /* ===== Modal Styling ===== */
        .modal-header {
            background-color: #0d6efd;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal-footer .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .modal-footer .btn-primary:hover {
            background-color: #0b5ed7;
        }

        /* ===== Table Container ===== */
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-section">
            <h3 class="mb-4">Edit Invoice #<?= htmlspecialchars($invoice['serial_no']) ?></h3>
            <form method="post" action="update_invoice.php" id="invoiceForm">
                <input type="hidden" name="id" value="<?= $invoice['id'] ?>">

                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Serial No</label>
                        <input type="text" name="serial_no" class="form-control" value="<?= htmlspecialchars($invoice['serial_no']) ?>" readonly required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($invoice['date']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Invoice Type</label>
                        <input name="invoice_type" class="form-control" value="<?= htmlspecialchars($invoice['invoice_type']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">FBR Invoice #</label>
                        <input name="fbr_invoice_no" class="form-control" value="<?= htmlspecialchars($invoice['fbr_invoice_no']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">PO No</label>
                        <input name="po_no" class="form-control" value="<?= htmlspecialchars($invoice['po_no']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Terms of Payment</label>
                        <input name="terms_of_payment" class="form-control" value="<?= htmlspecialchars($invoice['terms_of_payment']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Scenario ID</label>
                        <input name="scenario_id" class="form-control" value="<?= htmlspecialchars($invoice['scenario_id']) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">Select Customer</option>
                            <?php foreach ($customers as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $invoice['customer_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Invoice Items Table -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-primary">
                            <tr>
                                <th>H.S Code</th>
                                <th>Item Name</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Rate</th>
                                <th>Disc %</th>
                                <th>Discount</th>
                                <th>Excl Tax Amt</th>
                                <th>Tax %</th>
                                <th>Inc Tax Amt</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <input type="hidden" name="item_id[]" value="<?= $it['id'] ?>">
                                    <td style="width: 150px;"><select class="form-select" name="hs_code[]" required>
                                            <option disabled>Select</option>
                                            <?php foreach ($hs_codes as $c): ?>
                                                <option value="<?= $c['hs_code'] ?>" <?= $it['hs_code'] == $c['hs_code'] ? 'selected' : '' ?>>
                                                    <?= $c['hs_code'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select></td>
                                    <td><input name="item_name[]" class="form-control" value="<?= $it['item_name'] ?>"></td>
                                    <td><input name="qty[]" class="form-control qty" type="number" step="0.01" value="<?= $it['qty'] ?>"></td>
                                    <td><input name="unit[]" class="form-control" value="<?= $it['unit'] ?>"></td>
                                    <td><input name="rate[]" class="form-control rate" type="number" step="0.01" value="<?= $it['rate'] ?>"></td>
                                    <td><input name="disc_perc[]" class="form-control discPerc" type="number" step="0.01" value="<?= $it['disc_perc'] ?>"></td>
                                    <td><input name="discount[]" class="form-control discount" value="<?= $it['discount'] ?>" readonly></td>
                                    <td><input name="excl_tax_amt[]" class="form-control exclTax" value="<?= $it['excl_tax_amt'] ?>" readonly></td>
                                    <td><input name="tax_perc[]" class="form-control taxPerc" type="number" step="0.01" value="<?= $it['tax_perc'] ?? 18 ?>"></td>
                                    <td><input name="tax_amt[]" class="form-control taxAmt" value="<?= $it['tax_amt'] ?>" readonly></td>
                                    <td><input name="amount[]" class="form-control amount" value="<?= $it['amount'] ?>" readonly></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="button" id="addRowBtn" class="btn btn-secondary">+ Add Row</button>

                <!-- Summary Fields -->
                <div class="row g-3">
                    <div class="col-md-8">
                    </div>
                    <div class="col-md-2">
                        <label for="totalAmount" class="form-label"><strong>Gross Total (Excl. Tax):</strong></label>
                        <input type="text" id="totalAmount" class="form-control" readonly>
                    </div>
                    <div class="col-md-2">
                        <label for="grandTotal" class="form-label"><strong>Grand Total (Incl. Tax):</strong></label>
                        <input type="text" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="index.php" class="btn btn-secondary px-4">Back</a>
                    <button type="submit" class="btn btn-success px-4">💾 Update Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const hsCodes = <?= json_encode(array_column($hs_codes, 'hs_code')) ?>;

        document.getElementById('addRowBtn').addEventListener('click', () => {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            let hsOptions = '<option disabled selected>Select</option>';
            hsCodes.forEach(code => hsOptions += `<option value="${code}">${code}</option>`);
            row.innerHTML = `
        <input type="hidden" name="item_id[]" value="">
        <td style="width: 150px;"><select class="form-select" name="hs_code[]" required>${hsOptions}</select></td>
        <td><input name="item_name[]" class="form-control"></td>
        <td><input name="qty[]" class="form-control qty" type="number" step="0.01"></td>
        <td><input name="unit[]" class="form-control"></td>
        <td><input name="rate[]" class="form-control rate" type="number" step="0.01"></td>
        <td><input name="disc_perc[]" class="form-control discPerc" type="number" step="0.01"></td>
        <td><input name="discount[]" class="form-control discount" readonly></td>
        <td><input name="excl_tax_amt[]" class="form-control exclTax" readonly></td>
        <td><input name="tax_perc[]" class="form-control taxPerc" type="number" step="0.01" value="18"></td>
        <td><input name="tax_amt[]" class="form-control taxAmt" readonly></td>
        <td><input name="amount[]" class="form-control amount" readonly></td>
        <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
    `;
            tbody.appendChild(row);
            bindEvents();
        });

        function bindEvents() {
            document.querySelectorAll('.removeRow').forEach(btn => {
                btn.onclick = () => {
                    const tbody = document.querySelector('#itemsTable tbody');
                    if (tbody.querySelectorAll('tr').length > 1) btn.closest('tr').remove();
                    else alert("At least one item is required.");
                    updateTotals();
                };
            });

            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                const qty = row.querySelector('.qty');
                const rate = row.querySelector('.rate');
                const discPerc = row.querySelector('.discPerc');
                const taxPerc = row.querySelector('.taxPerc');

                if (qty && rate && discPerc && taxPerc) {
                    [qty, rate, discPerc, taxPerc].forEach(el => el.oninput = () => {
                        const q = parseFloat(qty.value) || 0;
                        const r = parseFloat(rate.value) || 0;
                        const dP = parseFloat(discPerc.value) || 0;
                        const tP = parseFloat(taxPerc.value) || 0;

                        const amt = q * r;
                        const disc = amt * (dP / 100);
                        const exTax = amt - disc;
                        const taxAmt = exTax * (tP / 100);
                        const totalAmt = exTax + taxAmt;

                        row.querySelector('.discount').value = disc.toFixed(2);
                        row.querySelector('.exclTax').value = exTax.toFixed(2);
                        row.querySelector('.taxAmt').value = taxAmt.toFixed(2);
                        row.querySelector('.amount').value = totalAmt.toFixed(2);
                        updateTotals();
                    });
                }
            });
        }

        function updateTotals() {
            let totalExcl = 0;
            let grandTotal = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                totalExcl += parseFloat(row.querySelector('.exclTax').value) || 0;
                grandTotal += parseFloat(row.querySelector('.amount').value) || 0;
            });
            document.getElementById('totalAmount').value = totalExcl.toFixed(2);
            document.getElementById('grandTotal').value = grandTotal.toFixed(2);
        }

        bindEvents();
        updateTotals();
    </script>
</body>

</html>