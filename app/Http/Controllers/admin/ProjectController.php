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
    public function store(Request $request){

        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'content' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 401,
                'errors' => $validator->errors()
            ], 401);
        }

        $project = new Project();
        $project->title = $request->title;
        $project->content = $request->content;
        $project->site = $request->site;
        $project->status = $request->status;
        $project->save();


        if ($request->imageId > 0) {

            $tempImage = TempImage::find($request->imageId);

            if ($tempImage) {

                $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
                $fileName = time() . '_' . $project->id . '.' . $ext;

                $sourcePath = public_path('uploads/temp/' . $tempImage->name);

                // SMALL
                $smallPath = storage_path('app/tmp_small_' . $fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath);
                $image->coverDown(1108, 600)->save($smallPath);

                SupabaseStorageService::upload(
                    "projects/small/$fileName",
                    $smallPath,
                    mime_content_type($smallPath)
                );


                // LARGE
                $largePath = storage_path('app/tmp_large_' . $fileName);
                $image = $manager->read($sourcePath);
                $image->scaleDown(1200)->save($largePath);

                SupabaseStorageService::upload(
                    "projects/large/$fileName",
                    $largePath,
                    mime_content_type($largePath)
                );

                // cleanup temp files
                @unlink($smallPath);
                @unlink($largePath);

                $project->image = $fileName;
                $project->save();
            }
        }


         return response()->json([
                'status' => 200,
                'message' => 'Project added successfully.'
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

    public function update($id, Request $request){

        $project =  Project::find($id);

        if(!$project){
             return response()->json([
                'status' => 404,
                'message' => 'Project not found'
            ], 404);
        }


        $validator = Validator::make($request->all(),[
            'title' => 'required',
            'content' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 401,
                'errors' => $validator->errors()
            ], 401);
        }


        $project->title = $request->title;
        $project->content = $request->content;
        $project->site = $request->site;
        $project->status = $request->status;
        $project->save();


       if ($request->imageId > 0) {

            $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);

        if ($tempImage) {

            $ext = pathinfo($tempImage->name, PATHINFO_EXTENSION);
            $fileName = time() . '_' . $project->id . '.' . $ext;

            $sourcePath = public_path('uploads/temp/' . $tempImage->name);

            $manager = new ImageManager(new Driver());

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

            // cleanup temp files
            @unlink($smallTmp);
            @unlink($largeTmp);

            // save new image
            $project->image = $fileName;
            $project->save();

            // delete old images from Supabase
            if ($oldImage) {
                SupabaseStorageService::delete("projects/small/$oldImage");
                SupabaseStorageService::delete("projects/large/$oldImage");
            }
        }
    }

            return response()->json([
            'status' => 200,
            'message' => 'Project updated successfully.'
        ], 200);


}

        public function destroy($id){
            $project =  Project::find($id);

            if($project == null){
                    return response()->json([
                    'status' => 404,
                    'message' => 'Project not found'
                ], 404);
            }

                SupabaseStorageService::delete("projects/large/$oldImage");
                SupabaseStorageService::delete("projects/small/$oldImage");

                $project->delete();

                return response()->json([
                    'status' => 200,
                    'message' => 'Project deleted successfully.'
                ], 200);
        }

}
