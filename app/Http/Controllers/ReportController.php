<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Note;
use App\Models\Participant;
use App\Models\Venue;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Dropdown options
        $allVenues = Venue::orderBy('name')->get();
        $allEvents = Event::orderBy('title')->get();
        $allParticipants = Participant::orderBy('name')->get();
        $allNotes = Note::orderBy('id')->get();

        // Chosen items (default: first one)
        $venue = Venue::find($request->query('venue_id')) ?? $allVenues->first();
        $event = Event::find($request->query('event_id')) ?? $allEvents->first();
        $participant = Participant::find($request->query('participant_id')) ?? $allParticipants->first();
        $note = Note::find($request->query('note_id')) ?? $allNotes->first();

        // 1. Every venue with its number of events
        $venuesWithCount = Venue::withCount('events')->orderBy('name')->get();

        // 2. All events at the chosen venue
        $venueEvents = $venue->events()->orderBy('starts_at')->get();

        // 3. Participants of the chosen event (status is on the pivot)
        $eventParticipants = $event->participants()->orderBy('name')->get();

        // 4. Events the chosen participant has joined
        $participantEvents = $participant->events()->orderBy('starts_at')->get();

        // 5. Events with at least one waitlisted participant
        $waitlistedEvents = Event::with('venue')
            ->whereHas('participants', fn ($q) => $q->where('event_participant.status', 'waitlisted'))
            ->orderBy('starts_at')
            ->get();

                // 6. Notes of the chosen event and chosen venue
            $eventNotes = $event->notes()->get();
            $venueNotes = $venue->notes()->get();

            // 7. Owner of the chosen note
            $owner = $note->load('noteable')->noteable;
            $ownerName = $owner instanceof Event ? $owner->title : $owner->name;
            $ownerKind = class_basename($note->noteable_type);

        return view('report', compact(
            'allVenues', 'allEvents', 'allParticipants', 'allNotes',
            'venue', 'event', 'participant', 'note',
            'venuesWithCount', 'venueEvents', 'eventParticipants', 'participantEvents',
            'waitlistedEvents', 'eventNotes', 'venueNotes', 'ownerName', 'ownerKind'
        ));
    }
}