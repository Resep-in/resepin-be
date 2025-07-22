<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class RecipeController extends Controller
{
    public function healthCheck()
    {
        try {
            $response = Http::get(env("APP_MODEL_URL") . '/health');
            if (!$response->getStatusCode() == 200) {
                return response()->json([
                    'message' => 'Model service is not available',
                    "model_loaded" => false,
                    'status_code' => $response->getStatusCode(),
                ], 503);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Model service is not available',
                "model_loaded" => false,
                'status_code' => 503,
            ], 503);
        }

        return $response->json();
    }

    public function predict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ingredients' => 'required|array',
            'ingredients.*' => 'string',
        ]);


        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // make ingredients.* singular
        $ingredients = collect($request->input('ingredients'))->map(function ($item) {
            return Str::singular($item);
        });
        $response = Http::post(env("APP_MODEL_URL") . '/predict', [
            'ingredients' => $ingredients,
        ]);

        return $response->json();
    }
}
