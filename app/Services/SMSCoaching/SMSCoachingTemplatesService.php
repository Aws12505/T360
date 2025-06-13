<?php

namespace App\Services\SMSCoaching;

use App\Models\SMSCoachingTemplates;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SMSCoachingTemplatesService
{
    public function list()
    {
        $tenantSlug = Auth::user()->tenant->slug;
        $permissions = Auth::user()->getAllPermissions();
        $templates = SMSCoachingTemplates::all();
        
        return [
            'templates' => $templates,
            'tenantSlug' => $tenantSlug,
            'permissions' => $permissions,
        ]; 
    }

    public function create(array $data): SMSCoachingTemplates
    {
        return SMSCoachingTemplates::create($data);
    }

    public function creating(){
        $tenantSlug = Auth::user()->tenant->slug;
        $permissions = Auth::user()->getAllPermissions();

        return [
            'tenantSlug' => $tenantSlug,
            'permissions' => $permissions,
        ]; 
    }

    public function update(SMSCoachingTemplates $template, array $data): SMSCoachingTemplates
    {
        $template->update($data);
        return $template;
    }

    public function delete(SMSCoachingTemplates $template): void
    {
        $template->delete();
    }

    public function getById(int $id): ?SMSCoachingTemplates
    {
        return SMSCoachingTemplates::findOrFail($id);
    }
}
