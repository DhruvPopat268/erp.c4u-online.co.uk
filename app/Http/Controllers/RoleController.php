<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Auth;

class RoleController extends Controller
{

    public function index()
{
    $user = \Illuminate\Support\Facades\Auth::user();

    if ($user->can('manage role')) {
        $rolesQuery = Role::select('roles.*', 'users.username as created_by_name')
            ->leftJoin('users', 'roles.created_by', '=', 'users.id'); // Joining users table

        if ($user->type != 'company') {
            $rolesQuery->where('roles.created_by', $user->id); // Filter by created_by for non-super admins
        }

        // Optimized filtering for super admins:
        if ($user->type === 'company') {
            $rolesQuery->whereNotIn('roles.id', [1, 2, 3, 4, 5]); // Exclude specific role IDs
        }

        $roles = $rolesQuery->get();

        return view('role.index', compact('roles'));
    } else {
        return redirect()->back()->with('error', 'Permission denied.');
    }
}


    public function create()
    {
        if (\Auth::user()->can('create role')) {
            $user = \Auth::user();
            if ($user->type == 'super admin') {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            } else {
                $permissions = new Collection();
                foreach ($user->roles as $role) {
                    $permissions = $permissions->merge($role->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.create', ['permissions' => $permissions]);
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create role')) {
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required|max:100|unique:roles,name,NULL,id,created_by,'.\Auth::user()->creatorId(),
                    'permissions' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $name = $request['name'];
            $role = new Role();
            $role->name = $name;
            $role->created_by = \Auth::user()->id;
            $role->company_id = \Auth::user()->creatorId();
            $permissions = $request['permissions'];
            $role->save();

            foreach ($permissions as $permission) {
                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            return redirect()->route('roles.index')->with(
                'Role successfully created.', 'Role '.$role->name.' added!'
            );
        } else {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }

    public function edit(Role $role)
    {
        if(\Auth::user()->can('edit role'))
        {

            $user = \Auth::user();
            if($user->type == 'super admin')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role1)
                {
                    $permissions = $permissions->merge($role1->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            return view('role.edit', compact('role', 'permissions'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function update(Request $request, Role $role)
    {

        if(\Auth::user()->can('edit role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:100|unique:roles,name,' . $role['id'] . ',id,created_by,' . \Auth::user()->creatorId(),
                                   'permissions' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $input       = $request->except(['permissions']);
            $permissions = $request['permissions'];
            $role->fill($input)->save();

            $p_all = Permission::all();

            foreach($p_all as $p)
            {
                $role->revokePermissionTo($p);
            }

            foreach($permissions as $permission)
            {

                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            return redirect()->route('roles.index')->with(
                'Role successfully updated.', 'Role ' . $role->name . ' updated!'
            );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }


    public function destroy(Role $role)
    {
        if(\Auth::user()->can('delete role'))
        {
            $role->delete();

            return redirect()->route('roles.index')->with(
                'success', 'Role successfully deleted.'
            );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }
}
