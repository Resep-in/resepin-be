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

    public function recommend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:20480', // 20MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $response = Http::attach(
                'image',
                file_get_contents($request->file('image')->getRealPath()),
                $request->file('image')->getClientOriginalName(),
                [
                    'Content-Type' => $request->file('image')->getMimeType(),
                ]
            )
                ->post(env("APP_MODEL_URL") . '/detect', [
                    'image' => $request->file('image')->getRealPath(),
                ]);

            if ($response->getStatusCode() !== 200) {
                return response()->json(['message' => 'Error processing image'], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing image'], 500);
        }


        $label = [];

        foreach ($response->json()["detections"] as $key => $value) {
            $label[] = $value['label'];
        }

        try {
            $response = Http::post(env("APP_MODEL_URL") . '/predict', [
                'ingredients' => $label,
            ]);
            if ($response->getStatusCode() !== 200) {
                return response()->json(['message' => 'Error processing prediction'], $response->getStatusCode());
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error processing prediction'], 500);
        }



        $recipesId = [];

        foreach ($response->json()["recommendations"] as $key => $value) {
            $recipesId[] = $value["id"];
        }

        $recipes = Recipe::whereIn('id', $recipesId)->get();

        return $recipes;
    }
}
