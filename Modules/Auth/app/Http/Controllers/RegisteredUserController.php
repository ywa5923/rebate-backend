<?php

namespace Modules\Auth\Http\Controllers;

use Modules\Auth\Http\Controllers\Controller;
use Modules\Auth\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;


class RegisteredUserController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Auth"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input"
     *     ),
     *    
     * )
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request): Response
    {


        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);



        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            //'password' => Hash::make($request->password),
            'password' => Hash::driver('bcrypt')->make($request->password)

        ]);

        // dd($user);

        //hash_hmac('sha256',$request->password,'snrg')


        event(new Registered($user));

        Auth::login($user);


        return response()->noContent();
    }
}
