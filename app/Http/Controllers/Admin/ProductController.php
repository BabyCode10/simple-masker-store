<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;

use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->path         = storage_path('app/public/images');
        $this->pathFile     = storage_path('app/public/files');
        $this->dimentions   = ['245', '300', '500'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $products   = Product::all();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return view('pages.admin.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.admin.product.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|max:100',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
            'description'   => 'required',
            'weight'        => 'required',
            'image'         => 'required|mimes:jpeg,jpg'
        ]);

        try {
            $product    = Product::create([
                'id'            => Str::uuid(),
                'user_id'       => Auth::id(),
                'title'         => $request->title,
                'slug'          => Str::slug($request->title, '-'),
                'price'         => $request->price,
                'stock'         => $request->stock,
                'description'   => $request->description,
                'weight'        => $request->weight
            ]);

            $fileName   = $this->uploadImage( $request->file('image'), $product->title );

            $product->productImage()->create([
                'id'            => Str::uuid(),
                'product_id'    => $product->id,
                'name'          => $fileName,
                'dimentions'    => implode( '|', $this->dimentions ),
                'path'          => $this->path
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('success', "Product $product->title stored!");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        return view('pages.admin.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        return view('pages.admin.product.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title'         => 'required|max:100',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric',
            'description'   => 'required',
            'weight'        => 'required',
            'image'         => 'mimes:jpeg,jpg'
        ]);

        try {
            $product->update([
                'id'            => Str::uuid(),
                'user_id'       => Auth::id(),
                'title'         => $request->title,
                'slug'          => Str::slug($request->title, '-'),
                'price'         => $request->price,
                'stock'         => $request->stock,
                'description'   => $request->description,
                'weight'        => $request->weight
            ]);

            if ( $request->hasFile('image') ) {
                $fileName   = $this->uploadImage( $request->file('image'), $product->title );

                $product->productImage()->update([
                    'id'            => Str::uuid(),
                    'product_id'    => $product->id,
                    'name'          => $fileName,
                    'dimentions'    => implode('|', $this->dimentions),
                    'path'          => $this->path
                ]);
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('info', "Product $product->title updated!");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try {
            $product->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.product.index')->with('warning', "Product $product->title destroyed!");
    }
}
