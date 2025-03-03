# 💰 Money Manager Backend (Laravel 10)

This is the **backend** of the **Money Manager** application, built with **Laravel 10**. It provides a **REST API** for user authentication, transactions, groups, and categories.

## 🚀 Features
- **User Authentication** (Login, Register, JWT)
- **Group-based Transactions** (Manage Income & Expenses)
- **Category Management** (Customizable by Groups)
- **File Upload** (Proof of Transactions)
- **Pagination & Filtering**
- **Secure API with JWT Tokens**
- **Nginx & Laravel Configuration for Production**

## 🏗️ Tech Stack
- **Laravel 10** (PHP Framework)
- **MySQL** (Database)
- **Sanctum / JWT** (Authentication)
- **Eloquent ORM** (Database Management)
- **API Resource Controllers** (REST API)
- **Storage & File Upload Handling**

---

## 📦 Installation
### 1️⃣ Clone the Repository
```sh
git clone https://github.com/yourusername/money-manager-backend.git
cd money-manager-backend
```

### 2️⃣ Install Dependencies
```sh
composer install
```

### 3️⃣ Create `.env` File
Create a `.env` file in the project root and set up your database credentials:
```sh
cp .env.example .env
```

Update the database credentials in the `.env` file:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=money_manager
DB_USERNAME=root
DB_PASSWORD=password
```

### 4️⃣ Generate Application Key
```sh
php artisan key:generate
```

### 5️⃣ Migrate Database
```sh
php artisan migrate --seed
```

### 6️⃣ Start the Laravel Development Server
```sh
php artisan serve
```
The API will be available at `http://localhost:8000/`.

## 🔧 API Endpoints
| Method | Endpoint                    | Description                  |
|--------|-----------------------------|------------------------------|
| POST   | /api/register               | Register a new user          |
| POST   | /api/login                  | Login and get JWT token      |
| GET    | /api/user                   | Get logged-in user data      |
| GET    | /api/groups                 | Get user groups              |
| POST   | /api/groups                 | Create a new group           |
| GET    | /api/categories             | Get transaction categories   |
| POST   | /api/transactions           | Add a new transaction        |
| PUT    | /api/transactions/{id}      | Update a transaction         |
| DELETE | /api/transactions/{id}      | Delete a transaction         |

Use `Authorization: Bearer {token}` for authenticated requests.

## 🛠 Deployment (Nginx + Laravel)
### 1️⃣ Set Permissions
```sh
sudo chown -R www-data:www-data /var/www/money-manager-backend/storage /var/www/money-manager-backend/bootstrap/cache
```

### 2️⃣ Configure Nginx for Laravel API
Edit the Nginx config file (`/etc/nginx/sites-available/money-manager`):
```nginx
server {
    listen 80;
    server_name your-backend-url;

    root /var/www/money-manager-backend/public;
    index index.php index.html index.htm;
    
    location / {
        try_files $uri /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 3️⃣ Restart Nginx & PHP
```sh
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

## 🔧 Environment Variables
|    Variable   |        Description       |
|---------------|--------------------------|
|   `APP_URL`   | Backend API URL          | 
|`DB_CONNECTION`| Database connection type |
|  `JWT_SECRET` | JWT Token Secret         |

## 🤝 Contributing
1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit changes (`git commit -m "Add new feature"`).
4. Push the branch (`git push origin feature-branch`).
5. Open a Pull Request.

## 📝 License
This project is licensed under the MIT License.