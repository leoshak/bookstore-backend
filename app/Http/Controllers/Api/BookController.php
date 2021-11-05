<?php

namespace App\Http\Controllers\Api;

use App\Book;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Author;

class BookController extends Controller
{

    public function store(Request $request)
    {
        $bookData = $request->validate([
            'title' => 'required',
            "book_price" => 'required',
        ]);

        $book = new Book();
        $book->author_id = auth()->user()->id;
        $book->title = $request->title;
        $book->description = $request->description;
        $book->book_cost = $request->book_price;

        try {
            $savedBook = $book->save();

            return response()->json([
                "error" => false,
                "message" => "Book is successfully saved",
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->message,
            ]);
        }
    }

    public function showAll() {
        try {
            $books = Book::all();

            return response()->json([
                "error" => false,
                "message" => count($books) . " books available",
                "data" => $books,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->message,
            ]);
        }
    }

    public function show($bookId)
    {
        try {
            $book = Book::where('id', $bookId)->get()->first();

            $message = empty($book) 
                ? "Book with id {$bookId} not available"
                : "Book with id {$bookId} available";

            return response()->json([
                "error" => false,
                "message" => $message,
                "data" => $book,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->message,
            ]);
        }
    }

    public function update(Request $request, $bookId)
    {
        $author_id = auth()->user()->id;

        if (Book::where([
            "author_id" => $author_id,
            "id" => $bookId
        ])->exists()) {

            $book = Book::find($bookId);

            $book->title = isset($request->title) ? $request->title : $book->title;
            $book->description = isset($request->description) ? $request->description : $book->description;
            $book->book_cost = isset($request->book_price) ? $request->book_price : $book->book_price;

            try {
                $book->save();
    
                return response()->json([
                    "error" => false,
                    "message" => "Author's book has updated successfully!",
                ]);
            } catch (Exception $e) {
                return response()->json([
                    "error" => true,
                    "message" => $e->message,
                ]);
            }
        } else {
            return response()->json([
                "error" => true,
                "message" => "Author book does not exists",
            ]);
        }
    }

    public function destroy($bookId)
    {
        $author_id = auth()->user()->id;

        if (Book::where([
            "author_id" => $author_id,
            "id" => $bookId
        ])->exists()) {
            
            try {
                $book = Book::find($bookId);
    
                $book->delete();
    
                return response()->json([
                    "error" => false,
                    "message" => "Author's book has deleted successfully!",
                ]);
            } catch (Exception $e) {
                return response()->json([
                    "error" => true,
                    "message" => $e->message,
                ]);
            }
        } else {
            return response()->json([
                "error" => true,
                "message" => "Author book does not exists",
            ]);
        }
    }

    public function showAuthorBooks() {
        $author = auth()->user();
        try {
            $books = Author::find($author->id)->authorsBooks;

            return response()->json([
                "error" => false,
                "message" => count($books) . " books of {$author->name} available",
                "data" => $books,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->message,
            ]);
        }    
    }

    public function showAuthorsSingleBook($bookId) {
        $author = auth()->user();
        try {
            $book = Book::where('author_id', $author->id)
                ->where('id', $bookId)
                ->get()
                ->first();

            $message = empty($book) 
                ? "Book with id {$bookId} of author {$author->name} not available"
                : "Book with id {$bookId} of author {$author->name} available";

            return response()->json([
                "error" => false,
                "message" => $message,
                "data" => $book,
            ]);
        } catch (Exception $e) {
            return response()->json([
                "error" => true,
                "message" => $e->message,
            ]);
        }
    }
}
