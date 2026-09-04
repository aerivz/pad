<?php

namespace App\Http\Controllers;

use App\Models\EmailDispatch;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Jobs\SendEmailDispatchJob;
use App\Support\AppUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmailDispatchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDispatch($request);
        $template = EmailTemplate::with('roles')->findOrFail((int) $data['plantilla_id']);
        $this->guardTemplateAccess($template);
        $recipientEmail = (string) DB::table('padres')->where('id', $data['padre_id'])->value('email_principal');

        EmailDispatch::create([
            ...$data,
            'usuario_id' => Auth::id(),
            'destinatario_email' => $recipientEmail,
            'adjuntos_generados' => $template->documentos_generados ?? [],
            'activo' => true,
        ]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo creado correctamente.');
    }

    public function update(Request $request, EmailDispatch $dispatch): RedirectResponse
    {
        if ($dispatch->en_cola) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'No puedes editar un correo mientras esta en cola.');
        }

        $data = $this->validateDispatch($request);
        $template = EmailTemplate::with('roles')->findOrFail((int) $data['plantilla_id']);
        $this->guardTemplateAccess($template);
        $recipientEmail = (string) DB::table('padres')->where('id', $data['padre_id'])->value('email_principal');

        $dispatch->update([
            ...$data,
            'usuario_id' => Auth::id(),
            'destinatario_email' => $recipientEmail,
            'adjuntos_generados' => $template->documentos_generados ?? [],
        ]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo actualizado correctamente.');
    }

    public function destroy(EmailDispatch $dispatch): RedirectResponse
    {
        if ($dispatch->en_cola) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'No puedes desactivar un correo mientras esta en cola.');
        }

        $dispatch->update(['activo' => false]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo desactivado correctamente.');
    }

    public function send(EmailDispatch $dispatch): RedirectResponse
    {
        $dispatch->load('template.roles');

        if (! $dispatch->activo) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'El registro de correo esta inactivo.');
        }

        $this->guardTemplateAccess($dispatch->template);

        if ($dispatch->en_cola) {
            return redirect(AppUrl::route('emails.index'))->with('error', 'Este correo ya esta en cola para envio.');
        }

        $dispatch->update([
            'estado' => 'pendiente',
            'en_cola' => true,
            'usuario_id' => Auth::id(),
            'error_mensaje' => null,
        ]);

        SendEmailDispatchJob::dispatch($dispatch->id)->onConnection('database')->onQueue('emails');

        return redirect(AppUrl::route('emails.index'))->with('status', 'Correo enviado a la cola de procesamiento.');
    }

    private function validateDispatch(Request $request): array
    {
        return $request->validate([
            'plantilla_id' => ['required', 'integer', 'exists:plantillas_correo,id'],
            'padre_id' => ['required', 'integer', 'exists:padres,id'],
            'alumno_id' => [
                'required',
                'integer',
                'exists:alumnos,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $exists = DB::table('padre_alumno')
                        ->where('padre_id', $request->input('padre_id'))
                        ->where('alumno_id', $value)
                        ->exists();

                    if (! $exists) {
                        $fail('El familiar seleccionado no esta vinculado con el alumno indicado.');
                    }
                },
            ],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
            'estado' => ['required', Rule::in(['pendiente', 'enviado', 'fallido'])],
        ]);
    }

    private function guardTemplateAccess(EmailTemplate $template): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $user->loadMissing('role');

        if (($user->role->nombre ?? null) === 'admin') {
            return;
        }

        $allowedRoleIds = $template->roles()->pluck('roles.id');

        if ($allowedRoleIds->isEmpty()) {
            return;
        }

        abort_unless($allowedRoleIds->contains((int) $user->rol_id), 403);
    }
}
