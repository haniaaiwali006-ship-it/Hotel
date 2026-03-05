<?php
include 'config.php';

// Get search parameters
$location = isset($_GET['location']) ? $_GET['location'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET['guests']) ? $_GET['guests'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? $_Get['price_max'] : '';
$rating = isset($_GET['rating']) ? $_GET['rating'] : '';
$hotel_type = isset($_GET['type']) ? $_GET['type'] : '';

// Build query
$query = "SELECT * FROM hotels WHERE 1=1";
$params = [];

if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($price_min)) {
    $query .= " AND price_per_night >= ?";
    $params[] = $price_min;
}

if (!empty($price_max)) {
    $query .= " AND price_per_night <= ?";
    $params[] = $price_max;
}

if (!empty($rating)) {
    $query .= " AND rating >= ?";
    $params[] = $rating;
}

if (!empty($hotel_type)) {
    $query .= " AND type = ?";
    $params[] = $hotel_type;
}

// Apply sorting
switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY price_per_night ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY price_per_night DESC";
        break;
    case 'rating':
        $query .= " ORDER BY rating DESC";
        break;
    default:
        $query .= " ORDER BY id DESC";
        break;
}

// Prepare and execute query
$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$hotels = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get unique hotel types for filter
$type_query = "SELECT DISTINCT type FROM hotels WHERE type IS NOT NULL AND type != ''";
$type_result = mysqli_query($conn, $type_query);
$hotel_types = mysqli_fetch_all($type_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotels - HotelBooking.com</title>
    <style>
        /* Reuse styles from index.php with additions */
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
        
        /* Page Header */
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
        
        .search-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-info p {
            margin: 0;
            font-size: 16px;
        }
        
        /* Filters and Sorting */
        .filters-section {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 992px) {
            .filters-section {
                grid-template-columns: 1fr;
            }
        }
        
        .filters {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: fit-content;
        }
        
        .filters h3 {
            color: #003580;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .filter-group {
            margin-bottom: 25px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #003580;
        }
        
        .filter-group input, .filter-group select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .price-range {
            display: flex;
            gap: 10px;
        }
        
        .price-range input {
            flex: 1;
        }
        
        .apply-filters {
            background: #0071c2;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .apply-filters:hover {
            background: #005a9e;
        }
        
        .sorting {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .results-count {
            font-size: 18px;
            font-weight: 600;
            color: #003580;
        }
        
        .sort-options select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            background: white;
        }
        
        /* Hotels Grid */
        .hotels-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }
        
        .hotel-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            display: grid;
            grid-template-columns: 300px 1fr;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        @media (max-width: 768px) {
            .hotel-item {
                grid-template-columns: 1fr;
            }
        }
        
        .hotel-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .hotel-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .hotel-details {
            padding: 25px;
            display: flex;
            flex-direction: column;
        }
        
        .hotel-header {
            margin-bottom: 15px;
        }
        
        .hotel-name {
            font-size: 24px;
            font-weight: 700;
            color: #003580;
            margin-bottom: 8px;
        }
        
        .hotel-location {
            color: #666;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .hotel-rating {
            display: inline-flex;
            align-items: center;
            background: #003580;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .hotel-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .hotel-amenities {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .amenity {
            background: #f0f7ff;
            color: #0071c2;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .hotel-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .hotel-price {
            font-size: 28px;
            font-weight: 700;
            color: #feba02;
        }
        
        .hotel-price span {
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        
        .book-btn {
            background: linear-gradient(to right, #feba02, #ff9800);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .book-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(254, 186, 2, 0.4);
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .no-results h3 {
            color: #003580;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .no-results p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .back-btn {
            background: #0071c2;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        
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
            
            .hotel-footer {
                flex-direction: column;
                align-items: flex-start;
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <h1>Find Your Perfect Hotel</h1>
            <p>Browse our selection of hotels and find the one that suits your needs</p>
        </div>
    </div>

    <div class="container">
        <!-- Search Info -->
        <div class="search-info">
            <p>
                <strong>Search Results:</strong> 
                <?php echo count($hotels); ?> hotel<?php echo count($hotels) != 1 ? 's' : ''; ?> found
                <?php if (!empty($location)): ?>
                    in <strong><?php echo htmlspecialchars($location); ?></strong>
                <?php endif; ?>
            </p>
            <div>
                <a href="index.php" class="back-btn" style="background: #0071c2; color: white; padding: 8px 20px; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-search"></i> New Search
                </a>
            </div>
        </div>

        <div class="filters-section">
            <!-- Filters -->
            <div class="filters">
                <h3><i class="fas fa-filter"></i> Filter Results</h3>
                <form method="GET" action="hotels.php">
                    <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                    <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                    <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                    <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                    
                    <div class="filter-group">
                        <label for="price_min">Price Range ($)</label>
                        <div class="price-range">
                            <input type="number" id="price_min" name="price_min" placeholder="Min" value="<?php echo $price_min; ?>">
                            <input type="number" id="price_max" name="price_max" placeholder="Max" value="<?php echo $price_max; ?>">
                        </div>
                    </div>
                    
                    <div class="filter-group">
                        <label for="rating">Minimum Rating</label>
                        <select id="rating" name="rating">
                            <option value="">Any Rating</option>
                            <option value="1" <?php echo $rating == '1' ? 'selected' : ''; ?>>1+ Star</option>
                            <option value="2" <?php echo $rating == '2' ? 'selected' : ''; ?>>2+ Stars</option>
                            <option value="3" <?php echo $rating == '3' ? 'selected' : ''; ?>>3+ Stars</option>
                            <option value="4" <?php echo $rating == '4' ? 'selected' : ''; ?>>4+ Stars</option>
                            <option value="4.5" <?php echo $rating == '4.5' ? 'selected' : ''; ?>>4.5+ Stars</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="type">Hotel Type</label>
                        <select id="type" name="type">
                            <option value="">All Types</option>
                            <?php foreach ($hotel_types as $type): ?>
                                <option value="<?php echo $type['type']; ?>" <?php echo $hotel_type == $type['type'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['type']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="apply-filters">
                        <i class="fas fa-check"></i> Apply Filters
                    </button>
                </form>
            </div>

            <!-- Hotels List -->
            <div class="hotels-content">
                <!-- Sorting -->
                <div class="sorting">
                    <div class="results-count">
                        <?php echo count($hotels); ?> hotel<?php echo count($hotels) != 1 ? 's' : ''; ?> found
                    </div>
                    <div class="sort-options">
                        <form method="GET" action="hotels.php" style="display: inline;">
                            <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                            <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
                            <input type="hidden" name="check_out" value="<?php echo $check_out; ?>">
                            <input type="hidden" name="guests" value="<?php echo $guests; ?>">
                            <input type="hidden" name="price_min" value="<?php echo $price_min; ?>">
                            <input type="hidden" name="price_max" value="<?php echo $price_max; ?>">
                            <input type="hidden" name="rating" value="<?php echo $rating; ?>">
                            <input type="hidden" name="type" value="<?php echo $hotel_type; ?>">
                            <select name="sort" onchange="this.form.submit()">
                                <option value="default" <?php echo $sort == 'default' ? 'selected' : ''; ?>>Sort By</option>
                                <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="rating" <?php echo $sort == 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Hotels -->
                <?php if (count($hotels) > 0): ?>
                    <div class="hotels-list">
                        <?php foreach ($hotels as $hotel): 
                            // Calculate total price for stay
                            $date1 = new DateTime($check_in);
                            $date2 = new DateTime($check_out);
                            $nights = $date1->diff($date2)->days;
                            $total_price = $hotel['price_per_night'] * $nights;
                        ?>
                            <div class="hotel-item">
                                <img src="<?php echo $hotel['image_url']; ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" class="hotel-image">
                                <div class="hotel-details">
                                    <div class="hotel-header">
                                        <h3 class="hotel-name"><?php echo htmlspecialchars($hotel['name']); ?></h3>
                                        <div class="hotel-location">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                                        </div>
                                        <div class="hotel-rating">
                                            <i class="fas fa-star"></i> <?php echo $hotel['rating']; ?> / 5
                                        </div>
                                    </div>
                                    
                                    <p class="hotel-description"><?php echo htmlspecialchars($hotel['description']); ?></p>
                                    
                                    <?php if (!empty($hotel['amenities'])): 
                                        $amenities = explode(',', $hotel['amenities']);
                                    ?>
                                        <div class="hotel-amenities">
                                            <?php foreach ($amenities as $amenity): ?>
                                                <span class="amenity"><?php echo trim($amenity); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="hotel-footer">
                                        <div class="hotel-price-info">
                                            <div class="hotel-price">$<?php echo number_format($hotel['price_per_night'], 2); ?> <span>per night</span></div>
                                            <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                                <?php echo $nights; ?> night<?php echo $nights != 1 ? 's' : ''; ?>: <strong>$<?php echo number_format($total_price, 2); ?></strong>
                                            </div>
                                        </div>
                                        <a href="booking.php?hotel_id=<?php echo $hotel['id']; ?>&check_in=<?php echo $check_in; ?>&check_out=<?php echo $check_out; ?>&guests=<?php echo $guests; ?>" class="book-btn">
                                            <i class="fas fa-calendar-check"></i> Book Now
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <h3>No hotels found</h3>
                        <p>Try adjusting your search criteria or filters to find more options.</p>
                        <a href="index.php" class="back-btn">
                            <i class="fas fa-search"></i> New Search
                        </a>
                    </div>
                <?php endif; ?>
            </div>
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
        // Set minimum dates
        const today = new Date().toISOString().split('T')[0];
        
        // Update price range inputs
        const priceMin = document.getElementById('price_min');
        const priceMax = document.getElementById('price_max');
        
        if (priceMin) {
            priceMin.addEventListener('change', function() {
                if (priceMax.value && parseFloat(this.value) > parseFloat(priceMax.value)) {
                    priceMax.value = this.value;
                }
            });
        }
        
        if (priceMax) {
            priceMax.addEventListener('change', function() {
                if (priceMin.value && parseFloat(this.value) < parseFloat(priceMin.value)) {
                    priceMin.value = this.value;
                }
            });
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
