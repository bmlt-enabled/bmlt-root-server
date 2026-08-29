<?php

namespace App\Aggregator;

use App\Models\RootServer;

/**
 * Fork addition: fills in a meeting's lang_enum when the source root server
 * provides none, using that root server's default language from its GetServerInfo
 * payload (nativeLang, falling back to the first of langs).
 */
class LocaleResolver
{
    /** @var array<int, string|null> memoized per root server id */
    private static array $cache = [];

    public static function reset(): void
    {
        self::$cache = [];
    }

    public function infer(int $rootServerId): ?string
    {
        if (array_key_exists($rootServerId, self::$cache)) {
            return self::$cache[$rootServerId];
        }

        $lang = null;
        $rootServer = RootServer::find($rootServerId);
        if (!is_null($rootServer) && !is_null($rootServer->server_info)) {
            $info = json_decode($rootServer->server_info);
            $native = $info->nativeLang ?? null;
            if (is_string($native) && $native !== '') {
                $lang = $native;
            } elseif (isset($info->langs) && is_string($info->langs) && $info->langs !== '') {
                $lang = explode(',', $info->langs)[0] ?: null;
            }
        }

        return self::$cache[$rootServerId] = $lang;
    }
}
