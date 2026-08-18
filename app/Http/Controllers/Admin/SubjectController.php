<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of the subjects.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Subject::class);

        $query = Subject::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        $subjects = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.subjects.index', compact('subjects'));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(): View
    {
        $this->authorize('create', Subject::class);

        return view('admin.subjects.create');
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        Subject::create($request->validated());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Asignatura creada exitosamente.');
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): View
    {
        $this->authorize('update', $subject);

        return view('admin.subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorize('update', $subject);

        $subject->update($request->validated());

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Asignatura actualizada exitosamente.');
    }

    /**
     * Toggle the active status of the specified subject.
     */
    public function toggleStatus(Subject $subject): RedirectResponse
    {
        $this->authorize('toggleStatus', $subject);

        $subject->update(['active' => !$subject->active]);

        $statusText = $subject->active ? 'activada' : 'desactivada';

        return redirect()->route('admin.subjects.index')
            ->with('success', "Asignatura '{$subject->name}' {$statusText} exitosamente.");
    }
}
