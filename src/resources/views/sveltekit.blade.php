<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <link rel="icon" href="{{ $basePath }}/favicon.png" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="smui.css" />
    <meta http-equiv="content-security-policy" content="">
    <link rel="modulepreload" href="{{ $startScript }}">
    @foreach ($chunks as $chunk)
    <link rel="modulepreload" href="{{ $chunk }}">
    @endforeach
</head>
<body data-sveltekit-prefetch class="mdc-typography">
<div>
    <script type="module" data-sveltekit-hydrate="45h">
        import { start } from "{{ $startScript }}";

        start({
            env: {},
            hydrate: null,
            paths: {"base":"{{ $basePath }}","assets":"{{ $basePath }}"},
            target: document.querySelector('[data-sveltekit-hydrate="45h"]').parentNode,
            trailing_slash: "never"
        });
    </script></div>
</body>
</html>
