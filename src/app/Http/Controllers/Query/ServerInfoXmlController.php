<?php

namespace App\Http\Controllers\Query;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ServerInfoXmlController extends Controller
{
    public function get(): Response
    {
        $version = config('app.version');
        $xml = <<<VERSION
<?xml version="1.0" encoding="utf-8"?>
<bmltInfo>
  <serverVersion>
    <readableString>$version</readableString>
  </serverVersion>
</bmltInfo>
VERSION;

        return response($xml)
            ->header('Content-Type', 'application/xml');
    }
}
