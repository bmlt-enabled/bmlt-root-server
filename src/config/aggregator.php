<?php

return [
    'ignore_root_servers' => json_decode(env('AGGREGATOR_IGNORE_ROOT_SERVERS') ?? 'null') ?? [],
    'ignore_service_bodies' => json_decode(env('AGGREGATOR_IGNORE_SERVICE_BODIES') ?? 'null') ?? [
            120 => [  # {"id":"120","name":"NA New Jersey","rootURL":"https://www.narcoticsanonymousnj.org/main_server/"}
                31, 32,  # Duplicated Eastern New York Region
                27, 28   # Duplicated Greater Philadelphia Region
            ],
    ],
    'rate_limit_root_servers' => json_decode(env('AGGREGATOR_RATE_LIMIT_ROOT_SERVERS') ?? 'null') ?? [
        # {"id":"139","name":"NA Argentina","rootURL":"https://www.na.org.ar/main_server/"},
        139 => ['request_delay' => 10, 'retry_delay' => 300],
    ],
];
