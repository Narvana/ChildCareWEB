<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use  App\Models\Content;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Cloudinary;

class ContentController extends Controller
{
    //
    public function ProAct()
    {
        $contents = Content::where('page', 'ProAct')->get();
        return response()->json([
            'success' => 1,
            'message' => 'Project & Activities List',
            'data' => $contents
        ]);
    }

    public function addProAct(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'content'=>'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5048,webp',
            'heading' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first() // Get the first error message directly
            ], 422);
        }
        
        $image = $request->file('image');
        $cloudinary = new Cloudinary();
        $uploadResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
        $imageUrl = $uploadResponse['secure_url'];

        Content::create([
            'page' => 'ProAct',
            'content' =>  str_replace("'", '#', $request->content),
            'image_url' => $imageUrl,
            'heading' => $request->heading
        ]);
        // return redirect()->route('business')->with('success', 'Business added successfully');
        return response()->json([
            'success' => 1,
            'message' => 'Project & Activities Added Successfully',
            'data' => []
        ]);
    }

    public function editProAct(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:contents',
            'content'=>'sometimes',
            'heading' => 'sometimes',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first() // Get the first error message directly
            ], 422);
        }

        $content = Content::where('id', $request->id)->first();

        if (!$content) {
            return response()->json([
                'success' => 0,
                'error' => 'No data found with the provided Id'
            ]);
        }

        if(!$request->hasfile('image') && !$request->content && !$request->heading)
        {
            return response()->json([
                'success' => 0,
                'error' => 'Provide the field that you want to update'
            ]); 
        }

        if($request->hasFile('image')) {
            $cloudinary = new Cloudinary();
            $uploadResponse = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
            $imageUrl = $uploadResponse['secure_url'];
        } else {
            $imageUrl = $content->image_url;
        }
    
        $content->update([
            'content' => $request->content ? str_replace("'", '#', $request->content) : $content->content ,
            'image_url' => $imageUrl,
            'heading' => $request->heading ? $request->heading : $content->heading,
        ]);

        return response()->json([
            'success' => 1,
            'message' => 'Project & Activities Updated Successfully',
            'data' => $content
        ]);
    }


    


}
