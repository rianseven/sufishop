function getScript(e, t) {
    var i = document.createElement("script");
    i.src = e;
    var a = document.getElementsByTagName("head")[0],
        o = !1;
    i.onload = i.onreadystatechange = function() {
        o || this.readyState && "loaded" != this.readyState && "complete" != this.readyState || (o = !0, t(), i.onload = i.onreadystatechange = null, a.removeChild(i))
    }, a.appendChild(i)
}

function prepareCookies() {
    ! function(e) {
        e(jQuery)
    }(function(e) {
        function t(e) {
            return e
        }

        function i(e) {
            return decodeURIComponent(e.replace(o, " "))
        }

        function a(e) {
            0 === e.indexOf('"') && (e = e.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, "\\"));
            try {
                return r.json ? JSON.parse(e) : e
            } catch (t) {}
        }
        var o = /\+/g,
            r = e.nCookie = function(o, n, s) {
                if (void 0 !== n) {
                    if (s = e.extend({}, r.defaults, s), "number" == typeof s.expires) {
                        var d = s.expires,
                            p = s.expires = new Date;
                        p.setDate(p.getDate() + d)
                    }
                    return n = r.json ? JSON.stringify(n) : String(n), document.cookie = [r.raw ? o : encodeURIComponent(o), "=", r.raw ? n : encodeURIComponent(n), s.expires ? "; expires=" + s.expires.toUTCString() : "", s.path ? "; path=" + s.path : "", s.domain ? "; domain=" + s.domain : "", s.secure ? "; secure" : ""].join("")
                }
                for (var m = r.raw ? t : i, u = document.cookie.split("; "), c = o ? void 0 : {}, l = 0, h = u.length; h > l; l++) {
                    var f = u[l].split("="),
                        g = m(f.shift()),
                        y = m(f.join("="));
                    if (o && o === g) {
                        c = a(y);
                        break
                    }
                    o || (c[g] = a(y))
                }
                return c
            };
        r.defaults = {}, e.nRemoveCookie = function(t, i) {
            return void 0 !== e.nCookie(t) ? (e.nCookie(t, "", e.extend({}, i, {
                expires: -1
            })), !0) : !1
        }
    })
}

function isMobile(e) {
    return /(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(e) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(e.substr(0, 4)) ? !0 : !1
}

function getTimestamp(e) {
    var t = e.match(/\d+/g);
    return +new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5])
}

function alisaleem252Notify() {
    "false" != jQuery("meta[name='notify:enabled']").attr("content") && jQuery("document").ready(function() {
        prepareCookies();
        10 * Math.ceil((new Date).getTime() / 1e3 / 10);
        jQuery.ajax({
            type: "GET",
            url: cl_sales.folder + "file.json",
            async: true,
            jsonpCallback: "parsePurchases",
            dataType: "jsonp"
        })
    })
}

function parsePurchases(e) {
    return void 0 === jQuery.nCookie ? (prepareCookies(), setTimeout("alisaleem252Notify()", 1e3), !1) : (obj = e, jQuery("body").append('<div id="someone-purchased" class="customized"></div>'), viewed = void 0 === jQuery.nCookie("viewed442") || null === jQuery.nCookie("viewed442") ? new Array : jQuery.nCookie("viewed442").split(";"), void setTimeout("runDisplay()", 1000 + 1))
}

function runDisplay() {
    // var delay = 8000; = 2 sec
    // var delay = 10500; // = 5 sec
    // Custom timer goes here.
    // var aSec = 2050;
    var aSec = 1200;
    var customSec = 5;
    if(cl_sales.delay != null) {
      customSec = cl_sales.delay;
    }
    var delay = aSec*customSec;
    if (void 0 === jQuery.nCookie) return prepareCookies(), setTimeout("runDisplay()", delay-100), !1;
    if (maximum_per_page > page_count) {
        page_count++;
        var e = !1;
        // var customTimeOut = Math.floor((Math.random() * 10000) + 5000);
        var customTimeOut = delay;
        console.log('Time out: '+ customTimeOut);
        jQuery.each(obj, function(t, i) {

            jQuery.each(i, function(y, z) {
              console.log('Each object: ', z);
              return -1 === jQuery.inArray(z.id.toString(), viewed) ? (display(z.buyer, z.city, z.province, z.country, z.created_at, z.product_title, z.image, z.url), viewed.unshift(z.id.toString()), finish = !0, e = !0, !1) : void 0
            }), e ? (jQuery.nCookie("viewed442", viewed.join(";"), {
                expires: 7,
                path: "/"
            }), setTimeout("runDisplay()", customTimeOut )) : (last_id = 0, viewed = new Array, jQuery.nCookie("viewed442", "", {
                expires: 7,
                path: "/"
            }), runDisplay())
        });
    }
}

