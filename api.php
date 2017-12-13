<?php
require_once("vendor/MysqliDb.php");
require_once("vendor/dbObject.php");

include_once("___auth.data.php");

function HTTPStatus($num) {
    $http_protocol = "HTTP/1.0"; 
    if(isset($_SERVER['SERVER_PROTOCOL']) && stripos($_SERVER['SERVER_PROTOCOL'],"HTTP") >= 0){
        $http_protocol = $_SERVER['SERVER_PROTOCOL']; 
    }
    $http = array(
        100 => $http_protocol . ' 100 Continue',
        101 => $http_protocol . ' 101 Switching Protocols',
        200 => $http_protocol . ' 200 OK',
        201 => $http_protocol . ' 201 Created',
        202 => $http_protocol . ' 202 Accepted',
        203 => $http_protocol . ' 203 Non-Authoritative Information',
        204 => $http_protocol . ' 204 No Content',
        205 => $http_protocol . ' 205 Reset Content',
        206 => $http_protocol . ' 206 Partial Content',
        300 => $http_protocol . ' 300 Multiple Choices',
        301 => $http_protocol . ' 301 Moved Permanently',
        302 => $http_protocol . ' 302 Found',
        303 => $http_protocol . ' 303 See Other',
        304 => $http_protocol . ' 304 Not Modified',
        305 => $http_protocol . ' 305 Use Proxy',
        307 => $http_protocol . ' 307 Temporary Redirect',
        400 => $http_protocol . ' 400 Bad Request',
        401 => $http_protocol . ' 401 Unauthorized',
        402 => $http_protocol . ' 402 Payment Required',
        403 => $http_protocol . ' 403 Forbidden',
        404 => $http_protocol . ' 404 Not Found',
        405 => $http_protocol . ' 405 Method Not Allowed',
        406 => $http_protocol . ' 406 Not Acceptable',
        407 => $http_protocol . ' 407 Proxy Authentication Required',
        408 => $http_protocol . ' 408 Request Time-out',
        409 => $http_protocol . ' 409 Conflict',
        410 => $http_protocol . ' 410 Gone',
        411 => $http_protocol . ' 411 Length Required',
        412 => $http_protocol . ' 412 Precondition Failed',
        413 => $http_protocol . ' 413 Request Entity Too Large',
        414 => $http_protocol . ' 414 Request-URI Too Large',
        415 => $http_protocol . ' 415 Unsupported Media Type',
        416 => $http_protocol . ' 416 Requested Range Not Satisfiable',
        417 => $http_protocol . ' 417 Expectation Failed',
        500 => $http_protocol . ' 500 Internal Server Error',
        501 => $http_protocol . ' 501 Not Implemented',
        502 => $http_protocol . ' 502 Bad Gateway',
        503 => $http_protocol . ' 503 Service Unavailable',
        504 => $http_protocol . ' 504 Gateway Time-out',
        505 => $http_protocol . ' 505 HTTP Version Not Supported',
    );

    header($http[$num]);

    return
        array(
            'code' => $num,
            'error' => $http[$num],
        );
}

function sortMailboxUsers($a, $b) {
    if ($a->ORDERNUMBER < $b->ORDERNUMBER) return -1;
    else if ($a->ORDERNUMBER > $b->ORDERNUMBER) return 1;
    else return 0;
}

// db instance
$db = new Mysqlidb('localhost', $db_user_name, $db_password, $db_scheme);
// enable class autoloading
dbObject::autoload("models");

