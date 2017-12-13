<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string code
 * @property string name
 * @property string descr
 */
class event_type extends dbObject {
    protected $dbTable = "EVENT_TYPE";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'CODE' => Array ('text', 'required'),
        'NAME' => Array ('text', 'required'),
        'DESCR' => Array ('text')
    );
}
?>