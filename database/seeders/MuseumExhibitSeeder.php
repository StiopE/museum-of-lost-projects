<?php

namespace Database\Seeders;

use App\Models\AdoptionRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MuseumExhibitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            if (User::query()->where('email', 'explorer@example.com')->exists()) {
                return;
            }

            $explorer = User::factory()->create([
                'name' => 'Mara Vale',
                'email' => 'explorer@example.com',
            ]);
            $curator = User::query()->where('email', 'admin@example.com')->firstOrFail();
            $atlas = Project::factory()->for($curator, 'creator')
                ->for(Category::query()->where('slug', 'software-apps')->firstOrFail())
                ->availableForAdoption()->create([
                    'title' => 'The Night Bus Atlas',
                    'description' => 'An offline map for the hours when the last train has already left.',
                    'story' => 'I started collecting night bus timetables after getting stranded at a station with a flat phone.',
                    'original_goal' => 'Fit the city’s late-night routes on one printable sheet.',
                    'abandonment_reason' => 'Keeping up with timetable changes took more time than drawing the map.',
                    'what_went_wrong' => 'I copied each departure by hand and had no way to catch changes.',
                    'what_could_be_improved' => 'Try one district first and generate the timetable from the transit feed.',
                ]);
            AdoptionRequest::factory()->for($atlas)->for($explorer, 'requester')->create([
                'message' => 'I take the night bus home. I would like to test a one-district edition on my route.',
            ]);

            Project::factory()->for($explorer, 'creator')
                ->for(Category::query()->where('slug', 'writing-stories')->firstOrFail())
                ->availableForAdoption()->create([
                    'title' => 'The Museum of Missing Sounds',
                    'description' => 'A field guide to the noises that disappear when a neighbourhood changes.',
                    'story' => 'The bell above our corner shop door was gone before I thought to record it. I began writing down the other sounds I remembered.',
                    'original_goal' => 'Pair twelve short essays with recordings from the streets that inspired them.',
                    'abandonment_reason' => 'I moved away after the third interview.',
                    'what_went_wrong' => 'The interviews outgrew the time I had for transcription.',
                    'what_could_be_improved' => 'Start with one street and invite the shopkeepers to write their own captions.',
                ]);
        });
    }
}
