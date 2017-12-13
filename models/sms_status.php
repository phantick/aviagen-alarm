<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string code
 * @property int i_code
 * @property string name
 */
class sms_status extends dbObject {
    protected $dbTable = "SMS_STATUS";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'CODE' => Array ('text', 'required'),
        'I_CODE' => Array ('int', 'required'),
        'NAME' => Array ('text')
    );
}
?>