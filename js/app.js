PLUGINS = [];
plugins = {};

jQuery.fn.extend({
    mohover: function() {
        return this.mouseenter(function() {
            var $this = $(this);
            if ($this.hasClass("disabled")) return;
            $this.addClass("hover");
        }).mouseleave(function() {
            var $this = $(this);
            $this.removeClass("hover mousedown");
        });
    },
    moactivate: function() {
        return this.mousedown(function() {
            var $this = $(this);
            if ($this.hasClass("disabled")) return;
            $this.addClass("mousedown");
        }).mouseup(function() {
            var $this = $(this);
            $this.removeClass("mousedown");
        });
    },
    mopress: function() {
        return this.mousedown(function() {
            var $this = $(this);
            if ($this.hasClass("disabled")) return;
            $this.addClass("press");
            ITooLabs.WebApp.app.addToRelease($this);
        });
    },
    moclick: function(fn) {
        return this.click(function(e) {
            var $this = $(this);
            if ($this.hasClass("disabled")) return false;
            return fn.apply(this,  Array.prototype.slice.call(arguments, 0));
        });
    },
    momousedown: function(fn) {
        return this.mousedown(function(e) {
            var $this = $(this);
            if ($this.hasClass("disabled")) return false;
            return fn.apply(this, Array.prototype.slice.call(arguments, 0));
        });
    },
    moone: function(type, fn) {
        var f = function(e) {
            var $this = $(this);
            if ($this.hasClass("disabled")) {
                return false;
            }
            var r = fn.apply(this, Array.prototype.slice.call(arguments, 0));
            $this.unbind(type);
            return r;
        };
        return this.bind(type, f);
    },
    disable: function() {
        return this.addClass("disabled");
    },
    enable: function() {
        return this.removeClass("disabled");
    },
    replaceLinks: function() {
        return this.each(function(){
            var node = this, next, val, new_val, remove = [];
            if (node.nodeType === 1) {
                // (Element node)
                if (node = node.firstChild) {
                    do {
                        // Recursively call traverseChildNodes
                        // on each child node
                        next = node.nextSibling;
                        $(node).replaceLinks(node);
                    } while(node = next);
                }
            } else if (node.nodeType === 3) {
                // (Text node)
                val = node.nodeValue.replace(/</g, "&lt;").replace(/>/g, "&gt;");
                new_val = val.replace(/(^|\s+)((?:(?:https?:\/\/[-a-z0-9]{2,}(?:\.[-a-z0-9]{2,})*(?:\.[a-z][-a-z0-9]+))|(?:[-a-z0-9]{2,}(?:\.[-a-z0-9]+)*(?:\.[a-z][-a-z0-9]+)))(?:[-\w%&$\/.=!;*:#@+,{}'"~()?\[\]]*))($|\s+)/mi, function(all, sp1, link, sp2) {
                    var href = link;
                    try {
                        link = decodeURI(link);
                    } catch(e) { }
                    link = link.replace(/&/g, '&amp;');
                    if (href.indexOf("://") < 0) {
                        href = "//"+href;
                    }
                    return (sp1||"")+'<a target="_blank" href="' + href + '">' + link + '</a>'+(sp2||"");
                });
                if (new_val !== val && (!node.parentNode || (node.parentNode.nodeName != "A" && node.parentNode.nodeName != "STYLE"))) {
                    $(node).before(new_val);
                    remove.push( node );
                }
            }
            remove.length && $(remove).remove();
        });
    },

    /*
     * delayKeyup
     * http://code.azerti.net/javascript/jquery/delaykeyup.htm
     * Inspired by CMS in this post : http://stackoverflow.com/questions/1909441/jquery-keyup-delay
     * Written by Gaten
     * Example : $("#input").delayKeyup(function(){ alert("5 secondes passed from the last event keyup.", 5000); });
     */
    delayKeyup: function (callback, ms) {
        var $el = $(this);
        $(this).keyup(function () {
            clearTimeout($el.data("delayKeyupTimer"));
            $el.data("delayKeyupTimer", setTimeout(callback, ms));
        });
        return $(this);
    },
    clearDelayKeyup: function () {
        clearTimeout($(this).data("delayKeyupTimer"));
    },
    onInputChange: function (selector, callback) {
        var $el = $(this);

        if (callback === undefined) {
            callback = selector;
            $el.focus(function () {
                $el.data("prevValue", $el.val());
            });
            $el.keyup(function () {
                if ($el.data("prevValue") !== $el.val()) callback();
            });
        } else {
            $el.on("focus", selector, function () {
                $el.find(selector).data("prevValue", $el.find(selector).val());
            });
            $el.on("keyup", selector, function () {
                if ($el.find(selector).data("prevValue") !== $el.find(selector).val()) callback();
            });
        }

        return $el;
    }
});
jQuery.extend({
    escaped: function(str) {
        if (!str) return "";
        return str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    },
    borderRadiusSupported: function() {
        var a = document.createElement("a");
        var s = ['borderRadius', 'BorderRadius','MozBorderRadius','WebkitBorderRadius','OBorderRadius','KhtmlBorderRadius'];
        for (var i=0; i<s.length; i++) {
            if(a.style[s[i]] !== undefined) return true;
        }
        return false;
    },
    htmlEntityDecode: function(str) {
        var ta=document.createElement("textarea");
        ta.innerHTML=str.replace(/\n/g,"_-sn-_").replace(/\r/g,"_-sr-_").replace(/</g,"&lt;").replace(/>/g,"&gt;");
        return ta.value.replace(/_-sn-_/g, "\n").replace(/_-sr-_/g, "\r");
    }
});
$.fn.reverse = [].reverse;

function appendScripts($c, $scripts) {
    if ($scripts) {
        var head = document.getElementsByTagName('head')[0]
            $body = $(document.body);
        $scripts.each(function() {
            var $this = $(this);
            if (this.tagName && this.tagName == "SCRIPT") {
                if ($this.attr("type") == "text/x-dot-template") {
                    $body.append($this);
                } else {
                    var src = $this.attr("src");
                    plugins[src.split("/").pop().replace(/.js$/, "")] = false;
                    var jsNode = document.createElement('script');
                    jsNode.type = 'text/javascript';
                    jsNode.src = src;
                    head.appendChild(jsNode);
                }
            } else if (this.tagName && this.tagName == "LINK" && this.getAttribute("type") == "text/css") {
                var href = $this.attr("href");
                var linkNode = document.createElement('link');
                linkNode.type = 'text/css';
                linkNode.href = href;
                linkNode.rel = $this.attr("rel");
                head.appendChild(linkNode);
            }
        });
    }
}
function loadPlugins($c, callback) {
    try {
        plugins = {};
        if (PLUGINS && PLUGINS.push) {
            for (var i=0; i<PLUGINS.length; i++) {
                $.ajax({url: "templates/"+PLUGINS[i]+".html", async: false, cache : false, success: function(data) {
                    appendScripts($c, $(data));
                }});
            }
        }
    } catch(e) {console.error(e.message)}
    var f = function() {
        var pluginsLoaded = true;
        for (var i in plugins) {
            if (!plugins[i]) {
                pluginsLoaded = false;
                break;
            }
        }
        if (!pluginsLoaded) {
            setTimeout(f, 100);
            return;
        }
        if (callback) callback.call();
    };
    setTimeout(f, 100);
}

App = function() {
    var $c, isAdmin, userId, userName,

        authenticated = function(cb) {
            API.authenticated(cb);
        },

        authenticate = function(login, password, cb) {
            API.authenticate(login, password, cb);
        },

        logout = function() {
            API.logout(function() {
                location.reload(true);
            });
        },

        start = function($container, authData) {
            $c = $container;
            isAdmin = authData.user_role == "ADMIN";
            userId = authData.user_id;
            userName = authData.user_nsme;

            $c.find(".name").text(authData.user_name);
            PLUGINS.push("history");
            if (isAdmin) {
                $c.find(".top-bar .left-side").append("<div class='item link' tag='admin'>Администрирование</div><div class='item link' tag='history'>История</div>")
                $c.find(".top-bar .item").click(function() {
                    var $this = $(this), tag = $this.attr("tag");
                    if (tag == "logout") {
                        logout();
                    } else {
                        location.hash = $this.attr("tag");
                    }
                });
                PLUGINS.push("admin");
            }
            loadPlugins($c, function() {
                $(window).hashchange(function(e) {
                    var hash = location.hash.substring(1).split("/"),
                        path = hash.slice(1);

                    $c.find(".top-bar .item").removeClass("selected");
                    $c.find(".top-bar .item[tag="+hash[0]+"]").addClass("selected");

                    for (var v in App) {
                        if (v == hash[0]) {
                            if (App[v].show) App[v].show($c, path);
                        } else {
                            if (App[v].hide) App[v].hide($c, path);
                        }
                    }
                });

                if (!location.hash || location.hash == "#") {
                    if (isAdmin) location.hash = "admin";
                    else location.hash = "history";
                } else {
                    $(window).hashchange()
                }
                $c.show();
            });
        };
    return {
        authenticated: authenticated,
        authenticate: authenticate,
        logout: logout,
        start: start
    }
}()