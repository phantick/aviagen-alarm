<?php
/**
 * To make IDEs autocomplete happy
 *
 * @property int id
 * @property string code
 * @property string name
 */
class role extends dbObject {
    protected $dbTable = "ROLE";
    protected $primaryKey = "ID";
    protected $dbFields = Array (
        'CODE' => Array ('text', 'required'),
        'NAME' => Array ('text', 'required')
    );
    protected $relations = Array (
        'users' => Array ("hasMany", "user", "ROLE_ID")
    );
}
?>