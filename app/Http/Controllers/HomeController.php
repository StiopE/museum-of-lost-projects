<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Project;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::query()
            ->withCount(['projects as exhibit_count' => fn ($query) => $query->where('status', ProjectStatus::Published)])
            ->orderBy('name')->get()->keyBy('slug');

        return view('welcome', [
            'categories' => $categories,
            'pressExhibit' => Project::query()->where('status', ProjectStatus::Published)
                ->where('title', 'Rain Ticket Press')->oldest('id')->first(['id']),
            'exhibitCount' => $categories->sum('exhibit_count'),
            'recentProjects' => Project::query()->where('status', ProjectStatus::Published)
                ->with(['category', 'creator'])->latest()->orderByDesc('id')->limit(4)->get(),
        ]);
    }
}
