<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMLT Root Server</title>
    <script>
        const settings = {
            apiBaseUrl: '{{ $baseUrl }}',
            autoGeocodingEnabled: {{ $autoGeocodingEnabled ? 'true' : 'false' }},
            bmltTitle: '{{ $bmltTitle }}',
            centerLongitude: '{{ $centerLongitude }}',
            centerLatitude: '{{ $centerLatitude }}',
            centerZoom: '{{ $centerZoom }}',
            countyAutoGeocodingEnabled: {{ $countyAutoGeocodingEnabled ? 'true' : 'false' }},
            customFields: {!! json_encode($customFields) !!},
            defaultClosedStatus: {{ $defaultClosedStatus ? 'true' : 'false' }},
            defaultDuration: '{{ $defaultDuration }}',
            defaultLanguage: '{{ $defaultLanguage }}',
            distanceUnits: '{{ $distanceUnits }}',
            googleApiKey: '{{ $googleApiKey }}',
            isLanguageSelectorEnabled: {{ $isLanguageSelectorEnabled ? 'true' : 'false' }},
            languageMapping: {{!! collect($languageMapping)->map(fn ($langName, $langAbbrev) => "$langAbbrev: '$langName'")->join(', ') !!}},
            meetingStatesAndProvinces: {!! json_encode(legacy_config('meeting_states_and_provinces', [])) !!},
            meetingCountiesAndSubProvinces: {!! json_encode(legacy_config('meeting_counties_and_sub_provinces', [])) !!},
            regionBias: '{{ $regionBias }}',
            version: '{{ $version }}',
            zipAutoGeocodingEnabled: {{ $zipAutoGeocodingEnabled ? 'true' : 'false' }}
        };
    </script>

    @vite('resources/js/app.ts')
</head>
<body>
</body>
</html>
