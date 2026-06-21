<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDependencyRequest;
use Illuminate\Http\Request;

class DependencyController extends Controller
{
    public function store(StoreDependencyRequest $request)
    {
        auth()->user()->dependencies()->create($request->validated());
        return back();
    }

    public function destroy(\App\Models\Dependency $dependency)
    {
        abort_unless($dependency->user_id === auth()->id(), 403);
        $dependency->delete(); // impulses уйдут по cascadeOnDelete
        return back();
    }
}
