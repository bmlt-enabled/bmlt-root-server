<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    public const CHANGE_TYPE_NEW = 'comdef_change_type_new';
    public const CHANGE_TYPE_CHANGE = 'comdef_change_type_change';
    public const CHANGE_TYPE_DELETE = 'comdef_change_type_delete';

    protected $table = 'comdef_changes';
    protected $primaryKey = 'id_bigint';
    public $timestamps = false;
    protected $fillable = [
        'user_id_bigint',
        'service_body_id_bigint',
        'lang_enum',
        'change_date',
        'object_class_string',
        'change_name_string',
        'change_description_text',
        'before_id_bigint',
        'before_lang_enum',
        'after_id_bigint',
        'after_lang_enum',
        'change_type_enum',
        'before_object',
        'after_object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id_bigint');
    }

    public function serviceBody()
    {
        return $this->belongsTo(ServiceBody::class, 'service_body_id_bigint');
    }

    public function beforeMeeting()
    {
        return $this->belongsTo(Meeting::class, 'before_id_bigint');
    }

    public function afterMeeting()
    {
        return $this->belongsTo(Meeting::class, 'after_id_bigint');
    }

    public function beforeObject(): Attribute
    {
        return Attribute::make(
            get: fn ($beforeObject) => $this->unserialize($beforeObject),
        );
    }

    public function afterObject(): Attribute
    {
        return Attribute::make(
            get: fn ($afterObject) => $this->unserialize($afterObject),
        );
    }

    private function unserialize($object)
    {
        if (!$object) {
            return null;
        }

        if ($this->object_class_string == 'c_comdef_meeting') {
            $ret = unserialize($object);
            $ret['main_table_values'] = unserialize($ret['main_table_values']);
            $ret['data_table_values'] = unserialize($ret['data_table_values']);
            $ret['longdata_table_values'] = unserialize($ret['longdata_table_values']);
            return $ret;
        }

        return unserialize($object);
    }
}
