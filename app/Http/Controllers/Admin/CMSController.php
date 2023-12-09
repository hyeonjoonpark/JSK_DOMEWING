<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CMSController extends Controller
{
    //This Controller will be used for Seller.

    public function loadSellerCMS(Request $request , $id){

        try{
            $user = DB::table('users')->where('remember_token',  $id)->first();

            if($user == NULL){
                Auth::logout();
                return redirect()->route('auth.login');
            }else if ( Auth::user()->id !== $user->id ){
                return redirect()->back();
            }

            $domain = DB::table('cms_domain')->where('user_id',  $user->id)->first();

            if($domain == null){
                $registerDomain = DB::table('cms_domain')->insert([
                    'domain_name' => $user->company,
                    'created_at' => now(),
                    'user_id' => $user->id,
                ]);

                $domain = DB::table('cms_domain')->where('user_id', $user->id)->first();
            }

            $images = DB::table('image_banner')->where('domain_id', $domain->domain_id)->where('status', '!=', 'INACTIVE')->get();

            $image_banners = DB::table('image_banner')->where('domain_id', $domain->domain_id)->where('status', 'ACTIVE')->where('status', '!=', 'INACTIVE')->get();

            $theme_color = DB::table('theme_color')->where('domain_id',  $domain->domain_id)->first();

            foreach ($images as $image) {
                $image->formatted_created_at = Carbon::parse($image->created_at)->format('d M Y');
            }

            return view('admin/cms_seller',[
                'domain' => $domain,
                'images' => $images,
                'image_banners' => $image_banners,
                'theme_color' => $theme_color,
            ]);

        }catch (Exception $e){
            Auth::logout();
            return redirect()->route('auth.login');
        }
    }

    public function editDomainName(Request $request){
        $remember_token=$request->input('remember_token');
        $domain_name=$request->input('domain_name');
        $domain_id=$request->input('domain_id');

        $validator = Validator::make($request->all(),[
            'domain_name' => 'required',
        ], [
            'required' => 'This field is required.',
        ]);

        if ($validator->fails()) {

            $data=[
                'status'=>-1,
                'return'=>'Please Enter Domain Name'
            ];

            return $data;
        }

        $user = DB::table('users')->where('remember_token',  $remember_token)->first();

        $domainUser = DB::table('cms_domain')
                        ->join('users', 'users.id', '=', 'cms_domain.user_id')
                        ->where('cms_domain.domain_id', $domain_id)
                        ->value('users.remember_token');

        if($user == NULL){
            Auth::logout();
            return redirect()->route('auth.login');
        }else if($domainUser != $remember_token){
            $data=[
                'status'=>-1,
                'return'=>'No Permission to Change Domain Name'
            ];

            return $data;
        }

        $domain = DB::table('cms_domain')->where('user_id',  $user->id)->first();

        //Check if the domain already exists and is active
        $existingDomain = DB::table('cms_domain')
                            ->where('domain_name', $domain_name)
                            ->where('is_active', 'ACTIVE')
                            ->where('domain_id', '!=', $domain->domain_id)
                            ->first();

        if ($existingDomain) {

            $data=[
                'status'=>-1,
                'return'=>'Domain Name Already Taken'
            ];

            return $data;
        }

        try{
            DB::table('cms_domain')->where('domain_id',$domain->domain_id)->update([
                'domain_name'=>$domain_name,
                'updated_at'=>now(),
            ]);
            $data=[
                'status'=>1,
                'return'=>'Information Updated'
            ];
        }catch (Exception $e){
            $data=[
                'status'=>-1,
                'return'=>$e->getMessage()
            ];
        }

        return $data;

    }

    public function uploadImageBanner(Request $request){
        $image = $request->file('file');
        $domainId = $request->input('domain_id');
        $remember_token = $request->input('remember_token');

        //assuming only seller can edit thier content only
        $domainUser = DB::table('cms_domain')
                        ->join('users', 'users.id', '=', 'cms_domain.user_id')
                        ->select('cms_domain.*', 'users.remember_token')
                        ->where('cms_domain.domain_id', $domainId)
                        ->first();

        $user = DB::table('users')->where('remember_token',  $remember_token)->first();

        if($user == NULL){
            Auth::logout();
            return redirect()->route('auth.login');

        }else if($domainUser->remember_token != $remember_token){

            $data=[
                'status'=>-1,
                'message'=>'No Permission To Upload'
            ];

            return $data;
        }

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
        $remember_token = $request->input('remember_token');

        $user = DB::table('users')->where('remember_token',  $remember_token)->first();

        $imageUser = DB::table('image_banner')
                        ->join('cms_domain', 'cms_domain.domain_id', '=', 'image_banner.domain_id')
                        ->join('users', 'users.id', '=', 'cms_domain.user_id')
                        ->where('image_banner.id', $image_id)
                        ->value('users.remember_token');

        if($user == NULL){

            Auth::logout();
            return redirect()->route('auth.login');

        } else if($imageUser != $remember_token){

            $data=[
                'status'=>-1,
                'return'=>'No Permission on Current Action'
            ];

            return $data;
        }

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
        $remember_token=$request->input('remember_token');

        $user = DB::table('users')->where('remember_token',  $remember_token)->first();

        $imageUser = DB::table('image_banner')
                        ->join('cms_domain', 'cms_domain.domain_id', '=', 'image_banner.domain_id')
                        ->join('users', 'users.id', '=', 'cms_domain.user_id')
                        ->where('image_banner.id', $image_id)
                        ->value('users.remember_token');

        if($user == NULL){

            Auth::logout();
            return redirect()->route('auth.login');

        } else if($imageUser != $remember_token){

            $data=[
                'status'=>-1,
                'return'=>'No Permission on Current Action'
            ];

            return $data;
        }

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
        $remember_token = $request->input('remember_token');

        $domainUser = DB::table('cms_domain')
                        ->join('users', 'users.id', '=', 'cms_domain.user_id')
                        ->where('cms_domain.domain_id', $domain_id)
                        ->value('users.remember_token');

        $user = DB::table('users')->where('remember_token',  $remember_token)->first();

        if($user == NULL){

            Auth::logout();
            return redirect()->route('auth.login');

        }else if($domainUser != $remember_token){

            $data=[
                'status'=>-1,
                'return'=>'No Permission To Change Theme Color'
            ];

            return $data;
        }

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
