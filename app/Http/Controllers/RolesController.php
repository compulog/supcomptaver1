<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;


class RolesController extends Controller
{


public function store(Request $request)

                {
                    $name = $request->input('roleName');
                    $description = $request->input('roleDescription');
                    DB::table('roles')
                        ->insert(['name' => $name, 'description'=> $description, 'created_at' => now(), 'updated_at' => now()]);
                    return redirect('/laravel-roles-management');
                }

            }