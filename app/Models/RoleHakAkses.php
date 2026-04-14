<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleHakAkses extends Model
{
    protected $table = 'role_hak_akses';

    protected $fillable = [
        'role_id',
        'hak_akses_id'
    ];
}