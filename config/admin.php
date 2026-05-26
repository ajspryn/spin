<?php

return [
     /*
    |--------------------------------------------------------------------------
    | Admin Panel Credentials
    |--------------------------------------------------------------------------
    | Set ADMIN_USERNAME and ADMIN_PASSWORD in your .env file.
    | ADMIN_PASSWORD must be a bcrypt hash.
    | Generate with: php artisan tinker --execute="echo bcrypt('yourpassword');"
    */
     'username' => env('ADMIN_USERNAME', 'admin'),
     'password' => env('ADMIN_PASSWORD'),
];
