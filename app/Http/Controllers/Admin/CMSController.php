<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CMSController extends Controller
{
    public function loadCMS(Request $request , $id){

        $domain = DB::table('cms_domain')->where('domain_id',  $id)->first();

        $images = DB::table('image_banner')->where('domain_id', $id)->where('status', '!=', 'INACTIVE')->get();

        $image_banners = DB::table('image_banner')->where('domain_id', $id)->where('status', 'ACTIVE')->where('status', '!=', 'INACTIVE')->get();

        $theme_color = DB::table('theme_color')->where('domain_id',  $id)->first();

        foreach ($images as $image) {
            $image->formatted_created_at = Carbon::parse($image->created_at)->format('d M Y');
        }

        return view('admin/content_management_system',[
            'domain' => $domain,
            'images' => $images,
            'image_banners' => $image_banners,
            'theme_color' => $theme_color,
        ]);
    }

    public function uploadImageBanner(Request $request)
    {
        $image = $request->file('file');
        $domainId = $request->input('domain_id');

        // Check if a file was uploaded
        if ($image) {

            $ext = $image->getClientOriginalExtension();
            $imageName = "IMG" . date('YmdHis') . "." . $ext;

            // Move the uploaded file to the public library directory
            $image->move(public_path('library'), $imageName);

            try{
                DB::table('image_banner')->insert([
                    'source' => $imageName,
                    'domain_id' => $domainId,
                    'created_at' => now(),
                ]);

                $data=[
                    'status'=>1,
                    'message'=>'Image Uploaded Successfully'
                ];

            }catch (Exception $e){
                $data=[
                    'status'=>-1,
                    'message'=>'Failed to Upload Image'
                ];
            }
        } else {
            $data=[
                'status'=>-1,
                'message'=>'No File Uploaded'
            ];
        }

        return $data;
    }

    public function changeImageStatus(Request $request){
        $image_id=$request->input('image_id');

        try{
            $newStatus = 'ACTIVE';
            $checkstatus = DB::table('image_banner')->where('id', $image_id)->value('status');

            if($checkstatus== 'ACTIVE'){
                $newStatus = 'HIDDEN';
            }else if($checkstatus== 'INACTIVE'){
                $data=[
                    'status'=>-1,
                    'return'=>'Image Not Found'
                ];

                return $data;
            }

            DB::table('image_banner')->where('id',$image_id)->update([
                'status'=> $newStatus
            ]);

            $data=[
                'status'=>1,
                'return'=>'Image Status Changed Successfully'
            ];
        }catch(Exception $e){
            $data=[
                'status'=>-1,
                'return'=>$e->getMessage()
            ];
        }


        return $data;
    }

    public function removeImage(Request $request){
        $image_id=$request->input('image_id');

        try{
            DB::table('image_banner')->where('id',$image_id)->update([
                'status'=>'INACTIVE'
            ]);
            $data=[
                'status'=>1,
                'return'=>'success'
            ];
        }catch(Exception $e){
            $data=[
                'status'=>-1,
                'return'=>$e->getMessage()
            ];
        }


        return $data;
    }

    public function changeThemeColor(Request $request){
        $color=$request->input('color');
        $domain_id=$request->input('domain_id');

        $checkColor=DB::table('theme_color')->where('domain_id', $domain_id)->first();

        if($checkColor == null){
            //insert color into table
            try{
                DB::table('theme_color')->insert([
                    'color_code' => $color,
                    'domain_id' => $domain_id,
                    'created_at' => now()
                ]);

                $data = [
                    'status'=>1,
                    'return'=>'Theme Color Set Successfully'
                ];
            }catch (Exception $e){
                $data = [
                    'status'=>-1,
                    'return'=>$e->getMessage()
                ];
            }
        }else{
            //update the color
            try{
                DB::table('theme_color')->where('id',$checkColor->id)->update([
                    'color_code' => $color,
                    'updated_at' => now()
                ]);

                $data = [
                    'status'=>1,
                    'return'=>'Theme Color Updated Successfully'
                ];
            }catch (Exception $e){
                $data = [
                    'status'=>-1,
                    'return'=>$e->getMessage()
                ];
            }
        }

        return $data;
    }
}
