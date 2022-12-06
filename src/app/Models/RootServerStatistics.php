<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RootServerStatistics extends Model
{
    protected $table = 'root_server_statistics';
    protected $fillable = [
        'root_server_id',
        'num_zones',
        'num_regions',
        'num_areas',
        'num_groups',
        'num_total_meetings',
        'num_in_person_meetings',
        'num_virtual_meetings',
        'num_hybrid_meetings',
        'num_unknown_meetings',
        'is_latest',
    ];

    public function rootServer()
    {
        return $this->belongsTo(RootServer::class);
    }
}
