<?php

namespace App\Repositories;

use App\Interfaces\ChangeRepositoryInterface;
use App\Interfaces\ServiceBodyRepositoryInterface;
use App\Models\Change;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ChangeRepository implements ChangeRepositoryInterface
{
    private ServiceBodyRepositoryInterface $serviceBodyRepository;

    public function __construct(ServiceBodyRepositoryInterface $serviceBodyRepository)
    {
        $this->serviceBodyRepository = $serviceBodyRepository;
    }

    public function getMeetingChanges(string $startDate = null, string $endDate = null, int $meetingId = null, int $serviceBodyId = null, array $changeTypes = null): Collection
    {
        return $this->getBuilder($startDate, $endDate, $meetingId, $serviceBodyId, $changeTypes)->get();
    }

    public function getMeetingLastChangeTimes(string $startDate = null, string $endDate = null, int $meetingId = null, int $serviceBodyId = null, array $changeTypes = null): Collection
    {
        return $this->getBuilder($startDate, $endDate, $meetingId, $serviceBodyId, $changeTypes)
            ->selectRaw('MAX(change_date) as change_date, COALESCE(before_id_bigint, after_id_bigint) as meeting_id')
            ->groupByRaw('COALESCE(before_id_bigint, after_id_bigint)')
            ->get()
            ->mapWithKeys(fn ($row, $_) => [$row->meeting_id => strtotime($row->change_date)]);
    }

    private function getBuilder(string $startDate = null, string $endDate = null, int $meetingId = null, int $serviceBodyId = null, array $changeTypes = null)
    {
        $changes = Change::query()
            ->with([
                'user',
                'serviceBody',
                'beforeMeeting',
                'afterMeeting',
                'beforeMeeting.data' => fn ($query) => $query->where('key', 'meeting_name'),
                'afterMeeting.data' => fn ($query) => $query->where('key', 'meeting_name'),
            ])
            ->where('object_class_string', 'c_comdef_meeting')
            ->orderByDesc('change_date')
            ->orderByDesc('id_bigint');

        if (!is_null($meetingId)) {
            $changes = $changes->where(function (Builder $query) use ($meetingId) {
                $query->where('before_id_bigint', $meetingId)->orWhere('after_id_bigint', $meetingId);
            });
        }

        if (!is_null($serviceBodyId)) {
            $serviceBodyIds = $this->serviceBodyRepository->search(includeIds: [$serviceBodyId], recurseChildren: true)
                ->map(fn ($serviceBody) => $serviceBody->id_bigint)
                ->toArray();
            $changes = $changes->whereIn('service_body_id_bigint', $serviceBodyIds);
        }

        if (!is_null($startDate)) {
            $changes = $changes->where('change_date', '>=', $startDate);
        }

        if (!is_null($endDate)) {
            $changes = $changes->where('change_date', '<=', $endDate);
        }

        if (!is_null($changeTypes)) {
            $changes = $changes->whereIn('change_type_enum', $changeTypes);
        }

        return $changes;
    }
}
