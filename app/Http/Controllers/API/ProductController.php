<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Helpers\ResponseFormatter;

class ProductController extends Controller
{
    // fungsi untuk mengambil semua data yang ada di dalam produk 
    public function all(Request $request)

    {
        $id = $request->input('id');  //untuk mengambil inputan id  pada saat ingin menampilkan produk berdasarkan id  
        $name = $request->input('name');  //untuk mengambil inputan id  pada saat ingin menampilkan produk berdasarkan nama produk  
        $limit = $request->input('limit');  //untuk mengambil inputan limit  pada saat ingin menampilkan batas dari tampilan beberapa produk  
        $description = $request->input('description');  //untuk mengambil inputan description  pada saat ingin menampilkan produk berdasarkan deskripsi dari produk tersebut
        $tags = $request->input('tags'); // untuk mengambil inputan tags untuk mencari produk berdasarkan tags 
        $categories = $request->input('categories'); //untuk mengambil inputan berupa id categori untuk menampilkan produk sesuai dengan id kategori yang di inputkan 

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');
        //untuk mengambil inputan range harga dari terendah ke tertinggi untuk mencari produk berdasarkan harga yang sudah di tentukan 

        // pengkondisian untuk mencari produk berdasarkan id   
        if ($id) {
            // mengambil data produk dengan relasinya yaitu category dab galleri 
            $product = Product::with(['category', 'galleries'])->find($id);
            // pengondisian untuk mengambil product berdasarkan id dengan diikuti relasi dari porduk dengan category serta galleri
            if ($product) {
                return ResponseFormatter::success(
                    $product,
                    'Data Produk Berhasil diambil'
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    'Data Produk Tidak ada',
                    404
                );
            }
        }   // memanggil semua produk yang ada  berserta dengan relasi yang ada di model produk 

        $product = Product::with(['category', 'galleries']);

        // pengondisian untuk memanggil produk berdasarkan nama produk(name )
        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }

        // pengondisian untuk memanggil produk berdasarkan deskripsi  produk(description )
        if ($description) {
            $product->where('name', 'like', '%' . $description . '%');
        }

        // pengondisian untuk memanggil produk berdasarkan tag produk(tags )
        if ($tags) {
            $product->where('name', 'like', '%' . $tags . '%');
        }

        // pengondisian untuk memanggil produk berdasarkan harga batas awal  (price from )
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }

        // pengondisian untuk memanggil produk berdasarkan harga batas akhir (price to  )
        if ($price_from) {
            $product->where('price', '<=', $price_from);
        }

        // pengondisian untuk memanggil produk berdasarkan kategori yang sesuai dengan produk  (categories)
        if ($categories) {
            $product->where('categories', $categories);
        }

        // mengembalikannilai benar dengan mrngirimkan kode 200 dan menampilkan paginate atau batas data yang di tampilkan
        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data Produk Berhasil diambil'
        );
    }
}