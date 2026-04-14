<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';

    protected $fillable = [
        'nama_role'
    ];

    public function pengguna()
    {
        return $this->hasMany(Pengguna::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            HakAkses::class,
            'role_hak_akses',
            'role_id',
            'hak_akses_id'
        );
    }
}