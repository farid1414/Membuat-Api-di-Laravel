<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Laravel\Fortify\Rules\Password;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // membuat fungsi untuk api register 
    public function register(Request $request)
    {
        // menggunakan blok try catch yaitu apabila kondisi gagal otomatis dikeluarkan apabila benar maka akan dibuatkan login dan pendaftaran 

        try {

            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'max:255', 'unique:users'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'phone' => ['nullable', 'string'],
                'password' => ['required', 'string', new Password]
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }



            User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);

            // memanggil data yang sudah dibuat sesuai dengan email karena email unique dan tidak akan duplikate 
            $user = User::where('email', $request->email)->first();

            // membuat token supaya setelah register langsung masuk tidak akan login terlebih dahulu dan tokne digunakan untuk penanda login
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'User Registered'); //untuk menampilkan pesan berhasil registrasi 

        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something When wrong',
                'error' => $error
            ], 'authentication failed ', 500); //500 untuk kode bahwa menunjukkan eror 
        }
    }

    public function login(Request $request)
    {
        // menggunakan block try catch seperti register apabola kondisi gaga; otomatis akan dikeluarkan 
        try {
            // 1. validasi terlebih dahulu untuk email dan password 
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required']
            ]);

            // 2. mengecek validasi apakah benar atau salah 
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            // 3. membuat credential untuk mengambil inputan email dan password 
            $credentials = request(['email', 'password']);

            // 4. apabila inputan tidak seusi dengan database maka akan langsung erro dan mengembalikan kode 500
            if (!Auth::attempt($credentials)) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authenticated Failed', 500);
            }

            // 5. megambil data email sesuai dengan inputan email dengan yang ada di db 
            $user = User::where('email', $request->email)->first(); // mengambil data yang pertama sesuai dengan email 

            // 6. Mengecek apakah apakh inputan  password sesuai dengan pada waktu register atau database atau tidak apabila 
            // tidak sesuai maka akan dibuang ke exception atau catch serta erro mengrimkan kode 500
            if (!Hash::check($request->password, $user->password, [])) {
                throw new \Exception('Invalid Credential');
            }

            // 7. apabila email dan password sesuai maka akan dibuatkan token untuk penanda bahwa sudah login 
            $tekonResult = $user->createToken('authToken')->plainTextToken;

            // 8. apabila email dan password  sesuai dan sukses maka akan dikembalikan dengan kode 200
            return ResponseFormatter::success([
                'access_token' => $tekonResult,
                'type_token' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        }

        // apabila inputan error langsung menuju catch dan akan terjadi eror serta mengirim kode 500
        catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something Went Wrong',
                'error' => $error
            ] . 'Authenticated Error ', 500);
        }
    }

    // fungsi utnuk mengambil data user apabila berhasil login 
    public function fetch(Request $request)
    {
        // untuk pemanggilan user hanya menggunkan response formatter succes karena sudah melalui miidleware untuk pengecekan 
        // harus memasukkan Authorization token yang diperoleh dari login 
        return ResponseFormatter::success($request->user(), 'Data User berhasil diambil');
    }

    // fungsi untuk update data user untuk name email usernam dam phoene 
    public function updateProfil(Request $request)
    {
        // 1. Memvalidasi isian yang akan di update supaya sesuai dengan ketentuan di tabel database 
        $validator = Validator::make($request->all(), [
            'name' => ['string', 'max:255'],
            'email' => ['string', 'max:255'],
            'usernmae' => ['string', 'max:255']
        ]);

        // 2. mengecek apakah validasi sesuai atau tidak apabila tidak seuai maka akan error dan mengirimkan code 500
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // 3. Deklarasi inputan untuk update data 
        $data = ([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'phone' => $request->phone,
        ]);

        // 4. mengambil data user 
        $user = Auth::user();

        // 5. update data user 
        $user->update($data);

        // 6.Menegembalikan nilai sukses dengan kode 200
        return ResponseFormatter::success($user, 'Profile has Updated');
    }

    // fungsi untuk logout 
    public function logout(Request $request)
    {
        // menambil token yang aktif sekarang dari login utnuk dihapus dan token tersebut tidak dapat digunakan kembali
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Rekoved');
    }
}