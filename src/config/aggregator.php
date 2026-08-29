<?php

return [
    // Fork additions (virtual aggregator). All default off => upstream behavior unchanged.
    'virtual_only' => (bool) env('AGGREGATOR_VIRTUAL_ONLY', false),
    'geocode_timezones' => (bool) env('AGGREGATOR_GEOCODE_TIMEZONES', false),
    'infer_locale' => (bool) env('AGGREGATOR_INFER_LOCALE', false),
    // Optional address->coords fallback for meetings with no lat/long (off by default).
    'geocode_addresses' => (bool) env('AGGREGATOR_GEOCODE_ADDRESSES', false),
    'geocoder_endpoint' => env('AGGREGATOR_GEOCODER_URL', 'https://nominatim.openstreetmap.org/search'),
    'geocoder_user_agent' => env('AGGREGATOR_GEOCODER_UA', 'bmlt-virtual-aggregator'),

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
];
