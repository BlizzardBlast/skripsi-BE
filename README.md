
# Perquisites
- PHP 8.x
- Composer
- MySQL
# Deployment
**1. Clone the repository**
```
git clone https://github.com/BlizzardBlast/skripsi-FE
cd skripsi-FE
```

**2. Install dependencies**
```
composer install
```

**3.  Create a new .env file**
```
cp .env.example .env
```

**4. Generate application key**
```
php artisan key:generate
```

**5. Database migration**
- update the .env file with the database credentials
	```
	DB_DATABASE=your_database_name
	DB_USERNAME=your_username
	DB_PASSWORD=your_password
	```
- Run migration
	```
	php artisan migrate
	```

**6. Set application url**
- update the .env file with the application url
	```
	APP_URL=http://localhost
	```

**7. Serve**
```
php artisan serve
```
