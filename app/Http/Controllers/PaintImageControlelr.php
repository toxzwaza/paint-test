<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PaintImageControlelr extends Controller
{
    //
    public function index()
    {

        return Inertia::render('PaintImage/Index');
    }
    public function store(Request $request)
    {
        // 画像データをBase64からデコード
        $imageData = $request->input('image');
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = base64_decode($imageData);

        // 画像ファイルを保存
        $fileName = 'annotated_image_' . time() . '.png';
        Storage::disk('public')->put($fileName, $imageData);

        return response()->json(['message' => 'Image saved successfully!', 'file_name' => $fileName]);
    }
}
