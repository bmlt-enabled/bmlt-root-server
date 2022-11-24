<?php

namespace App\Http\Resources\Query;

use App\Http\Resources\JsonResource;
use App\Repositories\RootServerRepository;
use Illuminate\Support\Collection;

class FormatResource extends JsonResource
{
    private static bool $isRequestInitialized = false;
    private static bool $isAggregatorModeEnabled = false;
    private static ?Collection $rootServerUrls = null;

    // Allows tests to reset state
    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
        self::$isAggregatorModeEnabled = false;
        self::$rootServerUrls = null;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (!self::$isRequestInitialized) {
            $this->initializeRequest($request);
            self::$isRequestInitialized = true;
        }

        return [
            'key_string' => $this->key_string,
            'name_string' => $this->name_string ?? '',
            'description_string' => $this->description_string ?? '',
            'lang' => $this->lang_enum,
            'id' => (string)$this->shared_id_bigint,
            'world_id' => $this->worldid_mixed ?? '',
            'format_type_enum' => $this->format_type_enum ?? '',
            'root_server_uri' => $this->root_server_id && self::$rootServerUrls ? self::$rootServerUrls->get($this->root_server_id) : $request->getSchemeAndHttpHost() . $request->getBaseUrl(),
            'root_server_id' => $this->when(self::$isAggregatorModeEnabled, $this->root_server_id ?? '')
        ];
    }

    private function initializeRequest($request)
    {
        self::$isAggregatorModeEnabled = (bool)legacy_config('is_aggregator_mode_enabled');
        if (self::$isAggregatorModeEnabled) {
            $rootServerRepository = new RootServerRepository();
            self::$rootServerUrls = $rootServerRepository->search()->mapWithKeys(fn ($rs, $_) => [$rs->id => $rs->url]);
        }
    }
}
