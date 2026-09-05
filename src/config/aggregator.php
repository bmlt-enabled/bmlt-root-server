<?php

return [
    'ignore_root_servers' => json_decode(env('AGGREGATOR_IGNORE_ROOT_SERVERS') ?? 'null') ?? [],
    'ignore_service_bodies' => json_decode(env('AGGREGATOR_IGNORE_SERVICE_BODIES') ?? 'null') ?? [
            120 => [  # {"id":"120","name":"NA New Jersey","url":"https://www.narcoticsanonymousnj.org/main_server/"}
                31, 32,  # Duplicated Eastern New York Region
                27, 28   # Duplicated Greater Philadelphia Region
            ],
    ],
    'rate_limit_root_servers' => json_decode(env('AGGREGATOR_RATE_LIMIT_ROOT_SERVERS') ?? 'null') ?? [
        # {"id":"139","name":"NA Argentina","url":"https://www.na.org.ar/main_server/"},
        139 => ['request_delay' => 10, 'retry_delay' => 300],
    ],

    // Aggregator-only: fill in a time zone for virtual/hybrid meetings that arrive from a
    // root server without one, by looking up the meeting's coordinates against the offline
    // time zone boundary data. A time zone supplied by the source server is always kept, so
    // if the source later sets the field it wins on the next sync. Set to false to disable.
    'derive_missing_timezones' => filter_var(env('AGGREGATOR_DERIVE_MISSING_TIMEZONES', true), FILTER_VALIDATE_BOOLEAN),

    // Where the time zone boundary data lives. These are build artifacts the Makefile
    // downloads into public/ (they are also served to the frontend), so they are overridable
    // rather than hard-coded in case they are ever relocated. Empty means use the default.
    'timezone_index_path' => env('AGGREGATOR_TIMEZONE_INDEX_PATH'),
    'timezone_data_path' => env('AGGREGATOR_TIMEZONE_DATA_PATH'),

    // Each root server's configured map center, keyed by source_id, as [latitude, longitude].
    // A meeting sitting exactly on its server's map center was never given a location of its
    // own, so its coordinates say nothing about where it is and we must not derive a time zone
    // from them (see MeetingRepository::isPlaceholderCoordinate).
    //
    // The live value is also read from each server's imported server_info, but that only
    // reflects the center as of the last import: if an admin later moves the center, meetings
    // still sitting on today's value would stop being recognized. Freezing the current values
    // here keeps them recognized regardless. Snapshot taken 2026-09-05 from the aggregator's
    // own /api/v1/rootservers (aggregator.bmltenabled.org). Regenerate when servers are added.
    'known_map_centers' => [
        99 => [34.235918, -118.563659], // Aotearoa New Zealand Region
        100 => [34.235918, -118.563659], // Texas, Louisiana, Mississippi, Arkansas
        101 => [37.556953503804, -77.473032474518], // Autonomy Zone
        102 => [41.37, -73.18], // Greater New York Server
        103 => [34.129652827655, -118.00356030464], // Southern California Region
        105 => [39.952422081955, -75.163543033203], // Greater Philadelphia Region
        106 => [40.236294937885, -83.366661071777], // Ohio Region
        111 => [22.561264646541, 78.3984375], // NA India
        112 => [34.235918, -118.563659], // German-Speaking Region
        113 => [53.364817, -5.98528], // NA Ireland
        116 => [21.30216955583, -157.85705566406], // NA Hawai'i
        117 => [37.324304518138, -121.89605712891], // San Jose Area
        119 => [34.235918, -118.563659], // Wisconsin Region
        120 => [40.495003732305, -74.459838867188], // NA New Jersey
        121 => [34.235918, -118.563659], // Connecticut Region
        122 => [34.235918, -118.563659], // Volunteer Region
        124 => [-25.482951175355, 134.6484375], // NA Australia
        127 => [43.421008829947, -84.52880859375], // Michigan NA
        129 => [45.55252525134, -94.21875], // NA Minnesota
        131 => [39.739026162151, -104.98938560486], // NA Colorado
        133 => [12.49825, 43.00251], // NA Italia
        134 => [34.23592, -118.5639326], // New England
        135 => [39.055350984189, -94.585719108582], // Show-Me Region
        136 => [34.235918, -118.563659], // NA Denmark
        137 => [36.065752051707, -79.793701171875], // Southeastern Zonal Forum
        138 => [0.0, 0.0], // NA Kentucky
        139 => [-34.740215358312, -58.297083186707], // NA Argentina
        141 => [34.235918, -118.563659], // Plains States Zonal Forum
        144 => [34.235918, -118.563659], // NA France
        145 => [51.714469493143, -87.99969415625], // Canadian Assembly
        146 => [34.235918, -118.563659], // Brazilian Zonal Forum
        147 => [34.235918, -118.563659], // Greater Illinois Region
        149 => [34.235918, -118.563659], // Polish Region
        152 => [34.235918, -118.563659], // Western States Zonal Forum
        153 => [34.235918, -118.563659], // Tri-State Region
        154 => [40.486444, -86.133693], // Indiana Region
        155 => [46.5577566, -115.3273741], // Montana Region
        156 => [34.235918, -118.563659], // NA Switzerland
        157 => [34.235918, -118.563659], // Chicagoland Region
        159 => [-25.2637, -57.5759], // Paraguay
        160 => [34.235918, -118.563659], // Iran
        161 => [38.021406648054, 23.744016953821], // NA Greece
        162 => [34.235918, -118.563659], // Czechoslovakia
    ],
];
