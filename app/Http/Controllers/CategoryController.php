<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => CategoryResource::collection($categories)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            // $user = auth()->user();

            // // Check admin role
            // if ($user->role !== 'admin') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized'
            //     ], 401);
            // }

            // Validate request data
            $validatedCategory = Validator::make($request->all(), [
                'title' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png',
                'description' => 'required|string',
            ]);

            if ($validatedCategory->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validatedCategory->errors()
                ], 422);
            }

            // Process image upload
            $validatedData = $validatedCategory->validated();

            if ($request->hasFile('image')) {
                $uploadPath = 'uploads/categories/';
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();

                try {
                    $file->move($uploadPath, $filename);
                    $validatedData['image'] = "$uploadPath$filename";
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload image'
                    ], 500);
                }
            }

            // Save category
            $category = Category::create([
                'title' => $validatedData['title'],
                'image' => $validatedData['image'] ?? null,
                'description' => $validatedData['description'],
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully',
                'data' => $category
            ]);
        } catch (\Throwable $th) {
            // Handle any other exceptions
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {

        try {
            // $user = auth()->user();
            // if ($user->role !== 'admin') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized'
            //     ], 401);
            // }

            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Validate request data
            $validatedCategory = Validator::make($request->all(), [
                'title' => 'required|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png',
                'description' => 'required|string',
            ]);

            // return response()->json([
            //     'success' => true,
            //     'message' => 'Category updated successfully',
            //     'data' => $request->all()
            // ]);

            if ($validatedCategory->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validatedCategory->errors()
                ], 422);
            }

            // Process image update
            $validatedData = $validatedCategory->validated();

            // Process image uploa

            if ($request->hasFile('image')) {
                $uploadPath = 'uploads/categories/';
                $file = $request->file('image');
                $filename = time() . '_' . $file->getClientOriginalName();
                try {
                    $file->move($uploadPath, $filename);
                    $validatedData['image'] = "$uploadPath$filename";

                    // Delete old image if exists
                    if ($category->image && file_exists($category->image)) {
                        unlink($category->image);
                    }
                } catch (\Throwable $th) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to upload image'
                    ], 500);
                }
            }

            // Update category
            $category->update([
                'title' => $validatedData['title'],
                'image' => $validatedData['image'] ?? $category->image,
                'description' => $validatedData['description'],
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully',
                'data' => new CategoryResource($category)
            ]);
        } catch (\Throwable $th) {
            // Handle any other exceptions
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {

        try {
            // $user = auth()->user();
            // if ($user->role !== 'admin') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Unauthorized'
            //     ], 401);
            // }

            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // Delete image if exists
            if ($category->image && file_exists($category->image)) {
                unlink($category->image);
            }

            // Delete category
            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully',
            ]);
        } catch (\Throwable $th) {
            // Handle any other exceptions
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
