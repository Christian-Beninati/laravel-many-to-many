<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::all();
        return view(' admin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        $types = Type::all();

        return view('admin.projects.create', compact('project', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'title' => 'required|string|unique:projects',
                'url' => 'required|unique:projects|url:http,https',
                'image' => 'nullable|image:jpg,jpeg,png',
                'description' => 'nullable|string',
                'type_id' => 'nullable|exists:types,id'
            ],
            [
                'title.required' => ' Title is required',
                'title.unique' => " There is already a project called $request->title",
                'url.required' => ' Title is required',
                'url.unique' => " This link already exists  $request->url",
                'url.url' => ' The link is not valid',
                'description.required' => 'There can be no project without a description',
                'image.image' => 'The uploaded file is not valid',
                'type_id.exists' => 'The type entered is non-existent'
            ]
        );

        $data = $request->all();


        $project = new Project();

        if (array_key_exists('image', $data)) {
            $img_url = Storage::putFile('post_images', $data['image']);
            $data['image'] = $img_url;
        }

        $project->fill($data);

        $project->save();

        return to_route('admin.projects.show', $project)->with('alert-type', 'success')->with('alert-message', 'Project successfully added ');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $types = Type::select('id', 'label')->get();

        return view('admin.projects.edit', compact('project', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate(
            [
                'title' => ['required', 'string', Rule::unique('projects')->ignore($project->id)],
                'url' => ['required', 'url:http,https', Rule::unique('projects')->ignore($project->id)],
                'image' => 'nullable|image:jpg,jpeg,png',
                'description' => 'nullable|string',
                'type_id' => 'nullable|exists:types,id'

            ],
            [
                'title.required' => ' Title is required',
                'title.unique' => " There is already a project called $request->title",
                'url.required' => ' Title is required',
                'url.unique' => " This link already exists $request->url",
                'url.url' => ' The link is not valid',
                'description.required' => 'There can be no project without a description',
                'image.image' => 'The uploaded file is not valid',
                'type_id.exists' => 'The type entered is non-existent'

            ]
        );

        $data = $request->all();

        if (array_key_exists('image', $data)) {
            if ($project->image) Storage::delete($project->image);
            $img_url = Storage::putFile('post_images', $data['image']);
            $data['image'] = $img_url;
        }

        $project->update($data);

        return to_route('admin.projects.show', $project)->with('alert-message', 'Successfully modified project')->with('alert-type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return to_route('admin.projects.index')->with('alert-type', 'success')->with('alert-message', 'Project successfully deleted');
    }

    public function forceDelete($id)
    {
        $project = Project::onlyTrashed()->find($id);
        if (!$project) return to_route('admin.projects.index')->with('alert-type', 'danger')->with('alert-message', 'Project not found');

        if ($project->image) Storage::delete($project->image);
        $project->forceDelete();
    }
}
