# 📰 NewsHub – Core PHP News Aggregator

**NewsHub** is a lightweight **Core PHP application** that aggregates news from [NewsAPI.org](https://newsapi.org), stores them in **MySQL**, and displays them in a modern, responsive interface with **search** and **category filters**.

---

## ✨ Features
- 🌍 Fetches top headlines from **NewsAPI** via PHP cURL  
- 🗄 Stores articles in **MySQL** with sanitization & duplicate prevention  
- 🔍 Search news by keywords  
- 📂 Filter by categories (Business, Entertainment, Sports, Health, etc.)  
- 🎨 Responsive UI with **Bootstrap 5**  
- 🔐 Secure DB operations using **PDO prepared statements**

---

## 🛠️ Tech Stack
- **Core PHP (no framework)**
- **MySQL (PDO)**
- **Bootstrap 5**
- **cURL for API integration**

---

## 🚀 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/newshub.git
   cd newshub
   
2. **Import Database**
   Create a database in MySQL (example: newshub_db) and import the migration file:
   ```bash
   mysql -u root -p newshub_db < migrate.sql
   
3. **Configure Database & API Key**
   Update your config.php file with MySQL credentials and [NewsAPI.org](https://newsapi.org) API Key:
   ```bash
   <?php
      return [
          "db" => [
              "host" => "localhost",
              "name" => "newshub_db",
              "user" => "root",
              "pass" => "your_password"
          ],
          "newsapi_key" => "your_newsapi_key_here"
      ];
   
4. **Fetch News Articles**
   Run the following command to fetch latest news from NewsAPI and insert into DB:
   ```bash
   php fetch.php

5. **Run Project**
   Run ProjectPlace the project inside your web server root (e.g., /var/www/html/newshub/) and open in browser:
   http://localhost/newshub/index.php