session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data) && is_string($data->op)) {
        $ret = (object)array();
        header('Content-Type: application/json');
        if ($data->op == "check-auth") {
            $ret->user_id = $_SESSION['user_id'];
            $ret->user_name = $_SESSION['user_name'];
            $ret->user_role = $_SESSION['user_role'];
            echo json_encode($ret);
            return;
        } else if ($data->op == "auth" && is_string($data->login) && is_string($data->password)) {
            session_destroy();
            session_start();
            $user = user::where("LOGIN", $data->login)->where("PASSWORD", $data->password)->getOne();

            if ($user instanceof user) {
                $_SESSION['user_id'] = $user->ID;
                $_SESSION['user_name'] = $user->NAME;
                $_SESSION['user_role'] = $user->role->CODE;
                $ret->user_id = $user->ID;
                $ret->user_name = $user->NAME;
                $ret->user_role = $user->role->CODE;
            }
            if (isset($_SESSION['user_id'])) {
                echo json_encode($ret);
                return;
            } else {
                return HTTPStatus(401);
            }
        }
        if (!isset($_SESSION['user_id'])) {
            return HTTPStatus(401);
        }

        if ($data->op == "logout") {
            session_destroy();
            session_start();
        } else {
            $isAdmin = $_SESSION['user_role'] == "ADMIN";
            $op = split(":",$data->op, 2);

            ## USER
            if ($op[0] == "user") {
                if ($op[1] == "list" && $isAdmin) {
                    $ret->users = [];
                    $users = user::get();
                    if ($users) {
                        foreach ($users as $u) {
                            $uu = (object)array();
                            $uu->ID = $u->ID;
                            $uu->LOGIN = $u->LOGIN;
                            $uu->NAME = $u->NAME;
                            $uu->EMAIL= $u->EMAIL;
                            $uu->MOBILE = $u->MOBILE;
                            $uu->ROLE_ID = $u->ROLE_ID;
                            $uu->ROLE_CODE = $u->role->CODE;
                            $uu->ROLE_NAME = $u->role->NAME;
                            $ret->users[] = $uu;
                        }
                    }
                } else if ($op[1] == "get" && ($isAdmin || $data->ID == $_SESSION['user_id']) && 
                            is_int($data->ID)) {
                    $user = user::byId($data->ID);
                    if ($user instanceof user) {
                        $uu = (object)array();
                        $uu->ID = $user->ID;
                        $uu->LOGIN = $user->LOGIN;
                        $uu->NAME = $user->NAME;
                        $uu->EMAIL= $user->EMAIL;
                        $uu->MOBILE = $user->MOBILE;
                        $uu->ROLE_ID = $user->ROLE_ID;
                        $uu->ROLE_CODE = $user->role->CODE;
                        $uu->ROLE_NAME = $user->role->NAME;
                        $ret->user = $uu;
                    } else {
                        $ret->error = "User not found";
                    }
                } else if ($op[1] == "create" && $isAdmin) {
                    $user = new user(get_object_vars($data));
                    $id = $user->save();
                    if ($id == null) {
                        $ret->errors = $user->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && ($isAdmin || $data->ID == $_SESSION['user_id']) && 
                        is_int($data->ID)) {
                    $user = user::byId($data->ID);
                    if ($user instanceof user) {
                        $rc = $user->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $user->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "User not found";
                    }
                } else if ($op[1] == "delete" && $isAdmin &&
                            is_int($data->ID)) {
                    $user = user::byId($data->ID);
                    if ($user instanceof user) {
                        $rc = $user->delete();
                        if (!$rc) {
                            $ret->errors = $user->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "User not found";
                    }
                } else {
                   return HTTPStatus(400);
                }

            ## ROLE
            } else if ($op[0] == "role" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->roles = [];
                    $roles = role::get();
                    if ($roles) {
                        foreach ($roles as $r) {
                            $rr = (object)array();
                            $rr->ID = $r->ID;
                            $rr->CODE = $r->CODE;
                            $rr->NAME = $r->NAME;
                            $ret->roles[]  = $rr;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $role = role::byId($data->ID);
                    if ($role instanceof role) {
                        $rr = (object)array();
                        $rr->ID = $role->ID;
                        $rr->CODE = $role->CODE;
                        $rr->NAME = $role->NAME;
                        $ret->role = $rr;
                    } else {
                        $ret->error = "Role not found";
                    }
                } else if ($op[1] == "create") {
                    $role = new role(get_object_vars($data));
                    $id = $role->save();
                    if ($id == null) {
                        $ret->errors = $role->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $role = role::byId($data->ID);
                    if ($role instanceof role) {
                        $rc = $role->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $role->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Role not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $role = role::byId($data->ID);
                    if ($role instanceof role) {
                        $rc = $role->delete();
                        if (!$rc) {
                            $ret->errors = $role->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Role not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## SMS_STATUS
            } else if ($op[0] == "sms_status" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->statuses = [];
                    $statuses = sms_status::get();
                    if ($statuses) {
                        foreach ($statuses as $s) {
                            $ss = (object)array();
                            $ss->ID = $s->ID;
                            $ss->CODE = $s->CODE;
                            $ss->I_CODE = $s->I_CODE;
                            $ss->NAME = $s->NAME;
                            $ret->statuses[] = $ss;
                        }
                    }   
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $status = sms_status::byId($data->ID);
                    if ($status instanceof sms_status) {
                        $ss = (object)array();
                        $ss->ID = $status->ID;
                        $ss->CODE = $status->CODE;
                        $ss->I_CODE = $status->I_CODE;
                        $ss->NAME = $status->NAME;
                        $ret->status = $ss;
                    } else {
                        $ret->error = "SMS status not found";
                    }
                } else if ($op[1] == "create") {
                    $status = new sms_status(get_object_vars($data));
                    $id = $status->save();
                    if ($id == null) {
                        $ret->errors = $status->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $status = sms_status::byId($data->ID);
                    if ($status instanceof sms_status) {
                        $rc = $status->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $status->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "SMS status not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $status = sms_status::byId($data->ID);
                    if ($status instanceof sms_status) {
                        $rc = $status->delete();
                        if (!$rc) {
                            $ret->errors = $status->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "SMS status not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## SMS_ERROR
            } else if ($op[0] == "sms_error" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->sms_errors = [];
                    $sms_errors = sms_error::get();
                    if ($sms_errors) {
                        foreach ($sms_errors as $e) {
                            $ee = (object)array();
                            $ee->ID = $e->ID;
                            $ee->CODE = $e->CODE;
                            $ee->I_CODE = $e->I_CODE;
                            $ee->NAME = $e->NAME;
                            $ret->sms_errors[] = $ee;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $sms_error = sms_error::byId($data->ID);
                    if ($sms_error instanceof sms_error) {
                        $ee = (object)array();
                        $ee->ID = $sms_error->ID;
                        $ee->CODE = $sms_error->CODE;
                        $ee->I_CODE = $sms_error->I_CODE;
                        $ee->NAME = $sms_error->NAME;
                        $ret->sms_error = $ee;
                    } else {
                        $ret->error = "SMS error not found";
                    }
                } else if ($op[1] == "create") {
                    $sms_error = new sms_error(get_object_vars($data));
                    $id = $sms_error->save();
                    if ($id == null) {
                        $ret->errors = $sms_error->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $sms_error = sms_error::byId($data->ID);
                    if ($sms_error instanceof sms_error) {
                        $rc = $sms_error->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $sms_error->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "SMS error not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $sms_error = sms_error::byId($data->ID);
                    if ($sms_error instanceof sms_error) {
                        $rc = $sms_error->delete();
                        if (!$rc) {
                            $ret->errors = $sms_error->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "SMS error not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## EVENT_TYPE
            } else if ($op[0] == "event_type" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->event_types = [];
                    $event_types = event_type::get();
                    if ($event_types) {
                        foreach ($event_types as $e) {
                            $ee = (object)array();
                            $ee->ID = $e->ID;
                            $ee->CODE = $e->CODE;
                            $ee->NAME = $e->NAME;
                            $ee->DESCR = $e->DESCR;
                            $ret->event_types[] = $ee;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $event_type = event_type::byId($data->ID);
                    if ($event_type instanceof event_type) {
                        $ee = (object)array();
                        $ee->ID = $event_type->ID;
                        $ee->CODE = $event_type->CODE;
                        $ee->NAME = $event_type->NAME;
                        $ee->DESCR = $event_type->DESCR;
                        $ret->event_type = $ee;
                    } else {
                        $ret->error = "Event type not found";
                    }
                } else if ($op[1] == "create") {
                    $event_type = new event_type(get_object_vars($data));
                    $id = $event_type->save();
                    if ($id == null) {
                        $ret->errors = $event_type->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $event_type = event_type::byId($data->ID);
                    if ($event_type instanceof event_type) {
                        $rc = $event_type->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Event type not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $event_type = event_type::byId($data->ID);
                    if ($event_type instanceof event_type) {
                        $rc = $event_type->delete();
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Event type not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## MAILBOX
            } else if ($op[0] == "mailbox" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->list = [];
                    $list = mailbox::orderBy("NAME", "asc")->get();
                    if ($list) {
                        foreach ($list as $l) {
                            $li = (object)array();
                            $li->ID = $l->ID;
                            $li->NAME = $l->NAME;
                            $li->PASSWORD = $l->PASSWORD;
                            $li->QUEUE = $l->QUEUE;
                            $mailbox_users = $l->mailbox_users;
                            if (count($mailbox_users) > 0) {
                                $li->mailbox_users = [];
                                for ($i=0; $i<count($mailbox_users); $i++) {
                                    $mu = (object)array();
                                    $mu->ID = $mailbox_users[$i]->ID;
                                    $mu->ORDERNUMBER = $mailbox_users[$i]->ORDERNUMBER;
                                    $mu->MOBILE_USER_ID = $mailbox_users[$i]->MOBILE_USER_ID;
                                    $mu->MOBILE_USER_NAME = $mailbox_users[$i]->mobile_user->NAME;
                                    $mu->MOBILE_USER_MOBILE = $mailbox_users[$i]->mobile_user->MOBILE;
                                    $li->mailbox_users[] = $mu;
                                }
                                usort($li->mailbox_users, "sortMailboxUsers");
                            }
                            $ret->list[] = $li;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $l = mailbox::byId($data->ID);
                    if ($l instanceof mailbox) {
                        $li = (object)array();
                        $li->ID = $l->ID;
                        $li->NAME = $l->NAME;
                        $li->PASSWORD = $l->PASSWORD;
                        $li->QUEUE = $l->QUEUE;
                        $mailbox_users = $l->mailbox_users;
                        if (count($mailbox_users) > 0) {
                            $li->mailbox_users = [];
                            for ($i=0; $i<count($mailbox_users); $i++) {
                                $mu = (object)array();
                                $mu->ID = $mailbox_users[$i]->ID;
                                $mu->ORDERNUMBER = $mailbox_users[$i]->ORDERNUMBER;
                                $mu->MOBILE_USER_ID = $mailbox_users[$i]->MOBILE_USER_ID;
                                $mu->MOBILE_USER_NAME = $mailbox_users[$i]->mobile_user->NAME;
                                $mu->MOBILE_USER_MOBILE = $mailbox_users[$i]->mobile_user->MOBILE;
                                $li->mailbox_users[] = $mu;
                            }
                            usort($li->mailbox_users, "sortMailboxUsers");
                        }
                        $ret->item = $li;
                    } else {
                        $ret->error = "Mailbox not found";
                    }
                } else if ($op[1] == "create") {
                    $l = new mailbox(get_object_vars($data));
                    $id = $l->save();
                    if ($id == null) {
                        $ret->errors = $event_type->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $l = mailbox::byId($data->ID);
                    if ($l instanceof mailbox) {
                        $rc = $l->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mailbox not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $l = mailbox::byId($data->ID);
                    if ($l instanceof mailbox) {
                        $rc = $l->delete();
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mailbox not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## MAILBOX_USER
            } else if ($op[0] == "mailbox_user" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->list = [];
                    $list = mailbox_user::get();
                    if ($list) {
                        foreach ($list as $l) {
                            $li = (object)array();
                            $li->ID = $l->ID;
                            $li->MAILBOX_ID = $l->MAILBOX_ID;
                            $li->MOBILE_USER_ID = $l->MOBILE_USER_ID;
                            $ret->list[] = $li;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $l = mailbox_user::byId($data->ID);
                    if ($l instanceof mailbox_user) {
                        $li = (object)array();
                        $li->ID = $l->ID;
                        $li->MAILBOX_ID = $l->MAILBOX_ID;
                        $li->MOBILE_USER_ID = $l->MOBILE_USER_ID;
                        $ret->item = $li;
                    } else {
                        $ret->error = "Mailbox user not found";
                    }
                } else if ($op[1] == "create") {
                    $li = new mailbox_user(get_object_vars($data));
                    $id = $mailbox_user->save();
                    if ($id == null) {
                        $ret->errors = $event_type->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $l = mailbox_user::byId($data->ID);
                    if ($l instanceof mailbox_user) {
                        $rc = $l->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mailbox user not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $l = mailbox_user::byId($data->ID);
                    if ($l instanceof mailbox_user) {
                        $rc = $l->delete();
                        if (!$rc) {
                            $ret->errors = $event_type->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mailbox user not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## EVENT
            } else if ($op[0] == "event" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->list = [];
                    $list = event::get();
                    if ($list) {
                        foreach ($list as $l) {
                            $li = (object)array();
                            $li->ID = $l->ID;
                            $li->MAILBOX_ID = $l->MAILBOX_ID;
                            $li->M_ID = $l->M_ID;
                            $li->EVENT_TYPE_ID = $l->EVENT_TYPE_ID;
                            $li->TS = $l->TS;
                            $li->TEXT = $l->TEXT;
                            $li->event_type = $l->event_type->data;
                            $li->sms = [];
                            $sms_list = $l->sms;
                            if (Count($sms_list) > 0) {
                                foreach ($sms_list as $sms) {
                                    $sms_data = $sms->data;
                                    $sms_data['sms_st'] = $sms->sms_st;
                                    $li->sms[] = $sms_data;
                                }
                            }
                            $ret->list[] = $li;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $l = event::byId($data->ID);
                    if ($l instanceof event) {
                        $li = (object)array();
                        $li->ID = $l->ID;
                        $li->MAILBOX_ID = $l->MAILBOX_ID;
                        $li->M_ID = $l->M_ID;
                        $li->EVENT_TYPE_ID = $l->EVENT_TYPE_ID;
                        $li->TS = $l->TS;
                        $li->TEXT = $l->TEXT;
                        $li->event_type = $l->event_type->data;
                        $li->sms = [];
                        $sms_list = $l->sms;
                        if (Count($sms_list) > 0) {
                            foreach ($sms_list as $sms) {
                                $sms_data = $sms->data;
                                $sms_data->sms_st = $sms->sms_st;
                                $li->sms[] = $sms_data;
                            }
                        }
                        $ret->item = $li;
                    } else {
                        $ret->error = "Event not found";
                    }
                } else {
                    return HTTPStatus(400);
                }

            ## MOBILE_USER
            } else if ($op[0] == "mobile_user" && $isAdmin) {
                if ($op[1] == "list") {
                    $ret->mobile_users = [];
                    $users = mobile_user::get();
                    if ($users) {
                        foreach ($users as $u) {
                            $uu = (object)array();
                            $uu->ID = $u->ID;
                            $uu->NAME = $u->NAME;
                            $uu->MOBILE = $u->MOBILE;
                            $uu->EMAIL= $u->EMAIL;
                            $uu->USER_ID = $u->USER_ID;
                            if ($u->user) {
                                $uu->USER_NAME = $u->user->NAME;
                            }
                            $ret->mobile_users[] = $uu;
                        }
                    }
                } else if ($op[1] == "get" && is_int($data->ID)) {
                    $user = mobile_user::byId($data->ID);
                    if ($user instanceof mobile_user) {
                        $uu = (object)array();
                        $uu->ID = $user->ID;
                        $uu->NAME = $user->NAME;
                        $uu->MOBILE = $user->MOBILE;
                        $uu->EMAIL= $user->EMAIL;
                        $uu->USER_ID = $user->USER_ID;
                        $ret->mobile_user = $uu;
                    } else {
                        $ret->error = "Mobile user not found";
                    }
                } else if ($op[1] == "create") {
                    $user = new mobile_user(get_object_vars($data));
                    $id = $user->save();
                    if ($id == null) {
                        $ret->errors = $user->errors;
                        $ret->error = $db->getLastError();
                    } else {
                        $ret->ID = $id;
                    }
                } else if ($op[1] == "update" && is_int($data->ID)) {
                    $user = mobile_user::byId($data->ID);
                    if ($user instanceof mobile_user) {
                        $rc = $user->save(get_object_vars($data));
                        if (!$rc) {
                            $ret->errors = $user->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mobile user not found";
                    }
                } else if ($op[1] == "delete" && is_int($data->ID)) {
                    $user = mobile_user::byId($data->ID);
                    if ($user instanceof mobile_user) {
                        $rc = $user->delete();
                        if (!$rc) {
                            $ret->errors = $user->errors;
                            $ret->error = $db->getLastError();
                        }
                    } else {
                        $ret->error = "Mobile user not found";
                    }
                } else {
                   return HTTPStatus(400);
                }
            } else {
                return HTTPStatus(400);
            }
            echo json_encode($ret);    
        }
    } else {
        return HTTPStatus(400);
    }
}
?>