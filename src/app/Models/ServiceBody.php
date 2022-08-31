<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBody extends Model
{
    protected string $table = 'comdef_service_bodies';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
}
