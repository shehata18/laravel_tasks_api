<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     operationId="registerUser",
     *     tags={"Register"},
     *     summary="Register a new user",
     *     description="User Registration Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name","email","password","password_confirmation"},
     *                 @OA\Property(property="name",type="text"),
     *                 @OA\Property(property="email",type="text"),
     *                 @OA\Property(property="password",type="password"),
     *                 @OA\Property(property="password_confirmation",type="password"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User Registered Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Registered Successfull",
     *       @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent()
     *     ),
     * )
     */




    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:8',
            'password_confirmation' => 'required|string|same:password',
        ]);
        $validatedData['password'] = bcrypt($validatedData['password']);
        $user= User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;
        return response()->json(['user' => $user->name,'access_token'=>$accessToken ,'message' => 'User created!'], 201);
    }

    //  Login API
    /**
     * @OA\Post(
     *     path="/api/login",
     *     operationId="loginUser",
     *     tags={"Login"},
     *     summary="Login a user",
     *     description="User Login Endpoint",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email","password"},
     *                 @OA\Property(property="email",type="text"),
     *                 @OA\Property(property="password",type="password"),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="User Login Successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *       response="200",
     *       description="Login Successfull",
     *       @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Unprocessable Entity",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad Request",
     *         @OA\JsonContent()
     *     ),
     * )
     */

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($validatedData)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        return response()->json([
            'user' => [auth()->user()->name,'email' => auth()->user()->email],
            'access_token'=>$accessToken ,
            'message' => 'User created!'], 201);

    }
}
