<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\JsonResource;
use App\Repositories\FormatRepository;
use App\Repositories\ServiceBodyRepository;
use App\Http\Resources\Traits\ChangeDetailsTrait;
use Illuminate\Support\Collection;

class MeetingChangeResource extends JsonResource
{
    use ChangeDetailsTrait;
    private static bool $isRequestInitialized = false;
    private static Collection $allFormats;
    private static Collection $allServiceBodies;

    // Allows tests to reset state
    public static function resetStaticVariables()
    {
        self::$isRequestInitialized = false;
    }

    public function toArray($request): array
    {
        if (!self::$isRequestInitialized) {
            $this->initializeRequest($request);
            self::$isRequestInitialized = true;
        }

        return [
            'dateString' => date('g:i A, n/j/Y', strtotime($this->change_date)),
            'userName' => $this->user?->name_string ?? '',
            'serviceBodyName' => $this->serviceBody?->name_string ?? '',
            'details' => $this->getChangeDetails(true),
        ];
    }

    private function initializeRequest(): void
    {
        $formatRepository = new FormatRepository();
        self::$allFormats = $formatRepository->search(showAll: true)->groupBy(['shared_id_bigint', 'lang_enum'], preserveKeys: true);

        $serviceBodyRepository = new ServiceBodyRepository();
        self::$allServiceBodies = $serviceBodyRepository->search()->mapWithKeys(fn ($sb) => [$sb->id_bigint => $sb]);
    }
}
