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
            <h3 class="mb-4">🧾 Create Invoice</h3>

            <!-- Invoice Header Form -->
            <form action="save_invoice.php" method="POST" id="invoiceForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="serial_no" class="form-label">Serial No</label>
                        <input type="text" class="form-control" id="serial_no" name="serial_no"
                            value="<?= $serialNo ?>" readonly required>
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
                                <!-- <th>Item Code</th> -->
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
                                <td><input type="number" step="0.01" min="0" name="amount[]" class="form-control amount" readonly></td>
                                <td><button type="button" class="btn btn-sm btn-danger removeRow">X</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-outline-secondary" id="addRowBtn">+ Add Row</button>

                <!-- Summary Fields -->
                <div class="row mt-4 g-3">
                    <div class="col-md-4">
                        <label for="discount" class="form-label">Discount (%)</label>
                        <input type="number" name="discount" id="discount" class="form-control" value="0" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-4">
                        <label for="tax" class="form-label">Tax (%)</label>
                        <input type="number" name="tax" id="tax" class="form-control" value="18" step="0.01" min="0" max="100">
                    </div>
                    <div class="col-md-4">
                        <label for="totalAmount" class="form-label"><strong>Gross Total:</strong></label>
                        <input type="text" id="totalAmount" class="form-control" readonly>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4 offset-md-8">
                        <label for="grandTotal" class="form-label"><strong>Grand Total (Incl. Tax):</strong></label>
                        <input type="text" id="grandTotal" class="form-control" readonly>
                    </div>
                </div>

                <!-- Save Button -->
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

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <script>
        // Show bootstrap modal
        const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));

        document.getElementById('addCustomerBtn').addEventListener('click', () => {
            customerModal.show();
        });

        // Handle customer form submit via AJAX
        document.getElementById('customerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            fetch('save_customer.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Hide modal and reload customers dropdown
                        customerModal.hide();
                        loadCustomers();
                    } else {
                        alert(data.message);
                    }
                });
        });

        // Reload customer dropdown options
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

        // Invoice Items logic
        const hsCodes = <?php echo json_encode(array_column($hs_codes, 'hs_code')); ?>;
        document.getElementById('addRowBtn').addEventListener('click', () => {
            const tbody = document.querySelector('#itemsTable tbody');
            const newRow = document.createElement('tr');
            // Build HS Code options
            let hsOptions = '<option disabled selected>Select</option>';
            hsCodes.forEach(code => {
                hsOptions += `<option value="${code}">${code}</option>`;
            });
            newRow.innerHTML = `
              <td style="width: 150px;"><select class="form-select" name="hs_code[]" required>${hsOptions}</select></td>
              <td><input type="text" name="item_name[]" class="form-control"></td>
              <td><input type="number" step="1" min="0" name="qty[]" class="form-control qty"></td>
              <td><input type="text" name="unit[]" class="form-control"></td>
              <td><input type="number" step="0.01" min="0" name="rate[]" class="form-control rate"></td>
              <td><input type="number" step="0.01" min="0" max="100" name="disc_perc[]" class="form-control discPerc"></td>
              <td><input type="number" step="0.01" min="0" name="discount[]" class="form-control discount" readonly></td>
              <td><input type="number" step="0.01" min="0" name="excl_tax_amt[]" class="form-control exclTax" readonly></td>
              <td><input type="number" step="0.01" min="0" name="amount[]" class="form-control amount" readonly></td>
              <td><button type="button" class="btn btn-danger btn-sm removeRow">X</button></td>
            `;
            tbody.appendChild(newRow);
            bindEvents();
        });

        // Remove Row & recalc totals
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