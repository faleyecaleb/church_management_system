# Hostinger Shared Hosting Deployment Guide

## Issue: exec() Function Disabled

Hostinger shared hosting disables the `exec()` function for security reasons, which causes issues with Laravel's storage symlink creation and some artisan commands.

## Solutions Implemented

### 1. AppServiceProvider.php
- Added error handling for symlink creation
- Added detection for shared hosting environments
- Falls back gracefully when exec() is not available

### 2. AttendanceSettingsController.php
- Added fallback for config cache clearing
- Uses Artisan::call() instead of exec() when possible
- Manual cache file deletion as last resort

## Manual Steps Required on Hostinger

### 1. Create Storage Symlink Manually
Since automatic symlink creation fails, you need to create it manually:

**Option A: Through cPanel File Manager**
1. Go to cPanel → File Manager
2. Navigate to your domain's public_html folder
3. Create a new folder called `storage`
4. This folder should point to `storage/app/public` in your Laravel installation

**Option B: Contact Hostinger Support**
Ask them to create a symlink from `public_html/storage` to `storage/app/public`

**Option C: Use Alternative File Structure**
Instead of symlinks, you can:
1. Copy files from `storage/app/public` to `public/storage` manually
2. Modify your file upload logic to save directly to `public/storage`

### 2. Environment Configuration
Make sure your `.env` file has:
```
APP_ENV=production
APP_DEBUG=false
FILESYSTEM_DISK=public
```

### 3. File Permissions
Ensure these directories have write permissions (755 or 775):
- `storage/`
- `storage/app/`
- `storage/app/public/`
- `storage/logs/`
- `storage/framework/`
- `storage/framework/cache/`
- `storage/framework/sessions/`
- `storage/framework/views/`
- `bootstrap/cache/`

### 4. Database Setup
1. Create a MySQL database in cPanel
2. Update your `.env` file with database credentials:
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
After uploading files and setting up the database:
```bash
php artisan migrate --force
```

## Testing the Fix
1. Upload the modified files to your Hostinger hosting
2. Run `composer install --no-dev --optimize-autoloader`
3. The application should now start without the exec() error
4. Manually create the storage symlink as described above

## Alternative: Use Local Storage
If symlinks continue to cause issues, you can modify the filesystem configuration to use local storage instead of public storage for certain files.