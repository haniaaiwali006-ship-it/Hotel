<?php
include 'config.php';

// Create tables if they don't exist
$sql_hotels = "CREATE TABLE IF NOT EXISTS hotels (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    rating DECIMAL(3,2) DEFAULT 0.00,
    image_url VARCHAR(500),
    amenities TEXT,
    type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

$sql_bookings = "CREATE TABLE IF NOT EXISTS bookings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT(11) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    user_email VARCHAR(255) NOT NULL,
    user_phone VARCHAR(20),
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'confirmed',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
)";

// Create reviews table
$sql_reviews = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT(11) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    rating INT(1) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
)";

// Execute queries
if (mysqli_query($conn, $sql_hotels)) {
    echo "Hotels table created successfully.<br>";
} else {
    echo "Error creating hotels table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_bookings)) {
    echo "Bookings table created successfully.<br>";
} else {
    echo "Error creating bookings table: " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_reviews)) {
    echo "Reviews table created successfully.<br>";
} else {
    echo "Error creating reviews table: " . mysqli_error($conn) . "<br>";
}

// Insert sample hotel data if table is empty
$check_hotels = "SELECT COUNT(*) as count FROM hotels";
$result = mysqli_query($conn, $check_hotels);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $sample_hotels = [
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Grand Plaza Hotel', 'New York', 'Luxury hotel in downtown Manhattan with stunning city views', 299.99, 4.5, 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=500', 'WiFi,Pool,Spa,Gym,Restaurant', 'Luxury')",
        
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Sunset Resort', 'Miami', 'Beachfront resort with private beach access and tropical gardens', 189.99, 4.2, 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?w-500', 'WiFi,Pool,Beach,Spa,Bar', 'Resort')",
        
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Metro Inn', 'Chicago', 'Affordable hotel near business district with modern amenities', 129.99, 3.8, 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?w=500', 'WiFi,Gym,Breakfast', 'Budget')",
        
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Alpine Lodge', 'Denver', 'Mountain view lodge perfect for ski enthusiasts and nature lovers', 159.99, 4.7, 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?w=500', 'WiFi,Fireplace,Ski,Restaurant', 'Lodge')",
        
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Royal Suites', 'Las Vegas', 'Premium suites with casino access and entertainment shows', 399.99, 4.9, 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?w=500', 'WiFi,Casino,Pool,Spa,Bar,Club', 'Luxury')",
        
        "INSERT INTO hotels (name, location, description, price_per_night, rating, image_url, amenities, type) 
        VALUES ('Harbor View Hotel', 'San Francisco', 'Historic hotel with bay views and Victorian architecture', 249.99, 4.3, 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?w=500', 'WiFi,Restaurant,Bar,Historic', 'Boutique')"
    ];
    
    foreach ($sample_hotels as $query) {
        mysqli_query($conn, $query);
    }
    echo "Sample hotel data inserted successfully.<br>";
}

// Insert sample reviews
$check_reviews = "SELECT COUNT(*) as count FROM reviews";
$result = mysqli_query($conn, $check_reviews);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    $sample_reviews = [
        "INSERT INTO reviews (hotel_id, user_name, rating, comment) VALUES (1, 'John Smith', 5, 'Excellent service and amazing views!')",
        "INSERT INTO reviews (hotel_id, user_name, rating, comment) VALUES (1, 'Maria Garcia', 4, 'Great location, room was clean and comfortable.')",
        "INSERT INTO reviews (hotel_id, user_name, rating, comment) VALUES (2, 'Robert Johnson', 5, 'Perfect beach vacation spot!')",
        "INSERT INTO reviews (hotel_id, user_name, rating, comment) VALUES (3, 'Sarah Williams', 4, 'Good value for money, convenient location.')",
        "INSERT INTO reviews (hotel_id, user_name, rating, comment) VALUES (4, 'Michael Brown', 5, 'Breathtaking mountain views, cozy rooms.')"
    ];
    
    foreach ($sample_reviews as $query) {
        mysqli_query($conn, $query);
    }
    echo "Sample review data inserted successfully.<br>";
}

echo "<a href='index.php'>Go to Homepage</a>";
mysqli_close($conn);
?>
