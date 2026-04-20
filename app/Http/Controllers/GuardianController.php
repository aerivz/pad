<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuardianController extends Controller
{
    private const RELATIONSHIP_OPTIONS = [
        'Padre',
        'Madre',
        'Tio',
        'Tia',
        'Hermano',
        'Hermana',
        'Abuelo',
        'Abuela',
        'Encargado',
        'Otro',
    ];

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateGuardian($request);

        DB::transaction(function () use ($data): void {
            $guardian = Guardian::create(Arr::only($data, ['nombres', 'apellidos', 'email_principal']));
            $this->syncMembers($guardian->id, $data['members']);
        });

        return redirect('/pad/familias')->with('status', 'Miembro familiar creado correctamente.');
    }

    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $data = $this->validateGuardian($request, $guardian);

        DB::transaction(function () use ($guardian, $data): void {
            $guardian->update(Arr::only($data, ['nombres', 'apellidos', 'email_principal']));
            $this->syncMembers($guardian->id, $data['members']);
        });

        return redirect('/pad/familias')->with('status', 'Miembro familiar actualizado correctamente.');
    }

    public function destroy(Guardian $guardian): RedirectResponse
    {
        $guardian->update(['activo' => false]);

        return redirect('/pad/familias')->with('status', 'Miembro familiar desactivado correctamente.');
    }

    private function validateGuardian(Request $request, ?Guardian $guardian = null): array
    {
        $data = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'email_principal' => [
                'required',
                'email',
                'max:150',
                $guardian
                    ? Rule::unique('padres', 'email_principal')->ignore($guardian->id)
                    : 'unique:padres,email_principal',
            ],
            'members' => ['required', 'array', 'min:1'],
            'members.*.alumno_id' => ['required', 'integer', 'distinct', 'exists:alumnos,id'],
            'members.*.parentesco' => ['required', 'string', Rule::in(self::RELATIONSHIP_OPTIONS)],
        ]);

        $data['members'] = collect($data['members'])
            ->map(fn (array $member) => [
                'alumno_id' => (int) $member['alumno_id'],
                'parentesco' => $member['parentesco'],
            ])
            ->values()
            ->all();

        return $data;
    }

    private function syncMembers(int $guardianId, array $members): void
    {
        DB::table('padre_alumno')->where('padre_id', $guardianId)->delete();

        DB::table('padre_alumno')->insert(
            collect($members)->map(fn (array $member) => [
                'padre_id' => $guardianId,
                'alumno_id' => $member['alumno_id'],
                'parentesco' => $member['parentesco'],
            ])->all()
        );
    }
}
