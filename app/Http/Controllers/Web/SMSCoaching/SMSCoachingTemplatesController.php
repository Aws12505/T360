<?php

namespace App\Http\Controllers\Web\SMSCoaching;

use App\Http\Requests\SMSCoaching\SMSCoachingTemplateRequest;
use App\Models\SMSCoachingTemplates;
use App\Services\SMSCoaching\SMSCoachingTemplatesService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;

class SMSCoachingTemplatesController extends Controller
{
    protected SMSCoachingTemplatesService $service;

    public function __construct(SMSCoachingTemplatesService $service)
    {
        $this->service = $service;
    }

    public function index(): Response
    {
        return Inertia::render('SMSCoachingTemplates/Index', $this->service->list());
    }

    public function show($id): Response
     {
        $template = $this->service->getById($id);
        return Inertia::render('SMSCoachingTemplates/Show', compact('template'));
    }

    public function create(): Response
    {
        return Inertia::render('SMSCoachingTemplates/Create', $this->service->creating());
    }

    public function store(SMSCoachingTemplateRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('sms-coaching-templates.index')->with('success', 'Template created.');
    }

    public function edit($id): Response
    {
        $template = $this->service->getById($id);
        return Inertia::render('SMSCoachingTemplates/Edit', compact('template'));
    }

    public function update(SMSCoachingTemplateRequest $request, $id): RedirectResponse
    {
        $template = $this->service->getById($id);
        $this->service->update($template, $request->validated());
        return redirect()->route('sms-coaching-templates.index')->with('success', 'Template updated.');
    }

    public function destroy($id): RedirectResponse
    {
        $template = $this->service->getById($id);
        $this->service->delete($template);
        return redirect()->route('sms-coaching-templates.index')->with('success', 'Template deleted.');
    }
}
