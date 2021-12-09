<?php

namespace App\Http\Controllers;

use App\Models\AccessImage;
use App\Models\UploadImage;
use App\Models\User;
use Illuminate\Http\Request;

class AccessImageController extends Controller
{
    //
    public function givePermission(Request $request)
    {
        $request->validate([
            'access_to' => 'required|email',
            'link' => 'required'
        ]);
        //call a helper function to decode user id
        dd('ss');
        $userID = DecodeUser($request);
        $imageLink = UploadImage::where('link', $request->link)->first();
        $user = User::where('id', $userID)->first();
        $allUsers = User::where('email',$request->access_to)->first();
        if ($user->email == $request->access_to) {
            return ['Message' => 'you cannot give permission to yourself', 'link' => $imageLink->link];
        }
        if ($allUsers == null) {
            return ['User not Exists'];
        }
        AccessImage::create([
            'access_to' => $request->access_to,
            'access_by' => $userID,
            'link' => $imageLink->link,
        ]);
        return response(['message' => 'Permission has been granted'], 200);
    }

    public function ViewImage(Request $request)
    {
        //call a helper function to decode user id
        $userID = DecodeUser($request);
        $request->validate([
            'link' => 'required',
        ]);
        $viewImage = UploadImage::where('link', $request->link)->first();
        $user = User::where('id', $userID)->first();
        $access = AccessImage::where('link', $request->link)->where('access_to', $user->email)->first();
        if ($viewImage == null) {
            return response(['message' => 'Please Enter Valid Link']);
        }
        // dd($access);
        if ($user->email == $user->email) {
            return response(['Your Image to View' => $viewImage->link]);
        }
        // dd($access);
        if ($access == null) {
            return response(['message' => 'you dont have permission!. Please grant for Access']);
        }
        if ($viewImage->status == 'hidden' && $access->link == $request->link) {
            return ['Message' => 'This Image is hidden'];
        }
        if ($access->access_to == $user->email) {
            return ['Link to View' => $viewImage->link];
        }
    }
}
