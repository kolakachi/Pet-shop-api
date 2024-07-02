# Pet Shop API

## Requirements

* Access to the Command Line
* OpenSSL Installed
* PHP 8.3
* Write Permissions to storage

## Installation

### Step 1: Clone the repository
```
git clone https://github.com/kolakachi/Pet-shop-api.git
```
cd into project directory
```
cd project-name
```

### Step 2: Install Laravel dependencies
```
composer install
```

### Step 3: Set up your environment file
Copy the .env.example file to .env and set up your environment variables, including your database configurations
```
cp .env.example .env
```

Edit the .env file to match your database configuration:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

```

### Step 4: Generate keys for JWT
```
openssl genpkey -algorithm RSA -out storage/oauth-private.key -aes256
openssl rsa -pubout -in storage/oauth-private.key -out storage/oauth-public.key
```
Add your JWT configuration to the .env file:
```
JWT_PASSPHRASE=your_passphrase
```

### Step 5: Generate the application key
```
php artisan key:generate
```

### Step 6: Run the migrations and seed the database
```
php artisan migrate --seed
```

### Step 7: Create symbolic link to storage
```
php artisan storage:link
```

## Usage

### Running the Application
To start the Laravel development server, run:
```
php artisan serve
```

Visit http://127.0.0.1:8000 in your browser.

## API Documentation
The API documentation can be found at the following URL:

Visit http://127.0.0.1:8000/api/documentation

## Testing
To run tests, use:
```
php artisan test

```

## Additional Notes
* Make sure you have the appropriate PHP version installed.
* Ensure your database is set up and accessible by the Laravel application.
* If you encounter any issues, check the Laravel documentation or the lcobucci/jwt documentation for troubleshooting tips.
