<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $fillable = ['nombre_departamento'];

    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    public function trabajos()
    {
        return $this->hasMany(Trabajo::class);
    }

    public function subtrabajos()
    {
        return $this->hasMany(Subtrabajo::class);
    }
}
