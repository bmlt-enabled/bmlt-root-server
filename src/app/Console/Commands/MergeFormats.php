<?php

namespace App\Console\Commands;

use App\Interfaces\FormatRepositoryInterface;
use App\Interfaces\MeetingRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeFormats extends Command
{
    protected $signature = 'bmlt:MergeFormats {formatIds} {targetFormatId}';

    protected $description = 'Merge formats';

    public function handle(
        FormatRepositoryInterface $formatRepository,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $formatIds = $this->argument('formatIds');
        $formatIds = array_map(fn ($id) => trim($id), explode(',', $formatIds));
        $formatIds = ensure_integer_array($formatIds);
        $formats = $formatRepository->search(formatsInclude: $formatIds, showAll: true);
        if (count($formatIds) != $formats->unique(fn ($f) => $f->shared_id_bigint)->count()) {
            $this->error("Some of the specified formatIds do not exist.");
            return 1;
        }

        $targetFormatId = $this->argument('targetFormatId');
        $targetFormatId = intval($targetFormatId);
        $targetFormats = $formatRepository->search(formatsInclude: [$targetFormatId], showAll: true);
        if ($targetFormats->isEmpty()) {
            $this->error("The target format {$targetFormatId} does not exist.");
            return 1;
        }

        $meetings = $meetingRepository->getSearchResults(formatsInclude: $formatIds, formatsComparisonOperator: 'OR');

        DB::transaction(function () use ($meetings, $formatIds, $targetFormatId, $formats) {
            foreach ($meetings as $meeting) {
                $newFormats = collect(explode(',', $meeting->formats))
                    ->map(fn ($formatId) => intval($formatId))
                    ->reject(fn($formatId) => in_array($formatId, $formatIds))
                    ->concat([$targetFormatId])
                    ->unique()
                    ->sort()
                    ->join(',');
                $this->info("$meeting->id_bigint: $meeting->formats -> $newFormats");
                $meeting->update(['formats' => $newFormats]);
            }

            foreach ($formats as $format) {
                $format->delete();
                $this->info("deleted format $format->shared_id_bigint");
            }
        });
    }
}
