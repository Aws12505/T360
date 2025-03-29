<?php

namespace App\Services\Roles;

use App\Models\Role;
class RoleService{
    /**
     * Create a new role.
     *
     * @param array $data
     * @return \App\Models\Role
     */
    public function createRole(array $data)
    {
        $role = Role::create(['name' => $data['name']]);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Update an existing role.
     *
     * @param int $roleId
     * @param array $data
     * @return  \App\Models\Role
     */
    public function updateRole($roleId, array $data)
    {
        $role = Role::findOrFail($roleId);
        $role->update(['name' => $data['name']]);
        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return $role;
    }

    /**
     * Update a role as Admin.
     *
     * @param int $roleId
     * @param array $data
     * @return  \App\Models\Role
     */
    public function updateRoleAdmin($roleId, array $data)
    {
        return $this->updateRole($roleId, $data);
    }

    /**
     * Delete a role.
     *
     * @param int $roleId
     * @return void
     */
    public function deleteRole($roleId)
    {
        $role = Role::findOrFail($roleId);
        $role->delete();
    }

}