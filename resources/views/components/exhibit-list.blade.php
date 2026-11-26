@props(['projects'])
<ul class="exhibit-list">
    @foreach ($projects as $project)
        <li><a href="{{ route('projects.show', $project) }}">
            <span class="exhibit-code" aria-label="Exhibit {{ $project->id }}">MFP / {{ str_pad((string) $project->id, 3, '0', STR_PAD_LEFT) }}</span>
            <span class="list-story"><strong>{{ $project->title }}</strong><span>{{ $project->description }}</span></span>
            <span class="list-credit">{{ $project->creator->name }}<small>{{ $project->category->name }}</small></span>
            <span class="list-status">{{ $project->adoption_available ? 'Open for adoption' : ($project->parent_project_id ? 'Revived project' : 'On view') }}</span>
        </a></li>
    @endforeach
</ul>
