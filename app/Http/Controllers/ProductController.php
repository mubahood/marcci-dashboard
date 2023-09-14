<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Utils;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->all();
        
        // Store the uploaded photo
        if ($request->has('photo')) {
            $photoData = $request->input('photo');
            list($type, $photoData) = explode(';', $photoData);
            list(, $photoData) = explode(',', $photoData);
            $photoData = base64_decode($photoData);
        
            $photoPath = 'images/' . uniqid() . '.jpg'; 
            Storage::disk('admin')->put($photoPath, $photoData);
            
            $data['photo'] = $photoPath;
        }
    
    
        $product = Product::create($data);
        return Utils::success($product, 'Product submitted successfully.');
    }
    

    public function show($id)
    {
        $product = Product::find($id);

        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        $user = auth('api')->user();

        if ($user->id != $product->administrator_id) {
            return Utils::error('You are not authorized to edit this product.');
        }

        $data = $request->all();

         // Store the uploaded photo
         if ($request->has('photo')) {
            $photoData = $request->input('photo');
            list($type, $photoData) = explode(';', $photoData);
            list(, $photoData) = explode(',', $photoData);
            $photoData = base64_decode($photoData);
        
            $photoPath = 'images/' . uniqid() . '.jpg'; 
            Storage::disk('admin')->put($photoPath, $photoData);
            
            $data['photo'] = $photoPath;
        }
    
        $product->update($data);
        return Utils::success($product, 'Product edited successfully.');
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        //check if the logged in user is the owner of the product
        $user = auth('api')->user();
        if ($user->id != $product->administrator_id) {
            return Utils::error('You are not authorized to delete this product.');
        }

        $product->delete();
        return Utils::success($product, 'Product deleted successfully.');
    }
}
