
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(512) NOT NULL,
    description TEXT NULL,
    url VARCHAR(512) NULL,
    image_url VARCHAR(1024) NULL,
    source VARCHAR(100) NULL,
    category VARCHAR(100) NULL,
    published_date DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
