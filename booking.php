<?php
include 'config.php';

// Check if hotel_id is provided
$hotel_id = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;

// Handle form submission
$booking_success = false;
$booking_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hotel_id = intval($_POST['hotel_id']);
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $user_email = mysqli_real_escape_string($conn, $_POST['user_email']);
    $user_phone = mysqli_real_escape_string($conn, $_POST['user_phone']);
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = intval($_POST['guests']);
    $special_requests = mysqli_real_escape_string($conn, $_POST['special_requests']);
    
    // Get hotel price
    $hotel_query = "SELECT price_per_night FROM hotels WHERE id = $hotel_id";
    $hotel_result = mysqli_query($conn, $hotel_query);
    $hotel = mysqli_fetch_assoc($hotel_result);
    
    // Calculate total price
    $date1 = new DateTime($check_in);
    $date2 = new DateTime($check_out);
    $nights = $date1->diff($date2)->days;
    $total_price = $hotel['price_per_night'] * $nights;
    
    // Insert booking
    $query = "INSERT INTO bookings (hotel_id, user_name, user_email, user_phone, check_in, check_out, total_price, status) 
              VALUES ($hotel_id, '$user_name', '$user_email', '$user_phone', '$check_in', '$check_out', $total_price, 'confirmed')";
    
    if (mysqli_query($conn, $query)) {
        $booking_id = mysqli_insert_id($conn);
        $booking_success = true;
        
        // Store booking in session for confirmation
        $_SESSION['last_booking'] = [
            'id' => $booking_id,
            'user_name' => $user_name,
            'hotel_id' => $hotel_id
        ];
    } else {
        $booking_error = "Error: " . mysqli_error($conn);
    }
}

// Get hotel details if hotel_id is provided
$hotel = null;
if ($hotel_id > 0) {
    $query = "SELECT * FROM hotels WHERE id = $hotel_id";
    $result = mysqli_query($conn, $query);
    $hotel = mysqli_fetch_assoc($result);
}

