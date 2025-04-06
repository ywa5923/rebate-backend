<?php

namespace Modules\Translations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Storage;

use Modules\Translations\Services\LocalesTranslator;
use App\Services\StorageService;
use Modules\Translations\Models\Locales;
use Modules\Translations\Transformers\LocalesCollection;
class LocaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $locales = Locales::all();
            $transformedLocales = new LocalesCollection($locales);

            return new JsonResponse([
                'status' => true,
                'data' => $transformedLocales
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Failed to fetch locales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LocalesTranslator $translator,StorageService $storageService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'country' => 'required|string|max:100',
            'code' => 'required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $existingLocale = Locales::where('code', $request->code)->first();
        if ($existingLocale) {
            return new JsonResponse([
                'status' => false,
                'message' => 'The locale code already exists.',
            ], 400);
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $storageService->uploadFile($image, 'flags');
            }

            $results = $translator->translateLocales($request->country, $request->code);
          
            if ($results['success'] == false) {
                throw new \Exception("Translation failed: " . implode(',', $results['errors']));
            }

            $locale = Locales::create([
                'country' => $request->country,
                'code' => $request->code,
                'description' => $request->description,
                'flag_path' => $imagePath
            ]);

            return new JsonResponse([
                'status' => true,
                'message' => 'Locale created successfully',
                'data' => $locale,
                'flag_path' => $imagePath,
                'ai_translation' => $results
            ], 201);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Failed to create locale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $locale = Locales::findOrFail($id);

        return new JsonResponse([
            'status' => true,
            'data' => $locale
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $locale = Locales::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'country' => 'sometimes|required|string|max:100',
            'code' => 'sometimes|required|string|max:20',
            'description' => 'nullable|string|max:1000',
            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $imagePath = $locale->image_path;
            if ($request->hasFile('image')) {
                if ($locale->image_path) {
                    Storage::disk('public')->delete($locale->image_path);
                }
                $image = $request->file('image');
                $imagePath = $image->store('images', 'public');
            }

            $locale->update([
                'country' => $request->input('country', $locale->country),
                'code' => $request->input('code', $locale->code),
                'description' => $request->input('description', $locale->description),
                'image_path' => $imagePath
            ]);

            return new JsonResponse([
                'status' => true,
                'message' => 'Locale updated successfully',
                'data' => $locale,
                'flag_path' => $imagePath
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Failed to update locale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $locale = Locales::findOrFail($id);

        try {
            if ($locale->image_path) {
                Storage::disk('public')->delete($locale->image_path);
            }

            $locale->delete();

            return new JsonResponse([
                'status' => true,
                'message' => 'Locale deleted successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => false,
                'message' => 'Failed to delete locale',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
