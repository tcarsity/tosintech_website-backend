<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class TempImageController extends Controller
{
    public function tempImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()->first('image'),
            ], 422);
        }

        $image = $request->file('image');

        $extension = $image->extension();
        $imageName = Str::uuid()->toString() . '.' . $extension;

        $basePath = public_path('uploads/temp');

        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }

        $image->move($basePath, $imageName);

        $tempImage = TempImage::create([
            'name' => $imageName,
        ]);


        return response()->json([
            'status' => true,
            'data' => $tempImage,
            'message' => 'Image uploaded successfully.',
        ], 200);
    }
}
