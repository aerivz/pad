# Guia tecnica para Codex y desarrollo

## Objetivo

Este documento explica como esta organizado `EduNotas` para que otro Codex o desarrollador pueda entrar al proyecto y entender rapido:

- que hace cada modulo
- donde vive la logica
- que tablas son clave
- como se resuelven rutas, menus, media y permisos
- que partes son delicadas al modificar

Ruta del proyecto usada actualmente:

```text
C:\xampp\htdocs\Pad
```

## Stack real del proyecto

- Laravel 12
- PHP 8.2
- MySQL / MariaDB
- Blade
- AdminLTE 3
- Bootstrap 4
- jQuery
- SweetAlert2
- DomPDF

## Mapa rapido del codigo

### Rutas

Archivo principal:

- [routes/web.php](C:/xampp/htdocs/Pad/routes/web.php)

Puntos importantes:

- existe soporte dual para rutas en raiz y en subcarpeta `/pad`
- las rutas nombradas principales viven en raiz
- existe un bloque duplicado bajo `Route::prefix('pad')` para compatibilidad con XAMPP y despliegues en subcarpeta
- `dashboard` esta en `/`
- `media` existe en:
  - `/media/{path}`
  - `/pad/media/{path}`

### Layout y vistas

Layout principal del panel:

- [resources/views/layouts/panel.blade.php](C:/xampp/htdocs/Pad/resources/views/layouts/panel.blade.php)

Aqui vive:

- sidebar
- breadcrumbs
- estilos comunes de mantenimientos
- SweetAlert2
- filtros rapidos de tablas
- render de `@stack('styles')` y `@stack('scripts')`

Vistas del panel:

- [resources/views/panel](C:/xampp/htdocs/Pad/resources/views/panel)

Patron actual de mantenimientos:

- toolbar superior
- filtros visibles
- tabla estilo catalogo
- `Nuevo` y `Editar` en modal
- desactivacion logica por `DELETE`

### Controladores

Controlador mas importante:

- [app/Http/Controllers/PanelController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/PanelController.php)

Responsabilidad:

- armar datos de pantalla
- cargar catalogos
- consolidar reportes
- proveer vistas de los modulos administrativos

Controladores CRUD por modulo:

- [SectionController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/SectionController.php)
- [StudentController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/StudentController.php)
- [TeacherController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/TeacherController.php)
- [SubjectController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/SubjectController.php)
- [AssignmentController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/AssignmentController.php)
- [GuardianController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/GuardianController.php)
- [EmailDispatchController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EmailDispatchController.php)
- [EmailTemplateController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EmailTemplateController.php)
- [MenuManagementController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/MenuManagementController.php)
- [RolePermissionController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/RolePermissionController.php)
- [UserManagementController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/UserManagementController.php)
- [SystemBackupController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/SystemBackupController.php)
- [EvaluationController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EvaluationController.php)

Controladores especiales:

- [AuthController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/AuthController.php)
- [MediaController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/MediaController.php)

## Modelos clave

Ubicacion:

- [app/Models](C:/xampp/htdocs/Pad/app/Models)

### Academico base

- `Section`
- `Student`
- `Teacher`
- `Subject`
- `Assignment`

### Seguridad

- `User`
- `Role`
- `Menu`

### Familias

- `Guardian`

### Correos

- `EmailDispatch`
- `EmailTemplate`

### Notas / colector

- `CollectorCategory`
- `StudentCategoryGrade`
- `StudentConduct`

Modelos legado que siguen existiendo:

- `Note`
- `Evaluation`
- `EvaluationScore`

Importante:

- el sistema actual de colector trabaja principalmente con:
  - `categorias_evaluacion`
  - `notas_alumnos`
  - `conducta_alumnos`
- los modelos viejos de notas detalladas existen por compatibilidad historica y no deben ser el primer lugar a tocar si el cambio es del colector actual

## Base de datos: tablas importantes

### Catalogos base

- `secciones`
- `alumnos`
- `profesores`
- `materias`
- `asignaciones`
- `trimestres`

### Seguridad y menu

- `usuarios`
- `roles`
- `menus`
- `rol_menu`

### Familias

- `padres`
- `padre_alumno`

### Correos

- `plantillas_correo`
- `envios_correo`

### Colector de notas

- `categorias_evaluacion`
- `notas_alumnos`
- `conducta_alumnos`

### Soporte

- `auditoria_notas`
- `backups_sistema`

