<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMLT Root Server</title>
    <script>
        var apiBaseUrl = '{{ request()->getBaseUrl() }}';
    </script>

    @viteReactRefresh
    @vite('resources/js/App.tsx')
</head>
<body>
<div id="root"></div>
</body>
</html>
