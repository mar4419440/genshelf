<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->get();
        $roles = Role::all();
        $audit = DB::table('audit_logs')->latest()->take(50)->get();

        return view('pages.users.index', compact('users', 'roles', 'audit'));
    }

    // User CRUD
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id'
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['display_name'] = $validated['name'];

        User::create($validated);
        return redirect()->back()->with('success', __('User created successfully.'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id'
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $validated['display_name'] = $validated['name'];

        $user->update($validated);
        return redirect()->back()->with('success', __('User updated successfully.'));
    }

    public function destroyUser(User $user)
    {
        if ($user->id === 1) {
            return redirect()->back()->with('error', __('Cannot delete the root admin.'));
        }
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', __('Cannot delete your own account.'));
        }
        
        $user->delete();
        return redirect()->back()->with('success', __('User deleted successfully.'));
    }

    // Role CRUD
    public function storeRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'nullable|array'
        ]);

        Role::create($validated);
        return redirect()->back()->with('success', __('Role created successfully.'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        $role->update($validated);
        return redirect()->back()->with('success', __('Role updated successfully.'));
    }

    public function destroyRole(Role $role)
    {
        if ($role->id === 1) {
            return redirect()->back()->with('error', __('Cannot delete the core Admin role.'));
        }
        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', __('Cannot delete role assigned to active users.'));
        }
        
        $role->delete();
        return redirect()->back()->with('success', __('Role deleted successfully.'));
    }
}
