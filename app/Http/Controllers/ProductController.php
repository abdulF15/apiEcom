<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::with('category:id,title,slug,image', 'productImages:product_id,image')->get();
            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => ProductResource::collection($products)
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
            $validatedProducts = Validator::make($request->all(), [
                'title' => 'required|string',
                'description' => 'required|string',
                'stock' => 'required|integer',
                'price' => 'required|integer',
                'category_id' => 'required|integer',
                'image' => 'nullable|array',
                'image.*' => 'nullable|mimes:jpeg,png,jpg'
            ]);

            if ($validatedProducts->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validatedProducts->errors()
                ], 401);
            }

            $category = Category::find($request->category_id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Category not found'
                ], 401);
            }

            $product = $category->products()->create([
                'title' => $request->title,
                'description' => $request->description,
                'stock' => $request->stock,
                'price' => $request->price,
                'category_id' => $request->category_id
            ]);

            $fileImagePathNames = [];

            if ($request->hasFile('image')) {
                $uploadPath = 'uploads/products';

                foreach ($request->file('image') as $imageFile) {
                    $filename = time() . '_' . $imageFile->getClientOriginalName();
                    $imageFile->move($uploadPath, $filename);
                    $fileImagePathNames[] = $uploadPath . '/' . $filename;
                }
            }

            foreach ($fileImagePathNames as $fileImagePathName) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $fileImagePathName,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ]);
        } catch (\Throwable $th) {

            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 401);
        }
    }
}
