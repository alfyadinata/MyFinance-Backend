<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Finance;
use Carbon\Carbon;
use DB;

class FinanceController extends Controller
{
    // income field 1=true & 2 = false
    public function chart()
    {
        $one_week_ago = Carbon::now()->subDays(6)->format('Y-m-d');

        $data = DB::table('finances')
        ->select(DB::raw('sum(price) as total'),DB::raw('date(created_at) as dates'))
        ->groupBy('dates')
        ->orderBy('dates','DESC')
       ->get();
       
       return response()->json([
           'data'   =>  $data
       ]);

    }

    public function last() 
    {
        $finanaces  =   Finance::where('user_id',Auth::user()->id)->latest()->limit(5)->get();

        return response()->json([
            'data'   =>  $finanaces
        ]);        
    }

    public function index()
    {
        try {
            $user   =   Auth::user();

            if ($user) {
                $data   =   Finance::where('user_id',$user->id)->latest()->get();

                return response()->json([
                    'code'  =>  200,
                    'data'  =>  $data
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'code'  =>  500,
                'mssg'  =>  $th
            ]);
        }
    }

    public function store(Request $request)
    {
        // $validation     =   $this->validate($request,[
        //     'income'        =>  'required',
        //     'category'      =>  'required',
        //     'price'         =>  'required',
        //     'date'          =>  'required',
        //     'description'   =>  'required'
        // ]);
        try {
            $request['user_id']     =   Auth::user()->id;

            $data   =   Finance::create($request->all());

            if ($data) {
                return response()->json([
                    'code'  =>  201,
                    'mssg'  =>  'success created data',
                    'data'  =>  $data
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'code'  =>  500,
                'mssg'  =>  $th
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data   =   Finance::find($id);

            if (!$data) {
                return response()->json([
                    'code'  =>  404,
                    'data'  =>  []
                ]);
            }

            $result     =   $data->update($request->all());

            return response()->json([
                'code'  =>  201,
                'data'  =>  $result
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'code'  =>  500,
                'mssg'  =>  $th
            ]);
        }
    }

    public function destroy($id)
    {
        $data   =   Finance::find($id);

        if (!$data) {
            return response()->json([
                'code'  =>  203,
                'data'  =>  $data
            ]);
        }
        
        $data->delete();

        return response()->json([
            'code'  =>  200,
            'data'  =>  $data
        ]);
    }
}
