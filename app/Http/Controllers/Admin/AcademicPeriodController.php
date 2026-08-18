<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAcademicPeriodRequest;
use App\Http\Requests\UpdateAcademicPeriodRequest;
use App\Models\AcademicPeriod;
use App\Services\Academic\AcademicPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicPeriodController extends Controller
{
    /**
     * Display a listing of the academic periods.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicPeriod::class);

        $query = AcademicPeriod::with('partials');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('active', true);
            } elseif ($status === 'inactive') {
                $query->where('active', false);
            }
        }

        $periods = $query->orderByDesc('starts_at')->paginate(25)->withQueryString();

        return view('admin.academic-periods.index', compact('periods'));
    }

    /**
     * Show the form for creating a new academic period.
     */
    public function create(): View
    {
        $this->authorize('create', AcademicPeriod::class);

        return view('admin.academic-periods.create');
    }

    /**
     * Store a newly created academic period in storage.
     */
    public function store(StoreAcademicPeriodRequest $request, AcademicPeriodService $service): RedirectResponse
    {
        $service->createPeriod($request->validated());

        return redirect()->route('admin.academic-periods.index')
            ->with('success', 'Período académico y sus dos parciales (P1 y P2) creados exitosamente.');
    }

    /**
     * Show the form for editing the specified academic period.
     */
    public function edit(AcademicPeriod $academicPeriod): View
    {
        $this->authorize('update', $academicPeriod);

        $academicPeriod->load('partials');

        return view('admin.academic-periods.edit', compact('academicPeriod'));
    }

    /**
     * Update the specified academic period in storage.
     */
    public function update(
        UpdateAcademicPeriodRequest $request,
        AcademicPeriod $academicPeriod,
        AcademicPeriodService $service
    ): RedirectResponse {
        $this->authorize('update', $academicPeriod);

        $service->updatePeriod($academicPeriod, $request->validated());

        return redirect()->route('admin.academic-periods.index')
            ->with('success', 'Período académico actualizado exitosamente.');
    }

    /**
     * Activate the specified academic period.
     */
    public function activate(AcademicPeriod $academicPeriod, AcademicPeriodService $service): RedirectResponse
    {
        $this->authorize('toggleStatus', $academicPeriod);

        $service->activatePeriod($academicPeriod);

        return redirect()->route('admin.academic-periods.index')
            ->with('success', "El período '{$academicPeriod->name}' ha sido activado exitosamente.");
    }

    /**
     * Toggle the active status of the specified academic period.
     */
    public function toggleStatus(AcademicPeriod $academicPeriod, AcademicPeriodService $service): RedirectResponse
    {
        $this->authorize('toggleStatus', $academicPeriod);

        if ($academicPeriod->active) {
            $academicPeriod->update(['active' => false]);
            $statusText = 'desactivado';
        } else {
            $service->activatePeriod($academicPeriod);
            $statusText = 'activado';
        }

        return redirect()->route('admin.academic-periods.index')
            ->with('success', "El período '{$academicPeriod->name}' ha sido {$statusText} exitosamente.");
    }
}
