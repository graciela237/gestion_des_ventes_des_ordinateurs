<?php
// Flutterwave API Configuration
define('FLUTTERWAVE_PUBLIC_KEY', 'FLWPUBK_TEST-93beb09dca975291be9b9b489fe7eff5-X'); // Replace with your actual public key
define('FLUTTERWAVE_SECRET_KEY', 'FLWSECK_TEST-f9eee9852263ec7edc209aad56649718-X'); // Replace with your actual secret key
define('FLUTTERWAVE_API_URL', 'https://api.flutterwave.com/v3');
define('FLUTTERWAVE_REDIRECT_URL', 'https://yourdomain.com/payment_callback.php'); // Replace with your actual domain

// Function to initialize payment
function initializeFlutterwavePayment($userData, $orderData) {
    // Generate a unique transaction reference
    $txRef = 'TECHPRO_' . time() . '_' . rand(10000, 99999);
    
    // Prepare payment data
    $paymentData = [
        'tx_ref' => $txRef,
        'amount' => $orderData['amount'],
        'currency' => 'FCFA', 
        'redirect_url' => FLUTTERWAVE_REDIRECT_URL,
        'customer' => [
            'email' => $userData['email'],
            'name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'phone_number' => $userData['phone_number'] ?? ''
        ],
        'meta' => [
            'order_id' => $orderData['orderId'],
            'user_id' => $userData['user_id']
        ],
        'customizations' => [
            'title' => 'TechPro Ecommerce',
            'description' => 'Payment for order #' . $orderData['orderId'],
            'logo' => 'https://yourdomain.com/images/logo.png'
        ],
        'payment_options' => 'card,mobilemoneysn,mobilemoneyci,mobilemoneygh,mpesa,ussd' // Include mobile money options
    ];
    
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => FLUTTERWAVE_API_URL . '/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FLUTTERWAVE_SECRET_KEY
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $err
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        return [
            'success' => true,
            'payment_link' => $result['data']['link'],
            'tx_ref' => $txRef
        ];
    }
    
    return [
        'success' => false,
        'message' => $result['message'] ?? 'Failed to initialize payment'
    ];
}

// Function to verify payment
function verifyFlutterwavePayment($transactionId) {
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => FLUTTERWAVE_API_URL . '/transactions/' . $transactionId . '/verify',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FLUTTERWAVE_SECRET_KEY
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $err
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        // Check if the payment was successful
        if ($result['data']['status'] === 'successful') {
            return [
                'success' => true,
                'data' => $result['data'],
                'message' => 'Payment verified successfully'
            ];
        } else {
            return [
                'success' => false,
                'data' => $result['data'],
                'message' => 'Payment not successful. Status: ' . $result['data']['status']
            ];
        }
    }
    
    return [
        'success' => false,
        'message' => $result['message'] ?? 'Failed to verify payment'
    ];
}

