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
     * Retrieve a list of Projects & Activities.
     *
     * This endpoint fetches all content entries where the `page` is set to "ProAct".
     * If no content is found, it returns an error message.
     *
     * @response 200 {
     *   "success": 1,
     *   "message": "Project & Activities List",
     *   "data": [
     *     {
     *       "id": 1,
     *       "page": "ProAct",
     *       "content": "Sample content",
     *       "image_url": "https://example.com/image.jpg",
     *       "heading": "Sample Heading",
     *       "created_at": "2024-11-04T10:00:00.000000Z",
     *       "updated_at": "2024-11-04T10:00:00.000000Z"
     *     }
     *   ]
     * }
     *
     * @response 404 {
     *   "success": 0,
     *   "error": "No Content Found"
     * }
     */
    public function getProAct()
    {
        $contents = Content::where('page', 'ProAct')->get();
        if($contents->isEmpty())
        {
            return response()->json([
                'success' => 0,
                'error' => 'No Content Found',
            ],404);
        }
        return response()->json([
            'success' => 1,
            'message' => 'Project & Activities List',
            'data' => $contents
        ],200);
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
     * @response 201 {
     *   "success": 1,
     *   "message": "Project & Activities Content Added Successfully",
     *   "data": {
     *            "page": "ProAct",
     *               "content": "This is the api wh#ere you can add content for Project and Activities",
     *              "image_url": "https://res.cloudinary.com/douuxmaix/image/upload/v1730797140/kcqitrbuiev0e7cbbolc.webp",
     *               "heading": "Adding Project and Activities Heading",
     *              "updated_at": "2024-11-05T08:58:54.000000Z",
     *            "created_at": "2024-11-05T08:58:54.000000Z",
     *               "id": 4
     *  }
     * }
     * @resposne 422 {
     * "success": 0,
     * "error" : "Validation Error Message"
     * } 
     * @response 500 {
     * "success": 0,
     * "message": "Error while Adding Project and Activities",
     * "error": "Error Message"
     * }
     */

    public function addProAct(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'content'=>'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
            'heading' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first() // Get the first error message directly
            ], 422);
        }

        try {
            //code...
            $image = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
            $imageUrl = $uploadResponse['secure_url'];
    
            $content = Content::create([
                'page' => 'ProAct',
                'content' =>  str_replace("'", '#', $request->content),
                'image_url' => $imageUrl,
                'heading' => $request->heading
            ]);
            return response()->json([
                'success' => 1,
                'message' => 'Project & Activities Content Added Successfully',
                'data' => $content
            ],201);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => 0,
                'message' => 'Error while Adding Project and Activities',
                'error' => $e->getMessage()
            ], 500);
        }
        

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
     * @response 400 {
     *   "success": 0,
     *   "error": "Content found, but it does not belong to the Project and Activities page"
     * }
     * @response 500 {
     * "success": 0,
     * "message": "Error while Updating Project and Activities",
     * "error": "Error Message"
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

        try {
            //code...
            $content = Content::where('id', $request->id)->first();

            if (!$content) {
                return response()->json([
                    'success' => 0,
                    'error' => 'No data found with the provided Id'
                ]);
            }
            if ($content->page !== 'ProAct') {
                return response()->json([
                    'success' => 0,
                    'error' => 'Content found, but it does not belong to the Project and Activities page',
                ],400);
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
                'message' => 'Project & Activities Content Updated Successfully',
                'data' => $content
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => 0,
                'message' => 'Error while Adding Project and Activities',
                'error' => $e->getMessage()
            ], 500);
        }
    }




}
