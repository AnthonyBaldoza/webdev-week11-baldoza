<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 10 venues
        $venueNames = ['Main Auditorium', 'Gymnasium', 'Function Hall', 'Library Atrium', 'Science Lab',
            'Open Grounds', 'Student Center', 'Chapel', 'Computer Lab', 'Covered Court'];
        $venueIds = [];
        foreach ($venueNames as $name) {
            $venueIds[] = Venue::create([
                'name' => $name,
                'address' => 'PUP Calauan Campus',
                'capacity' => random_int(100, 800),
            ])->id;
        }

        // 20,000 events, inserted in chunks of 1,000
        $phrases = [
            'Join us for a day of learning and collaboration.',
            'Open to all students and faculty members.',
            'Bring a valid school ID for entry.',
            'Refreshments will be served during the break.',
            'Guest speakers from local industry will share their experience.',
            'Hands-on workshop with limited slots.',
            'Certificates will be given to all attendees.',
            'Come early to get the best seats.',
            'A friendly competition with prizes for the winners.',
            'Network with alumni and future employers.',
        ];
        $dressCodes = ['casual', 'formal', 'smart casual', 'uniform', 'business'];
        $tagPool = ['music', 'sports', 'tech', 'career', 'art', 'food', 'workshop', 'seminar'];

        $rows = [];
        for ($i = 1; $i <= 20000; $i++) {
            $description = implode(' ', Arr::random($phrases, 3));
            if ($i % 200 === 0) {                 // rare word: 100 events = 0.5%
                $description .= ' Includes a telescope session.';
            }
            $dressCode = ($i % 150 === 0)         // rare value: 133 events = 0.67%
                ? 'costume'
                : Arr::random($dressCodes);

            $rows[] = [
                'venue_id' => $venueIds[array_rand($venueIds)],
                'title' => 'Campus Event #' . $i,
                'starts_at' => $now->copy()->addDays(random_int(-30, 90))->addHours(random_int(0, 23)),
                'description' => $description,
                'details' => json_encode([
                    'dress_code' => $dressCode,
                    'capacity' => random_int(30, 500),
                    'tags' => Arr::random($tagPool, 2),
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($rows) === 1000) {
                DB::table('events')->insert($rows);
                $rows = [];
            }
        }

        // 200 participants
        $participants = [];
        for ($i = 1; $i <= 200; $i++) {
            $participants[] = [
                'name' => 'Participant ' . $i,
                'email' => "participant{$i}@example.com",
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('participants')->insert($participants);

        // Registrations for the first 100 events (6 each = 600 rows)
        $eventIds = DB::table('events')->orderBy('id')->limit(100)->pluck('id');
        $participantIds = DB::table('participants')->pluck('id')->all();
        $registrations = [];
        foreach ($eventIds as $eventId) {
            foreach (Arr::random($participantIds, 6) as $participantId) {
                $registrations[] = [
                    'event_id' => $eventId,
                    'participant_id' => $participantId,
                    'status' => random_int(1, 4) === 1 ? 'waitlisted' : 'registered',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        DB::table('event_participant')->insert($registrations);

        // A few notes (polymorphic, from Week 10)
        Event::find(1)->notes()->create(['body' => 'Bring a valid school ID.']);
        Event::find(2)->notes()->create(['body' => 'Bring your own laptop.']);
        Venue::find($venueIds[0])->notes()->create(['body' => 'Projector needs a warm-up.']);
        Venue::find($venueIds[1])->notes()->create(['body' => 'No outside food allowed.']);
    }
}