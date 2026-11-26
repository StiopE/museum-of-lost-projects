<?php

namespace Database\Seeders;

use App\Enums\AdoptionRequestStatus;
use App\Enums\ProjectStatus;
use App\Models\AdoptionRequest;
use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\ProjectUpdate;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File as Files;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MuseumCommunitySeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $storedPaths = [];
        try {
            DB::transaction(function () use (&$storedPaths): void {
                $names = [
                    'Iris' => 'Iris Bell',
                    'Ada' => 'Ada Mercer',
                    'Luz' => 'Luz Vidal',
                    'Tomas' => 'Tomas Reed',
                    'Nell' => 'Nell Okoro',
                    'Noa' => 'Noa Field',
                ];
                $emails = array_map(fn (string $name): string => strtolower($name).'@museum.example.test', array_keys($names));
                if (User::query()->whereIn('email', $emails)->exists()) {
                    $this->command?->info('Community demo accounts already exist. Skipping this collection.');

                    return;
                }

                $today = now();
                $joined = $today->copy()->subDays(120);
                $creators = [];
                foreach ($names as $first => $fullName) {
                    $creators[$first] = User::factory()->create([
                        'name' => $fullName,
                        'email' => strtolower($first).'@museum.example.test',
                        'email_verified_at' => $joined,
                        'created_at' => $joined,
                        'updated_at' => $joined,
                    ]);
                }

                $categories = Category::query()->get()->keyBy('slug');
                $projects = [];
                $exhibits = Files::json(__DIR__.'/fixtures/community-exhibits.json');
                foreach ($exhibits as $exhibit) {
                    $creator = $creators[$exhibit['creator']];
                    $created = $today->copy()->subDays($exhibit['days_ago']);
                    $original = isset($exhibit['origin']) ? $projects[$exhibit['origin']] : null;
                    $project = Project::factory()->for($creator, 'creator')->for($categories[$exhibit['category']])->create([
                        ...$exhibit['details'],
                        'status' => ($exhibit['draft'] ?? false) ? ProjectStatus::Draft : ProjectStatus::Published,
                        'adoption_available' => $exhibit['open'],
                        'parent_project_id' => $original?->id,
                        'created_at' => $created,
                        'updated_at' => $created->copy()->addDays(count($exhibit['updates'])),
                    ]);
                    $projects[$project->title] = $project;

                    foreach ($exhibit['updates'] as $index => $content) {
                        $posted = $created->copy()->addDays($index + 1);
                        ProjectUpdate::factory()->for($project)->for($creator, 'author')->create([
                            'content' => $content,
                            'created_at' => $posted,
                            'updated_at' => $posted,
                        ]);
                    }

                    if ($original !== null) {
                        AdoptionRequest::factory()->for($original)->for($creator, 'requester')->create([
                            'message' => $exhibit['proposal'],
                            'status' => AdoptionRequestStatus::Approved,
                            'revived_project_id' => $project->id,
                            'created_at' => $created->copy()->subDays(4),
                            'responded_at' => $created,
                            'updated_at' => $created,
                        ]);
                    }

                    if (isset($exhibit['image'])) {
                        $path = Storage::disk('local')->putFile('project-images', new File(__DIR__.'/fixtures/'.$exhibit['image']));
                        if ($path === false) {
                            throw new RuntimeException('Could not store the exhibit image '.$exhibit['image']);
                        }
                        $storedPaths[] = $path;
                        ProjectImage::factory()->for($project)->create([
                            'disk' => 'local',
                            'image_path' => $path,
                            'caption' => $exhibit['caption'],
                            'created_at' => $created,
                            'updated_at' => $created,
                        ]);
                    }
                }

                $sent = $today->copy()->subDays(2);
                foreach ([
                    ['Iris', 'The Platform Minute', 'I can print a second hour with the same two inks. May I try the evening rush?'],
                    ['Nell', 'Rain Ticket Press', 'I have an indoor receipt printer and a window beside the gauge. Can I test the two-piece version?'],
                ] as [$first, $title, $message]) {
                    AdoptionRequest::factory()->for($projects[$title])->for($creators[$first], 'requester')->create([
                        'message' => $message,
                        'created_at' => $sent,
                        'updated_at' => $sent,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            throw $exception;
        }
    }
}
