<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Admin Staff ERP Index - List all system staff users.
     */
    public function index()
    {
        $users = User::where('role', '!=', 'student')->latest()->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show form to create a new staff user.
     */
    public function create()
    {
        $roles = Role::query()
            ->where('guard_name', config('auth.defaults.guard'))
            ->where('name', '!=', 'student')
            ->orderBy('name')
            ->get();

        return view('users.create', compact('roles'));
    }

    /**
     * Store new staff user in database and assign Spatie role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'national_id' => 'required|unique:users,national_id',
            'dob' => 'required|date',
            'ec_number' => 'required|unique:users,ec_number',
            'password' => 'required|min:8',
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')
                    ->where('guard_name', config('auth.defaults.guard'))
                    ->where('name', '!=', 'student'),
            ],
        ]);

        $selectedRole = Role::query()
            ->where('guard_name', config('auth.defaults.guard'))
            ->where('name', $request->role)
            ->firstOrFail();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'national_id' => $request->national_id,
            'dob' => $request->dob,
            'phone_number' => $request->phone_number,
            'ec_number' => $request->ec_number,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Automatically assign the Spatie Role upon creation
        $user->assignRole($selectedRole);

        return redirect()->route('users.index')->with('success', 'Staff account created successfully!');
    }

    /**
     * Show edit form for staff user, roles, and direct permissions.
     */
    public function edit($id)
    {
        $staff = User::findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();
        
        return view('users.edit', compact('staff', 'roles', 'permissions'));
    }

    /**
     * Update staff user account, sync Spatie roles and direct permissions.
     */
    public function update(Request $request, $id)
    {
        $staff = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $staff->id,
            'national_id' => 'required|string|unique:users,national_id,' . $staff->id,
            'dob' => 'required|date',
            'ec_number' => 'required|string|unique:users,ec_number,' . $staff->id,
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone_number = $request->phone_number;
        $staff->ec_number = $request->ec_number;
        $staff->national_id = $request->national_id;
        $staff->dob = $request->dob;

        if ($request->has('roles') && count($request->roles) > 0) {
            $staff->role = $request->roles[0];
        }

        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
        }

        $staff->save();

        if ($request->has('roles')) {
            $staff->syncRoles($request->roles);
        } else {
            $staff->syncRoles([]);
        }

        if ($request->has('permissions')) {
            $staff->syncPermissions($request->permissions);
        } else {
            $staff->syncPermissions([]);
        }

        return redirect()->route('users.index')->with('success', 'Staff member and roles updated successfully.');
    }

    /**
     * Delete a staff user account.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'Staff member deleted successfully.');
    }
}
