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
        body {
            background-color: #f4f6f9;
            padding-top: 40px;
        }

        .form-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table thead th {
            vertical-align: middle;
            text-align: center;
            background: linear-gradient(90deg, #b2d8ff, #e0f0ff);
            color: #333;
        }

        .table td,
        .table th {
            vertical-align: middle;
            text-align: center;
        }

        .btn-success,
        .btn-primary,
        .btn-secondary {
            min-width: 120px;
        }

        .modal-header {
            background-color: #0d6efd;
            color: white;
        }

        .modal-content {
            border-radius: 10px;
        }

        #addRowBtn {
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
        }

        input[type="text"]:read-only,
        input[type="number"]:read-only {
            background-color: #e9ecef;
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
                        <div class="input-group">
                            <select class="form-select" id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php foreach ($customers as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $invoice['customer_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['code']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-outline-primary" id="addCustomerBtn" title="Add New Customer">+</button>
                        </div>
                    </div>
                </div>

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
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $it): ?>
                                <tr>
                                    <!-- Hidden field for existing item ID -->
                                    <input type="hidden" name="item_id[]" value="<?= $it['id'] ?>">
                                    <td class="d-none"><input name="item_code[]" class="form-control" value="0001"></td>
                                    <td style="width: 150px;">
                                        <select class="form-select" name="hs_code[]" required>
                                            <option disabled>Select</option>
                                            <?php foreach ($hs_codes as $c): ?>
                                                <option value="<?= htmlspecialchars($c['hs_code']) ?>" <?= $it['hs_code'] == $c['hs_code'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['hs_code']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><input name="item_name[]" class="form-control" value="<?= htmlspecialchars($it['item_name']) ?>"></td>
                                    <td><input name="qty[]" class="form-control qty" value="<?= $it['qty'] ?>" type="number" step="0.01"></td>
                                    <td><input name="unit[]" class="form-control" value="<?= htmlspecialchars($it['unit']) ?>"></td>
                                    <td><input name="rate[]" class="form-control rate" value="<?= $it['rate'] ?>" type="number" step="0.01"></td>
                                    <td><input name="disc_perc[]" class="form-control discPerc" value="<?= $it['disc_perc'] ?>" type="number" step="0.01"></td>
                                    <td><input name="discount[]" class="form-control discount" value="<?= $it['discount'] ?>" readonly></td>
                                    <td><input name="excl_tax_amt[]" class="form-control exclTax" value="<?= $it['excl_tax_amt'] ?>" readonly></td>
                                    <td><input name="amount[]" class="form-control amount" value="<?= $it['amount'] ?>" readonly></td>
                                    <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button type="button" id="addRowBtn" class="btn btn-secondary">+ Add Row</button>

                <div class="row mt-4 g-3">
                    <div class="col-md-4">
                        <label class="form-label">Discount (%)</label>
                        <input name="discount" id="discount" class="form-control" type="number" step="0.01" value="<?= $invoice['discount'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax (%)</label>
                        <input name="tax" id="tax" class="form-control" type="number" step="0.01" value="<?= $invoice['tax'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><strong>Gross Total:</strong></label>
                        <input type="text" id="totalAmount" class="form-control" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4 offset-md-8">
                        <label class="form-label"><strong>Grand Total (Inc Tax):</strong></label>
                        <input type="text" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>

                <div class="text-end">
                    <a href="index.php" class="btn btn-secondary mt-4 px-4">Back</a>
                    <button type="submit" class="btn btn-success mt-4 px-4">💾 Update Invoice</button>
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
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));

        // HS Codes array for dynamic row
        const hsCodes = <?php echo json_encode(array_column($hs_codes, 'hs_code')); ?>;

        document.getElementById('addCustomerBtn').addEventListener('click', () => customerModal.show());
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('save_customer.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.json()).then(data => {
                    if (data.status === 'success') {
                        customerModal.hide();
                        loadCustomers();
                    } else alert(data.message);
                });
        });

        function loadCustomers() {
            fetch('get_customers.php').then(res => res.json()).then(data => {
                const dropdown = document.getElementById('customer_id');
                dropdown.innerHTML = '<option value="">Select Customer</option>';
                data.forEach(c => {
                    dropdown.innerHTML += `<option value="${c.id}">${c.name} (${c.code})</option>`;
                });
            });
        }

        document.getElementById('addRowBtn').addEventListener('click', function() {
            const tbody = document.querySelector('#itemsTable tbody');
            const row = document.createElement('tr');
            let hsOptions = '<option disabled selected>Select</option>';
            hsCodes.forEach(code => {
                hsOptions += `<option value="${code}">${code}</option>`;
            });
            row.innerHTML = `
                <input type="hidden" name="item_id[]" value="">
                <input type="hidden" name="item_code[]" value="0001">
                <td style="width: 150px;"><select class="form-select" name="hs_code[]" required>${hsOptions}</select></td>
                <td><input name="item_name[]" class="form-control"></td>
                <td><input name="qty[]" class="form-control qty" type="number" step="0.01"></td>
                <td><input name="unit[]" class="form-control"></td>
                <td><input name="rate[]" class="form-control rate" type="number" step="0.01"></td>
                <td><input name="disc_perc[]" class="form-control discPerc" type="number" step="0.01"></td>
                <td><input name="discount[]" class="form-control discount" readonly></td>
                <td><input name="excl_tax_amt[]" class="form-control exclTax" readonly></td>
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
                    if (tbody.querySelectorAll('tr').length > 1) {
                        btn.closest('tr').remove();
                        updateTotals();
                    } else {
                        alert("You must have at least one row in the invoice.");
                    }
                };
            });

            // Calculate amounts on input
            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                const qty = row.querySelector('.qty');
                const rate = row.querySelector('.rate');
                const discPerc = row.querySelector('.discPerc');
                if (qty && rate && discPerc) {
                    [qty, rate, discPerc].forEach(el => {
                        el.oninput = () => {
                            const q = parseFloat(qty.value) || 0;
                            const r = parseFloat(rate.value) || 0;
                            const dP = parseFloat(discPerc.value) || 0;
                            const amt = q * r;
                            const disc = amt * (dP / 100);
                            const exTax = amt - disc;
                            row.querySelector('.amount').value = amt.toFixed(2);
                            row.querySelector('.discount').value = disc.toFixed(2);
                            row.querySelector('.exclTax').value = exTax.toFixed(2);
                            updateTotals();
                        };
                    });
                }
            });
        }

        function updateTotals() {
            let total = 0;
            document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
                total += parseFloat(row.querySelector('.exclTax').value) || 0;
            });
            document.getElementById('totalAmount').value = total.toFixed(2);
            const discountPct = parseFloat(document.getElementById('discount').value) || 0;
            const taxPct = parseFloat(document.getElementById('tax').value) || 0;
            let afterDisc = total - (total * discountPct / 100);
            let grand = afterDisc + (afterDisc * taxPct / 100);
            document.getElementById('grandTotal').value = grand.toFixed(2);
        }

        document.getElementById('discount').addEventListener('input', updateTotals);
        document.getElementById('tax').addEventListener('input', updateTotals);

        bindEvents();
        updateTotals();
    </script>
</body>

</html>