<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{

public function store(){

$attributes = request()->validate([
    'email' => 'required|unique:users,email',
    'name' =>'required|',
    'password' => 'required|confirmed|min:7',
    'picture' => 'required|mimes:jpg,jpeg,png,bmp,tiff |max:4096',
    'role_id' => 'required|exists:roles,id',
]);

$path = request()->picture->store('profile', 'public');
$attributes['picture'] = "$path";


User::create($attributes);

return redirect('users-management')->withStatus('User successfully created.');
}

}