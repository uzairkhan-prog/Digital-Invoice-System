<?php
require 'db.php';

// Fetch customers for selection dropdown
$stmt = $pdo->query("SELECT id, code, name FROM customers ORDER BY name");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch HS Code for selection dropdown
$stmt = $pdo->query("SELECT id, hs_code FROM hs_codes ORDER BY id");
$hs_codes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch last serial number from invoices table
$stmt = $pdo->query("SELECT serial_no FROM invoices ORDER BY id DESC LIMIT 1");
$lastSerial = $stmt->fetchColumn();

if ($lastSerial) {
    $nextSerial = intval($lastSerial) + 1;
} else {
    $nextSerial = 1;
}

// Format with leading zeros (e.g. 001, 002, 003...)
$serialNo = str_pad($nextSerial, 3, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Add Invoice</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
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
            <h3 class="mb-4">🧾 Create Invoice</h3>

            <form action="save_invoice.php" method="POST" id="invoiceForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="serial_no" class="form-label">Serial No</label>
                        <input type="text" class="form-control" id="serial_no" name="serial_no" value="<?= $serialNo ?>" readonly required>
                    </div>
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="invoice_type" class="form-label">Invoice Type</label>
                        <input type="text" class="form-control" id="invoice_type" name="invoice_type" value="Sale Invoice">
                    </div>
                    <div class="col-md-3">
                        <label for="fbr_invoice_no" class="form-label">FBR Invoice #</label>
                        <input type="text" class="form-control" id="fbr_invoice_no" name="fbr_invoice_no">
                    </div>
                    <div class="col-md-3">
                        <label for="po_no" class="form-label">PO No.</label>
                        <input type="text" class="form-control" id="po_no" name="po_no">
                    </div>
                    <div class="col-md-3">
                        <label for="terms_of_payment" class="form-label">Terms of Payment</label>
                        <input type="text" class="form-control" id="terms_of_payment" name="terms_of_payment" value="Standard">
                    </div>
                    <div class="col-md-3">
                        <label for="scenario_id" class="form-label">Scenario ID</label>
                        <input type="text" class="form-control" id="scenario_id" name="scenario_id" value="SND001">
                    </div>
                    <div class="col-md-3">
                        <label for="customer_id" class="form-label">Customer</label>
                        <div class="input-group">
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= htmlspecialchars($c['id']) ?>">
                                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="addCustomerBtn" title="Add New Customer">+</button>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items Table -->
                <div class="table-responsive mt-4">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
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
                            <tr>
                                <td style="width: 150px;">
                                    <select class="form-select" name="hs_code[]" required>
                                        <option disabled selected>Select</option>
                                        <?php foreach ($hs_codes as $c): ?>
                                            <option value="<?= htmlspecialchars($c['hs_code']) ?>">
                                                <?= htmlspecialchars($c['hs_code']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input type="text" name="item_name[]" class="form-control"></td>
                                <td><input type="number" step="1" min="0" name="qty[]" class="form-control qty"></td>
                                <td><input type="text" name="unit[]" class="form-control"></td>
                                <td><input type="number" step="0.01" min="0" name="rate[]" class="form-control rate"></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="disc_perc[]" class="form-control discPerc"></td>
                                <td><input type="number" step="0.01" min="0" name="discount[]" class="form-control discount" readonly></td>
                                <td><input type="number" step="0.01" min="0" name="excl_tax_amt[]" class="form-control exclTax" readonly></td>
                                <td><input type="number" step="0.01" min="0" max="100" name="tax_perc[]" class="form-control taxPerc" value="18"></td>
                                <td><input type="number" step="0.01" min="0" name="tax_amt[]" class="form-control taxAmt" readonly></td>
                                <td><input type="number" step="0.01" min="0" name="amount[]" class="form-control amount" readonly></td>
                                <td><button type="button" class="btn btn-sm btn-danger removeRow">X</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-secondary" id="addRowBtn">+ Add Row</button>

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

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary mt-4 px-4">Back</a>
                    <button type="submit" class="btn btn-success mt-4 px-4">💾 Save Invoice</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Customer Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="customerForm" class="p-3">
                    <div class="modal-header">
                        <h5 class="modal-title" id="customerModalLabel">Add New Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input name="code" placeholder="Customer Code" class="form-control mb-2" required>
                        <input name="name" placeholder="Customer Name" class="form-control mb-2" required>
                        <input name="cnic" placeholder="CNIC" class="form-control mb-2" required>
                        <input name="email" placeholder="Email" type="email" class="form-control mb-2" required>
                        <input name="phone" placeholder="Phone" class="form-control mb-2">
                        <input name="address" placeholder="Address" class="form-control mb-2">
                        <input name="city" placeholder="City" class="form-control mb-2">
                        <input name="strn" placeholder="STRN" class="form-control mb-2">
                        <input name="ntn" placeholder="NTN" class="form-control mb-2">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeModal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
        const hsCodes = <?php echo json_encode(array_column($hs_codes, 'hs_code')); ?>;

        document.getElementById('addCustomerBtn').addEventListener('click', () => customerModal.show());

        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('save_customer.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        customerModal.hide();
                        loadCustomers();
                    } else alert(data.message);
                });
        });

        function loadCustomers() {
            fetch('get_customers.php')
                .then(res => res.json())
                .then(data => {
                    const dropdown = document.getElementById('customer_id');
                    dropdown.innerHTML = '<option value="">Select Customer</option>';
                    data.forEach(c => {
                        dropdown.innerHTML += `<option value="${c.id}">${c.name} (${c.code})</option>`;
                    });
                });
        }

        document.getElementById('addRowBtn').addEventListener('click', () => {
            const tbody = document.querySelector('#itemsTable tbody');
            const newRow = document.createElement('tr');
            let hsOptions = '<option disabled selected>Select</option>';
            hsCodes.forEach(code => hsOptions += `<option value="${code}">${code}</option>`);
            newRow.innerHTML = `
                <td><select class="form-select" name="hs_code[]" required>${hsOptions}</select></td>
                <td><input type="text" name="item_name[]" class="form-control"></td>
                <td><input type="number" step="1" min="0" name="qty[]" class="form-control qty"></td>
                <td><input type="text" name="unit[]" class="form-control"></td>
                <td><input type="number" step="0.01" min="0" name="rate[]" class="form-control rate"></td>
                <td><input type="number" step="0.01" min="0" max="100" name="disc_perc[]" class="form-control discPerc"></td>
                <td><input type="number" step="0.01" min="0" name="discount[]" class="form-control discount" readonly></td>
                <td><input type="number" step="0.01" min="0" name="excl_tax_amt[]" class="form-control exclTax" readonly></td>
                <td><input type="number" step="0.01" min="0" max="100" name="tax_perc[]" class="form-control taxPerc" value="18"></td>
                <td><input type="number" step="0.01" min="0" name="tax_amt[]" class="form-control taxAmt" readonly></td>
                <td><input type="number" step="0.01" min="0" name="amount[]" class="form-control amount" readonly></td>
                <td><button type="button" class="btn btn-sm btn-danger removeRow">X</button></td>
            `;
            tbody.appendChild(newRow);
            bindEvents();
        });

        function bindEvents() {
            document.querySelectorAll('.removeRow').forEach(btn => {
                btn.onclick = () => {
                    const tbody = document.querySelector('#itemsTable tbody');
                    if (tbody.querySelectorAll('tr').length > 1) btn.closest('tr').remove();
                    else alert("You must have at least one row in the invoice.");
                    updateTotals();
                };
            });

            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                const qty = row.querySelector('.qty');
                const rate = row.querySelector('.rate');
                const discPerc = row.querySelector('.discPerc');
                const taxPerc = row.querySelector('.taxPerc');

                [qty, rate, discPerc, taxPerc].forEach(el => {
                    el.oninput = () => {
                        const q = parseFloat(qty.value) || 0;
                        const r = parseFloat(rate.value) || 0;
                        const dP = parseFloat(discPerc.value) || 0;
                        const tP = parseFloat(taxPerc.value) || 0;

                        const amt = q * r;
                        const disc = amt * (dP / 100);
                        const exTax = amt - disc;
                        const taxAmt = exTax * (tP / 100);
                        const totalAmt = exTax + taxAmt;

                        row.querySelector('.amount').value = totalAmt.toFixed(2);
                        row.querySelector('.discount').value = disc.toFixed(2);
                        row.querySelector('.exclTax').value = exTax.toFixed(2);
                        row.querySelector('.taxAmt').value = taxAmt.toFixed(2);

                        updateTotals();
                    };
                });
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