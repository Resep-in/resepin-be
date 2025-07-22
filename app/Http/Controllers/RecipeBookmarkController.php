<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecipeBookmarkController extends Controller
{
    public function addBookmark(Request $request)
    {
        // Validate the request
        $request->validate([
            'recipe_id' => 'required|integer',
        ]);

        // Assuming the user is authenticated and has a relationship with bookmarks
        $user = $request->user();
        $recipeId = $request->input('recipe_id');

        try {
            if ($user->bookmarks()->where('recipe_id', $recipeId)->exists()) {
                return response()->json(['message' => 'Recipe already bookmarked'], 409);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error checking bookmark'], 500);
        }

        try {
            $user->bookmarks()->create([
                'recipe_id' => $recipeId,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding bookmark'], 500);
        }

        return response()->json(['message' => 'Recipe bookmarked successfully']);
    }


    public function removeBookmark(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|integer',
        ]);

        $user = $request->user();
        $recipeId = $request->input('recipe_id');

        try {
            $bookmark = $user->bookmarks()->where('recipe_id', $recipeId)->first();
            if (!$bookmark) {
                return response()->json(['message' => 'Bookmark not found'], 404);
            }

            $bookmark->delete();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error removing bookmark'], 500);
        }

        return response()->json(['message' => 'Recipe bookmark removed successfully']);
    }

    public function listBookmarks(Request $request)
    {
        $user = $request->user();

        try {
            $bookmarks = $user->bookmarks()->cursorPaginate(100);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving bookmarks'], 500);
        }

        return response()->json($bookmarks);
    }
}