// Function to generate PDF receipt
function generateReceiptPDF($paymentData) {
    // You may need to install a PDF library like TCPDF, FPDF, or Dompdf
    // For this example, we'll use TCPDF which is a popular choice
    
    // Make sure you have TCPDF installed via Composer or included manually
    // require_once('tcpdf/tcpdf.php');
    
    // Check if TCPDF is available, if not return error
    if (!class_exists('TCPDF')) {
        // If you don't have TCPDF, generate a simple HTML receipt instead
        return generateHTMLReceipt($paymentData);
    }
    
    // Create new PDF document
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    
    // Set document information
    $pdf->SetCreator('TechPro Ecommerce');
    $pdf->SetAuthor('TechPro Ecommerce');
    $pdf->SetTitle('Payment Receipt');
    $pdf->SetSubject('Payment Receipt for Order #' . $paymentData['meta']['order_id']);
    
    // Remove header and footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Company logo and info
    $pdf->Image('https://yourdomain.com/images/logo.png', 10, 10, 30, 0, 'PNG');
    $pdf->SetXY(50, 10);
    $pdf->Cell(0, 10, 'TechPro Ecommerce', 0, 1);
    $pdf->SetX(50);
    $pdf->Cell(0, 10, 'https://yourdomain.com', 0, 1);
    $pdf->SetX(50);
    $pdf->Cell(0, 10, 'support@yourdomain.com', 0, 1);
    
    // Receipt title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 15, 'PAYMENT RECEIPT', 0, 1, 'C');
    
    // Transaction details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Transaction Details', 0, 1);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(80, 8, 'Transaction Reference:', 0);
    $pdf->Cell(0, 8, $paymentData['tx_ref'] ?? 'N/A', 0, 1);
    
    $pdf->Cell(80, 8, 'Transaction ID:', 0);
    $pdf->Cell(0, 8, $paymentData['id'] ?? 'N/A', 0, 1);
    
    $pdf->Cell(80, 8, 'Date:', 0);
    $pdf->Cell(0, 8, date('Y-m-d H:i:s', strtotime($paymentData['created_at'] ?? 'now')), 0, 1);
    
    $pdf->Cell(80, 8, 'Payment Method:', 0);
    $pdf->Cell(0, 8, $paymentData['payment_type'] ?? 'N/A', 0, 1);
    
    // Customer details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Customer Information', 0, 1);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(80, 8, 'Name:', 0);
    $pdf->Cell(0, 8, $paymentData['customer']['name'] ?? 'N/A', 0, 1);
    
    $pdf->Cell(80, 8, 'Email:', 0);
    $pdf->Cell(0, 8, $paymentData['customer']['email'] ?? 'N/A', 0, 1);
    
    // Order details
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Order Information', 0, 1);
    
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(80, 8, 'Order ID:', 0);
    $pdf->Cell(0, 8, $paymentData['meta']['order_id'] ?? 'N/A', 0, 1);
    
    $pdf->Cell(80, 8, 'Amount:', 0);
    $pdf->Cell(0, 8, number_format($paymentData['amount'], 2) . ' ' . $paymentData['currency'], 0, 1);
    
    // Thank you note
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'Thank you for your purchase!', 0, 1, 'C');
    
    // Generate filename
    $fileName = 'receipt_' . ($paymentData['tx_ref'] ?? time()) . '.pdf';
    $filePath = __DIR__ . '/receipts/' . $fileName;
    
    // Make sure the directory exists
    if (!is_dir(__DIR__ . '/receipts')) {
        mkdir(__DIR__ . '/receipts', 0755, true);
    }
    
    // Save PDF to file
    $pdf->Output($filePath, 'F');
    
    return [
        'success' => true,
        'file_path' => $filePath,
        'file_name' => $fileName
    ];
}

