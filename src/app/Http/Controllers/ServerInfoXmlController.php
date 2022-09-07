<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class ServerInfoXmlController extends Controller
{
    public function get(): Response
    {
        $version = Config::get('app.version');
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
