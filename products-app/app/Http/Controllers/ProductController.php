<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // All products
        $products = Product::all();

        // Json response
        return response()->json([
            'products' => $products
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        try{
            $imageName = Str::random(32).".".$request->image->getClientOriginalExtension();

            //Create Product
            Product::create([
                'name' => $request->name,
                'image' => $imageName,
                'description' => $request->description
            ]);

            //Save image in Storage folder
            Storage::disk('public')->put($imageName, file_get_contents($request->image));

            return response()->json([
                'message' => "Product successfully created!"
            ], 200);
        } catch(\Exception $e){
            return response()->json([
                'message' => "Something went wrong!"
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Show product
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'Product Not Found.'
            ], 404);
        }

        return response()->json([
            'product' => $product
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductStoreRequest $request, string $id)
    {
        try{
            //find product
            $product = Product::find($id);
            if(!$product){
                return response()->json([
                    'message'=>'Product not found.'
                ],404);
            }

            // echo "request : $request->name";
            // echo "description : $request->description";
            $product->name = $request->name;
            $product->description = $request->description;

            if($request->image){
                $storage = Storage::disk('public');
            }

            //delete old image
            if($storage->exists($product->image)){
                $storage->delete($product->image);
            }

            $imageName = Str::random(32).".".$request->image->getClientOriginalExtension();
            $product->image = $imageName;

            $storage->put($imageName, file_get_contents($request->image));

            //update product
            $product->save();

            return response()->json([
                'message' => "Product successufully updated!"
            ],200);
        } catch(\Exception $e) {
            return response()->json([
                'message' => "Something went wrong!"
            ], 500);

     }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $storage = Storage::disk('public');

        //Image delete
        if($storage->exists($product->image)){
            $storage->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => "Product successfully deleted."
        ], 200);
    }
}
