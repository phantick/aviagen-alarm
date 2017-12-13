API = function() {
    var 
        apiCall = function(what, params, cb) {
            params = (params||{});
            params.op = what;
            $.ajax({
              type: "POST",  
              contentType : "application/json; charset=utf-8",
              dataType: "json",
              url: "api.php",
              data: JSON.stringify(params)
            }).always(function (data, txtStatus, xhr) {
                var status = (data||{}).status||(xhr||{}).status
                if (status == 200) {
                    cb(data);
                } else if (status == 401) {
                    cb({});
                } else if (status == 400) {
                    cb({});
                } else {
                    cb({});
                }
            });
        },
        // Authentication
        authenticated = function(cb) {
            apiCall("check-auth", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        authenticate = function(login, password, cb) {
            apiCall("auth", {login: login, password: password}, function(rc) {
                if (cb) cb(rc||{});
            });
        },

        logout = function(cb) {
            apiCall("logout", {}, function(rc) {
                if (cb) cb();
            });
        },

        // USER
        userList = function(cb) {
            apiCall("user:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        userCreate = function(data, cb) {
            apiCall("user:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        userUpdate = function(data, cb) {
            apiCall("user:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        userDelete = function(id, cb) {
            apiCall("user:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // ROLE
        roleList = function(cb) {
            apiCall("role:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        roleCreate = function(data, cb) {
            apiCall("role:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        roleUpdate = function(data, cb) {
            apiCall("role:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        roleDelete = function(id, cb) {
            apiCall("role:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // MOBILE_USER
        mobile_userList = function(cb) {
            apiCall("mobile_user:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        mobile_userCreate = function(data, cb) {
            apiCall("mobile_user:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        mobile_userUpdate = function(data, cb) {
            apiCall("mobile_user:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        mobile_userDelete = function(id, cb) {
            apiCall("mobile_user:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // SMS_STATUS
        sms_statusList = function(cb) {
            apiCall("sms_status:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        sms_statusCreate = function(data, cb) {
            apiCall("sms_status:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        sms_statusUpdate = function(data, cb) {
            apiCall("sms_status:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        sms_statusDelete = function(id, cb) {
            apiCall("sms_status:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // SMS_ERROR
        sms_errorList = function(cb) {
            apiCall("sms_error:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        sms_errorCreate = function(data, cb) {
            apiCall("sms_error:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        sms_errorUpdate = function(data, cb) {
            apiCall("sms_error:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        sms_errorDelete = function(id, cb) {
            apiCall("sms_error:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // EVENT_TYPE
        event_typeList = function(cb) {
            apiCall("event_type:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        event_typeCreate = function(data, cb) {
            apiCall("event_type:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        event_typeUpdate = function(data, cb) {
            apiCall("event_type:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        event_typeDelete = function(id, cb) {
            apiCall("event_type:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // MAILBOX
        mailboxList = function(cb) {
            apiCall("mailbox:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        mailboxGet = function(id, cb) {
            apiCall("mailbox:get", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        mailboxCreate = function(data, cb) {
            apiCall("mailbox:create", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        mailboxUpdate = function(data, cb) {
            apiCall("mailbox:update", data||{}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },
        mailboxDelete = function(id, cb) {
            apiCall("mailbox:delete", {ID: id}, function(rc) {
                if (cb) cb((rc||{}));
            });
        },

        // EVENT
        eventsList = function(cb) {
            apiCall("event:list", {}, function(rc) {
                if (cb) cb((rc||{}));
            });
        };
    return {
        authenticated: authenticated,
        authenticate: authenticate,
        logout: logout,

        userList:           userList,
        userCreate:         userCreate,
        userUpdate:         userUpdate,
        userDelete:         userDelete,

        roleList:           roleList,
        roleCreate:         roleCreate,
        roleUpdate:         roleUpdate,
        roleDelete:         roleDelete,

        mobile_userList:    mobile_userList,
        mobile_userCreate:  mobile_userCreate,
        mobile_userUpdate:  mobile_userUpdate,
        mobile_userDelete:  mobile_userDelete,

        sms_statusList:     sms_statusList,
        sms_statusCreate:   sms_statusCreate,
        sms_statusUpdate:   sms_statusUpdate,
        sms_statusDelete:   sms_statusDelete,

        sms_errorList:      sms_errorList,
        sms_errorCreate:    sms_errorCreate,
        sms_errorUpdate:    sms_errorUpdate,
        sms_errorDelete:    sms_errorDelete,

        event_typeList:     event_typeList,
        event_typeCreate:   event_typeCreate,
        event_typeUpdate:   event_typeUpdate,
        event_typeDelete:   event_typeDelete,

        mailboxList:        mailboxList,
        mailboxGet:         mailboxGet,
        mailboxCreate:      mailboxCreate,
        mailboxUpdate:      mailboxUpdate,
        mailboxDelete:      mailboxDelete,

        eventsList:         eventsList
    }
}();