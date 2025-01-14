<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use App\Services\CheckExtensionServices;
use App\Services\FileUploadServices;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'img_name' => ['file', 'image', 'mimes:jpeg,png,jpg,gif','max:2000'],
            'self_introduction' => ['string', 'max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
         // ---ここから追加---
        //引数 $data から name='img_name'を取得(アップロードするファイル情報)
        $imageFile = $data['img_name'];

        //$imageFileからファイル名を取得(拡張⼦あり)
        $list = FileUploadServices::fileUpload($imageFile);

        list($extension, $fileNameToStore, $fileData) = $list;

        $data_url = CheckExtensionServices::checkExtension($fileData,$extension);

        //拡張⼦ごとに base64エンコード実施
        if ($extension = 'jpg'){
        $data_url = 'data:image/jpg;base64,'. base64_encode($fileData);
        }
        if ($extension = 'jpeg'){
        $data_url = 'data:image/jpg;base64,'. base64_encode($fileData);
        }
        if ($extension = 'png'){
        $data_url = 'data:image/png;base64,'. base64_encode($fileData);
        }
        if ($extension = 'gif'){
        $data_url = 'data:image/gif;base64,'. base64_encode($fileData);
        }
        //画像アップロード(Imageクラス makeメソッドを使⽤)
        $image = Image::make($data_url);

        //画像を横400px, 縦400pxにリサイズし保存
        $image->resize(400,400)->save(storage_path() . '/app/public/images/' . $fileNameToStore );
        
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'self_introduction' => $data['self_introduction'],
            'sex' => $data['sex'],
            'img_name' => $fileNameToStore,
        ]);
    }
}