## Como funciona cada modulo

### 1. Dashboard

Entrada:

- `PanelController::dashboard()`

Muestra:

- totales generales
- actividad resumida

### 2. Secciones, Alumnos, Profesores, Materias

Patron:

- `PanelController` carga la lista
- controlador CRUD guarda, actualiza o desactiva
- vista Blade del modulo muestra filtros + tabla + modal

Desactivacion:

- no se borra fisicamente
- se marca `activo = false`

### 3. Asignaciones

Relaciona:

- seccion
- materia
- profesor
- anio lectivo

Archivo clave:

- [AssignmentController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/AssignmentController.php)

Validaciones importantes:

- no duplicar asignacion activa de la misma materia en misma seccion y anio
- no repetir el mismo profesor en la misma combinacion por error

### 4. Familias

Permite:

- crear familiar
- relacionarlo con varios alumnos
- guardar parentesco

Vista clave:

- [guardians.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/guardians.blade.php)

Punto delicado:

- el modal maneja filas dinamicas con JavaScript
- tambien filtra alumnos por seccion dentro de cada vinculo

### 5. Correos

Tiene dos mantenimientos en una pantalla:

- historial/envios
- plantillas

Archivos clave:

- [emails.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/emails.blade.php)
- [EmailDispatchController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EmailDispatchController.php)
- [EmailTemplateController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EmailTemplateController.php)

Punto delicado:

- la relacion familiar-alumno se valida contra `padre_alumno`

### 6. Colector de notas

Pantalla principal:

- [gradebook.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/gradebook.blade.php)

Controlador:

- [EvaluationController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/EvaluationController.php)

Reglas funcionales actuales:

- trabaja por:
  - anio lectivo
  - seccion
  - materia
  - trimestre
- las categorias pueden ser `normal` o `laboratorio`
- el colector calcula:
  - progress 1
  - progress 2
  - report card
- hay importacion desde XLSX/CSV

Servicios relacionados:

- [GradeCollectorImportService.php](C:/xampp/htdocs/Pad/app/Services/GradeCollectorImportService.php)
- [GradeCollectorService.php](C:/xampp/htdocs/Pad/app/Services/GradeCollectorService.php)

### 7. Boletines

Pantalla:

- [reportcard.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/reportcard.blade.php)

PDF:

- [reportcard-pdf.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/reportcard-pdf.blade.php)

Logica:

- se apoya en asignaciones, categorias, notas por alumno y conducta
- puede generar por alumno o por seccion completa

### 8. Perfiles y menus

Pantallas:

- [profiles.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/profiles.blade.php)
- [menus.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/menus.blade.php)

Permisos:

- cada `role` se relaciona con `menus`
- el middleware bloquea rutas por clave de menu

Middleware:

- [EnsureMenuAccess.php](C:/xampp/htdocs/Pad/app/Http/Middleware/EnsureMenuAccess.php)

## Login, permisos y redirecciones

### Login

Controlador:

- [AuthController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/AuthController.php)

Puntos clave:

- usa tabla `usuarios`
- `logout`, `guest`, `419` y redirects intentan respetar host actual

### Menus y URLs

Archivo clave:

- [AppUrl.php](C:/xampp/htdocs/Pad/app/Support/AppUrl.php)

Responsabilidad:

- generar URLs internas segun raiz o `/pad`
- resolver media
- evitar hardcodes a `localhost`

Helpers globales:

- [helpers.php](C:/xampp/htdocs/Pad/app/Support/helpers.php)

Helpers disponibles:

- `app_nav_url()`
- `app_media_url()`

## Imagenes y media

Ruta especial:

- `media/{path}`
- `pad/media/{path}`

Controlador:

- [MediaController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/MediaController.php)

Se usa para:

- logo por defecto
- avatar por defecto
- archivos en `public/images`
- archivos en `public/uploads`

Punto delicado:

- no usar URLs absolutas duras a `localhost`
- preferir `app_media_url()`

## Backups

Pantalla:

- [backups.blade.php](C:/xampp/htdocs/Pad/resources/views/panel/backups.blade.php)

Controlador y job:

- [SystemBackupController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/SystemBackupController.php)
- [GenerateSystemBackupJob.php](C:/xampp/htdocs/Pad/app/Jobs/GenerateSystemBackupJob.php)
- [SystemBackupService.php](C:/xampp/htdocs/Pad/app/Services/SystemBackupService.php)

