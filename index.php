<?php
include 'config.php';

// Set default values for search
$location = isset($_GET['location']) ? $_GET['location'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET['guests']) ? $_GET['guests'] : 1;

// Get featured hotels
$featured_query = "SELECT * FROM hotels ORDER BY rating DESC LIMIT 3";
$featured_result = mysqli_query($conn, $featured_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HotelBooking.com - Find Your Perfect Stay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header Styles */
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
        
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0, 53, 128, 0.8), rgba(0, 113, 194, 0.8)), url('https://images.unsplash.com/photo-1564501049418-3c27787d01e8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hero h1 {
            font-size: 48px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 20px;
            max-width: 700px;
            margin: 0 auto 40px;
        }
        
        /* Search Form */
        .search-form {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: -50px auto 50px;
            position: relative;
            z-index: 10;
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #003580;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: #0071c2;
            outline: none;
        }
        
        .search-btn {
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
        }
        
        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(254, 186, 2, 0.4);
        }
        
        /* Featured Hotels */
        .section-title {
            text-align: center;
            margin-bottom: 40px;
            color: #003580;
            font-size: 32px;
            font-weight: 700;
        }
        
        .hotels-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        
        .hotel-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .hotel-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .hotel-info {
            padding: 20px;
        }
        
        .hotel-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #003580;
        }
        
        .hotel-location {
            color: #666;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .hotel-location i {
            margin-right: 8px;
            color: #0071c2;
        }
        
        .hotel-rating {
            background: #003580;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .hotel-price {
            font-size: 24px;
            font-weight: 700;
            color: #feba02;
            margin-bottom: 15px;
        }
        
        .hotel-price span {
            font-size: 14px;
            color: #666;
            font-weight: normal;
        }
        
        .view-btn {
            display: inline-block;
            background: #0071c2;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.3s;
            width: 100%;
            text-align: center;
        }
        
        .view-btn:hover {
            background: #005a9e;
        }
        
        /* Footer */
        footer {
            background: #003580;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 40px;
        }
        
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 20px;
            color: #feba02;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section ul li a:hover {
            color: #feba02;
        }
        
        .copyright {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            nav ul {
                margin-top: 20px;
                justify-content: center;
            }
            
            nav ul li {
                margin: 0 10px;
            }
            
            .hero h1 {
                font-size: 36px;
            }
            
            .hero p {
                font-size: 18px;
            }
            
            .search-form {
                margin: -30px auto 30px;
                padding: 20px;
            }
            
            .form-group {
                min-width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .hero {
                padding: 50px 0;
            }
            
            .hero h1 {
                font-size: 28px;
            }
            
            .section-title {
                font-size: 24px;
            }
            
            .hotels-grid {
                grid-template-columns: 1fr;
            }
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
                    <li><a href="booking.php">My Bookings</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Find Your Perfect Stay</h1>
            <p>Discover amazing hotels at unbeatable prices. Book now and enjoy your vacation!</p>
        </div>
    </section>

    <!-- Search Form -->
    <div class="container">
        <form action="hotels.php" method="GET" class="search-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Destination</label>
                    <input type="text" id="location" name="location" placeholder="Where are you going?" value="<?php echo htmlspecialchars($location); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="check_in"><i class="fas fa-calendar-alt"></i> Check-in Date</label>
                    <input type="date" id="check_in" name="check_in" value="<?php echo $check_in; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="check_out"><i class="fas fa-calendar-alt"></i> Check-out Date</label>
                    <input type="date" id="check_out" name="check_out" value="<?php echo $check_out; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="guests"><i class="fas fa-user-friends"></i> Guests</label>
                    <select id="guests" name="guests">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $guests == $i ? 'selected' : ''; ?>><?php echo $i; ?> Guest<?php echo $i > 1 ? 's' : ''; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="search-btn">Search Hotels</button>
        </form>
    </div>

    <!-- Featured Hotels -->
    <div class="container">
        <h2 class="section-title">Featured Hotels</h2>
        <div class="hotels-grid">
            <?php while ($hotel = mysqli_fetch_assoc($featured_result)): ?>
                <div class="hotel-card">
                    <img src="<?php echo $hotel['image_url']; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-img">
                    <div class="hotel-info">
                        <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                        <div class="hotel-location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                        </div>
                        <div class="hotel-rating">
                            <i class="fas fa-star"></i> <?php echo $hotel['rating']; ?>
                        </div>
                        <div class="hotel-price">$<?php echo $hotel['price_per_night']; ?> <span>per night</span></div>
                        <a href="hotels.php?hotel_id=<?php echo $hotel['id']; ?>" class="view-btn">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
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
                        <li><a href="booking.php">My Bookings</a></li>
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
        // Set minimum dates for check-in and check-out
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('check_in').min = today;
        document.getElementById('check_out').min = today;
        
        // Update check-out min date when check-in changes
        document.getElementById('check_in').addEventListener('change', function() {
            document.getElementById('check_out').min = this.value;
            if (document.getElementById('check_out').value < this.value) {
                document.getElementById('check_out').value = this.value;
            }
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
