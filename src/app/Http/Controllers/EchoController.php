<?php

namespace App\Http\Controllers;


class EchoController extends Controller
{
    public function get()
    {
        return response()->json([
            'echo' => 'echo'
        ]);
    }
}
