<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (Schema::hasTable('admins')) {
    echo "Admins table exists!\n";
    $admins = DB::table('admins')->get();
    foreach ($admins as $admin) {
        echo "ID: {$admin->id} | Name: {$admin->name} | Username: {$admin->username} | Email: {$admin->email} | Password Hash: {$admin->password}\n";
    }
} else {
    echo "No admins table found!\n";
}
