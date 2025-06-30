# Troubleshooting Guide

## Table Error: order_of_services doesn't exist

If you're getting "Table 'church_management_system.order_of_services' doesn't exist", the migration hasn't been run.

### Quick Fix Steps:

1. **Run the fix script:**
   ```batch
   fix_order_table.bat
   ```

2. **Or manually run migrations:**
   ```batch
   C:\xampp\php\php.exe artisan migrate --force
   ```

3. **Check if table was created:**
   ```batch
   C:\xampp\php\php.exe check_tables.php
   ```

## Form Error: Undefined variable $orderOfService

If you're getting "Undefined variable $orderOfService" when trying to create a new order item:

### Quick Fix Steps:

1. **Run the form fix script:**
   ```batch
   fix_form_error.bat
   ```

2. **Or manually clear view cache:**
   ```batch
   C:\xampp\php\php.exe artisan view:clear
   ```

## Route Error: services.order-of-services.edit not defined

If you're getting "Route [services.order-of-services.edit] not defined":

### Quick Fix Steps:

1. **Run the shallow route fix:**
   ```batch
   fix_shallow_routes.bat
   ```

2. **Or manually clear caches:**
   ```batch
   C:\xampp\php\php.exe artisan route:clear
   C:\xampp\php\php.exe artisan view:clear
   ```

**Explanation:** The system uses shallow routes, so edit/delete routes are named differently.
See ROUTE_REFERENCE.md for complete route documentation.

## Route Error: Missing required parameter for services.order-of-services.index

If you're getting "Missing required parameter for [Route: services.order-of-services.index]" when editing:

### Quick Fix Steps:

1. **Run the edit route fix:**
   ```batch
   fix_edit_routes.bat
   ```

2. **Or manually clear caches:**
   ```batch
   C:\xampp\php\php.exe artisan route:clear
   C:\xampp\php\php.exe artisan view:clear
   ```

**Explanation:** The controller methods needed to be updated to work properly with shallow routes.

## View Error: prayer-requests.index not found

If you're getting "View [prayer-requests.index] not found":

### Quick Fix Steps:

1. **Run the prayer requests fix:**
   ```batch
   fix_prayer_requests.bat
   ```

2. **Or manually clear caches:**
   ```batch
   C:\xampp\php\php.exe artisan view:clear
   ```

**Explanation:** The prayer request views were missing and have now been created with modern UI.

## Database Error: Column 'is_public' not found in prayer_requests table

If you're getting "Column not found: 1054 Unknown column 'is_public'" when creating prayer requests:

### Quick Fix Steps:

1. **Run the database fix:**
   ```batch
   fix_prayer_database.bat
   ```

2. **Or run the comprehensive fix:**
   ```batch
   fix_all_prayer_issues.bat
   ```

3. **Or manually run migration:**
   ```batch
   C:\xampp\php\php.exe artisan migrate --force
   ```

**Explanation:** The database table needs additional columns that were added to the model.

## Database Error: Table 'prayers' doesn't exist

If you're getting "Table 'church_management_system.prayers' doesn't exist" when viewing prayer requests:

### Quick Fix Steps:

1. **Run the prayers table fix:**
   ```batch
   fix_prayers_table.bat
   ```

2. **Or run the comprehensive fix:**
   ```batch
   fix_all_prayer_issues.bat
   ```

3. **Or manually run migration:**
   ```batch
   C:\xampp\php\php.exe artisan migrate --force
   ```

**Explanation:** The prayers table is needed to track individual prayers for each prayer request.

## Route Error: prayer-requests.pray not defined

If you're getting "Route [prayer-requests.pray] not defined":

### Quick Fix Steps:

1. **Run the prayer routes fix:**
   ```batch
   fix_prayer_routes.bat
   ```

2. **Or run the comprehensive fix:**
   ```batch
   fix_all_prayer_issues.bat
   ```

3. **Or manually clear route cache:**
   ```batch
   C:\xampp\php\php.exe artisan route:clear
   C:\xampp\php\php.exe artisan route:cache
   ```