// Function to generate HTML receipt (fallback if TCPDF is not available)
function generateHTMLReceipt($paymentData) {
    $fileName = 'receipt_' . ($paymentData['tx_ref'] ?? time()) . '.html';
    $filePath = __DIR__ . '/receipts/' . $fileName;
    
    // Make sure the directory exists
    if (!is_dir(__DIR__ . '/receipts')) {
        mkdir(__DIR__ . '/receipts', 0755, true);
    }
    
    // Generate HTML content
    $html = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Receipt</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { max-width: 150px; }
            .title { font-size: 24px; font-weight: bold; margin: 20px 0; }
            .section { margin-bottom: 20px; }
            .section-title { font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
            .row { display: flex; margin-bottom: 5px; }
            .label { width: 200px; font-weight: bold; }
            .value { flex: 1; }
            .footer { text-align: center; margin-top: 40px; color: #666; }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="https://yourdomain.com/images/logo.png" alt="TechPro Ecommerce" class="logo">
            <h1>TechPro Ecommerce</h1>
            <p>https://yourdomain.com | support@yourdomain.com</p>
            <div class="title">PAYMENT RECEIPT</div>
        </div>
        
        <div class="section">
            <div class="section-title">Transaction Details</div>
            <div class="row">
                <div class="label">Transaction Reference:</div>
                <div class="value">' . ($paymentData['tx_ref'] ?? 'N/A') . '</div>
            </div>
            <div class="row">
                <div class="label">Transaction ID:</div>
                <div class="value">' . ($paymentData['id'] ?? 'N/A') . '</div>
            </div>
            <div class="row">
                <div class="label">Date:</div>
                <div class="value">' . date('Y-m-d H:i:s', strtotime($paymentData['created_at'] ?? 'now')) . '</div>
            </div>
            <div class="row">
                <div class="label">Payment Method:</div>
                <div class="value">' . ($paymentData['payment_type'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Customer Information</div>
            <div class="row">
                <div class="label">Name:</div>
                <div class="value">' . ($paymentData['customer']['name'] ?? 'N/A') . '</div>
            </div>
            <div class="row">
                <div class="label">Email:</div>
                <div class="value">' . ($paymentData['customer']['email'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Order Information</div>
            <div class="row">
                <div class="label">Order ID:</div>
                <div class="value">' . ($paymentData['meta']['order_id'] ?? 'N/A') . '</div>
            </div>
            <div class="row">
                <div class="label">Amount:</div>
                <div class="value">' . number_format($paymentData['amount'], 2) . ' ' . $paymentData['currency'] . '</div>
            </div>
        </div>
        
        <div class="footer">
            <p>Thank you for your purchase!</p>
        </div>
    </body>
    </html>';
    
    // Save HTML to file
    file_put_contents($filePath, $html);
    
    return [
        'success' => true,
        'file_path' => $filePath,
        'file_name' => $fileName,
        'is_html' => true
    ];
}

// Modified function to process callback from Flutterwave that now includes receipt generation and download
function handleFlutterwaveCallback() {
    // Get the transaction ID from the callback URL
    $transactionId = $_GET['transaction_id'] ?? null;
    $status = $_GET['status'] ?? '';
    $tx_ref = $_GET['tx_ref'] ?? '';
    
    if (!$transactionId || $status !== 'successful') {
        return [
            'success' => false,
            'message' => 'Payment was not successful or transaction ID is missing'
        ];
    }
    
    // Verify the payment
    $verificationResult = verifyFlutterwavePayment($transactionId);
    
    if (!$verificationResult['success']) {
        return $verificationResult;
    }
    
    // Extract payment details from verification result
    $paymentData = $verificationResult['data'];
    
    // Make sure the amount paid matches the expected amount
    // This should be implemented based on your order storage system
    // $orderAmount = getOrderAmountFromDatabase($paymentData['meta']['order_id']);
    
    // if ($paymentData['amount'] < $orderAmount) {
    //     return [
    //         'success' => false,
    //         'message' => 'Amount paid does not match the order amount'
    //     ];
    // }
    
    // Update order status in database
    // updateOrderStatus($paymentData['meta']['order_id'], 'paid');
    
    // Generate receipt
    $receiptResult = generateReceiptPDF($paymentData);
    
    if (!$receiptResult['success']) {
        // If PDF generation fails, log the error but continue with the process
        logFlutterwavePaymentActivity('RECEIPT_GENERATION_FAILED', [
            'transaction_id' => $transactionId,
            'error' => $receiptResult['message'] ?? 'Unknown error'
        ]);
    }
    
    // Log successful payment
    logFlutterwavePaymentActivity('PAYMENT_SUCCESSFUL', [
        'transaction_id' => $transactionId,
        'amount' => $paymentData['amount'],
        'currency' => $paymentData['currency'],
        'customer' => $paymentData['customer']['email']
    ]);
    
    return [
        'success' => true,
        'message' => 'Payment completed and verified successfully',
        'data' => $paymentData,
        'receipt' => $receiptResult ?? null
    ];
}

// Function to download receipt
function downloadReceipt($filePath, $fileName, $isHtml = false) {
    if (!file_exists($filePath)) {
        header('HTTP/1.0 404 Not Found');
        echo 'Receipt file not found.';
        exit;
    }
    
    // Set the appropriate headers
    header('Content-Description: File Transfer');
    
    if ($isHtml) {
        header('Content-Type: text/html');
    } else {
        header('Content-Type: application/pdf');
    }
    
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    
    // Read the file and output it to the browser
    readfile($filePath);
    exit;
}

// Function to fetch transaction history
function getFlutterwaveTransactions($from = null, $to = null, $status = null, $page = 1) {
    $queryParams = [];
    
    if ($from) {
        $queryParams[] = 'from=' . urlencode($from);
    }
    
    if ($to) {
        $queryParams[] = 'to=' . urlencode($to);
    }
    
    if ($status) {
        $queryParams[] = 'status=' . urlencode($status);
    }
    
    $queryParams[] = 'page=' . $page;
    
    $queryString = implode('&', $queryParams);
    $url = FLUTTERWAVE_API_URL . '/transactions?' . $queryString;
    
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FLUTTERWAVE_SECRET_KEY
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $err
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        return [
            'success' => true,
            'data' => $result['data'],
            'meta' => $result['meta'] ?? []
        ];
    }
    
    return [
        'success' => false,
        'message' => $result['message'] ?? 'Failed to fetch transactions'
    ];
}

// Function to process refunds
function initiateFlutterwaveRefund($transactionId, $amount = null, $reason = null) {
    $refundData = [
        'id' => $transactionId
    ];
    
    if ($amount !== null) {
        $refundData['amount'] = $amount;
    }
    
    if ($reason !== null) {
        $refundData['reason'] = $reason;
    }
    
    // Initialize cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => FLUTTERWAVE_API_URL . '/transactions/' . $transactionId . '/refund',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($refundData),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FLUTTERWAVE_SECRET_KEY
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return [
            'success' => false,
            'message' => 'cURL Error: ' . $err
        ];
    }
    
    $result = json_decode($response, true);
    
    if ($result && isset($result['status']) && $result['status'] === 'success') {
        return [
            'success' => true,
            'data' => $result['data'],
            'message' => 'Refund initiated successfully'
        ];
    }
    
    return [
        'success' => false,
        'message' => $result['message'] ?? 'Failed to initiate refund'
    ];
}

