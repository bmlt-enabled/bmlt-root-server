<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMLT Root Server</title>
    <script>
        const settings = {
            apiBaseUrl: '{{ $baseUrl }}',
            defaultLanguage: '{{ $defaultLanguage }}',
            isLanguageSelectorEnabled: {{ $isLanguageSelectorEnabled ? 'true' : 'false' }},
            languageMapping: {{!! collect($languageMapping)->map(fn ($langName, $langAbbrev) => "$langAbbrev: '$langName'")->join(', ') !!}},
            version: '{{ $version }}'
        };
    </script>

    @vite('resources/js/app.ts')
</head>
<body>
</body>
</html>
