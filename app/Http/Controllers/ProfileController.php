<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update user profile (name and email only).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Explicitly update only validated name and email to prevent mass assignment privilege escalation
        $user->fill($request->only(['name', 'email']));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'Perfil actualizado correctamente.');
    }

    /**
     * Update user password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Contraseña actualizada correctamente.');
    }
}
