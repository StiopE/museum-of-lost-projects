<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('my.categories.index', ['categories' => Category::query()->withCount('projects')->orderBy('name')->paginate(20)]);
    }

    public function create(): View
    {
        return view('my.categories.form', ['category' => new Category]);
    }

    public function edit(Category $category): View
    {
        return view('my.categories.form', compact('category'));
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new Category);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        return $this->save($request, $category);
    }

    private function save(Request $request, Category $category): RedirectResponse
    {
        $category->fill($request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category)],
            'slug' => ['required', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255', Rule::unique('categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:2000'],
        ]))->save();

        return redirect()->route('admin.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $deleted = Category::query()->whereKey($category->id)->whereDoesntHave('projects')->delete();

        return back()->with('status', $deleted ? 'Category deleted.' : 'Move its projects to another category first.');
    }
}
