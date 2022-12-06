<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RootServer extends Model
{
    protected $table = 'root_servers';
    protected $fillable = [
        'source_id',
        'name',
        'url',
        'server_info',
    ];

    public function statistics()
    {
        return $this->hasMany(RootServerStatistics::class);
    }
}
