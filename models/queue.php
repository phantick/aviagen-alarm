<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property datetime ts
 * @property int sms_id
 * @property int ordernumber
 * @property int cloned
 */
class queue extends dbObject {
    protected $dbTable = "QUEUE";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'SMS_ID'        => Array ('int', 'required'),
        'ORDERNUMBER'   => Array ('int', 'required'),
        'TS'            => Array ('datetime', 'required'),
        'CLONED'        => Array ('int'),
    );

    protected $relations = Array (
        'sms'           => Array ("hasOne", "sms", "SMS_ID")
    );
}
?>