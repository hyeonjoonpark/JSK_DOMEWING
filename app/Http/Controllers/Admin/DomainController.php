<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DomainController extends Controller
{
    public function getDomain(Request $request){
        $domainId=$request->input('domainId');

        try{
            $domain=DB::table('cms_domain')->where('domain_id', $domainId)->first();

            $data=[
                'status'=>1,
                'companyName'=> $domain->company_name,
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
        $companyName=$request->input('companyName');
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
                'company_name'=>$companyName,
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

    public function registerDomain(Request $request)
    {
        $companyName = $request->input('companyName');
        $domainName = $request->input('domainName');

        // Check if the domain already exists and is active
        $existingDomain = DB::table('cms_domain')
                            ->where('domain_name', $domainName)
                            ->where('is_active', 'ACTIVE')
                            ->first();

        if ($existingDomain) {

            $data=[
                'status'=>-1,
                'message'=>'Domain Name Already Taken'
            ];

            return $data;
        }

        // store the domain in the database here
        try{

            $saveDomain = DB::table('cms_domain')->insert([
                'company_name' => $companyName,
                'domain_name' => $domainName,
                'created_at' => now(),
            ]);

            $data=[
                'status' => 1,
                'message' => 'Domain Saved Successfully'
            ];

        }catch(Exception $e){
            $data=[
                'status'=>-1,
                'return'=>$e->getMessage()
            ];
        }
        return $data;
    }
}
