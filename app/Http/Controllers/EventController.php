<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    // /events?mode=naive | /events?mode=naive&strict=1 | /events (fixed)
    public function index(Request $request)
    {
        $mode = $request->query('mode', 'fixed');
        DB::enableQueryLog();

        if ($mode === 'naive') {
            // allow lazy loading so the queries can be counted, unless strict=1
            if (! $request->boolean('strict')) {
                Model::preventLazyLoading(false);
            }

            $events = Event::upcoming()->soonest()->limit(50)->get();
            $rows = $events->map(fn ($e) => [
                'title' => $e->title,
                'venue' => $e->venue->name,                  // lazy load (1 query each)
                'participants' => $e->participants->count(), // lazy load (1 query each)
            ]);
        } else {
            $events = Event::with('venue')
                ->withCount('participants')
                ->upcoming()->soonest()
                ->limit(50)->get();
            $rows = $events->map(fn ($e) => [
                'title' => $e->title,
                'venue' => $e->venue->name,
                'participants' => $e->participants_count,
            ]);
        }

        return view('events.index', [
            'rows' => $rows,
            'mode' => $mode,
            'queryCount' => count(DB::getQueryLog()),
        ]);
    }
}