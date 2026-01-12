<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Build email body
        Mail::html (
             "<p><strong>Name:</strong> {$request->name}</p>
             <p><strong>Email:</strong> {$request->email}</p>
             <p><strong>Subject:</strong> {$request->subject}</p>
             <p>{$request->message}</p>",
            function ($message) {
                $message
                    ->to('tosinoluwaseun10@gmail.com')
                    ->subject('New Contact Message');
            }
        );

        return response()->json([
            'status' => true,
            'message' => 'Thanks for contacting us.'
        ]);
    }
}
