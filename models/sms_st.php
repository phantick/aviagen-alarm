<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property int sms_id
 * @property int status_id
 * @property int error_id
 * @property datetime ts
 */
class sms_st extends dbObject {
    protected $dbTable = "SMS_ST";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'SMS_ID' => Array ('int', 'required'),
        'STATUS_ID' => Array ('int'),
        'ERROR_ID' => Array ('int'),
        'TS' => Array ('datetime', 'required')
    );

    protected $relations = Array (
        'sms' => Array ("hasOne", "sms", "SMS_ID"),
        'sms_status' => Array ("hasOne", "sms_status", "STATUS_ID"),
        'sms_error' => Array ("hasOne", "sms_error", "ERROR_ID")
    );
}
?>