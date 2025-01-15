<?php
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/../../database/database.sqlite',
    'prefix' => '',
    'foreign_key_constraints' => true
]);

// Make this Capsule instance available globally
$capsule->setAsGlobal();

// Setup the Eloquent ORM
$capsule->bootEloquent();

// Enable query log for debugging
$capsule->getConnection()->enableQueryLog(); 