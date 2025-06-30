# Church Management System - Setup Guide

## Quick Setup

### 1. Database Setup
```bash
php artisan migrate --force
```

### 2. Create Admin User
```bash
php artisan db:seed --class=AdminUserSeeder
```

**Default Admin Credentials:**
- Email: `admin@church.com`
- Password: `admin123`

⚠️ **Important:** Change the password after first login!

### 3. Clear Caches (if needed)
```bash
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

## Features

### Prayer Requests
- ✅ Complete CRUD interface
- ✅ Prayer tracking and statistics
- ✅ Public/private privacy controls
- ✅ Prayer goals and progress tracking
- ✅ Interactive prayer recording
- ✅ Status management (active/completed/archived)

### Order of Service
- ✅ Modern timeline-based interface
- ✅ Drag & drop reordering
- ✅ Print-friendly layouts
- ✅ Service duplication
- ✅ Time and duration management

### UI/UX
- ✅ Glass morphism design
- ✅ Responsive layouts
- ✅ Interactive modals
- ✅ Progress visualization
- ✅ Professional print layouts

## Troubleshooting

### Common Issues

#### Missing Tables
If you get "table doesn't exist" errors:
```bash
php artisan migrate --force
```

#### Permission Errors
Make sure storage directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

#### Route Errors
Clear route cache:
```bash
php artisan route:clear
php artisan route:cache
```

### Database Requirements

The system requires these tables:
- `users` - Admin users
- `members` - Church members
- `services` - Church services
- `order_of_services` - Service program items
- `prayer_requests` - Prayer requests
- `prayers` - Individual prayer records

### Environment Requirements

- PHP 8.2+
- Laravel 12+
- MySQL/SQLite database
- Composer
- Node.js (for frontend assets)

## Development

### Adding New Features
1. Create migrations for database changes
2. Update models with relationships
3. Create/update controllers
4. Design responsive views
5. Add routes
6. Test functionality

### Code Style
- Follow Laravel conventions
- Use meaningful variable names
- Add proper comments
- Implement proper validation
- Handle errors gracefully

## Support

For issues or questions:
1. Check this setup guide
2. Review the troubleshooting section
3. Check Laravel documentation
4. Create an issue in the repository