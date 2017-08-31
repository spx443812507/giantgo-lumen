<?php
/**
 * Created by PhpStorm.
 * User: siler
 * Date: 2017/8/9
 * Time: 下午9:30
 */

namespace App\Http\Controllers;

use App\Services\OssService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    protected $bucketName;

    public function __construct()
    {
        $this->bucketName = env('OSS_BUCKET_NAME');
    }

    public function uploadAvatar(Request $request)
    {
        $fullPath = Storage::disk('public')->putFile('avatar', $request->file('file'), 'public');

        OssService::publicUpload($this->bucketName, $fullPath, storage_path('app/public/') . $fullPath);

        $ossPath = OssService::getPublicObjectURL($this->bucketName, $fullPath);

        return response()->json($ossPath);
    }
}