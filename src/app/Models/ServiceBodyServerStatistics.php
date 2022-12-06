<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBodyServerStatistics extends Model
{
    protected $table = 'service_body_statistics';
    protected $fillable = [
        'service_body_id',
        'num_groups',
        'num_total_meetings',
        'num_in_person_meetings',
        'num_virtual_meetings',
        'num_hybrid_meetings',
        'num_unknown_meetings',
        'is_latest',
    ];

    public function serviceBody()
    {
        return $this->belongsTo(ServiceBody::class, 'service_body_id_bigint');
    }
}
