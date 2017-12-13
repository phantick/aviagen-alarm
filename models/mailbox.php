<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string name
 * @property string pasword
 * @property int queue
 */
class mailbox extends dbObject {
    protected $dbTable = "MAILBOX";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'NAME' => Array ('text', 'required'),
        'PASSWORD' => Array ('text', 'required'),
        'QUEUE' => Array ('int', 'required')
    );
    protected $relations = Array (
        'mailbox_users' => Array ("hasMany", "mailbox_user", "MAILBOX_ID")
    );
}
?>