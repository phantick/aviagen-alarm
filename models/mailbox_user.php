<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property int mailbox_id
 * @property int mobile_user_id
 * @property int ordernumber
 */
class mailbox_user extends dbObject {
    protected $dbTable = "MAILBOX_USER";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'MAILBOX_ID' => Array ('int', 'required'),
        'MOBILE_USER_ID' => Array ('int', 'required'),
        'ORDERNUMBER' => Array ('int', 'required')
    );
    protected $relations = Array (
        'mobile_user' => Array ("hasOne", "mobile_user", "MOBILE_USER_ID"),
        'mailbox' => Array ("hasOne", "mailbox", "MAILBOX_ID"),
    );
}
?>