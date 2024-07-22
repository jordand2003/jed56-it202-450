CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    product_id VARCHAR(255),
    product_name VARCHAR(255),
    current_price DECIMAL(10, 2),
    original_price DECIMAL(10, 2),
    discount_percentage DECIMAL(5, 2),
    data_source VARCHAR(50)
);