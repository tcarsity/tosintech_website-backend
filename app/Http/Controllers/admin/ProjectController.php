<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use App\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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


        if($request->imageId > 0){
           
            $tempImage = TempImage::find($request->imageId);
            if($tempImage != null){
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now').$project->id.'.'.$ext;


                // create small thumbnail here
                $sourcePath = public_path('uploads/temp/'.$tempImage->name);
                $destPath = public_path('uploads/projects/small/'.$fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath); 
                $image->coverDown(1108, 600);
                $image->save($destPath);

                 // create large thumbnail here
                $desPath = public_path('uploads/projects/large/'.$fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath); 
                $image->scaleDown(1200);
                $image->save($desPath);

                $project->image = $fileName;
                $project->save();


                // if($oldImage != ''){
                //     File::delete(public_path('uploads/services/large/'.$oldImage));
                //     File::delete(public_path('uploads/services/small/'.$oldImage));
                // }
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

        if($project == null){
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


        if($request->imageId > 0){
           $oldImage = $project->image;
            $tempImage = TempImage::find($request->imageId);
            if($tempImage != null){
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $fileName = strtotime('now').$project->id.'.'.$ext;


                // create small thumbnail here
                $sourcePath = public_path('uploads/temp/'.$tempImage->name);
                $desPath = public_path('uploads/projects/small/'.$fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath); 
                $image->coverDown(640, 420);
                $image->save($desPath);

                 // create large thumbnail here
                $desPath = public_path('uploads/projects/large/'.$fileName);
                $manager = new ImageManager(Driver::class);
                $image = $manager->read($sourcePath); 
                $image->scaleDown(1200);
                $image->save($desPath);

                $project->image = $fileName;
                $project->save();
            }

            if($oldImage != ''){
                File::delete(public_path('uploads/projects/large/'.$oldImage));
                File::delete(public_path('uploads/projects/small/'.$oldImage));
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

                File::delete(public_path('uploads/projects/large/'.$project->image));
                File::delete(public_path('uploads/projects/small/'.$project->image));

                $project->delete();

                return response()->json([
                    'status' => 200,
                    'message' => 'Project deleted successfully.'
                ], 200);
        }

}