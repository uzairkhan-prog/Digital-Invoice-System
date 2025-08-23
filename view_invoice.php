<?php
// view_invoice.php
require 'db.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}
$id = (int)$id;

// Fetch invoice
$stmt = $pdo->prepare("
  SELECT invoices.*, customers.name AS customer_name, customers.address AS customer_address,
         customers.email AS customer_email, customers.phone AS customer_phone, customers.city AS customer_city
  FROM invoices
  JOIN customers ON invoices.customer_id = customers.id
  WHERE invoices.id = ?
");
$stmt->execute([$id]);
$invoice = $stmt->fetch();
if (!$invoice) {
    header('Location: index.php');
    exit;
}

// Fetch invoice items
$itemStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$itemStmt->execute([$id]);
$items = $itemStmt->fetchAll();

// print_r($items); // Debugging line to check fetched items
// exit;

// ===== CALCULATE TOTALS =====
$gross_total = 0;
$discount_total = 0;
$tax_total = 0;
$grand_total = 0;

foreach ($items as $key => $item) {
    $items[$key]['discount'] = ($item['rate'] * $item['qty']) * ($item['disc_perc'] / 100);
    $items[$key]['excl_tax_amt'] = ($item['rate'] * $item['qty']) - $items[$key]['discount'];
    $items[$key]['tax_amt'] = $items[$key]['excl_tax_amt'] * ($item['tax_perc'] / 100);
    $items[$key]['amount'] = $items[$key]['excl_tax_amt'] + $items[$key]['tax_amt'];

    $gross_total += $item['rate'] * $item['qty'];
    $discount_total += $items[$key]['discount'];
    $tax_total += $items[$key]['tax_amt'];
    $grand_total += $items[$key]['amount'];
}

// ===== HELPER FUNCTIONS =====
function nf($number, $decimals = 2) {
    return number_format((float)$number, $decimals);
}

function convertNumberToWords($number) {
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $words = array(
        '0' => '', '1' => 'One', '2' => 'Two', '3' => 'Three', '4' => 'Four',
        '5' => 'Five', '6' => 'Six', '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
        '10' => 'Ten','11' => 'Eleven','12' => 'Twelve','13' => 'Thirteen','14' => 'Fourteen',
        '15' => 'Fifteen','16' => 'Sixteen','17' => 'Seventeen','18' => 'Eighteen','19' => 'Nineteen',
        '20' => 'Twenty','30' => 'Thirty','40' => 'Forty','50' => 'Fifty','60' => 'Sixty',
        '70' => 'Seventy','80' => 'Eighty','90' => 'Ninety'
    );
    $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
    $i = 0;
    $str = [];
    $digits_1 = strlen($no);
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $number = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;
        if ($number) {
            $str[] = ($number < 21) ? $words[$number] . " " . $digits[count($str)]
                : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[count($str)];
        } else $str[] = null;
    }
    $str = array_reverse($str);
    $result = implode(' ', $str);
    $points = ($point) ? "and " . $words[floor($point / 10) * 10] . " " . $words[$point % 10] . " Paise" : '';
    return ucfirst(trim($result)) . " Rupees Only " . $points . " /-";
}

