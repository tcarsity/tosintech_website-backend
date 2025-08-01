<?php

namespace App\Http\Controllers\front;

use App\Mail\ContactEmail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;


class ContactController extends Controller
{
    public function index (Request $request){
        
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required'
            
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];


        Mail::to('tosinoluwaseun10@gmail.com')->send(new ContactEmail($mailData));


        return response()->json([
                'status' => true,
                'message' => 'Thanks For Contacting Us.'
            ]);
    }
}
