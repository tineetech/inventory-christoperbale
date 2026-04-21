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


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hakAkses()
    {
        return $this->belongsTo(HakAkses::class, 'hak_akses_id');
    }
}