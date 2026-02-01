-- Table for storing user holdings
CREATE TABLE user_holdings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    item_type ENUM('stock', 'forex', 'index'),
    item_symbol VARCHAR(20),
    quantity DECIMAL(10,2),
    purchase_price DECIMAL(10,2),
    FOREIGN KEY (user_email) REFERENCES registration(email)
);

-- Table for storing transaction history
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_email VARCHAR(255),
    transaction_type ENUM('buy', 'sell'),
    item_type ENUM('stock', 'forex', 'index'),
    item_symbol VARCHAR(20),
    quantity DECIMAL(10,2),
    price_per_unit DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (user_email) REFERENCES registration(email)
);

-- Table for payment details
CREATE TABLE payment_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    payment_method ENUM('credit_card', 'debit_card', 'bank_transfer'),
    card_number VARCHAR(255),
    expiry_date VARCHAR(7),
    cvv VARCHAR(255),
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
);

-- Table for bank details
CREATE TABLE bank_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    bank_name VARCHAR(255),
    account_number VARCHAR(255),
    ifsc_code VARCHAR(20),
    account_holder_name VARCHAR(255),
    status ENUM('pending', 'completed', 'failed'),
    FOREIGN KEY (transaction_id) REFERENCES transactions(id)
); 