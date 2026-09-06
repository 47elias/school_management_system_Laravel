<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        return view('roles.index', compact('roles', 'permissions'));
    }

    public function storeRole(Request $request)
    {
        $request->validate(['name' => 'required|unique:roles,name']);
        Role::create(['name' => strtolower($request->name)]);
        return back()->with('success', 'Role created successfully.');
    }

    public function storePermission(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        // Format permission name automatically (e.g. "Edit Users" -> "edit_users")
        $permissionName = strtolower(str_replace(' ', '_', $request->name));
        Permission::create(['name' => $permissionName]);
        return back()->with('success', 'Permission created successfully.');
    }

    public function destroyRole($id)
    {
        Role::findOrFail($id)->delete();
        return back()->with('success', 'Role deleted.');
    }

    public function destroyPermission($id)
    {
        Permission::findOrFail($id)->delete();
        return back()->with('success', 'Permission deleted.');
    }
}