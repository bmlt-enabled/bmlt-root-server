<?php

return [
    'ignore_root_servers' => json_decode(env('AGGREGATOR_IGNORE_ROOT_SERVERS') ?? 'null') ?? [],
    'ignore_service_bodies' => json_decode(env('AGGREGATOR_IGNORE_SERVICE_BODIES') ?? 'null') ?? [
            'https://www.narcoticsanonymousnj.org/main_server/' => [
            31, 32,  # Duplicated Eastern New York Region
            27, 28   # Duplicated Greater Philadelphia Region
        ]
    ],
];
