<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Notifications\NewRemark;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemarksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,$id)
    {
        //validate input
        $validated = $request->validate([
            'remark' => 'required',
        ]);
        DB::beginTransaction();
        try {
             //find activity from db
        $activity = Activity::find($id);
        //assign remark to activity
        $remark = $activity->remarks()->create(['remark'=>$validated['remark'], 'user_id'=>auth()->id(),'activity'=>$activity->activity]);
        //add update
        $activity->updates()->create([
            'type'=>"REMARK_ADDED_UPDATE",
            'user_id' => auth()->id(),
            'data' => $remark->remark
        ]);
        //notify users
        User::all()->each(function($each) use ($remark){
            $each->notify(new NewRemark($remark,auth()->user()));
        });
        DB::commit();
        return back();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to change status of new activity. Please try again');
        }
        
       
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
