App.admin = function() {
    var $c, $view, initialized, $menuItems, $container,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            $view = $(doT.template($("#adminViewTmpl").text())());
            $container = $view.find(".container");

            $menuItems = $view.find(".menu .item");
            $menuItems.moclick(function() {
                var $this = $(this),
                    tag = $this.attr("tag");
                location.hash = "admin/"+tag;
            });
            $c.append($view);

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);

            $menuItems.removeClass("selected");
            $menuItems.filter("[tag="+path[0]+"]").addClass("selected");

            for (var v in App.admin) {
                if (v == path[0]) {
                    if (App.admin[v].show) App.admin[v].show($container);
                } else {
                    if (App.admin[v].hide) App.admin[v].hide($container);
                }
            }
            $view.show();
        },
        hide = function($cont) {
            $view.hide();
        },
        fillData = function($t, data, controlsData, save, del) {
            var $ths = $t.find("th");
            for (var i=0; i<data.length; i++) {
                var $tr = $("<tr></tr>").attr("tag", data[i].ID);
                $ths.each(function() {
                    var $th = $(this),
                        tag = $th.attr("tag"),
                        showTag = $th.attr("showTag"),
                        $td = $(this.outerHTML.replace("<th", "<td").replace("th>", "td>"));
                    if ($td.hasClass("action")) {
                        $("<div class='link del'>Удалить</div>").moclick(function() {
                            if (del) {
                                var $this = $(this),
                                    $ptr = $this.parents("tr:first");
                                if (confirm("Уверен?")) {
	                                del(Number($ptr.attr("tag")), function(rc) {
	                                    if (rc.error || rc.errors) {
	
	                                    } else {
	                                        $ptr.remove();
	                                    }
	                                });
	                            }
                            }
                        }).appendTo($td);
                    } else {
                        if ($td.attr("type") == "checkbox") {
                            var $cb = $("<input type='checkbox'></input>").prop("checked", data[i][tag] > 0 ? true : false).change(function () {
                                var $this = $(this),
                                    $td = $this.parents("td:first"),
                                    $tr = $td.parents("tr:first");
                                if ($td.hasClass("editable") && !$td.hasClass("edit") && (!$t.hasClass("edit") || $this.hasClass("new"))) {
                                    $td.addClass("edit");
                                    $t.find("td:not(.edit,.new),.link.del,.link.create").disable();
                                    $t.addClass("edit");
                                    if (save) {
                                        var dt = {ID: Number($tr.attr("tag"))};
                                        dt[tag] = $this.prop("checked") ? 1 : 0;
                                        save(dt, function (rc) {
                                            if (rc.error || rc.errors) {
                                                $this.addClass("error");
                                            } else {
                                                if (dt[tag]) {
                                                    $tr.addClass(tag);
                                                } else {
                                                    $tr.removeClass(tag);
                                                }
                                                $t.removeClass("edit");
                                                $t.removeClass("edit");
                                                $t.find("td,.link").enable().removeClass("edit");
                                            }
                                        })
                                    } else {
                                        $t.removeClass("edit");
                                        $t.find("td,.link").enable().removeClass("edit");
                                    }
                                }
                            });
                            if (data[i][tag] > 0) {
                                $tr.addClass(tag);
                            }
                            $td.html($cb);
                        } else if ($td.attr("type") == "icon") {
                            var $icon = $("<div class='icon'></div>").click(function(e) {
                                var $this = $(this),
                                    $td = $this.parents("td:first"),
                                    $tr = $td.parents("tr:first"),
                                    tag = $td.attr("tag"),
                                    id = $tr.attr("tag");
                                if (controlsData[tag]) {
                                    controlsData[tag](Number(id), e);
                                }
                            });
                            $td.html($icon);
                        } else {
                            if ($td.attr("hidden-value")) {
                                $td.attr("val", "");
                                $td.text("******");
                            } else {
                                $td.attr("val", data[i][tag]);
                                $td.text(data[i][showTag || tag]==null?"------":data[i][showTag || tag]);
                            }
                        }
                    }
                    $tr.append($td)
                });
                $t.append($tr);
            }
            var onClick = function() {
                var $this = $(this),
                    tag = $this.attr("tag"),
                    controlType = $this.attr("type"),
                    $tr = $this.parents("tr:first");
                if ($this.hasClass("editable") && !$this.hasClass("edit") && (!$t.hasClass("edit") || $this.hasClass("new"))) {
                    $this.addClass("edit");
                    $t.find("td:not(.edit,.new),.link.del,.link.create").disable();
                    $t.addClass("edit");
                    var val = $this.attr("val");
                    if (controlType == "text" || controlType == "textarea" || controlType == "int") {
                        var $input = (controlType == "textarea"?$("<textarea></textarea>"):$("<input type='text'></input")).keydown(function(e) {
                                if ($this.hasClass("new")) return;
                                if (e.keyCode == 13 || e.keyCode == 9) {
                                    //TODO: validation here
                                    if (save) {
                                        var dt = {ID: Number($tr.attr("tag"))};
                                        dt[tag] = controlType == "int"?Number($input.val()):$input.val();
                                        save(dt, function(rc) {
                                            if (rc.error || rc.errors) {
                                                $input.addClass("error");
                                            } else {
                                                if ($this.attr("hidden-value")) {
                                                    $this.attr("val", "");
                                                    $this.empty().text("******");
                                                } else {
                                                    $this.empty().text($input.val());
                                                    $this.attr("val", $input.val()||"------");
                                                }
                                                $t.removeClass("edit");
                                                $t.find("td,.link").enable().removeClass("edit");

                                                if (e.keyCode == 9) {
                                                    var $allEd = $t.find("td.editable"),
                                                        idx = $allEd.index($this);
                                                    if (idx >= 0) {
                                                        if (e.shiftKey && idx > 0) {
                                                            idx--;
                                                        } else if (idx < $allEd.length) {
                                                            idx++;
                                                        }
                                                        $allEd.eq(idx).click();
                                                    }
                                                }
                                            }
                                        })
                                    } else {
                                        if ($this.attr("hidden-value")) {
                                            $this.attr("val", "");
                                            $this.empty().text("******");
                                        } else {
                                            $this.empty().text($input.val()||"------");
                                            $this.attr("val", $input.val());
                                        }
                                        $t.removeClass("edit");
                                        $t.find("td,.link").enable().removeClass("edit");
                                    }
                                    return false;
                                } else if (e.keyCode == 27){
                                    if ($this.attr("hidden-value")) {
                                        $this.empty().text("******");
                                    } else {
                                        $this.empty().text(val || "------");
                                    }
                                    $t.find("td,.link").enable().removeClass("edit");
                                    $t.removeClass("edit");
                                    return false;
                                }
                            });
                        if ($this.attr("maxlength")) $input.attr("maxlength", $this.attr("maxlength"));
                        $input.val(val);
                        $this.empty().append($input);
                        $input.focus();
                    } else if (controlType == "select") {
                        var object = $this.attr("object");
                        if (object && controlsData[object]) {
                            var $select = $("<select></select>").change(function() {
                                if ($this.hasClass("new")) return;
                                var dt = {ID: Number($tr.attr("tag"))||null};
                                dt[tag] = Number($select.val())||null;
                                if (save) {
                                    save(dt, function(rc) {
                                        if (rc.error || rc.errors) {
                                            $select.addClass("error");
                                        } else {
                                            $this.empty().text($select.find("option:selected").text());
                                            $this.attr("val", $select.val());
                                            $t.removeClass("edit");
                                            $t.find("td,.link").enable().removeClass("edit");
                                        }
                                    })
                                } else {
                                    $this.empty().text($select.find("option:selected").text());
                                    $this.attr("val", $select.val());
                                    $t.removeClass("edit");
                                    $t.find("td,.link").enable().removeClass("edit");
                                }
                            });
                            for (var d in controlsData[object]) {
                                $select.append($("<option></option>").attr("value", d).text(controlsData[object][d]).prop("selected", d==val))
                            }
                            $select.val(val);
                        }
                        $this.empty().append($select);
                    }
                }
            };
            var $createTr = $("<tr><td colspan='"+$ths.length+"' class='create'><div class='link create'>Создать</div></td></tr>");
            $t.append($createTr);
            $createTr.find(".link").moclick(function() {
                var $newTr = $("<tr class='new'></tr>");
                $ths.each(function() {
                    var $th = $(this),
                        $td = $(this.outerHTML.replace("<th", "<td").replace("th>", "td>"));
                    $td.addClass("new").text("");
                    if ($td.hasClass("action")) {
                        $("<div class='link save'>Сохранить</div>").moclick(function() {
                            var newDt = {isnew: true};
                            $newTr.find("td").each(function() {
                                var $this = $(this),
                                    tag = $this.attr("tag");
                                if ($this.hasClass("editable")) {
                                    var type = $this.attr("type");
                                    if (type == "text") {
                                        newDt[tag] = $this.find("input").val();
                                    } else if (type == "textarea") {
                                        newDt[tag] = $this.find("textarea").val();
                                    } else if (type == "int") {
                                        newDt[tag] = Number($this.find("input").val());
                                    } else if (type == "select") {
                                        newDt[tag] = Number($this.find("select").val())||null;
                                    } else if (type == "checkbox") {
                                        newDt[tag] = $this.find("input").prop("checked")?1:0;
                                    }
                                }
                            });

                            if (save) {
                                save(newDt, function(rc) {
                                    if (rc.error || rc.errors) {
                                        if (rc.errors) {

                                        }
                                    } else if (rc.ID) {
                                        $t.find("td,.link").enable().removeClass("edit");
                                        $t.removeClass("edit");
                                        $newTr.removeClass("new").attr("tag", rc.ID);
                                        $newTr.find(".new").removeClass("new");
                                        $newTr.find("td[tag=ID]").attr("val", rc.ID).text(rc.ID);
                                        $newTr.find(".link.save,.link.cancel").remove();
                                        $newTr.find(".link.del").show();
                                        $newTr.find("td.editable").each(function() {
                                            var $this = $(this),
                                                tag = $this.attr("tag"),
                                                controlType = $this.attr("type");
                                            if (controlType == "text" || controlType == "textarea" || controlType == "int") {
                                                if ($this.attr("hidden-value")) {
                                                    $this.attr("val", "");
                                                    $this.empty().text("******");
                                                } else {
                                                    var val = (controlType == "textarea"?$this.find("teaxarea"):$this.find("input")).val();
                                                    $this.attr("val", val);
                                                    $this.empty().text(val||"------");
                                                }
                                            } else if (controlType == "select") {
                                                var $select = $this.find("select"),
                                                    val = $select.find("option:selected").text();
                                                $this.empty().text(val);
                                                $this.attr("val", $select.val());
                                            } else if (controlType == "checkbox") {
                                                var $checkbox = $this.find("input[type='checkbox']");
                                                $checkbox.change(function () {
                                                    var $this = $(this),
                                                        $td = $this.parents("td:first"),
                                                        $tr = $td.parents("tr:first");
                                                    if ($td.hasClass("editable") && !$td.hasClass("edit") && (!$t.hasClass("edit") || $this.hasClass("new"))) {
                                                        $td.addClass("edit");
                                                        $t.find("td:not(.edit,.new),.link.del,.link.create").disable();
                                                        $t.addClass("edit");
                                                        if (save) {
                                                            var dt = {ID: Number($tr.attr("tag"))};
                                                            dt[tag] = $this.prop("checked") ? 1 : 0;
                                                            save(dt, function (rc) {
                                                                if (rc.error || rc.errors) {
                                                                    $this.addClass("error");
                                                                } else {
                                                                    if (dt[tag]) {
                                                                        $tr.addClass(tag);
                                                                    } else {
                                                                        $tr.removeClass(tag);
                                                                    }
                                                                    $t.removeClass("edit");
                                                                    $t.removeClass("edit");
                                                                    $t.find("td,.link").enable().removeClass("edit");
                                                                }
                                                            })
                                                        } else {
                                                            $t.removeClass("edit");
                                                            $t.find("td,.link").enable().removeClass("edit");
                                                        }
                                                    }
                                                });
                                                if ($checkbox.prop("checked")) {
                                                    $newTr.addClass(tag);
                                                } else {
                                                    $newTr.removeClass(tag);
                                                }
                                            }
                                        });
                                    }
                                    $createTr.show();
                                });
                            }
                        }).appendTo($td);
                        $("<div class='link cancel'>Отменить</div>").moclick(function() {
                            $newTr.remove();
                            $t.find("td,.link").enable().removeClass("edit");
                            $t.removeClass("edit");
                            $createTr.show();
                        }).appendTo($td);
                        $("<div class='link del'>Удалить</div>").moclick(function() {
                            if (del) {
                                var $this = $(this),
                                    $ptr = $this.parents("tr:first");
                                if (confirm("Уверен?")) {
	                                del(Number($ptr.attr("tag")), function(rc) {
	                                    if (rc.error || rc.errors) {
	
	                                    } else {
	                                        $ptr.remove();
	                                    }
	                                });
	                            }
                            }
                        }).appendTo($td).hide();
                    } else {
                        var controlType = $td.attr("type");
                        if (controlType == "checkbox") {
                            var $cb = $("<input type='checkbox'></input>")
                            $td.html($cb)
                        } else if (controlType == "icon") {
                            $td.html("<div class='icon'></div>");
                        }
                    }
                    $newTr.append($td)
                });
                $createTr.hide();
                $createTr.before($newTr);
                $newTr.find("td[type!='checkbox'][type!='icon']").moclick(onClick).each(function() {
                    $(this).click();
                });
                $newTr.find("input:first").focus();
            });
            $t.find("td[type!='checkbox'][type!='icon']").moclick(onClick);
        };

    return {
        show: show,
        hide: hide,
        fillData: fillData
    }
}();

