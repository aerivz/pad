<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailDispatchController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\MenuManagementController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'create']);

Route::prefix('pad')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', [PanelController::class, 'dashboard'])->middleware('menu.access:dashboard')->name('dashboard');

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

        Route::get('/familias', [PanelController::class, 'guardians'])->middleware('menu.access:guardians')->name('guardians.index');
        Route::post('/familias', [GuardianController::class, 'store'])->middleware('menu.access:guardians')->name('guardians.store');
        Route::patch('/familias/{guardian}', [GuardianController::class, 'update'])->middleware('menu.access:guardians')->name('guardians.update');
        Route::delete('/familias/{guardian}', [GuardianController::class, 'destroy'])->middleware('menu.access:guardians')->name('guardians.destroy');
        Route::redirect('/padres', '/pad/familias');

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

        Route::get('/configuracion', [PanelController::class, 'config'])->middleware('menu.access:config')->name('config.index');
    });
});
