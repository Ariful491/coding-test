<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use http\Env\Response;
use Image;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request  $request)
    {

        $title = $request->input('title');
        $variant = $request->input('variant');
        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        $date = $request->input('date');

         $products =  Product::when($title,function ($query,$title){
             return $query->Where('title', 'like', '%' .$title. '%');
         })->when($date, function ($query,$date) {
                return $query->whereDate('created_at',$date);
         })->paginate(2);
         foreach ($products as $product){
                 if($price_from >0 && $price_to >0){
                     $product->product_prices =  ProductVariantPrice::where('product_id',$product->id)->where('price',[$request->price_from,$request->price_to])
                         ->when($variant,function ($query,$variant){
                             return $query->where('product_variant_one','like', '%' . $variant . '%')->orWhere('product_variant_one','like', '%' . $variant . '%');
                         })->get();
                 }else{
                     $product->product_prices =  ProductVariantPrice::where('product_id',$product->id)
                         ->when($variant,function ($query,$variant){
                             return $query->where('product_variant_one','like', '%' . $variant . '%')->orWhere('product_variant_one','like', '%' . $variant . '%');
                         })->get();
                 }



         }

         $product_variants =  ProductVariant::all();

        return view('products.index')
            ->with('products',$products)
            ->with('product_variants',$product_variants);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $product  = new Product();
        $product->title =  $request->title;
        $product->sku   =  $request->sku ;
        $product->description =  $request->description;
        $product->save();
        if($request->product_image){
           foreach ($request->product_image  as $index=>$product_image){
               $file = explode(';',$product_image);
               $file = explode('/',$file[0]);
               $file_Extension  = end($file);
               $rand_name = rand(1,100);
               $file_name = $rand_name.'.'.$file_Extension;
               Image::make($product_image)->save(public_path('products/').$file_name);
               $product_image =  new ProductImage();
               $product_image->product_id = $product->id;
               $product_image->file_path = 'products/'.$file_name;
               $product_image->save();
           }
        }

      /*  if($request->product_variant){
            foreach ($request->product_variant as $iv=>$varient){
                 $tagall =  explode(',',$varient->tags);
                 foreach ($tagall as $i=>$tag){
                     $varient =  new ProductVariant();
                     $varient->product_variants = $tag[$i];
                     $varient->variant_id  = $varient->option;
                     $varient->product_id   = $product->id;
                     $varient->save();

                 }
                $data['Tags'] =  $varient->tags;
                $varient =  new ProductVariant();
                $varient->variant = $varient->tags;
                $varient->variant_id  = $varient->option;
                $varient->product_id   = 5;
                $varient->save();


            }
        }*/
//        if($request->product_variant_prices){
//            foreach ($request->product_variant_prices as $in => $varientPrices){
////                      $priceVarient =  new ProductVariantPrice();
////                       $priceVarient->price = $varientPrices->price[$in];
////                       $priceVarient->stock = $varientPrices->stock[$in];
////                       $titles= explode(',',$varientPrices->title);
////                       foreach ($titles as $index=>$title){
////                           $varient =  new ProductVariant();
////                           $varient->variant = $title[$in];
////                           foreach ($request->product_variant as $iv=>$varient) {
////                               $varient->variant_id = $varient->option;
////                           }
////                           $varient->product_id   = 10;
////                           $varient->save();
////                           if($index==0){
////                               $priceVarient->product_variant_one  = $varient->id;
////                           }
////                           if($index==1){
////                               $priceVarient->product_variant_two   = $varient->id;
////                           }
////                           if($index==2){
////                               $priceVarient->product_variant_three    = $varient->id;
////                           }
////
////
////                       }
////                       $priceVarient->product_id = 2;
////                       $priceVarient->save();
//                foreach ($varientPrices->price as $price){
//                    $price->price;
//                }
//            }
//
//        }





         return response()->json([
             $request->all(),
         ]);


    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {



    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::all();
        return view('products.edit', compact('variants'));
    }
    public function productDetails($id)
    {
        $variants = Variant::all();

        $products =  Product::where('id',$id)->get();
        foreach ($products as $product){
            $product->product_prices =  ProductVariantPrice::where('product_id',$product->id)->get();
            $product->images = ProductImage::where('product_id',$product->id)->select('file_path')->get();
        }


        return view('products.edit', compact('variants','products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
