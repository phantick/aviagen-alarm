<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property int event_id
 * @property int mobile_user_id
 * @property sting mobile
 * @property datetime ts
 * @property string text
 * @property string sms_id
 */
class sms extends dbObject {
    protected $dbTable = "SMS";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'EVENT_ID' => Array ('int', 'required'),
        'MOBILE_USER_ID' => Array ('int', 'required'),
        'MOBILE' => Array ('text', 'required'),
        'TS' => Array ('datetime', 'required'),
        'TEXT' => Array ('text', 'required'),
        'SMS_UID' => Array ('text')
    );

    protected $relations = Array (
        'event' => Array ("hasOne", "event", "EVENT_ID"),
        'mobile_user' => Array ("hasOne", "mobile_user", "MOBILE_USER_ID"),
        'sms_st' => Array ("hasMany", "sms_st", "SMS_ID")
    );
}
?>