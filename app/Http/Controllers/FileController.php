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
use Illuminate\Support\Str;

class FileController extends Controller
{
    protected $bucketName;

    public function __construct()
    {
        $this->bucketName = env('OSS_BUCKET_NAME');
    }

    public function uploadAvatar(Request $request)
    {
        $file = $request->file('file');

        $avatarName = Str::random(32) . time();

        $fullName = $avatarName . "." . $file->getClientOriginalExtension();

        $fullPath = 'avatar/' . $fullName;

        OssService::publicUpload($this->bucketName, $fullPath, $file->getPathname());

        $ossPath = OssService::getPublicObjectURL($this->bucketName, $fullPath);

        return response()->json($ossPath);
    }
}