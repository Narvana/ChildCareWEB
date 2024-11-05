<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use  App\Models\Content;
// use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Cloudinary\Cloudinary;

class ContentController extends Controller
{
    /**
     * @group Project & Activities
     * 
     * Get Project & Activities List
     *
     * Fetch the list of all Projects & Activities.
     *
     * @response {
     *   "success": 1,
     *   "message": "Project & Activities List",
     *   "data": [
     *     {
     *       "id": 1,
     *       "page": "ProAct",
     *       "content": "Sample content",
     *       "image_url": "https://example.com/image.jpg",
     *       "heading": "Sample Heading"
     *     }
     *   ]
     * }
     */
    
    public function getProAct()
    {
        $contents = Content::where('page', 'ProAct')->get();
        return response()->json([
            'success' => 1,
            'message' => 'Project & Activities List',
            'data' => $contents
        ]);
    }


    /**
     * @group Project & Activities
     * 
     * Add a Project & Activity
     *
     * Adds a new Project & Activity with content, image, and heading.
     *
     * @bodyParam content string required The content for the Project & Activity. Example: "Project description here."
     * @bodyParam image file required The image file for the Project & Activity (jpeg, png, jpg, gif, webp, max: 5048kb).
     * @bodyParam heading string required The heading for the Project & Activity. Example: "New Project Heading"
     * @response {
     *   "success": 1,
     *   "message": "Project & Activities Added Successfully",
     *   "data": []
     * }
     */

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

    /**
     * @group Project & Activities
     * 
     * Edit a Project & Activity
     *
     * Updates an existing Project & Activity based on the provided ID.
     *
     * @bodyParam id integer required The ID of the Project & Activity to update. Example: 1
     * @bodyParam content string The updated content for the Project & Activity. Example: "Updated project content."
     * @bodyParam heading string The updated heading for the Project & Activity. Example: "Updated Project Heading"
     * @bodyParam image file The updated image file for the Project & Activity (jpeg, png, jpg, gif, webp, max: 5048kb).
     * @response {
     *   "success": 1,
     *   "message": "Project & Activities Updated Successfully",
     *   "data": {
     *     "id": 1,
     *     "page": "ProAct",
     *     "content": "Updated content",
     *     "image_url": "https://example.com/updated-image.jpg",
     *     "heading": "Updated Heading"
     *   }
     * }
     * @response 422 {
     *   "success": 0,
     *   "error": "Validation error message"
     * }
     * @response 404 {
     *   "success": 0,
     *   "error": "No data found with the provided Id"
     * }
     * @response 400 {
     *   "success": 0,
     *   "error": "Provide the field that you want to update"
     * }
     */

    public function editProAct(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'id'=>'required|exists:contents,id',
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
