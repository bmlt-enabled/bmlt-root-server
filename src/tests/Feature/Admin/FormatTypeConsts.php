<?php

namespace Tests\Feature\Admin;

class FormatTypeConsts
{
    public const TYPE_MEETING_FORMAT = 'MEETING_FORMAT';
    public const TYPE_LOCATION_CODE = 'LOCATION';
    public const TYPE_COMMON_NEEDS = 'COMMON_NEEDS_OR_RESTRICTION';
    public const TYPE_OPEN_CLOSED = 'OPEN_OR_CLOSED';
    public const TYPE_LANGUAGE = 'LANGUAGE';

    public const TYPE_COMDEF_MEETING_FORMAT = 'FC1';
    public const TYPE_COMDEF_LOCATION_CODE = 'FC2';
    public const TYPE_COMDEF_COMMON_NEEDS = 'FC3';
    public const TYPE_COMDEF_OPEN_CLOSED = 'O';
    public const TYPE_COMDEF_LANGUAGE = 'LANG';

    public const TYPE_TO_COMDEF_TYPE_MAP = [
        self::TYPE_MEETING_FORMAT => self::TYPE_COMDEF_MEETING_FORMAT,
        self::TYPE_LOCATION_CODE => self::TYPE_COMDEF_LOCATION_CODE,
        self::TYPE_COMMON_NEEDS => self::TYPE_COMDEF_COMMON_NEEDS,
        self::TYPE_OPEN_CLOSED => self::TYPE_COMDEF_OPEN_CLOSED,
        self::TYPE_LANGUAGE => self::TYPE_COMDEF_LANGUAGE,
    ];

    public const COMDEF_TYPE_TO_TYPE_MAP = [
        self::TYPE_COMDEF_MEETING_FORMAT => self::TYPE_MEETING_FORMAT,
        self::TYPE_COMDEF_LOCATION_CODE => self::TYPE_LOCATION_CODE,
        self::TYPE_COMDEF_COMMON_NEEDS => self::TYPE_COMMON_NEEDS,
        self::TYPE_COMDEF_OPEN_CLOSED => self::TYPE_OPEN_CLOSED,
        self::TYPE_COMDEF_LANGUAGE => self::TYPE_LANGUAGE,
    ];
}
