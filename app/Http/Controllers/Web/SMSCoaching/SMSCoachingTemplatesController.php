<?php

namespace App\Http\Controllers\Web\SMSCoaching;

use App\Http\Requests\SMSCoaching\SMSCoachingTemplateRequest;
use App\Services\SMSCoaching\SMSCoachingTemplatesService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SMSCoachingTemplatesController extends Controller
{
    protected SMSCoachingTemplatesService $service;

    public function __construct(SMSCoachingTemplatesService $service)
    {
        $this->service = $service;
    }

    public function index(string $tenantSlug): Response
    {
        return Inertia::render('SMSCoachingTemplates/Index', 
            $this->service->list()
           );
    }

    public function show(string $tenantSlug, $id): Response
    {
        return Inertia::render('SMSCoachingTemplates/Show', $this->service->showing((int)$id));
    }

    public function create(string $tenantSlug): Response
    {
        return Inertia::render('SMSCoachingTemplates/Create', 
            $this->service->creating()
        );
    }

    public function store(string $tenantSlug, SMSCoachingTemplateRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        
        return redirect()
            ->route('sms-coaching-templates.index', $tenantSlug)
            ->with('success', 'Template created successfully.');
    }

    public function edit(string $tenantSlug, $id): Response
    {
        return Inertia::render('SMSCoachingTemplates/Edit', $this->service->editing((int)$id));
    }

    public function update(string $tenantSlug, SMSCoachingTemplateRequest $request, $id): RedirectResponse
    {
        $template = $this->service->getById((int)$id);
        
        if (!$template) {
            throw new NotFoundHttpException('Template not found');
        }

        $this->service->update($template, $request->validated());
        
        return redirect()
            ->route('sms-coaching-templates.index', $tenantSlug)
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(string $tenantSlug, $id): RedirectResponse
    {
        $template = $this->service->getById((int)$id);
        
        if (!$template) {
            throw new NotFoundHttpException('Template not found');
        }

        $this->service->delete($template);
        
        return redirect()
            ->route('sms-coaching-templates.index', $tenantSlug)
            ->with('success', 'Template deleted successfully.');
    }
}