Funcion:

- genera ZIP en segundo plano
- incluye base SQL, `public/images` y `public/uploads`

## Notificacion de errores por Telegram

Servicio:

- [TelegramErrorNotifier.php](C:/xampp/htdocs/Pad/app/Services/TelegramErrorNotifier.php)

Integracion:

- [bootstrap/app.php](C:/xampp/htdocs/Pad/bootstrap/app.php)

Config:

- [config/services.php](C:/xampp/htdocs/Pad/config/services.php)
- [.env.example](C:/xampp/htdocs/Pad/.env.example)

Variables:

```env
TELEGRAM_ERRORS_ENABLED=false
TELEGRAM_ERRORS_BOT_TOKEN=
TELEGRAM_ERRORS_CHAT_ID=
TELEGRAM_ERRORS_TIMEOUT=5
```

Comportamiento:

- reporta excepciones reales
- ignora ruido comun como `404`, `419`, `422`

## Convenciones del proyecto

### 1. Eliminacion logica

Regla general:

- evitar `DELETE` fisico
- usar `activo = false`

### 2. Formularios de mantenimiento

Patron actual:

- lista en tabla
- filtros arriba
- `Nuevo` y `Editar` en modal
- `Ver` opcional en modal de detalle

### 3. Commits

Se configuraron con plantilla local:

- [.gitmessage-es.txt](C:/xampp/htdocs/Pad/.gitmessage-es.txt)

Formato esperado:

```text
Modifico:

Porque:

Impacto positivo:
```

### 4. Estilos comunes

Los catalogos comparten CSS desde:

- [panel.blade.php](C:/xampp/htdocs/Pad/resources/views/layouts/panel.blade.php)

Si se ajusta una tabla de mantenimiento, casi siempre conviene tocar ahi primero.

## Riesgos y puntos delicados

### Rutas duplicadas

Hay rutas en raiz y bajo `/pad`.

Si agregas un modulo nuevo:

- crea ruta nombrada en raiz
- replica ruta en grupo `prefix('pad')`
- usa `AppUrl::route()` o `app_nav_url()`

### Modulos viejos vs modulos actuales

Existen restos de implementaciones anteriores de notas.

Antes de modificar:

- confirmar si el flujo real usa `notas_alumnos` y `conducta_alumnos`
- no asumir que `notas` o `evaluacion_notas` son la fuente actual

### Especialidad del profesor

Hoy se guarda como texto, pero el formulario la sugiere desde `materias`.

Eso significa:

- no hay FK real a `materias`
- hay compatibilidad con valores viejos

### Host actual

El sistema se ha ajustado para correr:

- en `localhost/pad`
- en raiz publica
- en IP publica

No introducir links absolutos si no es indispensable.

## Flujo recomendado para otro Codex

1. Leer:
   - [README.md](C:/xampp/htdocs/Pad/README.md)
   - [GUIA_TECNICA_CODEX.md](C:/xampp/htdocs/Pad/docs/GUIA_TECNICA_CODEX.md)
2. Revisar:
   - [routes/web.php](C:/xampp/htdocs/Pad/routes/web.php)
   - [PanelController.php](C:/xampp/htdocs/Pad/app/Http/Controllers/PanelController.php)
   - [panel.blade.php](C:/xampp/htdocs/Pad/resources/views/layouts/panel.blade.php)
3. Ubicar el modulo afectado:
   - vista Blade
   - controlador CRUD
   - modelo
4. Verificar si usa:
   - `AppUrl`
   - `app_media_url`
   - `activo`
   - permisos por menu
5. Limpiar cache despues de cambios:

```bash
php artisan optimize:clear
php artisan view:clear
```

6. Validar:

```bash
php artisan test
```

## Comandos utiles

```bash
php artisan serve
php artisan route:list
php artisan migrate
php artisan optimize:clear
php artisan view:clear
php artisan test
php artisan queue:work --queue=backups,default
```

## Resumen corto

Si otro Codex entra sin contexto, debe recordar esto:

- el cerebro del panel esta en `PanelController`
- las URLs deben pasar por `AppUrl`
- las imagenes deben pasar por `app_media_url`
- casi todo se desactiva con `activo = false`
- hay compatibilidad dual raiz y `/pad`
- el colector actual usa `categorias_evaluacion`, `notas_alumnos` y `conducta_alumnos`
- los permisos viven en `menus`, `roles` y `rol_menu`
