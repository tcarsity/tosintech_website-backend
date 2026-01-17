<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use App\Models\Project;
use App\Services\SupabaseStorageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;


class ProjectController extends Controller
{
    // This method will return all projects
    public function index(){

        $projects = Project::orderBy('created_at','DESC')->get();

        return response()->json([
                'status' => 200,
                'data' => $projects
            ], 200);
    }


    // This method will insert project in db
    public function store(Request $request)
    {

        // 1️⃣ Validate main fields
        $validator = Validator::make($request->all(), [
            'title'   => 'required',
            'content' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'errors' => $validator->errors(),
            ], 401);
        }

        // 2️⃣ Create project first (same as your logic)
        $project = Project::create([
            'title'  => $request->title,
            'content' => $request->content,
            'site'  => $request->site,
            'status'  => $request->status,
        ]);

        if ($request->filled('imageId') && (int) $request->imageId > 0) {

            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {

                // filename
                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                if(!$ext) {
                    $mime = mime_content_type($sourcePath);

                    $map = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];

                    $ext = $map[$mime] ?? 'jpg';
                }

                $fileName = time() . '_' . $project->id . '.' . $ext;

                // source temp image
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if(!file_exists($sourcePath)){
                        return response()->json([
                            'status' => false,
                            'errors' => 'Temp image file not found'
                        ], 422);
                    }


                if (file_exists($sourcePath)) {

                    SupabaseStorageService::upload(
                        "projects/small/{$fileName}",
                        $sourcePath,
                        mime_content_type($sourcePath)
                    );

                    SupabaseStorageService::upload(
                        "projects/large/{$fileName}",
                        $sourcePath,
                        mime_content_type($sourcePath)
                    );

                    $project->update(['image' =>  $fileName]);
                }
            }
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Project added successfully.',
        ], 200);
    }


    // This method will insert project in db
    public function show($id){
        $project =  Project::find($id);

        if($project == null){
             return response()->json([
                'status' => 404,
                'message' => 'Project not found'
            ], 404);
        }

         return response()->json([
                'status' => 200,
                'data' => $project
            ], 200);
    }


    public function update($id, Request $request)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'status' => 404,
                'message' => 'Project not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // update fields
        $project->update([
            'title'  => $request->title,
            'content' => $request->content,
            'site'  => $request->site,
            'status'  => $request->status,
        ]);


        // image replacement
            if ($request->filled('imageId') && (int) $request->imageId > 0) {

                $oldImage = $project->image;
                $tempImage = TempImage::find($request->imageId);

                if ($tempImage) {

                    $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);

                    if(!$ext) {
                    $mime = mime_content_type($sourcePath);

                    $map = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/webp' => 'webp',
                        'image/gif' => 'gif',
                    ];

                    $ext = $map[$mime] ?? 'jpg';
                }

                    $fileName = time() . '_' . $project->id . '.' . $ext;


                    $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                    if(!file_exists($sourcePath)){
                        return response()->json([
                            'status' => false,
                            'errors' => 'Temp image file not found'
                        ], 422);
                    }

                    if (file_exists($sourcePath)) {

                        SupabaseStorageService::upload(
                            "projects/small/{$fileName}",
                            $sourcePath,
                            mime_content_type($sourcePath)
                        );

                        SupabaseStorageService::upload(
                            "projects/large/{$fileName}",
                            $sourcePath,
                            mime_content_type($sourcePath)
                        );

                        $project->update(['image' =>  $fileName]);


                    // delete old images AFTER successful update
                    if ($oldImage) {
                        SupabaseStorageService::delete("projects/small/$oldImage");
                        SupabaseStorageService::delete("projects/large/$oldImage");
                    }
                }
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Project updated successfully.'
        ], 200);
    }


    public function destroy($id){
        $project =  Project::find($id);

        if(!$project){
                return response()->json([
                'status' => 404,
                'message' => 'Project not found'
            ], 404);
        }

            SupabaseStorageService::delete("projects/large");
            SupabaseStorageService::delete("projects/small");

            $project->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Project deleted successfully.'
            ], 200);
    }

}
