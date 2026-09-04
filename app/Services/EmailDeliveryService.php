<?php

namespace App\Services;

use App\Models\EmailDispatch;
use App\Models\EmailTemplate;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use App\Support\GeneratedDocumentCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailDeliveryService
{
    public function __construct(
        private readonly AnnualReportService $annualReportService,
        private readonly GeneratedDocumentCatalog $documentCatalog,
    ) {
    }

    public function sendDispatch(EmailDispatch $dispatch, ?User $actor = null): array
    {
        $template = $dispatch->template()->with('roles')->firstOrFail();
        $guardian = Guardian::active()->findOrFail($dispatch->padre_id);
        $student = Student::active()->findOrFail($dispatch->alumno_id);
        $trimester = DB::table('trimestres')->where('id', $dispatch->trimestre_id)->first();
        $preview = $this->preview($template, $guardian, $student, $trimester, $actor);
        $attachments = $this->buildAttachments($template->generated_documents ?? [], $student->id, $student->seccion_id);
        $recipient = $preview['recipient'];
        $subject = $preview['subject'];
        $html = $preview['html'];

        Mail::send([], [], function ($message) use ($recipient, $subject, $html, $attachments): void {
            $message->to($recipient)
                ->subject($subject)
                ->html($html);

            foreach ($attachments as $attachment) {
                $message->attachData(
                    $attachment['bytes'],
                    $attachment['name'],
                    ['mime' => 'application/pdf']
                );
            }
        });

        return [
            'recipient' => $recipient,
            'attachments' => collect($attachments)->pluck('code')->values()->all(),
        ];
    }

    /**
     * @return array{recipient:string,subject:string,html:string,attachments:array<int, array{code:string,label:string}>}
     */
    public function preview(EmailTemplate $template, Guardian $guardian, Student $student, ?object $trimester, ?User $actor = null): array
    {
        $roleName = $actor?->role()->value('nombre');
        $replacements = [
            '{{familiar_nombre}}' => trim($guardian->nombres.' '.$guardian->apellidos),
            '{{alumno_nombre}}' => trim($student->nombres.' '.$student->apellidos),
            '{{trimestre}}' => $trimester->nombre ?? 'Sin trimestre',
            '{{perfil}}' => $roleName ?? 'Sin perfil',
            '{{app_nombre}}' => (string) config('app.name', 'Sistema Escolar'),
            '{{app_url}}' => (string) config('app.url'),
        ];
        $labels = $this->documentCatalog->labels();

        return [
            'recipient' => (string) $guardian->email_principal,
            'subject' => $this->renderBody((string) $template->asunto, $replacements),
            'html' => $this->renderBody((string) $template->cuerpo_html, $replacements),
            'attachments' => collect($template->generated_documents ?? [])
                ->map(fn (string $code) => ['code' => $code, 'label' => $labels[$code] ?? $code])
                ->values()
                ->all(),
        ];
    }

    private function renderBody(string $body, array $replacements): string
    {
        return strtr($body, $replacements);
    }

    /**
     * @return array<int, array{code:string,name:string,bytes:string}>
     */
    private function buildAttachments(array $codes, int $studentId, ?int $sectionId = null): array
    {
        $attachments = [];

        foreach ($codes as $code) {
            if ($code !== 'report_card_annual_student') {
                throw new \RuntimeException('La plantilla contiene un tipo de documento no soportado: '.$code);
            }

            $bytes = $this->annualReportService->renderStudentPdfBytes($studentId, $sectionId);

            if ($bytes === null) {
                throw new \RuntimeException('No se pudo generar el boletin anual para el alumno seleccionado.');
            }

            $studentName = DB::table('alumnos')
                ->where('id', $studentId)
                ->selectRaw("CONCAT(nombres, ' ', apellidos) as nombre")
                ->value('nombre');

            $slug = Str::slug((string) $studentName);

            $attachments[] = [
                'code' => $code,
                'name' => 'boletin-anual-'.($slug !== '' ? $slug : $studentId).'.pdf',
                'bytes' => $bytes,
            ];
        }

        return $attachments;
    }
}
