<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\EmailDispatchController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SystemBackupController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserManagementController;
use App\Models\Menu;
use App\Support\AppUrl;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return app(AuthController::class)->create();
    }

    $user = request()->user();

    if ($user?->hasMenuAccess('dashboard')) {
        return app(PanelController::class)->dashboard();
    }

    $firstMenuKey = $user?->allowedMenuKeys()[0] ?? null;
    $firstMenuUrl = $firstMenuKey
        ? Menu::query()->where('clave', $firstMenuKey)->where('activo', true)->value('url')
        : null;

    if ($firstMenuUrl) {
        return redirect(AppUrl::menu($firstMenuUrl));
    }

    abort(403);
})->name('dashboard');
Route::redirect('/pad/pad', '/pad/');
Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::prefix('pad')->middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create']);
    Route::post('/login', [AuthController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/secciones', [PanelController::class, 'sections'])->middleware('menu.access:sections')->name('sections.index');
    Route::post('/secciones', [SectionController::class, 'store'])->middleware('menu.access:sections')->name('sections.store');
    Route::patch('/secciones/{section}', [SectionController::class, 'update'])->middleware('menu.access:sections')->name('sections.update');
    Route::delete('/secciones/{section}', [SectionController::class, 'destroy'])->middleware('menu.access:sections')->name('sections.destroy');

    Route::get('/alumnos', [PanelController::class, 'students'])->middleware('menu.access:students')->name('students.index');
    Route::post('/alumnos', [StudentController::class, 'store'])->middleware('menu.access:students')->name('students.store');
    Route::patch('/alumnos/{student}', [StudentController::class, 'update'])->middleware('menu.access:students')->name('students.update');
    Route::delete('/alumnos/{student}', [StudentController::class, 'destroy'])->middleware('menu.access:students')->name('students.destroy');

    Route::get('/profesores', [PanelController::class, 'teachers'])->middleware('menu.access:teachers')->name('teachers.index');
    Route::post('/profesores', [TeacherController::class, 'store'])->middleware('menu.access:teachers')->name('teachers.store');
    Route::patch('/profesores/{teacher}', [TeacherController::class, 'update'])->middleware('menu.access:teachers')->name('teachers.update');
    Route::delete('/profesores/{teacher}', [TeacherController::class, 'destroy'])->middleware('menu.access:teachers')->name('teachers.destroy');

    Route::get('/materias', [PanelController::class, 'subjects'])->middleware('menu.access:subjects')->name('subjects.index');
    Route::post('/materias', [SubjectController::class, 'store'])->middleware('menu.access:subjects')->name('subjects.store');
    Route::patch('/materias/{subject}', [SubjectController::class, 'update'])->middleware('menu.access:subjects')->name('subjects.update');
    Route::delete('/materias/{subject}', [SubjectController::class, 'destroy'])->middleware('menu.access:subjects')->name('subjects.destroy');

    Route::get('/asignaciones', [PanelController::class, 'assignments'])->middleware('menu.access:assignments')->name('assignments.index');
    Route::post('/asignaciones', [AssignmentController::class, 'store'])->middleware('menu.access:assignments')->name('assignments.store');
    Route::patch('/asignaciones/{assignment}', [AssignmentController::class, 'update'])->middleware('menu.access:assignments')->name('assignments.update');
    Route::delete('/asignaciones/{assignment}', [AssignmentController::class, 'destroy'])->middleware('menu.access:assignments')->name('assignments.destroy');

    Route::get('/familias', [PanelController::class, 'guardians'])->middleware('menu.access:guardians')->name('guardians.index');
    Route::post('/familias', [GuardianController::class, 'store'])->middleware('menu.access:guardians')->name('guardians.store');
    Route::patch('/familias/{guardian}', [GuardianController::class, 'update'])->middleware('menu.access:guardians')->name('guardians.update');
    Route::delete('/familias/{guardian}', [GuardianController::class, 'destroy'])->middleware('menu.access:guardians')->name('guardians.destroy');
    Route::redirect('/padres', '/familias');

    Route::get('/notas', [PanelController::class, 'gradeBook'])->middleware('menu.access:gradebook')->name('gradebook.index');
    Route::post('/notas/categorias', [EvaluationController::class, 'store'])->middleware('menu.access:gradebook')->name('gradebook.categories.store');
    Route::patch('/notas/categorias/{evaluation}', [EvaluationController::class, 'update'])->middleware('menu.access:gradebook')->name('gradebook.categories.update');
    Route::delete('/notas/categorias/{evaluation}', [EvaluationController::class, 'destroy'])->middleware('menu.access:gradebook')->name('gradebook.categories.destroy');
    Route::post('/notas/calificaciones', [EvaluationController::class, 'syncScores'])->middleware('menu.access:gradebook')->name('gradebook.scores.sync');
    Route::post('/notas/importar', [EvaluationController::class, 'import'])->middleware('menu.access:gradebook')->name('gradebook.import');
    Route::get('/notas/plantillas/{template}', [EvaluationController::class, 'template'])->middleware('menu.access:gradebook')->name('gradebook.templates');

    Route::get('/report-card', [PanelController::class, 'reportCard'])->middleware('menu.access:reportcard')->name('reportcard.index');
    Route::get('/report-card/pdf', [PanelController::class, 'reportCardPdf'])->middleware('menu.access:reportcard')->name('reportcard.pdf');

    Route::get('/correos', [PanelController::class, 'emails'])->middleware('menu.access:emails')->name('emails.index');
    Route::post('/correos', [EmailDispatchController::class, 'store'])->middleware('menu.access:emails')->name('emails.store');
    Route::patch('/correos/{dispatch}', [EmailDispatchController::class, 'update'])->middleware('menu.access:emails')->name('emails.update');
    Route::delete('/correos/{dispatch}', [EmailDispatchController::class, 'destroy'])->middleware('menu.access:emails')->name('emails.destroy');
    Route::post('/correos/plantillas', [EmailTemplateController::class, 'store'])->middleware('menu.access:emails')->name('emails.templates.store');
    Route::patch('/correos/plantillas/{template}', [EmailTemplateController::class, 'update'])->middleware('menu.access:emails')->name('emails.templates.update');
    Route::delete('/correos/plantillas/{template}', [EmailTemplateController::class, 'destroy'])->middleware('menu.access:emails')->name('emails.templates.destroy');

    Route::get('/usuarios', [PanelController::class, 'users'])->middleware('menu.access:users')->name('users.index');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->middleware('menu.access:users')->name('users.store');
    Route::patch('/usuarios/{user}', [UserManagementController::class, 'update'])->middleware('menu.access:users')->name('users.update');
    Route::delete('/usuarios/{user}', [UserManagementController::class, 'destroy'])->middleware('menu.access:users')->name('users.destroy');

    Route::get('/perfiles', [PanelController::class, 'profiles'])->middleware('menu.access:profiles')->name('profiles.index');
    Route::post('/perfiles', [RolePermissionController::class, 'store'])->middleware('menu.access:profiles')->name('profiles.store');
    Route::patch('/perfiles/{role}', [RolePermissionController::class, 'update'])->middleware('menu.access:profiles')->name('profiles.update');
    Route::delete('/perfiles/{role}', [RolePermissionController::class, 'destroy'])->middleware('menu.access:profiles')->name('profiles.destroy');

    Route::get('/menus', [PanelController::class, 'menus'])->middleware('menu.access:menus')->name('menus.index');
    Route::post('/menus', [MenuManagementController::class, 'store'])->middleware('menu.access:menus')->name('menus.store');
    Route::patch('/menus/{menu}', [MenuManagementController::class, 'update'])->middleware('menu.access:menus')->name('menus.update');
    Route::delete('/menus/{menu}', [MenuManagementController::class, 'destroy'])->middleware('menu.access:menus')->name('menus.destroy');

    Route::get('/backups', [PanelController::class, 'backups'])->middleware('menu.access:backups')->name('backups.index');
    Route::post('/backups', [SystemBackupController::class, 'store'])->middleware('menu.access:backups')->name('backups.store');
    Route::get('/backups/{backup}/download', [SystemBackupController::class, 'download'])->middleware('menu.access:backups')->name('backups.download');

    Route::get('/configuracion', [PanelController::class, 'config'])->middleware('menu.access:config')->name('config.index');
});

