<?php
require 'db.php';

// Fetch all customers
$stmt = $pdo->query("SELECT * FROM hs_codes ORDER BY name");
$hs_codes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>HS Code</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: #f4f8fb;
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn {
            border-radius: 50px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 0 40px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            background: #ffffff;
            padding: 20px;
        }

        .table thead th {
            background: linear-gradient(90deg, #b2d8ff, #e0f0ff);
            color: #333;
        }

        .table td {
            background-color: #ffffff;
        }

        .action-buttons a {
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <div class="container py-5">
        <div class="page-header mb-4">
            <h2 class="fw-bold mb-3">🏷 HS Code Management</h2>
            <div class="d-flex flex-wrap gap-2">
                <a href="add_hs_code.php" class="btn btn-success">+ Add New HS Code</a>
                <a href="index.php" class="btn btn-outline-secondary">← Back to Invoices</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light text-dark fw-semibold">
                Customer List
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>HS Code</th>
                                <!-- <th>Name</th> -->
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1; // Start numbering from 1
                            foreach ($hs_codes as $c):
                            ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td><?= htmlspecialchars($c['hs_code']) ?></td>
                                    <!-- <td><?= htmlspecialchars($c['name']) ?></td> -->
                                    <td class="text-center action-buttons">
                                        <a href="edit_hs_code.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_hs_code.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this HS code?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($hs_codes)): ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">No HS Code found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>