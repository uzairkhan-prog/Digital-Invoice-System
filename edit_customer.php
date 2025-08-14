<?php
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: customers.php");
    exit;
}

// Fetch existing customer
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    header("Location: customers.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $cnic = $_POST['cnic'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $city = $_POST['city'] ?? 'Karachi, Pakistan';
    $strn = $_POST['strn'] ?? '';
    $ntn = $_POST['ntn'] ?? '';

    if (!$code || !$name || !$cnic || !$email) {
        $error = "Code, Name, CNIC and Email are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE customers SET code=?, name=?, cnic=?, email=?, phone=?, address=?, city=?, strn=?, ntn=? WHERE id=?");
        $stmt->execute([$code, $name, $cnic, $email, $phone, $address, $city, $strn, $ntn, $id]);
        header("Location: customers.php");
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
                        <div class="col-md-4">
                            <label for="code" class="form-label">Customer Code <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code" class="form-control" required value="<?= htmlspecialchars($customer['code']) ?>">
                            <div class="invalid-feedback">Customer Code is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($customer['name']) ?>">
                            <div class="invalid-feedback">Customer Name is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($customer['email']) ?>">
                            <div class="invalid-feedback">Valid Email is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control" value="<?= htmlspecialchars($customer['address']) ?>">
                        </div>
                        <!-- <div class="col-md-4">
                            <label for="city" class="form-label">Province</label>
                            <input type="text" id="city" name="city" class="form-control" value="<?= htmlspecialchars($customer['city']) ?>">
                        </div> -->
                        <div class="col-md-4">
                            <label for="cnic" class="form-label">CNIC <span class="text-danger">*</span></label>
                            <input type="text" id="cnic" name="cnic" class="form-control" required value="<?= htmlspecialchars($customer['cnic']) ?>">
                            <div class="invalid-feedback">CNIC is required.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="strn" class="form-label">STRN</label>
                            <input type="text" id="strn" name="strn" class="form-control" value="<?= htmlspecialchars($customer['strn']) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="ntn" class="form-label">NTN</label>
                            <input type="text" id="ntn" name="ntn" class="form-control" value="<?= htmlspecialchars($customer['ntn']) ?>">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success px-4">Update Customer</button>
                        <a href="customers.php" class="btn btn-secondary ms-2">Back to Customers</a>
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