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

    /**
     * @group Environment
     * 
     * Retrieve a list of Environment.
     *
     * This endpoint fetches all content entries where the `page` is set to "Environment.
     * If no content is found, it returns an error message.
     *
     * @response 200 {
     *   "success": 1,
     *   "message": "Clean and Safe Environment List",
     *   "data": [
     *     {
     *       "id": 1,
     *       "page": "Environment",
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
     *   "error": "No Environment Content Found"
     * }
     */

    public function getEnvironment()
    {
        $contents = Content::where('page', 'Environment')->get();
        if($contents->isEmpty())
        {
            return response()->json([
                'success' => 0,
                'error' => 'No Environment Content Found',
            ],404);
        }
        return response()->json([
            'success' => 1,
            'message' => 'Clean and Safe Enviroment Content List',
            'data' => $contents
        ],200);
    }

    /**
     * @group Environment
     * 
     * Add Environment Content
     *
     * Adds a new Environment Content with content, image, and heading.
     *
     * @bodyParam content string required The content for the Environment. Example: "Environment description here."
     * @bodyParam image file required The image file for the Environment (jpeg, png, jpg, gif, webp, max: 5048kb).
     * @bodyParam heading string required The heading for the Environment. Example: "New Environment Heading"
     * @response 201 {
     *   "success": 1,
     *   "message": "Environment Content Added Successfully",
     *   "data": {
     *            "page": "Environment",
     *               "content": "This is the api where you can add content for Environment",
     *              "image_url": "https://res.cloudinary.com/douuxmaix/image/upload/v1730797140/kcqitrbuiev0e7cbbolc.webp",
     *               "heading": "Adding Environment Heading",
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
     * "message": "Error while Adding Environment Content",
     * "error": "Error Message"
     * }
     */

    public function addEnvironment(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'content'=>'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
            'heading' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => 0,
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            //code...
            $image = $request->file('image');
            $cloudinary = new Cloudinary();
            $uploadResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
            $imageUrl = $uploadResponse['secure_url'];
    
            $content=Content::create([
                'page' => 'Environment',
                'content' =>  str_replace("'", '#', $request->content),
                'image_url' => $imageUrl,
                'heading' => $request->heading
            ]);
    
            return response()->json([
                'success' => 1,
                'message' => 'Clean and Safe Environment Content Added Successfully',
                'data' =>  $content
            ]);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => 0,
                'message' => 'Error while Adding Enviroment Content',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @group Environment
     * 
     * Edit a Environment
     *
     * Updates an existing Environment based on the provided ID.
     *
     * @bodyParam id integer required The ID of the Environment to update. Example: 1
     * @bodyParam content string The updated content for the Environment. Example: "Updated Environment content."
     * @bodyParam heading string The updated heading for the Environment. Example: "Updated Environment Heading"
     * @bodyParam image file The updated image file for the Environment (jpeg, png, jpg, gif, webp, max: 5048kb).
     * @response {
     *   "success": 1,
     *   "message": "Environment Updated Successfully",
     *   "data": {
     *     "id": 1,
     *     "page": "Environment",
     *     "content": "Updated Environment content",
     *     "image_url": "https://example.com/updated-image.jpg",
     *     "heading": "Updated Environment Heading"
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
     *   "error": "Content found, but it does not belong to the Environment page"
     * }
     * @response 500 {
     * "success": 0,
     * "message": "Error while Updating Environment",
     * "error": "Error Message"
     * }
     */

    public function editEnvironment(Request $request)
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

            if($content->page !== 'Environment')
            {
                return response()->json([
                    'success' => 0,
                    'error' => 'Content found, but it does not belong to the Environment page',
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


    /**
     * @group HomeReading
     * 
     * Retrieve a list of Home and Reading List.
     *
     * This endpoint fetches all content entries where the `page` is set to "HomeReading".
     * If no content is found, it returns an error message.
     *
     * @response 200 {
     *   "success": 1,
     *   "message": "Home and Reading List",
     *   "data": [
     *     {
     *       "id": 1,
     *       "page": "HomeReading",
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
     *   "error": "No Home and Reading Content Found"
     * }
     */

     public function getHomeReading()
     {
         $contents = Content::where('page', 'HomeReading')->get();
         if($contents->isEmpty())
         {
             return response()->json([
                 'success' => 0,
                 'error' => 'No Home and Reading Content Found',
             ],404);
         }
         return response()->json([
             'success' => 1,
             'message' => 'Home and Reading Enviroment Content List',
             'data' => $contents
         ],200);
     }
 
     /**
      * @group HomeReading
      * 
      * Add Home and Reading Content
      *
      * Adds a new Home and Reading Content with content, image, and heading.
      *
      * @bodyParam content string required The content for the Home and Reading. Example: "Home and Reading description here."
      * @bodyParam image file required The image file for the Home and Reading (jpeg, png, jpg, gif, webp, max: 5048kb).
      * @bodyParam heading string required The heading for the Home and Reading. Example: "New Environment Heading"
      * @response 201 {
      *   "success": 1,
      *   "message": "Home and Reading Content Added Successfully",
      *   "data": {
      *            "page": "HomeReading",
      *            "content": "This is the api where you can add content for Home and Reading",
      *            "image_url": "https://res.cloudinary.com/douuxmaix/image/upload/v1730797140/kcqitrbuiev0e7cbbolc.webp",
      *            "heading": "Adding Home and Reading Heading",
      *            "updated_at": "2024-11-05T08:58:54.000000Z",
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
      * "message": "Error while Adding Home and Reading Content",
      * "error": "Error Message"
      * }
      */
 
     public function addHomeReading(Request $request)
     {
         $validator=Validator::make($request->all(),[
             'content'=>'required',
             'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
             'heading' => 'required'
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'success' => 0,
                 'error' => $validator->errors()->first()
             ], 422);
         }
 
         try {
             //code...
             $image = $request->file('image');
             $cloudinary = new Cloudinary();
             $uploadResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
             $imageUrl = $uploadResponse['secure_url'];
     
             $content=Content::create([
                 'page' => 'HomeReading',
                 'content' =>  str_replace("'", '#', $request->content),
                 'image_url' => $imageUrl,
                 'heading' => $request->heading
             ]);
     
             return response()->json([
                 'success' => 1,
                 'message' => 'Home and Reading Content Added Successfully',
                 'data' =>  $content
             ]);
         } catch (\Exception $e) {
             // Handle any exceptions
             return response()->json([
                 'success' => 0,
                 'message' => 'Error while Adding Home and Reading Content',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
 
     /**
      * @group HomeReading
      * 
      * Edit a Home and Reading
      *
      * Updates an existing Home and Reading based on the provided ID.
      *
      * @bodyParam id integer required The ID of the Home and Reading to update. Example: 1
      * @bodyParam content string The updated content for the Home and Reading Content. Example: "Updated Home and Reading content."
      * @bodyParam heading string The updated heading for the Home and Reading Content. Example: "Updated Home and Reading Heading"
      * @bodyParam image file The updated image file for the Home and Reading Content (jpeg, png, jpg, gif, webp, max: 5048kb).
      * @response {
      *   "success": 1,
      *   "message": "Home and Reading Content Updated Successfully",
      *   "data": {
      *     "id": 1,
      *     "page": "HomeReading",
      *     "content": "Updated Home and Reading content",
      *     "image_url": "https://example.com/updated-image.jpg",
      *     "heading": "Updated Home and Reading Heading"
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
      *   "error": "Content found, but it does not belong to the HomeReading page"
      * }
      * @response 500 {
      * "success": 0,
      * "message": "Error while Updating Home and Reading Content",
      * "error": "Error Message"
      * }
      */
 
     public function editHomeReading(Request $request)
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
 
             if($content->page !== 'HomeReading')
             {
                 return response()->json([
                     'success' => 0,
                     'error' => 'Content found, but it does not belong to the Home and Reading page',
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
                'message' => 'Home and Reading Content Updated Successfully',
                'data' => $content
             ]);
         } catch (\Exception $e) {
             // Handle any exceptions
             return response()->json([
                 'success' => 0,
                 'message' => 'Error while Updated Home and Reading',
                 'error' => $e->getMessage()
             ], 500);
         }
     }

     /**
     * @group MusicMovement
     * 
     * Retrieve a list of Music and Movement List.
     *
     * This endpoint fetches all content entries where the `page` is set to "MusicMovement".
     * If no content is found, it returns an error message.
     *
     * @response 200 {
     *   "success": 1,
     *   "message": "Music and Movement List",
     *   "data": [
     *     {
     *       "id": 1,
     *       "page": "MusicMovement",
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
     *   "error": "No Music and Movement Content Found"
     * }
     */

     public function getMusicMovement()
     {
         $contents = Content::where('page', 'MusicMovement')->get();
         if($contents->isEmpty())
         {
             return response()->json([
                 'success' => 0,
                 'error' => 'No Music and Movement Content Found',
             ],404);
         }
         return response()->json([
             'success' => 1,
             'message' => 'Music and Movement Content List',
             'data' => $contents
         ],200);
     }
 
     /**
      * @group MusicMovement
      * 
      * Add Music and Movement Content
      *
      * Adds a new Music and Movement Content with content, image, and heading.
      *
      * @bodyParam content string required The content for the Music and Movement. Example: "Home and Reading description here."
      * @bodyParam image file required The image file for the Music and Movement (jpeg, png, jpg, gif, webp, max: 5048kb).
      * @bodyParam heading string required The heading for the Music and Movement. Example: "New Music and Movement Heading"
      * @response 201 {
      *   "success": 1,
      *   "message": "Music and Movement Content Added Successfully",
      *   "data": {
      *            "page": "MusicMovement",
      *            "content": "This is the api where you can add content for Music and Movement",
      *            "image_url": "https://res.cloudinary.com/douuxmaix/image/upload/v1730797140/kcqitrbuiev0e7cbbolc.webp",
      *            "heading": "Adding Music and Movement Heading",
      *            "updated_at": "2024-11-05T08:58:54.000000Z",
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
      * "message": "Error while Adding Music and Movement Content",
      * "error": "Error Message"
      * }
      */
 
     public function addMusicMovement(Request $request)
     {
         $validator=Validator::make($request->all(),[
             'content'=>'required',
             'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5048',
             'heading' => 'required'
         ]);
 
         if ($validator->fails()) {
             return response()->json([
                 'success' => 0,
                 'error' => $validator->errors()->first()
             ], 422);
         }
 
         try {
             //code...
             $image = $request->file('image');
             $cloudinary = new Cloudinary();
             $uploadResponse = $cloudinary->uploadApi()->upload($image->getRealPath());
             $imageUrl = $uploadResponse['secure_url'];
     
             $content=Content::create([
                 'page' => 'MusicMovement',
                 'content' =>  str_replace("'", '#', $request->content),
                 'image_url' => $imageUrl,
                 'heading' => $request->heading
             ]);
     
             return response()->json([
                 'success' => 1,
                 'message' => 'Music and Movement Content Added Successfully',
                 'data' =>  $content
             ]);
         } catch (\Exception $e) {
             // Handle any exceptions
             return response()->json([
                 'success' => 0,
                 'message' => 'Error while Adding Music and Movement Content',
                 'error' => $e->getMessage()
             ], 500);
         }
     }
 
     /**
      * @group MusicMovement
      * 
      * Edit a Music and Movement
      *
      * Updates an existing Music and Movement based on the provided ID.
      *
      * @bodyParam id integer required The ID of the Music and Movement to update. Example: 1
      * @bodyParam content string The updated content for the Music and Movement Content. Example: "Updated Music and Movement content."
      * @bodyParam heading string The updated heading for the Music and Movement Content. Example: "Updated Music and Movement Heading"
      * @bodyParam image file The updated image file for the Music and Movement Content (jpeg, png, jpg, gif, webp, max: 5048kb).
      * @response {
      *   "success": 1,
      *   "message": "Music and Movement Content Updated Successfully",
      *   "data": {
      *     "id": 1,
      *     "page": "MusicMovement",
      *     "content": "Updated Music and Movement content",
      *     "image_url": "https://example.com/updated-image.jpg",
      *     "heading": "Updated Music and Movement Heading"
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
      *   "error": "Content found, but it does not belong to the HomeReading page"
      * }
      * @response 500 {
      * "success": 0,
      * "message": "Error while Updating Music and Movement Content",
      * "error": "Error Message"
      * }
      */

     public function editMusicMovement(Request $request)
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
 
             if($content->page !== 'MusicMovement')
             {
                 return response()->json([
                     'success' => 0,
                     'error' => 'Content found, but it does not belong to the Music and Movement page',
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
                'message' => 'Music and Movement Content Updated Successfully',
                'data' => $content
             ]);
         } catch (\Exception $e) {
             // Handle any exceptions
             return response()->json([
                 'success' => 0,
                 'message' => 'Error while Updated Music and Movement',
                 'error' => $e->getMessage()
             ], 500);
         }
     }


}
