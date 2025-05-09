<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SemanticWorkshopController extends Controller
{
    public static function get(Request $request): Response
    {

        $path = public_path('semantic');
        $jsFiles = glob($path . '/assets/index*.js');
        $cssFiles = glob($path . '/assets/index*.css');

        if (empty($jsFiles) || empty($cssFiles)) {
            throw new \RuntimeException('Required semantic UI assets not found.');
        }

        $baseUrl = $request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/';

        return response()->view('semantic', [
            'baseUrl' => $baseUrl,
            'jsAssetUrl' => $baseUrl . 'semantic/assets/' . basename($jsFiles[0]),
            'cssAssetUrl' => $baseUrl . 'semantic/assets/' . basename($cssFiles[0]),
        ]);
    }
}
