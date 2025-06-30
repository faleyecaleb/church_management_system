@echo off
echo Running Database Migrations - Church Management System
echo ========================================================

echo.
echo Step 1: Checking current migration status...
C:\xampp\php\php.exe artisan migrate:status

echo.
echo Step 2: Publishing Spatie Permission migrations...
C:\xampp\php\php.exe artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force

echo.
echo Step 3: Running all pending migrations...
C:\xampp\php\php.exe artisan migrate --force

echo.
echo Step 4: Checking final migration status...
C:\xampp\php\php.exe artisan migrate:status

echo.
echo Step 5: Clearing caches...
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear

echo.
echo Migrations completed!
echo.
echo If the order_of_services table still doesn't exist, check:
echo 1. Database connection in .env file
echo 2. Database permissions
echo 3. Run this script again
echo.
pause