<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Services\AnnualReportService;
use App\Services\EmailDeliveryService;
use App\Support\AppUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmailPreviewController extends Controller
{
    public function show(Request $request, EmailDeliveryService $delivery): \Illuminate\View\View
    {
        [$template, $guardian, $student, $trimester] = $this->previewData($request);

        return view('panel.email-preview', [
            'preview' => $delivery->preview($template, $guardian, $student, $trimester, Auth::user()),
            'documentUrlBase' => AppUrl::route('emails.preview.document'),
            'query' => $request->only(['plantilla_id', 'padre_id', 'alumno_id', 'trimestre_id']),
        ]);
    }

    public function document(Request $request, AnnualReportService $reports): \Symfony\Component\HttpFoundation\Response
    {
        [$template, $guardian, $student] = $this->previewData($request);
        $code = (string) $request->query('documento');
        abort_unless(in_array($code, $template->documentos_generados ?? [], true), 404);
        abort_unless($code === 'report_card_annual_student', 404);

        $bytes = $reports->renderStudentPdfBytes($student->id, $student->seccion_id);
        abort_if($bytes === null, 404, 'No se pudo generar el documento.');

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="boletin-anual-'.$student->id.'.pdf"',
        ]);
    }

    private function previewData(Request $request): array
    {
        $data = $request->validate([
            'plantilla_id' => ['required', 'integer', 'exists:plantillas_correo,id'],
            'padre_id' => ['required', 'integer', 'exists:padres,id'],
            'alumno_id' => ['required', 'integer', 'exists:alumnos,id'],
            'trimestre_id' => ['required', 'integer', 'exists:trimestres,id'],
        ]);
        $template = EmailTemplate::active()->with('roles')->findOrFail($data['plantilla_id']);
        $this->guardTemplateAccess($template);
        $guardian = Guardian::active()->findOrFail($data['padre_id']);
        $student = Student::active()->findOrFail($data['alumno_id']);
        abort_unless($guardian->students()->whereKey($student->id)->exists(), 422);
        $trimester = DB::table('trimestres')->where('id', $data['trimestre_id'])->first();

        return [$template, $guardian, $student, $trimester];
    }

    private function guardTemplateAccess(EmailTemplate $template): void
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');
        if (($user->role->nombre ?? null) === 'admin') return;
        $allowedRoleIds = $template->roles()->pluck('roles.id');
        abort_unless($allowedRoleIds->isEmpty() || $allowedRoleIds->contains((int) $user->rol_id), 403);
    }
}
