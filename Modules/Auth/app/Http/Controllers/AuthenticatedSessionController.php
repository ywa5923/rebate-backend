<?php

namespace Modules\Auth\Http\Controllers;

use Modules\Auth\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Http\Requests\LoginRequest;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="FXREBATE PROJECT",
 *      description="A PROGRESSIVE WEB APP"
 * )
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     tags={"Auth"},
     *     summary="Login user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=422,
     *         description="These credentials do not match our records"
     *     )
     * )
     */
    public function store(LoginRequest $request): Response
    {

        $request->authenticate();
        $request->session()->regenerate();
        //session()->regenerate();

        return response()->noContent();
    }



    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation"
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
