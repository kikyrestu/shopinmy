<?php
use Spatie\Permission\Models\Role;
use App\Models\User;

$role = Role::firstOrCreate(['name' => 'Super Admin']);
$user = User::where('email', 'admin@tokoku.com')->first();
if($user) {
    $user->assignRole($role);
    echo "Assigned Super Admin role.\n";
} else {
    echo "User not found.\n";
}
