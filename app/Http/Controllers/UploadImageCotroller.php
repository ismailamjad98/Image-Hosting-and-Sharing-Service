<?php

namespace App\Http\Controllers;

use App\Models\UploadImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadImageCotroller extends Controller
{
    //Functions to convert base64 back to image
    public function getBytes($data)
    {
        for ($count = 0; $count < strlen($data); $count += 2)
            $bytes[] = chr(hexdec(substr($data, $count, 2)));
        return implode($bytes);
    }

    public function getExtensuon($data)
    {
        $extensionArray = array(
            "jpeg" => "FFD8",
            "png" => "89504E470D0A1A0A",
            "gif" => "474946",
        );

        foreach ($extensionArray as $ext => $hexbytes) {
            $bytes = $this->getBytes($hexbytes);
            if (substr($data, 0, strlen($bytes)) == $bytes)
                return $ext;
        }
        return NULL;
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required',
            'status' => 'required'
        ]);
        //change image to bse 64
        $picture = $request->image;
        $trimmer = explode(",", $picture);

        foreach ($trimmer as $value) {
            $imagedata = trim($value);
        }
        //Get Extension
        $imgdata = base64_decode($imagedata);
        $extension = $this->getExtensuon($imgdata);
        $imagedata = str_replace(' ', '+', $imagedata);
        $imageName = date('YmdHis') . 'picture.' . $extension;
        $imagePath =  asset('storage') . '/' . $imageName;
        $check =  Storage::disk('image')->put($imageName, base64_decode($imagedata));
        //call a helper function to decode user id
        $userID = DecodeUser($request);
        //if user is logged in get UserId
        if (isset($userID)) {
            UploadImage::create([
                'image' => $imageName,
                'link' => $imagePath,
                'user_id' => $userID,
                'status' => $request->status
            ]);
        }
        //if user is not logged in
        if (!isset($userID)) {
            UploadImage::create([
                'image' => $imageName,
                'link' => $imagePath,
                'status' => $request->status
            ]);
        }
        //message on Success
        return response([
            'message' => 'Image Upload successfully',
            'shareable Link' => $imagePath
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

    public function searchImage(Request $request, $image_name)
    {
        $image_name = $request->image;

        $image = UploadImage::where('status', 'public')->where('image', 'LIKE', '%' . $image_name . '%')->orWhere('link', 'LIKE', '%' . $image_name . '%')->orWhere('created_at', 'LIKE', '%' . $image_name . '%')->orWhere('status', 'LIKE', '%' . $image_name . '%')->get();
        if (count($image) > 0)
            return response(['Images' => $image], 200);
        else {
            return response(['Images' => 'No Details found. Try to search again !'], 200);
        }
    }
}
