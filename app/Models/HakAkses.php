<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    protected $table = 'hak_akses';

    protected $fillable = [
        'nama_permission'
    ];

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_hak_akses',
            'hak_akses_id',
            'role_id'
        );
    }
}
