<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string m_id
 * @property datetime ts
 * @property string text
 * @property string event_type_id
 * @property int mailbox_id
 */
class event extends dbObject {
    protected $dbTable = "EVENT";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'M_ID' => Array ('text', 'required'),
        'TS' => Array ('datetime', 'required'),
        'TEXT' => Array ('text'),
        'EVENT_TYPE_ID' => Array ('int', 'required'),
        'MAILBOX_ID' => Array ('int', 'required')
    );

    protected $relations = Array (
        'event_type' => Array ("hasOne", "event_type", "EVENT_TYPE_ID"),
        'mailbox' => Array ("hasOne", "mailbox", "MAILBOX_ID"),
        'sms' => Array ("hasMany", "sms", "EVENT_ID")
    );
}
?>