function display(buyer, e, t, i, a, o, r, n) {
    console.log(buyer, e, t, i, a, o, r, n);
    var s = Math.round((new Date).getTime() / 1e3);
    if (limit > s - a)
        if (jQuery.prototype.timeago) var a = "<small>" + jQuery.timeago(a.toString()) + "</small>";
        else var a = "";
    else var a = "";
    var d = '<a href="' + n + '">' + o + "</a>";
    var thaBuyer = '';
    var someone = cl_sales.translation[0];
    var thaCity = " {{ city }} ";
    if( cl_sales.display_name == 1 ) {
      thaBuyer = buyer + " " + cl_sales.translation[16];
      someone  = '';
    }
    var p = thaBuyer + someone + thaCity + cl_sales.translation[1] + "{{ product_with_link }} {{ time_ago }}";
    var m = p.replace("{{ city }}", e).replace("{{ province }}", t).replace("{{ country }}", i).replace("{{ time_ago }}", '').replace("{{ product_with_link }}", d).replace("{{ product }}", o);
    var m = m.replace("in , ", "");
    jQuery("#someone-purchased").css("bottom", 0).css("opacity", 0).html('<img src="' + r + '"><p>' + m + "</p>"), r || jQuery("#someone-purchased img").remove(), jQuery("#someone-purchased").show().animate({
        bottom: "20px",
        opacity: 1
    }, 1e3), setTimeout(function() {
        jQuery("#someone-purchased").show().animate({
            bottom: 0,
            opacity: 0
        }, 1e3)
    }, 5000)
}
"undefined" == typeof jQuery ? getScript("http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js", function() {
        jQuery.noConflict(), alisaleem252Notify()
    }) : alisaleem252Notify(),
    function(e) {
        e(jQuery)
    }(function(e) {
        function t() {
            var t = i(this),
                n = r.settings;
            return isNaN(t.datetime) || (0 == n.cutoff || o(t.datetime) < n.cutoff) && e(this).text(a(t.datetime)), this
        }

        function i(t) {
            if (t = e(t), !t.data("timeago")) {
                t.data("timeago", {
                    datetime: r.datetime(t)
                });
                var i = e.trim(t.text());
                r.settings.localeTitle ? t.attr("title", t.data("timeago").datetime.toLocaleString()) : !(i.length > 0) || r.isTime(t) && t.attr("title") || t.attr("title", i)
            }
            return t.data("timeago")
        }

        function a(e) {
            return r.inWords(o(e))
        }

        function o(e) {
            return (new Date).getTime() - e.getTime()
        }
        e.timeago = function(t) {
            return a(t instanceof Date ? t : "string" == typeof t ? e.timeago.parse(t) : "number" == typeof t ? new Date(t) : e.timeago.datetime(t))
        };
        var r = e.timeago;
        e.extend(e.timeago, {
            settings: {
                refreshMillis: 6e4,
                allowFuture: !1,
                localeTitle: !1,
                cutoff: 0,
                strings: {
                    prefixAgo: null,
                    prefixFromNow: null,
                    suffixAgo: cl_sales.translation[3],
                    suffixFromNow: null,
                    seconds: cl_sales.translation[5],
                    minute: cl_sales.translation[6],
                    minutes: "%d " + cl_sales.translation[7],
                    hour: cl_sales.translation[8],
                    hours: cl_sales.translation[9] + " %d " + cl_sales.translation[10],
                    day: cl_sales.translation[11],
                    days: "%d " + cl_sales.translation[12],
                    month: cl_sales.translation[13],
                    months: "%d " + cl_sales.translation[14],
                    year: cl_sales.translation[15],
                    years: "%d " + cl_sales.translation[16],
                    wordSeparator: " ",
                    numbers: []
                }
            },
            inWords: function(t) {
                function i(i, o) {
                    var r = e.isFunction(i) ? i(o, t) : i,
                        n = a.numbers && a.numbers[o] || o;
                    return r.replace(/%d/i, n)
                }
                var a = this.settings.strings,
                    o = '',
                    r = a.suffixAgo;
                var n = Math.abs(t) / 1e3,
                    s = n / 60,
                    d = s / 60,
                    p = d / 24,
                    m = p / 365,
                    u = 45 > n && i(a.seconds, Math.round(n)) || 90 > n && i(a.minute, 1) || 45 > s && i(a.minutes, Math.round(s)) || 90 > s && i(a.hour, 1) || 24 > d && i(a.hours, Math.round(d)) || 42 > d && i(a.day, 1) || 30 > p && i(a.days, Math.round(p)) || 45 > p && i(a.month, 1) || 365 > p && i(a.months, Math.round(p / 30)) || 1.5 > m && i(a.year, 1) || i(a.years, Math.round(m)),
                    c = a.wordSeparator || "";
                return void 0 === a.wordSeparator && (c = " "), e.trim([u, r].join(c))
            },
            parse: function(t) {
                if (t - 0 == t && t.length > 0) {
                    var i = new Date(t);
                    if (isNaN(i.getTime())) var i = new Date(1e3 * t);
                    return i
                }
                var i = e.trim(t);
                return i = i.replace(/-/, "/").replace(/-/, "/"), i = i.replace(/T/, " ").replace(/Z/, " UTC"), i = i.replace(/([\+-]\d\d)\:?(\d\d)/, " $1$2"), new Date(i)
            },
            datetime: function(t) {
                var i = r.isTime(t) ? e(t).attr("datetime") : e(t).attr("title");
                return r.parse(i)
            },
            isTime: function(t) {
                return "time" === e(t).get(0).tagName.toLowerCase()
            }
        });
        var n = {
            init: function() {
                var i = e.proxy(t, this);
                i();
                var a = r.settings;
                a.refreshMillis > 0 && setInterval(i, a.refreshMillis)
            },
            update: function(i) {
                e(this).data("timeago", {
                    datetime: r.parse(i)
                }), t.apply(this)
            },
            updateFromDOM: function() {
                e(this).data("timeago", {
                    datetime: r.parse(r.isTime(this) ? e(this).attr("datetime") : e(this).attr("title"))
                }), t.apply(this)
            }
        };
        e.fn.timeago = function(e, t) {
            var i = e ? n[e] : n.init;
            if (!i) throw new Error("Unknown function name '" + e + "' for timeago");
            return this.each(function() {
                i.call(this, t)
            }), this
        }, document.createElement("abbr"), document.createElement("time")
    });
