<?php
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: hs_code.php");
    exit;
}

// Fetch existing customer
$stmt = $pdo->prepare("SELECT * FROM hs_codes WHERE id = ?");
$stmt->execute([$id]);
$hs_code = $stmt->fetch();

if (!$hs_code) {
    header("Location: hs_codes.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hs_code = $_POST['hs_code'];
    $name = 'HS CODE';

    if (!$hs_code || !$name) {
        $error = "Code and Name are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE hs_codes SET hs_code=?, name=? WHERE id=?");
        $stmt->execute([$hs_code, $name, $id]);
        header("Location: hs_code.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Customer</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        label {
            font-weight: 500;
        }

        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
        }

        .btn-success:hover {
            background: linear-gradient(45deg, #218838, #198754);
        }
    </style>
</head>

<body class="py-5">

    <div class="container">
        <div class="card">
            <div class="card-header">
                Edit Customer
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="hs_code" class="form-label">HS Code <span class="text-danger">*</span></label>
                            <input type="text" id="hs_code" name="hs_code" class="form-control" required value="<?= htmlspecialchars($hs_code['hs_code']) ?>">
                            <div class="invalid-feedback">HS Code is required.</div>
                        </div>
                        <!-- <div class="col-md-6">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($hs_code['name']) ?>">
                            <div class="invalid-feedback">Name is required.</div>
                        </div> -->
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success px-4">Update HS Code</button>
                        <a href="hs_codes.php" class="btn btn-secondary ms-2">Back to HS Code</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', e => {
                    if (!form.checkValidity()) {
                        e.preventDefault()
                        e.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>