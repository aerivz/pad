<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista previa de correo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="bg-light p-3">
<main class="card mx-auto" style="max-width:860px;">
    <div class="card-header"><strong>Vista previa de correo</strong></div>
    <div class="card-body">
        <dl class="row mb-3"><dt class="col-sm-2">Para</dt><dd class="col-sm-10">{{ $preview['recipient'] }}</dd><dt class="col-sm-2">Asunto</dt><dd class="col-sm-10">{{ $preview['subject'] }}</dd></dl>
        <div class="border rounded p-3 bg-white">{!! $preview['html'] !!}</div>
        <div class="mt-3"><strong>Adjuntos</strong>
            @forelse ($preview['attachments'] as $attachment)
                <a class="btn btn-outline-danger btn-sm ml-2" target="_blank" href="{{ $documentUrlBase.'?'.http_build_query([...$query, 'documento' => $attachment['code']]) }}"><i class="fas fa-file-pdf"></i> {{ $attachment['label'] }}</a>
            @empty
                <span class="text-muted ml-2">Esta plantilla no incluye adjuntos.</span>
            @endforelse
        </div>
    </div>
</main>
</body>
</html>
