<?php
return [
    'jwt_secret'  => $_ENV['JWT_SECRET'] ?? 'change_this_secret',
    'jwt_expiry'  => 3600,
    'bcrypt_cost' => 12,
];
