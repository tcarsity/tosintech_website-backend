<?php

namespace App\Http\Controllers\front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ResendMailService;

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
        $html = "
            <h3>New Contact Message</h3>
            <p><strong>Name:</strong> {$request->name}</p>
            <p><strong>Email:</strong> {$request->email}</p>
            <p><strong>Subject:</strong> {$request->subject}</p>
            <p><strong>Message:</strong></p>
            <p>{$request->message}</p>
        ";

        // Send via Resend API
        ResendMailService::send(
            'tosinoluwaseun10@gmail.com',
            'Portfolio Contact Form',
            $html
        );

        return response()->json([
            'status' => true,
            'message' => 'Thanks for contacting me. I will get back to you shortly.'
        ]);
    }
}
