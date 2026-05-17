<?php
// Database configuration for web hosting
return [
    // Set driver to 'mysql' to use MySQL/MariaDB
    'driver' => 'mysql',
    
    // MySQL settings for web hosting
    'mysql' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'dramranc_smartfeedback',
        'user' => 'dramranc_smartfeedback',
        'pass' => 'NDDPxMTcADLc'
    ],
    
    // SQLite settings (backup option, not used in production)
    'sqlite' => [
        'path' => __DIR__ . '/../database/feedback.db'
    ]
];
