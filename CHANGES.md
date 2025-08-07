# Changes Implemented

## 1. Changed Site Title to "Les Hameçonnés 🎣"

- Updated browser tab title in `app.blade.php`
- Updated site header title in `NavigationMenu.vue`
- Updated login page title and heading

## 2. Implemented Login Requirement for Main Page

- Moved the main route (`/`) into the `middleware('auth')` group in `web.php`
- This ensures unauthenticated users are redirected to the login page
- Login page remains accessible without authentication

## Files Modified

1. `resources/views/app.blade.php` - Changed title
2. `resources/js/components/NavigationMenu.vue` - Updated header title
3. `resources/views/auth/login.blade.php` - Updated title and heading
4. `routes/web.php` - Added authentication requirement for main page

## Testing

To test the implementation:
1. Visit the application - you should be redirected to login
2. Log in with admin credentials (email: guillaume.rv29@gmail.com, password: azerty)
3. After login, you should see the main page
4. Verify "Les Hameçonnés 🎣" appears in browser tab and navigation