**Explanation:** Missing routes for prayer recording and status management functionality.

## Fatal Error: Cannot redeclare markAsCompleted()

If you're getting "Cannot redeclare App\Models\PrayerRequest::markAsCompleted()":

### Quick Fix Steps:

1. **Run the duplicate methods fix:**
   ```batch
   fix_prayer_duplicates.bat
   ```

2. **Or run the comprehensive fix:**
   ```batch
   fix_all_prayer_issues.bat
   ```

3. **Or manually clear cache:**
   ```batch
   C:\xampp\php\php.exe artisan cache:clear
   ```

**Explanation:** Duplicate method declarations were removed from the PrayerRequest model.

## Error: Call to a member function addEagerConstraints() on null

If you're getting "Call to a member function addEagerConstraints() on null":

### Quick Fix Steps:

1. **Run the relationship fix:**
   ```batch
   fix_prayer_relationships.bat
   ```

2. **Or run the comprehensive fix:**
   ```batch
   fix_all_prayer_issues.bat
   ```

3. **Or manually clear cache:**
   ```batch
   C:\xampp\php\php.exe artisan cache:clear
   ```

**Explanation:** Invalid relationship references were fixed in the Prayer Request system.

## Route Error: Missing required parameter for [Route: services.order-of-services.index]

If you're getting this error, it means there's a reference to the order of service route without the required service parameter.

### Quick Fix Steps:

1. **Clear all caches:**
   ```batch
   clear_cache.bat
   ```
   OR manually:
   ```batch
   C:\xampp\php\php.exe artisan view:clear
   C:\xampp\php\php.exe artisan route:clear
   C:\xampp\php\php.exe artisan config:clear
   C:\xampp\php\php.exe artisan cache:clear
   ```

2. **Restart your development server:**
   - Stop your current server (Ctrl+C)
   - Start it again: `C:\xampp\php\php.exe artisan serve`

3. **Check for any custom modifications:**
   - If you've made any custom changes to views or routes, make sure they use the correct route format:
   - ✅ Correct: `route('services.order-of-services.index', $service->id)`
   - ❌ Wrong: `route('services.order-of-services.index')`

### Admin User Creation Issues:

If you can't create an admin user:

1. **Try the batch file:**
   ```batch
   setup.bat
   ```

2. **Try the simple PHP script:**
   ```batch
   C:\xampp\php\php.exe create_admin_basic.php
   ```

3. **Try direct SQL:**
   - Open your database management tool (phpMyAdmin, etc.)
   - Run the SQL commands in `quick_admin_setup.sql`

4. **Manual database entry:**
   ```sql
   INSERT INTO users (name, email, email_verified_at, password, role, created_at, updated_at)
   VALUES (
       'Admin User',
       'admin@church.com',
       NOW(),
       '$2y$12$LQv3c1yqBCFcXDcjQjkzNOxCyd9wDoIp2VVasgiRJD.VyhAyUHfDa',
       'admin',
       NOW(),
       NOW()
   );
   ```

### Default Credentials:
- **Email:** admin@church.com
- **Password:** admin123

### Common Issues:

1. **PHP not found:** Make sure to use the full path: `C:\xampp\php\php.exe`
2. **Database connection:** Check your `.env` file for correct database settings
3. **Permissions:** Make sure the `storage` and `bootstrap/cache` directories are writable
4. **Cached views:** Always clear caches after making changes to views or routes

### Order of Service Usage:

1. Login to admin panel
2. Go to "Services" in the sidebar
3. Create a new service (e.g., "Sunday Morning Service")
4. Click the "Order" button on the service card
5. Add program items like:
   - Opening Prayer
   - Worship Songs
   - Scripture Reading
   - Sermon
   - Closing Prayer

### Features Available:
- ✅ Drag & drop reordering
- ✅ Time management (start/end times or duration)
- ✅ Leader assignment
- ✅ Print-friendly view
- ✅ Duplicate to other services
- ✅ Auto-calculation of total duration