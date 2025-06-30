-- Quick Admin User Setup for Church Management System
-- Run this SQL directly in your database

-- First, check if admin user exists
-- If it doesn't exist, insert the admin user

INSERT IGNORE INTO users (name, email, email_verified_at, password, role, created_at, updated_at)
VALUES (
    'Admin User',
    'admin@church.com',
    NOW(),
    '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- This is 'password'
    'admin',
    NOW(),
    NOW()
);

-- Alternative with 'admin123' password:
-- INSERT IGNORE INTO users (name, email, email_verified_at, password, role, created_at, updated_at)
-- VALUES (
--     'Admin User',
--     'admin@church.com',
--     NOW(),
--     '$2y$12$LQv3c1yqBCFcXDcjQjkzNOxCyd9wDoIp2VVasgiRJD.VyhAyUHfDa', -- This is 'admin123'
--     'admin',
--     NOW(),
--     NOW()
-- );

-- Display the created user
SELECT id, name, email, role, created_at FROM users WHERE email = 'admin@church.com';