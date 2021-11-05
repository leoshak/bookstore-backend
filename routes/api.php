<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;

Route::post("register", [AuthorController::class, "register"]);
Route::post("login", [AuthorController::class, "login"]);
Route::post("refresh", [AuthorController::class, "refreshToken"]);

Route::get("books", [BookController::class, "showAll"]);
Route::get("books/{id}", [BookController::class, "show"]);

Route::group(["middleware" => ["auth:api"]], function() {
    Route::get("profile", [AuthorController::class, "getProfile"]);
    Route::post("logout", [AuthorController::class, "logout"]);

    Route::post("books", [BookController::class, "store"]);
    Route::put("books/{id}", [BookController::class, "update"]);
    Route::delete("books/{id}", [BookController::class, "destroy"]);

    Route::get("authors/books", [BookController::class, "showAuthorBooks"]);
    Route::get("authors/books/{id}", [BookController::class, "showAuthorsSingleBook"]);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
