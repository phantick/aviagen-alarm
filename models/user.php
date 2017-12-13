<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string login
 * @property string password
 * @property string name
 * @property string email
 * @property string role_id
 */
class user extends dbObject {
    protected $dbTable = "USER";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'LOGIN' => Array ('text', 'required'),
        'PASSWORD' => Array ('text', 'required'),
        'NAME' => Array ('text', 'required'),
        'EMAIL' => Array ('text', 'required'),
        'ROLE_ID' => Array ('int', 'required')
    );

    protected $relations = Array (
        'role' => Array ("hasOne", "role", "ROLE_ID")
    );
}
?>