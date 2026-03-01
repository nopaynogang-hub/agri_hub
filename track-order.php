<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - AgriSolutions Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Same navigation styles as other pages */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            display: flex;
            min-height: 100vh;
        }

        .side-nav {
            width: 250px;
            background: linear-gradient(180deg, #1e5631 0%, #2d773d 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .nav-header {
            padding: 25px 20px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .nav-header .logo {
            font-size: 2.5rem;
            color: #ffd700;
            margin-bottom: 10px;
        }

        .nav-header h1 {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .nav-header p {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .nav-menu {
            padding: 20px 0;
        }

        .nav-menu ul {
            list-style: none;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
            padding: 15px 25px;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .nav-menu a:hover, .nav-menu a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: #ffd700;
            color: #ffd700;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            min-height: 100vh;
        }

        .top-bar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .tracking-container {
            padding: 60px 40px;
            background: white;
            min-height: calc(100vh - 200px);
        }

        .tracking-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .tracking-header h1 {
            color: #1e5631;
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .tracking-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .tracking-form {
            max-width: 600px;
            margin: 0 auto 50px;
        }

        .tracking-form .form-group {
            display: flex;
            gap: 10px;
        }

        .tracking-form input {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .tracking-form input:focus {
            border-color: #1e5631;
            outline: none;
        }

        .tracking-form button {
            padding: 15px 30px;
            background: #1e5631;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .tracking-form button:hover {
            background: #2d773d;
        }

        .order-details {
            max-width: 800px;
            margin: 0 auto;
            display: none;
        }

        .order-details.visible {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        .order-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #ffd700;
        }

        .order-number {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1e5631;
        }

        .order-status {
            padding: 8px 16px;
            background: #ffd700;
            color: #1e5631;
            border-radius: 20px;
            font-weight: 600;
        }

        .tracking-timeline {
            margin: 40px 0;
            position: relative;
        }

        .tracking-timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            width: 2px;
            height: 100%;
            background: #ddd;
        }

        .timeline-item {
            position: relative;
            padding-left: 60px;
            margin-bottom: 30px;
        }

        .timeline-icon {
            position: absolute;
            left: 0;
            width: 40px;
            height: 40px;
            background: white;
            border: 2px solid #1e5631;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e5631;
            z-index: 2;
        }

        .timeline-item.completed .timeline-icon {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .timeline-content {
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .timeline-content h4 {
            color: #1e5631;
            margin-bottom: 5px;
        }

        .timeline-content p {
            color: #666;
            font-size: 0.9rem;
        }

        .verification-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: #e8f5e9;
            padding: 10px 20px;
            border-radius: 5px;
            color: #1e5631;
            margin: 10px 0;
        }

        .verification-badge i {
            color: #28a745;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <nav class="side-nav" id="sideNav">
        <div class="nav-header">
            <div class="logo">
                <i class="fas fa-seedling"></i>
            </div>
            <h1>AgriSolutions Hub</h1>
            <p>Global Agricultural Platform</p>
        </div>
        
        <div class="nav-menu">
            <ul>
                <li><a href="index.html"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="about.html"><i class="fas fa-globe"></i> Global Business</a></li>
                <li><a href="market.html"><i class="fas fa-chart-line"></i> Market</a></li>
                <li><a href="order.html"><i class="fas fa-shopping-cart"></i> Place Order</a></li>
                <li><a href="track-order.php" class="active"><i class="fas fa-truck"></i> Track Order</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <header class="top-bar">
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="search-bar">
                <input type="text" placeholder="Search...">
                <button><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <button class="btn-login">Login</button>
                <button class="btn-signup">Sign Up</button>
            </div>
        </header>

        <div class="tracking-container">
            <div class="tracking-header">
                <h1>Track Your Order</h1>
                <p>Enter your order number to track shipment status</p>
            </div>

            <div class="tracking-form">
                <div class="form-group">
                    <input type="text" id="orderNumber" placeholder="Enter order number (e.g., ORD-2024-00123)" value="ORD-2024-00123">
                    <button onclick="trackOrder()">Track Order</button>
                </div>
            </div>

            <div class="order-details visible" id="orderDetails">
                <div class="order-card">
                    <div class="order-header">
                        <span class="order-number">Order #ORD-2024-00123</span>
                        <span class="order-status">In Transit</span>
                    </div>

                    <div class="verification-badge">
                        <i class="fas fa-check-circle"></i>
                        <span>4-Step Verification Complete</span>
                    </div>

                    <div class="tracking-timeline">
                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Order Placed & Verified</h4>
                                <p>March 15, 2024 - 10:30 AM</p>
                                <p>Order received with full 4-step verification</p>
                            </div>
                        </div>

                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Payment Confirmed</h4>
                                <p>March 15, 2024 - 10:35 AM</p>
                                <p>Payment verified via M-Pesa</p>
                            </div>
                        </div>

                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Order Processed</h4>
                                <p>March 15, 2024 - 2:00 PM</p>
                                <p>Seller confirmed and processing order</p>
                            </div>
                        </div>

                        <div class="timeline-item completed">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>Shipped</h4>
                                <p>March 16, 2024 - 9:00 AM</p>
                                <p>Package handed to courier - Tracking: AWB-123456789</p>
                            </div>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>In Transit</h4>
                                <p>Current Location: Morogoro Sorting Center</p>
                                <p>Estimated delivery: March 18-20, 2024</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="order-card">
                    <h3 style="color: #1e5631; margin-bottom: 20px;">Order Summary</h3>
                    <table style="width: 100%;">
                        <tr>
                            <td>Premium Maize Seeds</td>
                            <td>10 kg</td>
                            <td>TZS 50,000</td>
                        </tr>
                        <tr>
                            <td>Organic Rice</td>
                            <td>20 kg</td>
                            <td>TZS 64,000</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                            <td><strong>TZS 149,520</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function trackOrder() {
            const orderNumber = document.getElementById('orderNumber').value;
            if (orderNumber) {
                document.getElementById('orderDetails').classList.add('visible');
                alert(`Tracking order: ${orderNumber}`);
            } else {
                alert('Please enter an order number');
            }
        }
    </script>
</body>
</html>