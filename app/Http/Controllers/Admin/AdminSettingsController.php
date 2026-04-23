<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $managers = User::where('role', 'admin')->orderBy('name')->get();

        return view('admin.settings.index', compact('managers'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password'      => ['required'],
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        auth()->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function addManager(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => 'admin',
        ]);

        return back()->with('success', "Admin account for {$request->name} created successfully.");
    }

    public function removeManager(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot remove your own account.');
        }

        $user->delete();

        return back()->with('success', 'Admin account removed successfully.');
    }
}
