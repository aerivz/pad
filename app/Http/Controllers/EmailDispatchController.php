<?php

namespace App\Http\Controllers;

use App\Models\EmailDispatch;
use App\Support\AppUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EmailDispatchController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateDispatch($request);

        EmailDispatch::create([
            ...$data,
            'activo' => true,
        ]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo creado correctamente.');
    }

    public function update(Request $request, EmailDispatch $dispatch): RedirectResponse
    {
        $data = $this->validateDispatch($request);

        $dispatch->update($data);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo actualizado correctamente.');
    }

    public function destroy(EmailDispatch $dispatch): RedirectResponse
    {
        $dispatch->update(['activo' => false]);

        return redirect(AppUrl::route('emails.index'))->with('status', 'Registro de correo desactivado correctamente.');
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
}
