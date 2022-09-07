<?php

namespace App\Http\Middleware;

use Illuminate\Session\Middleware\StartSession as BaseStartSession;

class StartSession extends BaseStartSession
{
    public function handle($request, $next)
    {
        if (config('app.env') != 'testing') {
            // When under PHPUnit, the 'database.connections.mysql.database' key will always be null,
            // so we just skip the check. Running different codepaths under test is an anti-pattern,
            // and is pretty much always a bad idea. In this case, the block below should only get hit
            // when the config file is missing and the installer is running. There is probably a better
            // way, but I haven't thought of one yet.
            if (!config('database.connections.mysql.database')) {
                // This prevents a database access before the install wizard has written a config file
                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}
