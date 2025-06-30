@echo off
echo Church Management System Setup
echo ================================

echo.
echo Step 1: Running initial migrations...
C:\xampp\php\php.exe artisan migrate --force

echo.
echo Step 2: Publishing Spatie Permission migrations...
C:\xampp\php\php.exe artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --force

echo.
echo Step 3: Running all migrations (including new ones)...
C:\xampp\php\php.exe artisan migrate --force

echo.
echo Step 4: Checking for Order of Service table...
C:\xampp\php\php.exe artisan migrate:status

echo.
echo Step 5: Creating admin user...
C:\xampp\php\php.exe create_admin_basic.php

echo.
echo Step 6: Clearing all caches...
C:\xampp\php\php.exe artisan view:clear
C:\xampp\php\php.exe artisan route:clear
C:\xampp\php\php.exe artisan config:clear
C:\xampp\php\php.exe artisan cache:clear

echo.
echo Setup completed!
echo You can now access your application and login with:
echo Email: admin@church.com
echo Password: admin123
echo.
echo IMPORTANT: If you still get table errors, run: run_migrations.bat
echo.
pause