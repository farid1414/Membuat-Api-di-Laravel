<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    // nantinya category produk index maupun category product detail akan di handel oleh satu controller atau satu function all ini   
    {
        $id = $request->input('id');  //untuk mengambil inputan id  pada saat ingin menampilkan category produk berdasarkan id  
        $name = $request->input('name');  //untuk mengambil inputan namee  pada saat ingin menampilkan category produk berdasarkan nama category produk  
        $limit = $request->input('limit');  //untuk mengambil inputan limit  pada saat ingin menampilkan batas dari tampilan beberapa produk  
        $show_product = $request->input('show_product');  //untuk mengambil inputan id daro category  pada saat ingin menampilkan berbagai produk sesuai dengan id dari category produk 
        
        if($id)
        {
            $category = ProductCategory::with(['products'])->find($id);
            // pengondisisan untuk mengambil category produk beserta relasi yang terhubung dengan category produk yaitu produk 

            if($category)
            {
                return ResponseFormatter::success(
                    $category,
                    'Data category produk anda berhasil di ambil '
                );
            }
            else{
                return ResponseFormatter::error(
                    null,
                    'Data category produk kosong',
                    404
                );
            }
        }
        //memamnggil semua category produk dengan eleqouent query kosong   
        $category = ProductCategory::query();

        // pengondisian pemanggilan category produk berdasarkan nama category 
        if($name){
            $category->where('name', 'like', '%' . $name . '%');
        }
        // pengondisian pemanggilan produk yang sesuai dengan id category yang di panggil  
        if($show_product){
            $category->with('products');
        }

        // pemnanggialn data
        return ResponseFormatter::success(
            $category->limit($limit),
            'Data category produk berhasil diambil'
        );
    }
}