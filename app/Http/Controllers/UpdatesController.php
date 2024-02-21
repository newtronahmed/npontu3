<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpdatesController extends Controller
{
    public function index()
    {
        //mark all unread notifs as read
        // auth()->user()->unreadNotifications->markAsRead();
        //return view with all unread notifications
        // return view('updates')->with('notifications', auth()->user()->notifications()->latest()->paginate(5));
    //    $updates = Activity::with('updates')->get();
    $today = Carbon::today();
    $activitiesWithUpdatesForToday = Activity::whereHas('updates', function ($query) use ($today) {
        $query->whereDate('created_at', $today);
    })->with('updates')->get();
       dd($activitiesWithUpdatesForToday);
        return view('updates', compact('updates'));
    
    }

}
