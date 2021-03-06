<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Unit;

class UnitController extends Controller
{
    /**
     * Return Units response as Json.
     *
     * @return \Illuminate\Http\Response
     */

    public function ajaxPushSoftwareUpdateNotification()
    {
        try {
            $oses = ['mac', 'win'];
            foreach ($oses as $os) {
                $version = DB::table('settings')
                    ->where([
                        ['key', '=', 'LAST_UPLOADED_SOFTWARE_VERSION'],
                        ['param1', '=', $os],
                    ])
                    ->value('value');
                DB::table('settings')
                    ->where([
                        ['key', '=', 'LAST_PUSHED_SOFTWARE_VERSION'],
                        ['param1', '=', $os],
                    ])
                    ->update(['value' => $version]);
            }
            return response()->json(['success' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function ajaxPushFirmwareUpdateNotification()
    {
        try {
            $version = DB::table('settings')
                ->where([
                    ['key', '=', 'LAST_UPLOADED_FIRMWARE_VERSION'],
                ])
                ->value('value');
            DB::table('settings')
                ->where([
                    ['key', '=', 'LAST_PUSHED_FIRMWARE_VERSION'],
                ])
                ->update(['value' => $version]);
            return response()->json(['success' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function ajaxList(Request $request)
    {
        try {
            $filterField = $request->get('filterField');
            $filterKeyword = $request->get('filterKeyword', '');
            if(empty($filterKeyword)) {
                $units = Unit::whereRaw('1=1')
                    ->orderBy('id', 'ASC')
                    ->get();
            }
            else if(empty($filterField)) {
                $units = Unit::where('serial', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('qaqc', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('mainboard', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('mcuboard', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('inputboard1', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('inputboard2', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('inputboard3', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('inputboard4', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('status', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('software_reg_key', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('os', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('active_licenses_count', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('firstname', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('lastname', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('location', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('email', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('phone', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('warranty_type', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('warranty_claims', 'like', '%'.$filterKeyword.'%')
                    ->orWhere('customer_notes', 'like', '%'.$filterKeyword.'%')
                    ->orderBy('id', 'ASC')
                    ->get();
            }
            else {
                $units = Unit::where($filterField, 'like', '%'.$filterKeyword.'%')
                    ->orderBy('id', 'ASC')
                    ->get();
            }

            return response()->json(['success' => 1, 'data'=> $units]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function ajaxAdd(Request $request)
    {
        try {
            $serial = $request->get('serial');

            if(empty($serial)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Unit Serial # is empty!']);
            }

            $id = $request->get('id');

            if(empty($id)) {
                $unit = new Unit();
            }
            else {
                $unit = Unit::find($id);
            }

            $unit->serial = $serial;
            $unit->assembly_date = $request->get('assembly_date');
            $unit->qaqc = $request->get('qaqc');
            $unit->mainboard = $request->get('mainboard');
            $unit->mcuboard = $request->get('mcuboard');
            $unit->inputboard1 = $request->get('inputboard1');
            $unit->inputboard2 = $request->get('inputboard2');
            $unit->inputboard3 = $request->get('inputboard3');
            $unit->inputboard4 = $request->get('inputboard4');
            $unit->software_reg_key = $request->get('software_reg_key');
            $unit->status = $request->get('status');
            $unit->active_date = $request->get('active_date');
            $unit->os = $request->get('os');
            $unit->licenses_limit = $request->get('licenses_limit');
            $unit->active_licenses_count = $request->get('active_licenses_count');
            $unit->firstname = $request->get('firstname');
            $unit->lastname = $request->get('lastname');
            $unit->location = $request->get('location');
            $unit->email = $request->get('email');
            $unit->phone = $request->get('phone');
            $unit->warranty_type = $request->get('warranty_type');
            $unit->warranty_claims = $request->get('warranty_claims');
            $unit->warranty_active_date = $request->get('warranty_active_date');
            $unit->customer_notes = $request->get('customer_notes');
            $unit->is_repairing = $request->get('is_repairing');
            $unit->is_decommissioned = $request->get('is_decommissioned');

            $unit->save();

            $units = Unit::whereRaw('1=1')->orderBy('id', 'ASC')->get();

            return response()->json(['success' => 1, 'data'=> $units]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function signUpUser(Request $request)
    {
        try {
            $serial = $request->get('serial');

            if(empty($serial)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Unit Serial # is empty!']);
            }

            $unit = Unit::where('serial', $serial)
                ->first();

            if(empty($unit)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Unit Serial # not matching!']);
            }

            $unit->firstname = $request->get('firstname');
            $unit->lastname = $request->get('lastname');
            $unit->location = $request->get('location');
            $unit->email = $request->get('email');
            $unit->phone = $request->get('phone');
            $unit->active_date = date('Y-m-d H:i:s');

            $unit->save();

            return response()->json(['success' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function registerNewDevice(Request $request)
    {
        try {
            $serial = $request->get('serial');

            if(empty($serial)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Unit Serial # is empty!']);
            }

            $unit = Unit::where('serial', $serial)
                ->first();

            if(empty($unit)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Unit Serial # not matching!']);
            }

            $unit->firstname = $request->get('firstname');
            $unit->lastname = $request->get('lastname');
            $unit->location = $request->get('location');
            $unit->email = $request->get('email');
            $unit->phone = $request->get('phone');
            $unit->os = $request->get('os');
            $unit->status = 1;
            $unit->active_date = $request->get('activeDate');
            $unit->warranty_active_date = date('Y-m-d H:i:s');

            $unit->save();

            return response()->json(['success' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function checkRegKey(Request $request) {
        try {
            $key = $request->get('regKey');

            if(empty($key)) {
                return response()->json(['success' => 0, 'errMsg'=> 'Software Registration Key is empty!']);
            }

            $units = Unit::where('software_reg_key', $key)
                ->get();

            if(count($units) > 1) {
                return response()->json(['success' => 0, 'errMsg'=> 'Software Registration Key duplicate!']);
            }

            if(count($units) == 0) {
                return response()->json(['success' => 0, 'errMsg'=> 'Software Registration Key invalid!']);
            }
            
            $unit = $units[0];

            return response()->json(['success' => 1, 'active_licenses_count' => $unit->active_licenses_count, 'licenses_limit' => $unit->licenses_limit]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }

    public function useRegKey(Request $request) {
        try {
            $key = $request->get('regKey');

            if(empty($key)) {
                return response()->json(['success' => 0, 'retCode' => 201, 'errMsg'=> 'Software Registration Key is empty!']);
            }

            $units = Unit::where('software_reg_key', $key)
                ->get();

            if(count($units) > 1) {
                return response()->json(['success' => 0, 'retCode' => 202, 'errMsg'=> 'Software Registration Key duplicate!']);
            }
            
            if(count($units) == 0) {
                return response()->json(['success' => 0, 'retCode' => 203, 'errMsg'=> 'Software Registration Key invalid!']);
            }
            

            $unit = $units[0];

            if($unit->licenses_limit > -1 && $unit->active_licenses_count >= $unit->licenses_limit) {
                return response()->json(['success' => 0, 'retCode' => 204, 'errMsg'=> "Software Registration Key reached max count ({$unit->licenses_limit})"]);
            }

            $unit->active_licenses_count = $unit->active_licenses_count + 1;

            $unit->save();

            return response()->json(['success' => 1]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => 0, 'errMsg'=> $e->getMessage()]);
        }
    }
}