Route::prefix('pad')->middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy']);
    Route::get('/', [PanelController::class, 'dashboard'])->middleware('menu.access:dashboard');

    Route::get('/secciones', [PanelController::class, 'sections'])->middleware('menu.access:sections');
    Route::post('/secciones', [SectionController::class, 'store'])->middleware('menu.access:sections');
    Route::patch('/secciones/{section}', [SectionController::class, 'update'])->middleware('menu.access:sections');
    Route::delete('/secciones/{section}', [SectionController::class, 'destroy'])->middleware('menu.access:sections');

    Route::get('/alumnos', [PanelController::class, 'students'])->middleware('menu.access:students');
    Route::post('/alumnos', [StudentController::class, 'store'])->middleware('menu.access:students');
    Route::patch('/alumnos/{student}', [StudentController::class, 'update'])->middleware('menu.access:students');
    Route::delete('/alumnos/{student}', [StudentController::class, 'destroy'])->middleware('menu.access:students');

    Route::get('/profesores', [PanelController::class, 'teachers'])->middleware('menu.access:teachers');
    Route::post('/profesores', [TeacherController::class, 'store'])->middleware('menu.access:teachers');
    Route::patch('/profesores/{teacher}', [TeacherController::class, 'update'])->middleware('menu.access:teachers');
    Route::delete('/profesores/{teacher}', [TeacherController::class, 'destroy'])->middleware('menu.access:teachers');

    Route::get('/materias', [PanelController::class, 'subjects'])->middleware('menu.access:subjects');
    Route::post('/materias', [SubjectController::class, 'store'])->middleware('menu.access:subjects');
    Route::patch('/materias/{subject}', [SubjectController::class, 'update'])->middleware('menu.access:subjects');
    Route::delete('/materias/{subject}', [SubjectController::class, 'destroy'])->middleware('menu.access:subjects');

    Route::get('/asignaciones', [PanelController::class, 'assignments'])->middleware('menu.access:assignments');
    Route::post('/asignaciones', [AssignmentController::class, 'store'])->middleware('menu.access:assignments');
    Route::patch('/asignaciones/{assignment}', [AssignmentController::class, 'update'])->middleware('menu.access:assignments');
    Route::delete('/asignaciones/{assignment}', [AssignmentController::class, 'destroy'])->middleware('menu.access:assignments');

    Route::get('/familias', [PanelController::class, 'guardians'])->middleware('menu.access:guardians');
    Route::post('/familias', [GuardianController::class, 'store'])->middleware('menu.access:guardians');
    Route::patch('/familias/{guardian}', [GuardianController::class, 'update'])->middleware('menu.access:guardians');
    Route::delete('/familias/{guardian}', [GuardianController::class, 'destroy'])->middleware('menu.access:guardians');
    Route::redirect('/padres', '/pad/familias');

    Route::get('/notas', [PanelController::class, 'gradeBook'])->middleware('menu.access:gradebook');
    Route::post('/notas/categorias', [EvaluationController::class, 'store'])->middleware('menu.access:gradebook');
    Route::patch('/notas/categorias/{evaluation}', [EvaluationController::class, 'update'])->middleware('menu.access:gradebook');
    Route::delete('/notas/categorias/{evaluation}', [EvaluationController::class, 'destroy'])->middleware('menu.access:gradebook');
    Route::post('/notas/calificaciones', [EvaluationController::class, 'syncScores'])->middleware('menu.access:gradebook');
    Route::post('/notas/importar', [EvaluationController::class, 'import'])->middleware('menu.access:gradebook');
    Route::get('/notas/plantillas/{template}', [EvaluationController::class, 'template'])->middleware('menu.access:gradebook');

    Route::get('/report-card', [PanelController::class, 'reportCard'])->middleware('menu.access:reportcard');
    Route::get('/report-card/pdf', [PanelController::class, 'reportCardPdf'])->middleware('menu.access:reportcard');

    Route::get('/correos', [PanelController::class, 'emails'])->middleware('menu.access:emails');
    Route::post('/correos', [EmailDispatchController::class, 'store'])->middleware('menu.access:emails');
    Route::patch('/correos/{dispatch}', [EmailDispatchController::class, 'update'])->middleware('menu.access:emails');
    Route::delete('/correos/{dispatch}', [EmailDispatchController::class, 'destroy'])->middleware('menu.access:emails');
    Route::post('/correos/plantillas', [EmailTemplateController::class, 'store'])->middleware('menu.access:emails');
    Route::patch('/correos/plantillas/{template}', [EmailTemplateController::class, 'update'])->middleware('menu.access:emails');
    Route::delete('/correos/plantillas/{template}', [EmailTemplateController::class, 'destroy'])->middleware('menu.access:emails');

    Route::get('/usuarios', [PanelController::class, 'users'])->middleware('menu.access:users');
    Route::post('/usuarios', [UserManagementController::class, 'store'])->middleware('menu.access:users');
    Route::patch('/usuarios/{user}', [UserManagementController::class, 'update'])->middleware('menu.access:users');
    Route::delete('/usuarios/{user}', [UserManagementController::class, 'destroy'])->middleware('menu.access:users');

    Route::get('/perfiles', [PanelController::class, 'profiles'])->middleware('menu.access:profiles');
    Route::post('/perfiles', [RolePermissionController::class, 'store'])->middleware('menu.access:profiles');
    Route::patch('/perfiles/{role}', [RolePermissionController::class, 'update'])->middleware('menu.access:profiles');
    Route::delete('/perfiles/{role}', [RolePermissionController::class, 'destroy'])->middleware('menu.access:profiles');

    Route::get('/menus', [PanelController::class, 'menus'])->middleware('menu.access:menus');
    Route::post('/menus', [MenuManagementController::class, 'store'])->middleware('menu.access:menus');
    Route::patch('/menus/{menu}', [MenuManagementController::class, 'update'])->middleware('menu.access:menus');
    Route::delete('/menus/{menu}', [MenuManagementController::class, 'destroy'])->middleware('menu.access:menus');

    Route::get('/backups', [PanelController::class, 'backups'])->middleware('menu.access:backups');
    Route::post('/backups', [SystemBackupController::class, 'store'])->middleware('menu.access:backups');
    Route::get('/backups/{backup}/download', [SystemBackupController::class, 'download'])->middleware('menu.access:backups');

    Route::get('/configuracion', [PanelController::class, 'config'])->middleware('menu.access:config');
});
