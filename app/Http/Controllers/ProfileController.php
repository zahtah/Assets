<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'name'        => 'required|string|max:255',
            'department'  => 'required|string|max:255',
            'unit'        => 'nullable|string|max:255',
            'room_number' => 'nullable|string|max:255',
            'floor'       => 'nullable|string|max:255',
            'password'    => 'nullable|min:6|confirmed',
        ]);

        $user->name = $request->name;
        $user->unit = $request->unit;
        $user->department = $request->department;
        $user->room_number = $request->room_number;
        $user->floor = $request->floor;

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return back()->with('success', 'پروفایل با موفقیت بروزرسانی شد.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
