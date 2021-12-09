<?php

namespace App\Http\Controllers;

use App\Models\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Jorenvh\Share\ShareFacade;

class UploadImageCotroller extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'status' => 'required'
        ]);
        if ($image = $request->file('image')) {
            //make a path to store image
            $destinationPath = 'E:/ImageHosting-Copy/public/Upload_Images/';
            //change the image name for no duplication of same name
            $upload = time() . $image->getClientOriginalName();
            //store file in a provided path
            $image->move($destinationPath, $upload);
        }
        //call a helper function to decode user id
        $userID = DecodeUser($request);
        //create a shareable link
        $link = $destinationPath . time() .$image->getClientOriginalName();
        //if user is logged in get UserId
        if (isset($userID)) {
            UploadImage::create([
                'image' => $upload,
                'link' => $link,
                'user_id' => $userID,
                'status' => $request->status
            ]);
        }
        //if user is not logged in
        if (!isset($userID)) {
            UploadImage::create([
                'image' => $upload,
                'link' => $link,
                'status' => $request->status
            ]);
        }
        //message on Success
        return response([
            'message' => 'Image Upload successfully',
            'shareable Link' => $link
        ], 200);
    }

    public function deleteImage($id)
    {
        if (UploadImage::where('id', '=', $id)->delete($id)) {
            return response([
                'Status' => '200',
                'message' => 'Image Deleted successfully'
            ], 200);
        } else {
            return response([
                'message' => 'Not Found.'
            ], 200);
        }
    }

    // public function renameImage(Request $request, $id)
    // {
    //     $rename = UploadImage::all()->where('id', $id)->first();
    //     if (isset($rename)) {
    //         UploadImage::where('id', $id)->update(['image' => $request->image]);
    //         return response(['message' => 'Renamed Successfully'], 200);
    //     }
    //     if (!isset($rename)) {
    //         return response([
    //             'message' => 'No File Exists'
    //         ], 200);
    //     }
    // }

    public function myImages(Request $request)
    {
        //call a helper function to decode user id
        $userID = DecodeUser($request);
        $my_images = UploadImage::all()->where('user_id', $userID);

        if (json_decode($my_images) == null) {
            return response(['Images' => 'No Image'], 200);
        }
        //message on Successfully
        if ($my_images) {
            return response(['Images' => $my_images], 200);
        }
    }

    public function searchImage(Request $request , $image_name)
    {
        $image_name = $request->image;

        $image = UploadImage::where('status','public')->where('image', 'LIKE', '%' . $image_name . '%')->orWhere('link', 'LIKE', '%' . $image_name . '%')->orWhere('created_at', 'LIKE', '%' . $image_name . '%')->orWhere('status', 'LIKE', '%' . $image_name . '%')->get();
        if (count($image) > 0)
            return response(['Images' => $image], 200);
        else {
            return response(['Images' => 'No Details found. Try to search again !'], 200);
        }
    }
}