App.admin.Dialog = (function() {
    var _dialogs = {
            'MOBILE_USERS': "<div class='wrap'><div class='ttl'>Укажите пользователей почтового ящика</div><table class='content'><tr><td class='added'></td><td class='all'></td></tr></table><div class='clear'></div><div class='btns'><div class='button enabled' id='close' style='float: right;'>Закрыть</div><div class='button enabled' id='save' style='float: right;'>Сохранить</div></div></div>"
        },
        _getDialog = function (code, params, substs, cbs, hotkeys) {
            var hothandler = null;
            var $dialog = $("<div id='" + code + "' class='dialog'></div>");
            var html = _dialogs[code];
            if (substs && substs.push) {
                for (var i = 0; i < substs.length; i++) {
                    html = html.replaceAll("^" + i, substs[i]);
                }
            }
            $dialog.html(html);
            $dialog.appendTo($(document.body));
            if (params) {
                if (params.clazz) $dialog.addClass(params.clazz);
                if (params.width) $dialog.width(params.width);
                if (params.height) $dialog.width(params.height);
                if (params.position == "center") {
                    $dialog.css("left", ($(window).width() - $dialog.width()) / 2);
                    $dialog.css("top", ($(window).height() - $dialog.height()) / 2 + $(window).scrollTop());
                } else if (typeof params.position == "object") {
                    if (params.position.left) $dialog.css("left", params.position.left);
                    if (params.position.top) $dialog.css("top", params.position.top + $(window).scrollTop());
                }
            }
            if (cbs) {
                for (var i in cbs) {
                    $("*[id='" + i + "']", $dialog).click(function () {
                        var $this = $(this);
                        if ($this.hasClass("enabled")) {
                            cbs[$this.attr("id")]($this);
                        }
                    });
                }
            }
            if (hotkeys) {
                hothandler = function (e) {
                    var key = e.keyCode;
                    for (var i in hotkeys) {
                        if (Number(i) == key) {
                            if (jQuery.isFunction(hotkeys[i])) hotkeys[i]();
                            else {
                                $("#" + hotkeys[i], $dialog).click();
                            }
                        }
                    }
                }
            }
            return (function () {
                var
                    _show = function (animate, cb) {
                        if (animate) {
                            var pos = $dialog.position();
                            var $dhelper = $("<div class='dialog-helper'></div>");
                            $dhelper.css({
                                "left": animate.x != null ? animate.x : $(document).width() / 2,
                                "top": animate.y != null ? animate.y : $(document).height() / 2,
                                "width": animate.w != null ? animate.w : 0,
                                "height": animate.h != null ? animate.h : 0
                            }).appendTo($(document.body));
                            $dhelper.animate({
                                width: $dialog.width(),
                                height: $dialog.height(),
                                left: pos.left,
                                top: pos.top
                            }, animate.speed || 300, function () {
                                $dhelper.remove();
                                $dialog.css("visibility", "visible");
                                if (cb) cb();
                            });
                        } else {
                            $dialog.css("visibility", "visible");
                            if (cb) cb();
                        }
                        if (hothandler) $(document).bind("keyup", hothandler);
                    },
                    _hide = function (animate, cb) {
                        if (hothandler)
                            $(document).unbind("keyup", hothandler);
                        if (animate) {
                            var pos = $dialog.position();
                            var $dhelper = $("<div class='dialog-helper'></div>");
                            $dhelper.css({
                                "left": pos.left,
                                "top": pos.top,
                                "width": $dialog.width(),
                                height: $dialog.height()
                            });
                            $dhelper.appendTo($(document.body));
                            $dialog.css("visibility", "hidden");
                            $dhelper.animate({
                                width: animate.w != null ? animate.w : 0,
                                height: animate.h != null ? animate.h : 0,
                                left: animate.x != null ? animate.x : 0,
                                top: animate.y != null ? animate.y : 0
                            }, animate.speed || 300, function () {
                                $dhelper.remove();
                                if (cb) cb();
                            });
                        } else {
                            $dialog.css("visibility", "hidden");
                            if (cb) cb();
                        }
                    },
                    _close = function () {
                        if (hothandler)
                            $(document).unbind("keyup", hothandler);
                        $dialog.remove();
                    },
                    _getContainer = function () {
                        return $dialog;
                    };
                return {
                    show: _show,
                    hide: _hide,
                    close: _close,
                    getContainer: _getContainer
                }
            })();
        };
    return {
        getDialog: _getDialog
    }
})();

