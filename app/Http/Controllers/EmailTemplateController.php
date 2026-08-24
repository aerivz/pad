<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Support\GeneratedDocumentCatalog;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmailTemplateController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTemplate($request);

        $template = EmailTemplate::create([
            ...$data,
            'activo' => true,
        ]);
        $template->roles()->sync($data['roles'] ?? []);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Plantilla creada correctamente.');
    }

    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $data = $this->validateTemplate($request, $template);

        $template->update($data);
        $template->roles()->sync($data['roles'] ?? []);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Plantilla actualizada correctamente.');
    }

    public function destroy(EmailTemplate $template): RedirectResponse
    {
        $template->update(['activo' => false]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Plantilla desactivada correctamente.');
    }

    private function validateTemplate(Request $request, ?EmailTemplate $template = null): array
    {
        return $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:100',
                Rule::unique('plantillas_correo', 'nombre')->ignore($template?->id),
            ],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'asunto' => ['required', 'string', 'max:255'],
            'cuerpo_html' => ['required', 'string'],
            'documentos_generados' => ['nullable', 'array'],
            'documentos_generados.*' => ['string', Rule::in(app(GeneratedDocumentCatalog::class)->codes())],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ]);
    }
}
