-- Users Table (Customers)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    city VARCHAR(100),
    area VARCHAR(100),
    street_name VARCHAR(255),
    house_no VARCHAR(50),
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Electricians Table
CREATE TABLE electricians (
    electrician_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    city ENUM('Dhaka') NOT NULL,  -- Fixed city, only Dhaka allowed
    area ENUM('Badda', 'Uttara', 'Mirpur', 'Airport Area', 'Gulshan') NOT NULL,  -- Fixed areas
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Fixed Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name ENUM('TV Expert', 'Refrigerator Expert', 'AC Expert', 'Daily Necessary Expert') NOT NULL,  -- Only these categories are allowed
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Electrician-Categories Association Table
CREATE TABLE electrician_categories (
    electrician_id INT,
    category_id INT,
    PRIMARY KEY (electrician_id, category_id),
    FOREIGN KEY (electrician_id) REFERENCES electricians(electrician_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Appointments Table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    electrician_id INT,
    appointment_date DATETIME,
    status ENUM('pending', 'confirmed', 'completed', 'canceled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (electrician_id) REFERENCES electricians(electrician_id) ON DELETE CASCADE
);

-- Sample Insert Data for Categories (Fixed Categories)
INSERT INTO categories (category_name, description) 
VALUES 
('TV Expert', 'Electricians specializing in TV installation and repair'),
('Refrigerator Expert', 'Electricians specializing in refrigerator repair and installation'),
('AC Expert', 'Electricians specializing in air conditioning installation and repair'),
('Daily Necessary Expert', 'Electricians specializing in daily household electrical needs');

CREATE TABLE electrician_availability (
    electrician_id INT PRIMARY KEY,
    is_available BOOLEAN DEFAULT TRUE,  -- TRUE for available, FALSE for not available
    FOREIGN KEY (electrician_id) REFERENCES electricians(electrician_id) ON DELETE CASCADE
);
