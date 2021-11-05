<?php

namespace App\Http\Controllers\Api;

use App\Author;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;

class AuthorController extends Controller
{

    public function register(Request $request) {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:authors',
            'password' => 'required|confirmed',
            'phone_no' => 'required',
        ]);

        $author = new Author();
        $author->name = $request->name;
        $author->email = $request->email;
        $author->phone = $request->phone_no;
        $author->password = bcrypt($request->password);

        $author->save();

        return response()->json([
            "error" => false,
            "message" => "Author created successfully!",
        ]);

    }

    public function login(Request $request) {

        $userCredentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5',
        ]);

        if (!auth()->attempt($userCredentials)) {
            return response()->json([
                'error' => true,
                "message" => "Invalid credentials",
            ]);
        }
        $client = \DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        $data = [
            'grant_type' => 'password',
            'username' => $userCredentials['email'],
            'password' => $userCredentials['password'],
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => ''
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $content = json_decode(app()->handle($request)->getContent());

        $authToken = $content->access_token;
        $refreshToken =  $content->refresh_token;

        return response()->json([
            "error" => false,
            "message" => "Author logged in successfully!",
            "access_token" => $authToken,
            "refresh_token" => $refreshToken,
        ]);
    }

    public function refreshToken(Request $request) {
        $client = \DB::table('oauth_clients')
            ->where('password_client', true)
            ->first();

        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'scope' => ''
        ];

        $request = Request::create('/oauth/token', 'POST', $data);
        $content = json_decode(app()->handle($request)->getContent());

        if (!empty($content->error))
            return response()->json([
                'error' => true,
                'message' => $content->message,
                'hint' => $content->hint,
            ]);

        return response()->json([
            'error' => false,
            'data' => [
                'access_token' => $content->access_token,
                'refresh_token' => $content->refresh_token,
            ]
        ], Response::HTTP_OK);
    }

    public function getProfile() {

        $userData = auth()->user();

        if (empty($userData))
            return response()->json([
                'error' => true,
                'message' => 'User cannot be found'
            ]);

        return response()->json([
            'error' => false,
            'message' => 'Authenticated user retrieved successfully!',
            'data' => [
                'name' => $userData->name,
                'email' => $userData->email,
                'phone_no' => $userData->phone,
            ]
        ]);

    }

    public function logout(Request $request) {

        $authToken = $request->user()->token();

        if (empty($authToken))
            return response()->json([
                'error' => true,
                'message' => 'User already logged out!'
            ]);

        $authToken->revoke();

        return response()->json([
            'error' => false,
            'message' => 'Authenticated user logged out successfully!',
        ]);
    }
}
