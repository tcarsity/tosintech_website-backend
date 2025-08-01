<?php

namespace App\Http\Controllers\front;

use App\Models\Project;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    // This method will return all active projects
    public function index(){
        $projects = Project::where('status',1)->orderBy('created_at', 'DESC')->get();
        return response()->json([
            'status' => 200,
            'data' => $projects
        ], 200);
    }

    // This method will return latest active projects
    public function latestProjects(Request $request){
        $projects = Project::orderBy('created_at','DESC')
        ->where('status',1)
        ->limit($request->limit)
        ->get();

        return response()->json([
            'status' => 200,
            'data' => $projects
        ], 200);
    }

}
