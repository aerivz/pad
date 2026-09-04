# EduNotas

Sistema web de gestion academica desarrollado en Laravel para el control de notas escolares, familias, perfiles de acceso, reportes y seguimiento administrativo.

## Descripcion

EduNotas centraliza la administracion academica de un centro escolar en una sola plataforma. El sistema permite registrar estructuras academicas, alumnos, docentes, materias, notas, familias, usuarios internos y permisos por perfil, manteniendo historial y desactivacion logica de los registros.

El proyecto fue montado para ejecutarse en entorno local con XAMPP, PHP 8.2 y MySQL.

## Funcionalidades principales

- Dashboard administrativo con resumen general del sistema.
- Mantenimiento de secciones.
- Mantenimiento de alumnos.
- Mantenimiento de profesores.
- Mantenimiento de materias.
- Mantenimiento de familias con vinculacion por parentesco.
- Mantenimiento de notas con auditoria de cambios.
- Mantenimiento de envios de correo.
- Consulta de boletines y consolidado por estudiante.
- Mantenimiento de usuarios.
- Mantenimiento de perfiles y permisos por menu.
- Login por usuario y control de acceso segun perfil.
- Eliminacion logica en modulos administrativos.
- Alertas visuales con SweetAlert2.
- Filtros rapidos en usuarios, notas, correos y perfiles.

## Modulos del sistema

### 1. Dashboard

- Totales de alumnos, secciones, profesores y notas.
- Vista rapida de actividad academica.
- Base para ampliacion de indicadores.

### 2. Academico

- Secciones: grado, nombre y anio escolar.
- Alumnos: asignacion por seccion y promedio general.
- Profesores: especialidad, correo y asignaciones.
- Materias: control de catalogo academico.
- Asignaciones: relacion entre materia, seccion y profesor.

### 3. Notas

- Registro de nota por alumno, asignacion, trimestre y categoria.
- Edicion de notas.
- Desactivacion logica de notas.
- Tablero consolidado por asignacion y trimestre.
- Auditoria de insercion, actualizacion y desactivacion.

### 4. Familias

- Registro de miembros familiares.
- Asociacion de un familiar con uno o varios alumnos.
- Manejo de parentesco: padre, madre, tio, tia, hermano, hermana, abuelo, abuela, encargado u otro.

### 5. Correos

- Registro de envios por plantilla, familiar, alumno y trimestre.
- Control de estado: pendiente, enviado o fallido.
- Validacion de relacion real entre familiar y alumno.
- Plantillas HTML con variables, perfiles autorizados y adjuntos PDF generados por el sistema.
- Vista previa antes de enviar y envios masivos por seccion y trimestre.
- Cola con reintentos, limite de correos por minuto, historial CSV y aviso al finalizar cada lote.

### 6. Boletines

- Vista consolidada por alumno.
- Filtros por seccion, alumno y trimestre.
- Promedios por materia y promedio global.

### 7. Usuarios y seguridad

- Usuarios internos con perfil asignado.
- Login con autenticacion Laravel.
- Cierre de sesion.
- Restriccion de acceso por opcion de menu.
- Menu lateral visible solo segun permisos del perfil.

### 8. Perfiles y permisos

- Creacion y mantenimiento de perfiles.
- Asignacion de accesos por menu.
- Control centralizado de permisos.

## Tecnologias utilizadas

- PHP 8.2
- Laravel 12
- MySQL / MariaDB
- Blade
- AdminLTE 3
- Bootstrap 4
- SweetAlert2
- jQuery
- Git y GitHub
- XAMPP para entorno local

## Estructura tecnica destacada

- Migraciones para esquema academico y seguridad.
- Seeders con datos de ejemplo.
- Modelos Eloquent para usuarios, roles, menus, notas, familias y correos.
- Controladores separados por modulo.
- Middleware personalizado para control de acceso por menu.
- Vistas Blade organizadas por modulo dentro de `resources/views/panel`.

## Base de datos

La aplicacion utiliza la base de datos `pad`.

Incluye migraciones para:

- estructura academica
- control de notas
- auditoria
- familias y parentescos
- envios de correo
- perfiles, menus y permisos

## Instalacion

### Requisitos

- PHP 8.2 o superior
- Composer
- MySQL o MariaDB
- XAMPP o entorno equivalente

### Pasos

1. Clonar el repositorio.
2. Entrar al proyecto:

```bash
cd C:\xampp\htdocs\Pad
```

3. Instalar dependencias:

```bash
composer install
```

4. Copiar variables de entorno:

```bash
copy .env.example .env
```

5. Configurar la conexion MySQL en `.env`.

6. Generar clave de aplicacion:

```bash
php artisan key:generate
```

7. Ejecutar migraciones y seeders:

```bash
php artisan migrate --seed
```

8. Abrir en navegador:

```text
http://localhost/pad/login
```

## Credenciales por defecto

Usuario administrativo de prueba:

- Usuario: `admin`
- Contrasena: `123456`

## Activar correos en cola

1. Configure SMTP, remitente y limite por minuto en `/pad/configuracion`.
2. Ejecute migraciones para crear lotes y campos de cola:

```bash
php artisan migrate --force
```

3. En desarrollo, deje worker activo:

```bash
php artisan queue:work database --queue=emails --tries=3 --timeout=180
```

4. En hosting compartido, cree cron cada minuto. Laravel inicia worker corto y procesa correos pendientes:

```bash
* * * * * cd /ruta/a/pad && /ruta/a/php artisan schedule:run >> /dev/null 2>&1
```

Use ejecutable PHP 8.2+ configurado por hosting. El sistema notifica al creador del lote y usuarios administradores cuando termina.

## Rutas principales

- `/pad/login`
- `/pad/`
- `/pad/secciones`
- `/pad/alumnos`
- `/pad/profesores`
- `/pad/materias`
- `/pad/familias`
- `/pad/notas`
- `/pad/report-card`
- `/pad/correos`
- `/pad/usuarios`
- `/pad/perfiles`
- `/pad/configuracion`

## Estado actual del proyecto

El sistema ya cuenta con:

- CRUD funcional en los modulos principales.
- permisos por perfil conectados al menu y a las rutas.
- autenticacion de usuarios.
- filtros rapidos en varias pantallas.
- desactivacion logica en lugar de eliminacion fisica en mantenimientos clave.

## Documentacion tecnica interna

Para que otro Codex o desarrollador pueda entender rapido la base del sistema, revisa:

- [Indice de desarrollo](C:/xampp/htdocs/Pad/docs/INDICE_DESARROLLO.md)
- [Guia tecnica para Codex](C:/xampp/htdocs/Pad/docs/GUIA_TECNICA_CODEX.md)

## Notas de desarrollo

- El archivo `.env` no se versiona.
- `vendor`, `node_modules`, `storage` sensible y artefactos locales estan excluidos por `.gitignore`.
- El proyecto esta pensado para continuar creciendo con reportes, exportaciones y control mas fino de acciones por perfil.

## Posibles mejoras futuras

- Exportacion a PDF y Excel.
- Recuperacion de contrasena.
- Historial de actividad por usuario.
- Permisos por accion ademas de permisos por menu.
- Panel de reportes estadisticos avanzados.

## Autor

Repositorio publicado en:

[https://github.com/aerivz/pad](https://github.com/aerivz/pad)
