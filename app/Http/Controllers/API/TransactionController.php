<?php

namespace App\Http\Controllers\API;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    //membuat fungsu untuk mengambil data transaction 
    public function all(Request $request)
    {
        $id = $request->input('id'); //untuk mengambil inputan id untuk menampilkan transaksi berdasarkan id 
        $limit = $request->input('limit', 5); //untuk mengambil inputan limit dan menampilkan paginate batas data yang ingin ditampilkan disini saya beri batas 5
        $status = $request->input('status'); //untuk mengambil inputan status dan menampilakn data transaksi berdasarkan status 

        // pengondisian untuk mengambil data transaksi berdasarkan id 
        if ($id) {
            // mengambil data transaksi dengan relasinya yaitu items serta mengambil data dari relasi item yaitu produj 
            $transaction = Transaction::with(['items.product'])->find($id);

            // pengondisian apabila data transaksi  yang diambil ada 
            if ($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil '
                );
            }


            // apabild data transaksi yang diambil tidak ada maka akan error dan mengirimkan kode 4040
            else {
                return ResponseFormatter::error(
                    null,
                    'Data tidak dapat ditemukan',
                    404
                );
            }
        }

        // mengmambil daa transaksi dengan relasi item dan mengambil data dari relasi item yaitu data produk dengan syarat user id sesuai dengan id user yang sedang login 
        $transaction = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);

        // pengondisian untuk mengabil data berdasarkan status 
        if ($status) {
            $transaction->where('status', $status);
        }

        // mengembalikan kondisi apabila sukses dengan mnegirimkan kode 200
        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list transaksin berhasil diambil'
        );
    }

    // function untuk hasil checkout barang pembeluan 
    public function checkout(Request $request)
    {
        // 1, validasi terlebih dahulu dengan menggunakan validator untuk inputan checkout 
        $validator = Validator::make($request->all(), [
            'items' => ['required', 'array'], //untuk validasi inputan items karna items nya bisa lebih satu dan bentuk array maka menggunakan validasi array yang mengharuskan inputan tersebut apakah bentuk array atau bukan  
            'items.*.id' => ['exists:products, id'],  // .*. welcard untuk mengecek semua data yang ada di dalamnya  dan disamping untuk memvalidasi bahwa id items yang ada harus sesuai dengan id yang di product divalidasi dengan menggunalan exists 
            'total_price' => ['required'], //untuk memvalidasi inputan shipping price harus terisi tidak boleh null
            'shipping_price' => ['required'], //untuk memvalidasi inputan shipping price harus terisi tidak boleh null
            'status' => ['required', 'in:PENDING,SUCCESS,CANCELED'] //memvalidasi inputan status tidak boleh null dan isian harus sesuai dengan data yaitu harus pending atau success atau canceled dengan divalidasi menggunkan in 
        ]);

        // 2. apabila inputan error tidak sesuai dengan validasoi maka akan mengirim kode 404 dan akan erro  
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 3. menginput data chekout untuk trnsaksi kedalam database transaksi 
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'addres' => $request->addres,
            'shipping_price' => $request->shipping_price,
            'total_price' => $request->total_price,
            'status' => $request->status,
        ]);

        // 4. setetalh membuat data di dalam tabel produk maka akan membuat data item atau produk yang di beli untuk di simpan di dalam tabel transaksi item 
        // 5. dikarenakan itemsnya berupa aray maka untuk pengisiannya menggunakan blok foreach sehingga tidak mengisi satu satu 

        //untuk perulangannya dari banyaknya item atau produk yang dibeli 
        foreach ($request->items as $product) {
            TransactionItem::create([
                'users_id' => Auth::user()->id,
                'products_id' => $product['id'], //untuk produk id mengambil dari foreach untuk perulangan dari item atau produk yang dibeli dan karena berbentuk array maka tidak menggunkan panah diganti dengan kurung siku yang biasa digunakan untuk mengisi array 
                'transactions_id' => $transaction->id, //mengambil dari data inputan di tabel transaksi diatas 
                'quantity' => $product['quantity'], //untuk quantity mengambil dari perulangan produk dengan mengambil data dari tabel produk 
            ]);
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaksi berhasil');
    }
}