var period_int = 3,
    period = 1,
    t = 1,
    purchases = [],
    viewed = new Array,
    checkForNewOrdersEvery = 15e3,
    obj, last_id = 0;
2 == period_int && (t = 60), 3 == period_int && (t = 3600), 4 == period_int && (t = 86400);
var limit = period * t,
    maximum_per_page = 30,
    page_count = 0,
    css = "#someone-purchased{background:#fff;border:0;border-radius:0;bottom:20px;display:none;left:20px;padding:0;position:fixed;text-align:left;width:auto;z-index:99999;font-family:Arial,sans-serif;-webkit-box-shadow:0 0 4px 0 rgba(0,0,0,.4);-moz-box-shadow:0 0 4px 0 rgba(0,0,0,.4);box-shadow:0 0 4px 0 rgba(0,0,0,.4);margin-right:20px;}#someone-purchased img{float:left;margin-right:13px;max-height:80px;width:auto}#someone-purchased p{color:#000;float:left;font-size:13px;margin:0;width:auto;padding:12px 12px 0 0;line-height:20px}#someone-purchased p a{color:#000;display:block;font-size:15px;font-weight:700}#someone-purchased p a:hover{color:#000}#someone-purchased p small{display:block;font-size:10px}",
    head = document.getElementsByTagName("head")[0],
    style = document.createElement("style");
style.type = "text/css", style.styleSheet ? style.styleSheet.cssText = css : style.appendChild(document.createTextNode(css)), head.appendChild(style)
