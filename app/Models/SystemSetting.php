<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'configuraciones_sistema';

    protected $fillable = ['clave', 'valor', 'cifrado'];

    protected function casts(): array
    {
        return ['cifrado' => 'boolean'];
    }
}
