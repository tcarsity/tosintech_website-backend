<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use App\Models\Project;
use App\Services\SupabaseStorageService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;


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
        $project = new Project();
        $project->title   = $request->title;
        $project->content = $request->content;
        $project->site    = $request->site;
        $project->status  = $request->status;
        $project->save(); // IMPORTANT: must exist before image name

        // 3️⃣ Handle image ONLY if imageId exists
        if ($request->filled('imageId') && $request->imageId > 0) {

            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {

                // filename
                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $fileName = time() . '_' . $project->id . '.' . $ext;

                // source temp image
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                if (file_exists($sourcePath)) {

                    $manager = new ImageManager(new Driver());

                    /**
                     * SMALL IMAGE
                     */
                    $smallTmpPath = storage_path('app/tmp_small_' . $fileName);
                    $image = $manager->read($sourcePath);
                    $image->coverDown(1108, 600)->save($smallTmpPath);

                    SupabaseStorageService::upload(
                        "projects/small/{$fileName}",
                        $smallTmpPath,
                        mime_content_type($smallTmpPath)
                    );

                    /**
                     * LARGE IMAGE
                     */
                    $largeTmpPath = storage_path('app/tmp_large_' . $fileName);
                    $image = $manager->read($sourcePath);
                    $image->scaleDown(1200)->save($largeTmpPath);

                    SupabaseStorageService::upload(
                        "projects/large/{$fileName}",
                        $largeTmpPath,
                        mime_content_type($largeTmpPath)
                    );

                    // cleanup local temp files
                    @unlink($smallTmpPath);
                    @unlink($largeTmpPath);

                    // 4️⃣ Save image name to project (THIS WAS THE PROBLEM)
                    $project->image = $fileName;
                    $project->save();
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
        $project->title = $request->title;
        $project->content = $request->content;
        $project->site = $request->site;
        $project->status = $request->status;
        $project->save();

        // image replacement
        if ($request->filled('imageId') && $request->imageId > 0) {

            $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {

                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $fileName = time() . '_' . $project->id . '.' . $ext;
                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                $manager = new ImageManager(Driver::class);

                /** SMALL IMAGE */
                $smallTmp = storage_path("app/tmp_small_$fileName");
                $image = $manager->read($sourcePath);
                $image->coverDown(640, 420)->save($smallTmp);

                SupabaseStorageService::upload(
                    "projects/small/$fileName",
                    $smallTmp,
                    mime_content_type($smallTmp)
                );

                /** LARGE IMAGE */
                $largeTmp = storage_path("app/tmp_large_$fileName");
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200)->save($largeTmp);


                SupabaseStorageService::upload(
                    "projects/large/$fileName",
                    $largeTmp,
                    mime_content_type($largeTmp)
                );

                // cleanup local temp
                @unlink($smallTmp);
                @unlink($largeTmp);

                // update db image
                $project->image = $fileName;
                $project->save();

                // delete old images AFTER successful update
                if ($oldImage) {
                    SupabaseStorageService::delete("projects/small/$oldImage");
                    SupabaseStorageService::delete("projects/large/$oldImage");
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
