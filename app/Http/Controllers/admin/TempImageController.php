<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

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

        $imageName = Str::uuid().'.'.$image->getClientOriginalExtension();

        $basePath = public_path('uploads/temp');
        $thumbPath = public_path('uploads/temp/thumb');

        if (!file_exists($basePath)) {
            mkdir($basePath, 0755, true);
        }

        if (!file_exists($thumbPath)) {
            mkdir($thumbPath, 0755, true);
        }

        // Save DB record
        $tempImage = TempImage::create([
            'name' => $imageName,
        ]);

        // Move original
        $image->move($basePath, $imageName);

         // Create thumbnail
        try {
            $manager = new ImageManager(new Driver());
            $img = $manager->read($basePath.'/'.$imageName);
            $img->coverDown(300, 300);
            $img->save($thumbPath.'/'.$imageName);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Image processing failed',
            ], 500);
        }

        return response()->json([
            'status' => true,
            'data' => $tempImage,
            'message' => 'Image uploaded successfully.',
        ], 200);
    }
}
