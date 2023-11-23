<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DomainController extends Controller
{
    public function getDomain(Request $request){
        $domainId=$request->input('domainId');

        try{
            $domain=DB::table('cms_domain')->where('domain_id', $domainId)->first();

            $data=[
                'status'=>1,
                'domainName' => $domain->domain_name,
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

    public function editDomain(Request $request){
        $domainId=$request->input('domainId');
        $domainName=$request->input('domainName');

        // Check if the domain already exists and is active
        $existingDomain = DB::table('cms_domain')
                            ->where('domain_name', $domainName)
                            ->where('is_active', 'ACTIVE')
                            ->where('domain_id', '!=', $domainId)
                            ->first();

        if ($existingDomain) {

            $data=[
                'status'=>-1,
                'return'=>'Domain Name Already Taken'
            ];

            return $data;
        }

        try{
            DB::table('cms_domain')->where('domain_id',$domainId)->update([
                'domain_name'=>$domainName,
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

    public function removeDomain(Request $request){
        $domainId=$request->input('domainId');

        try{
            DB::table('cms_domain')->where('domain_id',$domainId)->update([
                'is_active'=>'INACTIVE'
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

    // public function registerDomain(Request $request)
    // {
    //     $domainName = $request->input('domainName');

    //     // Check if the domain already exists and is active
    //     $existingDomain = DB::table('cms_domain')
    //                         ->where('domain_name', $domainName)
    //                         ->where('is_active', 'ACTIVE')
    //                         ->first();

    //     if ($existingDomain) {

    //         $data=[
    //             'status'=>-1,
    //             'message'=>'Domain Name Already Taken'
    //         ];

    //         return $data;
    //     }

    //     // store the domain in the database here
    //     try{

    //         $saveDomain = DB::table('cms_domain')->insert([
    //             'domain_name' => $domainName,
    //             'created_at' => now(),
    //         ]);

    //         $data=[
    //             'status' => 1,
    //             'message' => 'Domain Saved Successfully'
    //         ];

    //     }catch(Exception $e){
    //         $data=[
    //             'status'=>-1,
    //             'return'=>$e->getMessage()
    //         ];
    //     }
    //     return $data;
    // }

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
}
