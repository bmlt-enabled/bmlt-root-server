<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\MeetingChangeResource;
use App\Interfaces\ChangeRepositoryInterface;
use App\Models\Meeting;
use Illuminate\Http\JsonResponse as BaseJsonResponse;

class MeetingChangeController extends ResourceController
{
    private ChangeRepositoryInterface $changeRepository;

    public function __construct(ChangeRepositoryInterface $changeRepository)
    {
        $this->changeRepository = $changeRepository;
        $this->authorizeResource('App\Models\Change,meeting', 'change,meeting');
    }

    public function index(Meeting $meeting): BaseJsonResponse
    {
        $changes = $this->changeRepository->getMeetingChanges(null, null, $meeting->id_bigint, null);
        return MeetingChangeResource::collection($changes)->response();
    }
}