$amountInWords = convertNumberToWords($grand_total);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= htmlspecialchars($invoice['serial_no']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10px;
        }

        .invoice-box {
            background: #fff;
            max-width: 900px;
            margin: auto;
            padding: 30px 10px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, .05);
        }

        .company-info {
            text-align: center;
            margin-bottom: 10px;
        }

        .company-info h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #3d4fab;
        }

        .company-info p {
            margin: 0;
            font-size: 12px;
        }

        .invoice-details p {
            margin-bottom: 5px;
        }

        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 25px;
            color: #dc3545;
        }

        .logo {
            width: 250px;
            height: auto;
            background: #041a2a;
            border-radius: 5px;
            padding: 0 10px;
        }

        .table thead {
            background-color: #0d6efd;
            color: white;
        }

        .table-bordered tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .table td,
        .table th {
            font-size: 10px;
        }

        .fw-bold {
            background-color: #ffc107 !important;
        }

        .signature-box {
            border-bottom: 2px solid #000;
            height: 60px;
            width: 80%;
            margin: 0 auto;
        }

        .signature-label {
            margin-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }

        .table>:not(caption)>*>* {
            padding: 1px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: #fff !important;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                margin: 0;
            }
        }

        table,
        tr,
        td,
        th {
            page-break-inside: avoid !important;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="mb-4 text-center no-print">
            <a href="index.php" class="btn btn-secondary me-2">Back</a>
            <button class="btn btn-primary" onclick="downloadPDF()">Export as PDF</button>
        </div>

        <div class="invoice-box" id="invoice-content">
            <div class="company-info">
                <h2>
                    <?php
                        $logo_url = "https://vibrantengineering.pk/wp-content/uploads/2023/08/check-logo.png";
                        $logo_data = base64_encode(file_get_contents($logo_url));
                    ?>
                    <img src="data:image/png;base64,<?= $logo_data ?>" alt="Logo" class="logo">
                </h2>
                <p>SHOP NO 13, FALAK PARK VIEW, NEAR ENQUIRY OFFICE NAZIMABAD Block 2</p>
                <p>District Central Nazimabad, KARACHI</p>
                <p>NTN: 4881920-5 &nbsp;&nbsp;&nbsp; STRN: 3277876243544</p>
            </div>

            <div class="invoice-title">SALE TAX INVOICE</div>

            <div class="d-flex justify-content-between invoice-header mb-3">
                <div>
                    <strong>Invoice #: </strong><?= htmlspecialchars($invoice['serial_no']) ?><br>
                    <strong>Date: </strong><?= htmlspecialchars($invoice['date']) ?>
                </div>
                <div class="text-end">
                    <strong>Created At:</strong> <?= htmlspecialchars($invoice['created_at']) ?><br>
                    <strong>FBR Invoice No:</strong> <?= htmlspecialchars($invoice['fbr_invoice_no'] ?? 'N/A') ?>
                </div>
            </div>

            <div class="row mb-3 invoice-details">
                <div class="col-md-7">
                    <h6>Bill To:</h6>
                    <p><strong>Customer:</strong> <?= htmlspecialchars($invoice['customer_name']) ?></p>
                    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($invoice['customer_address'])) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($invoice['customer_phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($invoice['customer_email']) ?></p>
                </div>
                <div class="col-md-5 text-end">
                    <h6>Invoice Info:</h6>
                    <p><strong>Invoice Type:</strong> <br> <?= htmlspecialchars($invoice['invoice_type']) ?></p>
                    <p><strong>PO No:</strong> <?= htmlspecialchars($invoice['po_no'] ?? 'N/A') ?></p>
                    <p><strong>Terms of Payment:</strong> <?= htmlspecialchars($invoice['terms_of_payment'] ?? 'N/A') ?></p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>HS Code</th>
                            <th>Item Name</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th>Rate</th>
                            <th>Disc %</th>
                            <th>Discount</th>
                            <th>Excl. Tax</th>
                            <th>Tax %</th>
                            <th>Tax Amt</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($item['hs_code']) ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= nf($item['qty'], 0) ?></td>
                                <td><?= htmlspecialchars($item['unit']) ?></td>
                                <td><?= nf($item['rate']) ?></td>
                                <td><?= nf($item['disc_perc']) ?></td>
                                <td><?= nf($item['discount']) ?></td>
                                <td><?= nf($item['excl_tax_amt']) ?></td>
                                <td><?= nf($item['tax_perc']) ?></td>
                                <td><?= nf($item['tax_amt']) ?></td>
                                <td><?= nf($item['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table">
                        <tr>
                            <th>Gross Total (Excl. Tax):</th>
                            <td><?= nf($gross_total) ?></td>
                        </tr>
                        <tr>
                            <th>Total Discount:</th>
                            <td>- <?= nf($discount_total) ?></td>
                        </tr>
                        <tr>
                            <th>Sub Total (After Discount):</th>
                            <td><?= nf($gross_total - $discount_total) ?></td>
                        </tr>
                        <tr>
                            <th>Total Tax:</th>
                            <td>+ <?= nf($tax_total) ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <th>Grand Total (Incl. Tax):</th>
                            <td><?= nf($grand_total) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 text-center">
                    <strong>AMOUNT IN WORDS :</strong>
                    <p><?= $amountInWords ?></p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 text-center">
                    <div class="signature-box"></div>
                    <div class="signature-label">Customer Signature</div>
                    <div><?= htmlspecialchars($invoice['customer_name']) ?></div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="signature-box"></div>
                    <div class="signature-label">Seller Signature</div>
                    <div>Authorized Representative</div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mt-3 text-center">
                    <p><strong>THANK YOU FOR YOUR BUSINESS !</strong></p>
                </div>
            </div>

        </div>
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: [0.2, 0.2, 0.2, 0.2],
                filename: 'Invoice_<?= htmlspecialchars($invoice['serial_no']) ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    allowTaint: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    mode: ['avoid-all', 'css', 'legacy']
                }
            };

            // Add crossOrigin attribute to all external images in the invoice
            const imgs = element.getElementsByTagName('img');
            for (let i = 0; i < imgs.length; i++) {
                if (!imgs[i].crossOrigin) {
                    imgs[i].crossOrigin = "anonymous";
                }
            }

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>