// Helper function to log payment activities
function logFlutterwavePaymentActivity($activityType, $data) {
    $logFile = __DIR__ . '/flutterwave_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logData = "[{$timestamp}] [{$activityType}] " . json_encode($data) . PHP_EOL;
    
    return file_put_contents($logFile, $logData, FILE_APPEND);
}

// Add this code to your payment_callback.php file to handle the auto-download
if (isset($_GET['transaction_id']) && isset($_GET['status']) && $_GET['status'] === 'successful') {
    // Process the payment callback
    $callbackResult = handleFlutterwaveCallback();
    
    if ($callbackResult['success'] && isset($callbackResult['receipt']) && $callbackResult['receipt']['success']) {
        // Auto download the receipt
        $filePath = $callbackResult['receipt']['file_path'];
        $fileName = $callbackResult['receipt']['file_name'];
        $isHtml = isset($callbackResult['receipt']['is_html']) && $callbackResult['receipt']['is_html'];
        
        // Trigger the download
        downloadReceipt($filePath, $fileName, $isHtml);
    } else {
        // Redirect to a success page without receipt download
        header('Location: https://yourdomain.com/payment-success.php?tx_ref=' . $_GET['tx_ref']);
        exit;
    }
}

// Example usage - initiate payment
/*
$userData = [
    'email' => 'customer@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'phone_number' => '+22950123456',
    'user_id' => 12345
];

$orderData = [
    'amount' => 5000, // Amount in FCFA
    'orderId' => 'ORD12345'
];

$paymentInitiation = initializeFlutterwavePayment($userData, $orderData);

if ($paymentInitiation['success']) {
    // Redirect user to payment page
    header('Location: ' . $paymentInitiation['payment_link']);
    exit;
} else {
    // Handle error
    echo 'Payment initialization failed: ' . $paymentInitiation['message'];
}
*/
?>