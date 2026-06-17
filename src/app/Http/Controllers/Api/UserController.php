<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    // GET /api/users?q=<email> — all disposable users by searched string, searched by email
    public function index(Request $request)
    {
        $email = $request->input('q');

        validator(
            ['q' => $email], 
            ['q' => 'required|string|max:255']
        )->validate();        

        return UserResource::collection(
            User::where('email', 'like', '%' . $email . '%')
                ->where('id', '!=', $request->user()->id)
                ->limit(30)
                ->get()
        );
    }
}