App.admin.user = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminUserTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.userList(function(udata) {
                API.roleList(function(rdata) {
                    App.admin.fillData($table, udata.users||[], {role: function() {var r = {}; (rdata.roles||[]).forEach(function(v) {r[v.ID] = v.NAME;}); return r;}()}, function(dt, cb) {
                        if (dt.isnew) {
                            API.userCreate(dt, cb);
                        } else {
                            API.userUpdate(dt, cb);
                        }
                    }, function(id, cb) {
                        API.userDelete(id, cb);
                    });
                    $c.empty().append($view);
                    $view.show();
                });
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.role = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminRoleTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.roleList(function(data) {
                App.admin.fillData($table, data.roles||[], {}, function(dt, cb) {
                    if (dt.isnew) {
                        API.roleCreate(dt, cb);
                    } else {
                        API.roleUpdate(dt, cb);
                    }
                }, function(id, cb) {
                    API.roleDelete(id, cb);
                });
                $c.empty().append($view);
                $view.show();
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.mobile_user = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminMobileUserTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.mobile_userList(function(data) {
                API.userList(function(udata) {
                    var users = {"": "------"};
                    (udata.users||[]).forEach(function(v) {users[v.ID] = v.NAME;});
                    App.admin.fillData($table, data.mobile_users||[], {user: users}, function(dt, cb) {
                        if (dt.isnew) {
                            API.mobile_userCreate(dt, cb);
                        } else {
                            API.mobile_userUpdate(dt, cb);
                        }
                    }, function(id, cb) {
                        API.mobile_userDelete(id, cb);
                    });
                    $c.empty().append($view);
                    $view.show();
                });
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.sms_status = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminSMSStatusTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.sms_statusList(function(data) {
                App.admin.fillData($table, data.statuses||[], {}, function(dt, cb) {
                    if (dt.isnew) {
                        API.sms_statusCreate(dt, cb);
                    } else {
                        API.sms_statusUpdate(dt, cb);
                    }
                }, function(id, cb) {
                    API.sms_statusDelete(id, cb);
                });
                $c.empty().append($view);
                $view.show();
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.sms_error = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminSMSErrorTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.sms_errorList(function(data) {
                App.admin.fillData($table, data.sms_errors||[], {}, function(dt, cb) {
                    if (dt.isnew) {
                        API.sms_errorCreate(dt, cb);
                    } else {
                        API.sms_errorUpdate(dt, cb);
                    }
                }, function(id, cb) {
                    API.sms_errorDelete(id, cb);
                });
                $c.empty().append($view);
                $view.show();
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.event_type = function() {
    var $c, $view, $table, template, initialized,
        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminEventTypeTableTmpl").text())();

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.event_typeList(function(data) {
                App.admin.fillData($table, data.event_types||[], {}, function(dt, cb) {
                    if (dt.isnew) {
                        API.event_typeCreate(dt, cb);
                    } else {
                        API.event_typeUpdate(dt, cb);
                    }
                }, function(id, cb) {
                    API.event_typeDelete(id, cb);
                });
                $c.empty().append($view);
                $view.show();
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

App.admin.mailbox = function() {
    var $c, $view, $table, template, initialized,
        $addedItemTmpl = $("<div class='item'><div class='text'></div><div class='up'></div><div class='down'></div><div class='rm'></div></div>"),
        $allItemTmpl = $("<div class='item'><div class='add'></div><div class='text'></div></div>"),
        $added, $all,

        init = function($cont) {
            if (initialized) return;
            $c = $cont;
            template = doT.template($("#adminMailboxTableTmpl").text())();

            $addedItemTmpl.find(".up").moclick(function() {
                var $this = $(this),
                    $item = $this.parents(".item:first"),
                    $prev = $item.prev(".item");
                $item.insertBefore($prev)
            });
            $addedItemTmpl.find(".down").moclick(function() {
                var $this = $(this),
                    $item = $this.parents(".item:first"),
                    $next = $item.next(".item");
                $item.insertAfter($next)
            });
            $addedItemTmpl.find(".rm").moclick(function() {
                var $this = $(this),
                    $item = $this.parents(".item:first"),
                    id = $item.attr("tag");
                $item.remove();
                $all.find(".item[tag='"+id+"']").show();
            });

            $allItemTmpl.find(".add").moclick(function() {
                var $this = $(this),
                    $item = $this.parents(".item:first"),
                    id = $item.attr("tag"),
                    $newItem = $addedItemTmpl.clone(true).attr("tag", id);

                $newItem.find(".text").html($item.find(".text").html());
                $added.append($newItem);
                $item.hide();
            });

            initialized = true;
        },
        show = function($cont, path) {
            if (!initialized) init($cont);
            $view = $(template);
            $table = $view.find("table");

            API.mailboxList(function(data) {
                App.admin.fillData($table, data.list||[], {"MOBILE_USERS": function(id, e) {
                    API.mailboxGet(id, function(mailbox) {
                        if (mailbox && mailbox.item) {
                            API.mobile_userList(function(data) {
                                if (data && data.mobile_users) {
                                    var all = data.mobile_users||[],
                                        added = [],
                                        d = App.admin.Dialog.getDialog("MOBILE_USERS",
                                            {width: "720px", position: "center"},
                                            {},
                                            {
                                                "close": function() {
                                                    d.close();
                                                },
                                                "save": function() {
                                                    var mailboxUsers = [];
                                                    $added.find(".item").each(function() {
                                                        mailboxUsers.push(Number($(this).attr("tag")));
                                                    });
                                                    API.mailboxSetMobileUsers({ID: id, MOBILE_USERS: mailboxUsers}, function(rc) {

                                                    });

                                                    d.close();
                                                }
                                            },
                                            {"27" : "close", "13" : "save"}
                                        ),
                                        $d = d.getContainer();

                                        $added = $d.find(".added"),
                                        $all = $d.find(".all");

                                    (mailbox.item.mailbox_users||[]).forEach(function(u) {
                                        added.push(u.MOBILE_USER_ID);
                                        var $item = $addedItemTmpl.clone(true).attr("tag", u.MOBILE_USER_ID);
                                        $item.find(".text").text(u.MOBILE_USER_NAME).append($("<span></span>").text(" ("+u.MOBILE_USER_MOBILE+")"));
                                        $added.append($item)
                                    });
                                    all.forEach(function(u) {
                                        var $item = $allItemTmpl.clone(true).attr("tag", u.ID);
                                        $item.find(".text").text(u.NAME).append($("<span></span>").text(" ("+u.MOBILE+")"));
                                        if (added.indexOf(u.ID) >= 0) {
                                            $item.hide();
                                        }
                                        $all.append($item);
                                    });

                                    d.show({x:e.pageX, y: e.pageY});
                                }
                            })
                        }
                    })
                }}, function(dt, cb) {
                    if (dt.isnew) {
                        API.mailboxCreate(dt, cb);
                    } else {
                        API.mailboxUpdate(dt, cb);
                    }
                }, function(id, cb) {
                    API.mailboxDelete(id, cb);
                });
                $c.empty().append($view);
                $view.show();
            });
        },
        hide = function($cont) {
            if ($view) $view.hide();
        };

    return {
        show: show,
        hide: hide
    }
}();

plugins.admin = true;