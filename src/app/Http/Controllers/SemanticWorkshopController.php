<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SemanticWorkshopController extends Controller
{
    public function index(Request $request)
    {
        $data = [
            'apiBaseUrl' => $request->getSchemeAndHttpHost() . $request->getBaseUrl() . '/',
        ];
        $content = file_get_contents(public_path('semantic/index.html'));
        $jsVar = json_encode($data);
        $content = str_replace(
            '</head>',
            "<script>const settings = $jsVar;</script>\n</head>",
            $content
        );
        
        return response($content)->header('Content-Type', 'text/html');
    }
}
