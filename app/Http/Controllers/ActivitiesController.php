<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Update;
use App\Models\User;
use App\Notifications\NewActivity;
use App\Notifications\Updates;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActivitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        return $this->middleware('auth');
    }
    public function index()
    {
        $activities = Activity::with('updates')->latest()->get();
        return view('home', compact('activities'));
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $activity = Activity::create([
                'activity' => $validated['activity'],
            ]);
            User::all()->each(function ($each) use ($activity) {
                $each->notify(new NewActivity($activity, auth()->user()));
            });
            $activity->updates()->create([
                'type' => "NEW_ACTIVITY_UPDATE",
                'user_id' => auth()->id(),
                'data' => $activity->activity
            ]);
            DB::commit();
            return back()->with('success', "Successfully added a new activity");
        } catch (Exception $e) {
            // If an error occurs, rollback the transaction
            DB::rollBack();
            dd($e);
            // Return an error response
            return back()->with('error', "Failed to add a new activity. Please try again.");
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showUpdates()
    {
        // $activities = [];
        // $updatesForToday = Update::whereDateBetween('created_at',(new Carbon)->subDay()->toDateString(),(new Carbon)->now()->toDateString() )->get();
        $updatesForToday = Update::whereDateBetween('created_at', Carbon::yesterday(), Carbon::today())->get();

        return view('updates', compact('updatesForToday'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */




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
    public function update($id)
    {
        DB::beginTransaction();
        try {
            $activity = Activity::findOrFail($id);
            User::all()->each(function ($each) use ($activity) {
                $each->notify(new Updates($activity, auth()->user()));
            });

            // set activity to other status

            if ($activity->status == 1) {
                $activity->status = 0;
                $message = "Pending";
            } else {
                $activity->status = 1;
                $message = "Done";
            }
            //add update
            $activity->updates()->create([
                'type' => "ACTIVITY_STATUS_UPDATE",
                'user_id' => auth()->id(),
                'data' => $message
            ]);
            $activity->save();

            DB::commit();
            //code...
        } catch (Exception $e) {
            DB::rollBack();
          
            return back()->with('error', 'Failed to change status of new activity. Please try again');
        }



        return back();
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
