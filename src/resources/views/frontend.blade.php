<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMLT Root Server</title>
    <script>
        var apiBaseUrl = '{{ $baseUrl }}';
        var defaultLanguage = '{{ $defaultLanguage }}';
        var isLanguageSelectorEnabled = {{ $isLanguageSelectorEnabled ? 'true' : 'false' }};
        var languageMapping = {{!! collect($languageMapping)->map(fn ($langName, $langAbbrev) => "$langAbbrev: '$langName'")->join(', ') !!}};
        var currentVersion = '{{ $currentVersion }}';
    </script>

    @viteReactRefresh
    @vite('resources/js/App.tsx')
</head>
<body>
<div id="root"></div>
</body>
</html>
