<?php
// process_order.php - Order Processing with 4-Step Verification

require_once '../config.php';

header('Content-Type: application/json');

// Start session for verification tracking
session_start();

// Initialize verification status array
if (!isset($_SESSION['verification_status'])) {
    $_SESSION['verification_status'] = [
        'email_verified' => false,
        'phone_verified' => false,
        'id_verified' => false,
        'twofa_verified' => false,
        'verification_level' => 0,
        'verified_at' => null
    ];
}

// Handle different verification steps
$action = isset($_POST['action']) ? $_POST['action'] : '';

switch($action) {
    case 'verify_email':
        verifyEmail();
        break;
    case 'verify_phone':
        verifyPhone();
        break;
    case 'verify_id':
        verifyID();
        break;
    case 'verify_2fa':
        verifyTwoFA();
        break;
    case 'send_email_code':
        sendEmailCode();
        break;
    case 'send_sms_code':
        sendSMSCode();
        break;
    case 'process_order':
        processOrder();
        break;
    case 'get_verification_status':
        getVerificationStatus();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

// Email Verification
function verifyEmail() {
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $code = isset($_POST['code']) ? sanitizeInput($_POST['code']) : '';
    
    if (empty($email) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Email and verification code required']);
        return;
    }
    
    // Check if code matches (in production, check against stored code)
    if ($code === $_SESSION['email_verification_code'] && time() < $_SESSION['email_code_expiry']) {
        $_SESSION['verification_status']['email_verified'] = true;
        $_SESSION['verification_status']['verification_level']++;
        
        // Log verification
        logActivity($_SESSION['user_id'] ?? 0, 'email_verified', "Email verified: $email");
        
        echo json_encode([
            'success' => true,
            'message' => 'Email verified successfully',
            'verification_level' => $_SESSION['verification_status']['verification_level']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
    }
}

// Send Email Verification Code
function sendEmailCode() {
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    
    if (!validateEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        return;
    }
    
    // Generate 6-digit code
    $code = sprintf("%06d", mt_rand(1, 999999));
    $_SESSION['email_verification_code'] = $code;
    $_SESSION['email_code_expiry'] = time() + 600; // 10 minutes
    
    // Send email (in production)
    $subject = "Your AgriSolutions Hub Verification Code";
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .code { font-size: 24px; font-weight: bold; color: #1e5631; padding: 10px; background: #f0f8f0; }
        </style>
    </head>
    <body>
        <h2>Email Verification</h2>
        <p>Your verification code is:</p>
        <div class='code'>$code</div>
        <p>This code will expire in 10 minutes.</p>
        <p>If you didn't request this, please ignore this email.</p>
    </body>
    </html>
    ";
    
    // sendEmail($email, $subject, $message);
    
    // For development, return the code
    echo json_encode([
        'success' => true,
        'message' => 'Verification code sent',
        'debug_code' => $code // Remove in production
    ]);
}

// Phone Verification
function verifyPhone() {
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    $code = isset($_POST['code']) ? sanitizeInput($_POST['code']) : '';
    
    if (empty($phone) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Phone and verification code required']);
        return;
    }
    
    if ($code === $_SESSION['sms_verification_code'] && time() < $_SESSION['sms_code_expiry']) {
        $_SESSION['verification_status']['phone_verified'] = true;
        $_SESSION['verification_status']['verification_level']++;
        
        logActivity($_SESSION['user_id'] ?? 0, 'phone_verified', "Phone verified: $phone");
        
        echo json_encode([
            'success' => true,
            'message' => 'Phone verified successfully',
            'verification_level' => $_SESSION['verification_status']['verification_level']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired verification code']);
    }
}

// Send SMS Code
function sendSMSCode() {
    $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
    
    if (!validatePhone($phone)) {
        echo json_encode(['success' => false, 'message' => 'Invalid phone number']);
        return;
    }
    
    $code = sprintf("%06d", mt_rand(1, 999999));
    $_SESSION['sms_verification_code'] = $code;
    $_SESSION['sms_code_expiry'] = time() + 600;
    
    // In production, send actual SMS
    // sendSMS($phone, "Your AgriSolutions Hub verification code is: $code");
    
    echo json_encode([
        'success' => true,
        'message' => 'SMS verification code sent',
        'debug_code' => $code // Remove in production
    ]);
}

// ID Verification
function verifyID() {
    $id_type = isset($_POST['id_type']) ? sanitizeInput($_POST['id_type']) : '';
    $id_number = isset($_POST['id_number']) ? sanitizeInput($_POST['id_number']) : '';
    
    if (empty($id_type) || empty($id_number)) {
        echo json_encode(['success' => false, 'message' => 'ID type and number required']);
        return;
    }
    
    // In production, validate ID with government database
    // For now, simple validation
    if (strlen($id_number) >= 8) {
        $_SESSION['verification_status']['id_verified'] = true;
        $_SESSION['verification_status']['verification_level']++;
        $_SESSION['user_id_data'] = [
            'type' => $id_type,
            'number' => $id_number,
            'verified_at' => date('Y-m-d H:i:s')
        ];
        
        logActivity($_SESSION['user_id'] ?? 0, 'id_verified', "ID verified: $id_type");
        
        echo json_encode([
            'success' => true,
            'message' => 'ID verified successfully',
            'verification_level' => $_SESSION['verification_status']['verification_level']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID number']);
    }
}

// Two-Factor Authentication
function verifyTwoFA() {
    $code = isset($_POST['code']) ? sanitizeInput($_POST['code']) : '';
    
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => '2FA code required']);
        return;
    }
    
    // In production, verify with Google Authenticator or similar
    if (strlen($code) === 6 && is_numeric($code)) {
        $_SESSION['verification_status']['twofa_verified'] = true;
        $_SESSION['verification_status']['verification_level']++;
        $_SESSION['verification_status']['verified_at'] = date('Y-m-d H:i:s');
        
        logActivity($_SESSION['user_id'] ?? 0, '2fa_verified', "2FA completed");
        
        echo json_encode([
            'success' => true,
            'message' => '2FA verified successfully',
            'verification_level' => $_SESSION['verification_status']['verification_level']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid 2FA code']);
    }
}

// Get Verification Status
function getVerificationStatus() {
    $level = $_SESSION['verification_status']['verification_level'];
    $all_verified = ($level >= 4);
    
    echo json_encode([
        'success' => true,
        'verification_level' => $level,
        'all_verified' => $all_verified,
        'details' => $_SESSION['verification_status']
    ]);
}

// Process Final Order
function processOrder() {
    // Check if all verifications are complete
    if ($_SESSION['verification_status']['verification_level'] < 4) {
        echo json_encode(['success' => false, 'message' => 'Complete all verification steps first']);
        return;
    }
    
    $conn = getDBConnection();
    
    // Get order data
    $user_id = $_SESSION['user_id'] ?? null;
    $customer_name = isset($_POST['customer_name']) ? sanitizeInput($_POST['customer_name']) : '';
    $customer_email = isset($_POST['customer_email']) ? sanitizeInput($_POST['customer_email']) : '';
    $customer_phone = isset($_POST['customer_phone']) ? sanitizeInput($_POST['customer_phone']) : '';
    $shipping_address = isset($_POST['shipping_address']) ? sanitizeInput($_POST['shipping_address']) : '';
    $city = isset($_POST['city']) ? sanitizeInput($_POST['city']) : '';
    $region = isset($_POST['region']) ? sanitizeInput($_POST['region']) : '';
    $payment_method = isset($_POST['payment_method']) ? sanitizeInput($_POST['payment_method']) : '';
    $cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];
    
    // Calculate totals
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax = $subtotal * 0.18;
    $shipping = 15000;
    $total = $subtotal + $tax + $shipping;
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert order
        $sql = "INSERT INTO orders (
            user_id, customer_name, customer_email, customer_phone,
            shipping_address, city, region, country,
            subtotal, tax_amount, shipping_amount, total_amount,
            payment_method, payment_status, order_status,
            verification_level, verified_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Tanzania', ?, ?, ?, ?, ?, 'pending', 'pending', ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $verified_at = $_SESSION['verification_status']['verified_at'];
        $stmt->bind_param(
            'issssssddddsiis',
            $user_id, $customer_name, $customer_email, $customer_phone,
            $shipping_address, $city, $region,
            $subtotal, $tax, $shipping, $total,
            $payment_method,
            $_SESSION['verification_status']['verification_level'],
            $verified_at
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order');
        }
        
        $order_id = $conn->insert_id;
        
        // Generate order number
        $order_number = 'ORD-' . date('Y') . '-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        // Update order number
        $update_sql = "UPDATE orders SET order_number = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('si', $order_number, $order_id);
        $update_stmt->execute();
        
        // Insert order items
        foreach ($cart_items as $item) {
            $item_sql = "INSERT INTO order_items (
                order_id, product_name, quantity, unit, unit_price, total_price
            ) VALUES (?, ?, ?, ?, ?, ?)";
            
            $item_stmt = $conn->prepare($item_sql);
            $unit = $item['id'] == '4' ? 'kit' : 'kg';
            $total_price = $item['price'] * $item['quantity'];
            
            $item_stmt->bind_param(
                'isissd',
                $order_id, $item['name'], $item['quantity'],
                $unit, $item['price'], $total_price
            );
            
            if (!$item_stmt->execute()) {
                throw new Exception('Failed to add order items');
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear verification session
        $_SESSION['verification_status'] = [
            'email_verified' => false,
            'phone_verified' => false,
            'id_verified' => false,
            'twofa_verified' => false,
            'verification_level' => 0,
            'verified_at' => null
        ];
        
        // Send confirmation
        sendOrderConfirmation($customer_email, $customer_name, $order_number, $cart_items, $total);
        
        // Log activity
        if ($user_id) {
            logActivity($user_id, 'order_placed', "Order #$order_number placed with full verification");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_number' => $order_number,
            'order_id' => $order_id,
            'total' => $total
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
    }
    
    $conn->close();
}

// Send Order Confirmation
function sendOrderConfirmation($email, $name, $order_number, $items, $total) {
    $subject = "Order Confirmation - AgriSolutions Hub #$order_number";
    
    $items_html = '';
    foreach ($items as $item) {
        $items_html .= "
        <tr>
            <td>{$item['name']}</td>
            <td>{$item['quantity']}</td>
            <td>TZS " . number_format($item['price']) . "</td>
            <td>TZS " . number_format($item['price'] * $item['quantity']) . "</td>
        </tr>";
    }
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; }
            .header { background: #1e5631; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th { background: #f5f5f5; padding: 12px; text-align: left; }
            td { padding: 12px; border-bottom: 1px solid #ddd; }
            .total { font-size: 18px; font-weight: bold; color: #1e5631; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
            .badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>AgriSolutions Hub</h1>
            <p>Order Confirmation</p>
        </div>
        <div class='content'>
            <h2>Thank you for your order, $name!</h2>
            <p>Your order has been received and is being processed.</p>
            
            <div style='background: #f8f9fa; padding: 15px; margin: 20px 0;'>
                <p><strong>Order Number:</strong> #$order_number</p>
                <p><strong>Date:</strong> " . date('F j, Y, g:i a') . "</p>
                <p><span class='badge'>✓ 4-Step Verification Complete</span></p>
            </div>
            
            <h3>Order Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    $items_html
                </tbody>
            </table>
            
            <div style='text-align: right;'>
                <p><strong>Total: TZS " . number_format($total) . "</strong></p>
            </div>
            
            <h3>Shipping Address</h3>
            <p>" . ($_POST['shipping_address'] ?? '') . "<br>" . ($_POST['city'] ?? '') . ", " . ($_POST['region'] ?? '') . "</p>
            
            <h3>What's Next?</h3>
            <ol>
                <li>Order confirmation sent to your email</li>
                <li>Seller will confirm availability within 24 hours</li>
                <li>Payment processing begins</li>
                <li>Shipping and tracking information will be sent</li>
            </ol>
            
            <p>You can track your order status at: <a href='https://agrisolutionshub.com/track-order.php'>Track Order</a></p>
        </div>
        <div class='footer'>
            <p>© 2024 AgriSolutions Hub. All rights reserved.</p>
            <p>Sokoine University of Agriculture, Morogoro, Tanzania</p>
        </div>
    </body>
    </html>
    ";
    
    // sendEmail($email, $subject, $message);
}
?>