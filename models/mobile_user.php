<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string name
 * @property string mobile
 * @property string email
 * @property int user_id
 */
class mobile_user extends dbObject {
    protected $dbTable = "MOBILE_USER";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'NAME' => Array ('text', 'required'),
        'MOBILE' => Array ('text', 'required'),
        'EMAIL' => Array ('text'),
        'USER_ID' => Array ('int')
    );

    protected $relations = Array (
        'user' => Array ("hasOne", "user", "USER_ID")
    );
}
?>