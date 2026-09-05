<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function mapCenter(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                if (empty($this->server_info)) {
                    return null;
                }
                $info = json_decode($this->server_info, true);
                if (!is_array($info)) {
                    return null;
                }
                $latitude = $info['centerLatitude'] ?? null;
                $longitude = $info['centerLongitude'] ?? null;
                if (!is_numeric($latitude) || !is_numeric($longitude)) {
                    return null;
                }
                return ['latitude' => (float) $latitude, 'longitude' => (float) $longitude];
            },
        );
    }
}
