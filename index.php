<?php
require 'db.php';

// Handle pagination
$limit = 10;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

$sql = "SELECT invoices.*, customers.name AS customer_name 
        FROM invoices 
        JOIN customers ON invoices.customer_id = customers.id 
        WHERE 1 ";

$count_sql = "SELECT COUNT(*) FROM invoices 
              JOIN customers ON invoices.customer_id = customers.id 
              WHERE 1 ";

$params = [];

// Search
if ($search) {
    $sql .= " AND (invoices.serial_no LIKE :search OR customers.name LIKE :search) ";
    $count_sql .= " AND (invoices.serial_no LIKE :search OR customers.name LIKE :search) ";
    $params['search'] = "%$search%";
}

// Date filtering
if ($date_from && $date_to) {
    $sql .= " AND invoices.date BETWEEN :date_from AND :date_to ";
    $count_sql .= " AND invoices.date BETWEEN :date_from AND :date_to ";
    $params['date_from'] = $date_from;
    $params['date_to'] = $date_to;
}

$sql .= " ORDER BY invoices.id DESC LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll();

// Get total records for pagination
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Totals
$gross_total = 0;
$grand_total = 0;
foreach ($invoices as $inv) {
    $gross_total += $inv['gross_total'];
    $grand_total += $inv['grand_total'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Invoice List</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #eef3f8;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            animation: fadeInBody 0.6s ease-in-out;
        }

        @keyframes fadeInBody {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            animation: slideDown 0.5s ease-in-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .invoice-header h2 {
            font-weight: 700;
            background: #041a2a;
            border-radius: 10px;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
        }

        .logo {
            width: 300px;
            height: auto;
        }

        .invoice-header .btn {
            border-radius: 50px;
            padding: 6px 16px;
            font-weight: 500;
            transition: all 0.3s ease-in-out;
        }

        .invoice-header .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #fff;
            animation: fadeUp 0.5s ease-in-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card-header {
            background: linear-gradient(90deg, #041a2a, #4dabf7);
            color: white;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 20px;
        }

        .table thead th {
            background: #f7f9fc;
            color: #495057;
            font-weight: 600;
            padding: 12px;
            border-bottom: 2px solid #dee2e6;
        }

        .table tbody tr {
            transition: background 0.2s ease-in-out;
        }

        .table tbody tr:hover {
            background: #f1f5ff;
        }

        .table td {
            padding: 10px;
            vertical-align: middle !important;
        }

        .totals-row td {
            font-weight: bold;
            background: #f8f9fa;
        }

        .pagination .page-link {
            border-radius: 50px;
            padding: 6px 12px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            transform: scale(1.1);
        }

        .pagination .active .page-link {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        form label {
            font-weight: 500;
            font-size: 13px;
            margin-bottom: 3px;
        }

        form input,
        form button {
            font-size: 13px;
        }

        .table-responsive {
            border-radius: 0 0 12px 12px;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="invoice-header mb-4">
            <h2>
                <img src="https://vibrantengineering.pk/wp-content/uploads/2023/08/check-logo.png" alt="Logo" class="logo">
            </h2>
            <h3>Invoice Management</h3>
            <div class="d-flex flex-wrap gap-2">
                <a href="add_invoice.php" class="btn btn-success">+ Add Invoice</a>
                <a href="add_customer.php" class="btn btn-outline-secondary">+ Add Customer</a>
                <a href="customers.php" class="btn btn-primary">View Customers</a>
                <a href="hs_code.php" class="btn btn-info">View HS Code</a>
            </div>
        </div>

        <form method="get" class="row gy-2 gx-2 align-items-end mb-4 bg-white p-3 rounded shadow-sm" style="justify-content: space-between; animation: fadeUp 0.5s ease-in-out;">
            <div class="col-md-6">
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Serial or Customer" />
            </div>
            <div class="col-md-2">
                <label>From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
            </div>
            <div class="col-md-2">
                <label>To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
            </div>
            <div class="col-md-auto">
                <button class="btn btn-primary">Filter</button>
                <?php if ($search || ($date_from && $date_to)): ?>
                    <a href="index.php" class="btn btn-outline-danger">Reset</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card">
            <div class="card-header">Invoice List</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Serial No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Gross Total</th>
                            <th>Grand Total</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($invoices): ?>
                            <?php $rowNum = 1; ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr>
                                    <td><?= $rowNum++ ?></td>
                                    <td><?= htmlspecialchars($inv['serial_no']) ?></td>
                                    <td><?= htmlspecialchars($inv['date']) ?></td>
                                    <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                                    <td>Rs. <?= number_format($inv['gross_total'], 2) ?></td>
                                    <td>Rs. <?= number_format($inv['grand_total'], 2) ?></td>
                                    <td class="text-center">
                                        <a href="view_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-success">Invoice</a>
                                        <a href="edit_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_invoice.php?id=<?= $inv['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this invoice?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="totals-row">
                                <td colspan="4" class="text-end">Total</td>
                                <td>Rs. <?= number_format($gross_total, 2) ?></td>
                                <td>Rs. <?= number_format($grand_total, 2) ?></td>
                                <td></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No invoices found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</body>

</html>