// Get user bookings if viewing bookings
$user_bookings = [];
if (isset($_GET['view']) && $_GET['view'] == 'my_bookings') {
    $query = "SELECT b.*, h.name as hotel_name, h.location, h.image_url 
              FROM bookings b 
              JOIN hotels h ON b.hotel_id = h.id 
              ORDER BY b.booking_date DESC 
              LIMIT 10";
    $result = mysqli_query($conn, $query);
    $user_bookings = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking - HotelBooking.com</title>
    <style>
        /* Reuse styles from previous pages with additions */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            margin: 0;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        header {
            background: linear-gradient(135deg, #003580 0%, #0071c2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }
        
        .logo span {
            color: #feba02;
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 25px;
        }
        
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #feba02;
        }
        
        .page-header {
            background: #0071c2;
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
        }
        
        .page-header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        /* Booking Section */
        .booking-section {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
            margin-bottom: 60px;
        }
        
        @media (max-width: 992px) {
            .booking-section {
                grid-template-columns: 1fr;
            }
        }
        
        /* Booking Form */
        .booking-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .booking-form h2 {
            color: #003580;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #003580;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: #0071c2;
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .submit-btn {
            background: linear-gradient(to right, #feba02, #ff9800);
            color: white;
            border: none;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
            width: 100%;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(254, 186, 2, 0.4);
        }
        
        /* Booking Summary */
        .booking-summary {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
        }
        
        .booking-summary h2 {
            color: #003580;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .hotel-info-summary {
            margin-bottom: 25px;
        }
        
        .hotel-name-summary {
            font-size: 22px;
            font-weight: 700;
            color: #003580;
            margin-bottom: 10px;
        }
        
        .hotel-location-summary {
            color: #666;
            margin-bottom: 15px;
        }
        
        .dates-summary, .price-summary {
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .summary-item-label {
            color: #666;
        }
        
        .summary-item-value {
            font-weight: 600;
        }
        
        .total-price {
            display: flex;
            justify-content: space-between;
            font-size: 24px;
            font-weight: 700;
            color: #feba02;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
            margin-top: 20px;
        }
        
        /* Confirmation Message */
        .confirmation {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 40px;
        }
        
        .confirmation h2 {
            color: #2ecc71;
            margin-bottom: 20px;
        }
        
        .confirmation p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #666;
        }
        
        .confirmation-details {
            background: #f9f9f9;
            padding: 25px;
            border-radius: 8px;
            text-align: left;
            margin-bottom: 30px;
        }
        
        .confirmation-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s;
        }
        
        .btn-primary {
            background: #0071c2;
            color: white;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* My Bookings */
        .bookings-list {
            margin-bottom: 60px;
        }
        
        .booking-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 200px 1fr;
        }
        
        @media (max-width: 768px) {
            .booking-card {
                grid-template-columns: 1fr;
            }
        }
        
        .booking-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .booking-details {
            padding: 25px;
        }
        
        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .booking-hotel-name {
            font-size: 22px;
            font-weight: 700;
            color: #003580;
        }
        
        .booking-status {
            background: #2ecc71;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .booking-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item label {
            display: block;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-item .value {
            font-weight: 600;
            color: #333;
        }
        
        .booking-price {
            font-size: 24px;
            font-weight: 700;
            color: #feba02;
            text-align: right;
        }
        
        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .no-bookings h3 {
            color: #003580;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .no-bookings p {
            color: #666;
            margin-bottom: 25px;
        }
        
        footer {
            background: #003580;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 20px;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            nav ul li {
                margin: 5px 10px;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container header-content">
            <a href="index.php" class="logo">Hotel<span>Booking</span>.com</a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="hotels.php">Hotels</a></li>
                    <li><a href="booking.php?view=my_bookings">My Bookings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <?php if (isset($_GET['view']) && $_GET['view'] == 'my_bookings'): ?>
                <h1>My Bookings</h1>
                <p>View and manage your hotel reservations</p>
            <?php elseif ($booking_success): ?>
                <h1>Booking Confirmed!</h1>
                <p>Your reservation has been successfully completed</p>
            <?php else: ?>
                <h1>Complete Your Booking</h1>
                <p>Fill in your details to confirm your reservation</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <?php if ($booking_success): ?>
            <!-- Confirmation Message -->
            <div class="confirmation">
                <h2><i class="fas fa-check-circle"></i> Booking Confirmed!</h2>
                <p>Thank you for your booking. Your reservation has been confirmed and a confirmation email has been sent to your email address.</p>
                
                <div class="confirmation-details">
                    <?php
                    $booking_query = "SELECT b.*, h.name as hotel_name, h.location 
                                     FROM bookings b 
                                     JOIN hotels h ON b.hotel_id = h.id 
                                     WHERE b.id = {$_SESSION['last_booking']['id']}";
                    $booking_result = mysqli_query($conn, $booking_query);
                    $booking_details = mysqli_fetch_assoc($booking_result);
                    ?>
                    <h3>Booking Details</h3>
                    <p><strong>Booking ID:</strong> #<?php echo str_pad($booking_details['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    <p><strong>Hotel:</strong> <?php echo htmlspecialchars($booking_details['hotel_name']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($booking_details['location']); ?></p>
                    <p><strong>Check-in:</strong> <?php echo date('F d, Y', strtotime($booking_details['check_in'])); ?></p>
                    <p><strong>Check-out:</strong> <?php echo date('F d, Y', strtotime($booking_details['check_out'])); ?></p>
                    <p><strong>Total Price:</strong> $<?php echo number_format($booking_details['total_price'], 2); ?></p>
                </div>
                
                <div class="confirmation-buttons">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                    <a href="booking.php?view=my_bookings" class="btn btn-secondary">
                        <i class="fas fa-list"></i> View All Bookings
                    </a>
                </div>
            </div>
            
        <?php elseif (isset($_GET['view']) && $_GET['view'] == 'my_bookings'): ?>
            <!-- My Bookings Section -->
            <div class="bookings-list">
                <h2 style="color: #003580; margin-bottom: 30px;">Recent Bookings</h2>
                
                <?php if (count($user_bookings) > 0): ?>
                    <?php foreach ($user_bookings as $booking): ?>
                        <div class="booking-card">
                            <img src="<?php echo $booking['image_url']; ?>" alt="<?php echo htmlspecialchars($booking['hotel_name']); ?>" class="booking-image">
                            <div class="booking-details">
                                <div class="booking-header">
                                    <h3 class="booking-hotel-name"><?php echo htmlspecialchars($booking['hotel_name']); ?></h3>
                                    <span class="booking-status"><?php echo ucfirst($booking['status']); ?></span>
                                </div>
                                
                                <div class="booking-info">
                                    <div class="info-item">
                                        <label>Booking ID</label>
                                        <div class="value">#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Location</label>
                                        <div class="value"><?php echo htmlspecialchars($booking['location']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Check-in</label>
                                        <div class="value"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Check-out</label>
                                        <div class="value"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Guest Name</label>
                                        <div class="value"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                    </div>
                                    <div class="info-item">
                                        <label>Booking Date</label>
                                        <div class="value"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></div>
                                    </div>
                                </div>
                                
                                <div class="booking-price">$<?php echo number_format($booking['total_price'], 2); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-bookings">
                        <h3>No bookings found</h3>
                        <p>You haven't made any bookings yet. Start by searching for hotels and making a reservation.</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search Hotels
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
        <?php elseif ($hotel): ?>
            <!-- Booking Form -->
            <?php
            // Calculate nights and total price
            $date1 = new DateTime($check_in);
            $date2 = new DateTime($check_out);
            $nights = $date1->diff($date2)->days;
            $total_price = $hotel['price_per_night'] * $nights;
            ?>
            
            <?php if ($booking_error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $booking_error; ?>
                </div>
            <?php endif; ?>
            
            <div class="booking-section">
                <div class="booking-form">
                    <h2><i class="fas fa-user-circle"></i> Guest Information</h2>
                    <form method="POST" action="booking.php">
                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>">
                        <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                        <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                        <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_name">Full Name *</label>
                                <input type="text" id="user_name" name="user_name" required placeholder="Enter your full name">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="user_email">Email Address *</label>
                                <input type="email" id="user_email" name="user_email" required placeholder="Enter your email">
                            </div>
                            <div class="form-group">
                                <label for="user_phone">Phone Number *</label>
                                <input type="tel" id="user_phone" name="user_phone" required placeholder="Enter your phone number">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="special_requests">Special Requests (Optional)</label>
                            <textarea id="special_requests" name="special_requests" rows="4" placeholder="Any special requests or requirements..."></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-lock"></i> Confirm Booking
                        </button>
                    </form>
                </div>
                
                <div class="booking-summary">
                    <h2><i class="fas fa-receipt"></i> Booking Summary</h2>
                    
                    <div class="hotel-info-summary">
                        <h3 class="hotel-name-summary"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <div class="hotel-location-summary">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                        </div>
                        <div class="hotel-rating-summary">
                            <span style="background: #003580; color: white; padding: 3px 10px; border-radius: 20px; font-weight: 600;">
                                <i class="fas fa-star"></i> <?php echo $hotel['rating']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="dates-summary">
                        <h3 style="color: #003580; margin-bottom: 15px; font-size: 18px;">Stay Details</h3>
                        <div class="summary-item">
                            <span class="summary-item-label">Check-in</span>
                            <span class="summary-item-value"><?php echo date('F d, Y', strtotime($check_in)); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-item-label">Check-out</span>
                            <span class="summary-item-value"><?php echo date('F d, Y', strtotime($check_out)); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-item-label">Nights</span>
                            <span class="summary-item-value"><?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-item-label">Guests</span>
                            <span class="summary-item-value"><?php echo $guests; ?> guest<?php echo $guests != 1 ? 's' : ''; ?></span>
                        </div>
                    </div>
                    
                    <div class="price-summary">
                        <h3 style="color: #003580; margin-bottom: 15px; font-size: 18px;">Price Details</h3>
                        <div class="summary-item">
                            <span class="summary-item-label">Price per night</span>
                            <span class="summary-item-value">$<?php echo number_format($hotel['price_per_night'], 2); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-item-label"><?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?></span>
                            <span class="summary-item-value">$<?php echo number_format($total_price, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="total-price">
                        <span>Total</span>
                        <span>$<?php echo number_format($total_price, 2); ?></span>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- No hotel selected -->
            <div class="no-bookings" style="margin-bottom: 60px;">
                <h3>No Hotel Selected</h3>
                <p>Please select a hotel from the search results to start the booking process.</p>
                <div class="confirmation-buttons">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search Hotels
                    </a>
                    <a href="hotels.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> Browse Hotels
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>HotelBooking.com</h3>
                    <p>Find the best hotels at the best prices. Book with confidence with our secure booking system.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="hotels.php">Hotels</a></li>
                        <li><a href="booking.php?view=my_bookings">My Bookings</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-phone"></i> +1 (800) 123-4567</li>
                        <li><i class="fas fa-envelope"></i> support@hotelbooking.com</li>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Travel Street, Suite 100</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> HotelBooking.com. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Form validation
        const bookingForm = document.querySelector('form');
        if (bookingForm) {
            bookingForm.addEventListener('submit', function(e) {
                const email = document.getElementById('user_email');
                const phone = document.getElementById('user_phone');
                
                // Simple email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email.value)) {
                    alert('Please enter a valid email address');
                    e.preventDefault();
                    return false;
                }
                
                // Simple phone validation
                const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                if (!phoneRegex.test(phone.value.replace(/[^0-9+]/g, ''))) {
                    alert('Please enter a valid phone number');
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        }
        
        // Set minimum dates
        const today = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
