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
            if (! $request->boolean('strict')) {
                Model::preventLazyLoading(false);
            }

            $events = Event::orderBy('id')->limit(50)->get();
            $rows = $events->map(fn ($e) => [
                'title' => $e->title,
                'venue' => $e->venue->name,                  // lazy load (1 query each)
                'participants' => $e->participants->count(), // lazy load (1 query each)
            ]);
        } else {
            $events = Event::with('venue')
                ->withCount('participants')
                ->orderBy('id')
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

    // /events/filter?dress_code=costume
    public function filter(Request $request)
    {
        $code = $request->query('dress_code', 'costume');

        $arrow = Event::with('venue')->withDressCode($code);                                     // details->>'dress_code' = ?
        $contains = Event::with('venue')->whereJsonContains('details', ['dress_code' => $code]); // @>

        return view('events.filter', [
            'code' => $code,
            // displayed list: three scopes chained in one query
            'events' => Event::with('venue')->withDressCode($code)->upcoming()->soonest()->limit(50)->get(),
            'arrowSql' => $arrow->toSql(),
            'arrowRaw' => $arrow->toRawSql(),
            'containsSql' => $contains->toSql(),
            'containsRaw' => $contains->toRawSql(),
            'containsCount' => $contains->count(),
        ]);
    }

    // /events/search?q=telescope
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));
        $query = Event::with('venue')->whereFullText('description', $q === '' ? 'telescope' : $q)->limit(50);

        return view('events.search', [
            'q' => $q,
            'events' => $q === '' ? collect() : $query->get(),
            'sql' => $query->toSql(),
            'raw' => $query->toRawSql(),
        ]);
    }
}