(function(window, $) {
    if (typeof($) == 'undefined') {
        console.log("\u6c89\u68a6\u63d0\u793a\u60a8\uff1a\u5438\u9644\u63d0\u793a\u63d2\u4ef6\u521d\u59cb\u5316\u5931\u8d25\uff0c\u004a\u0051\u4f9d\u8d56\u672a\u627e\u5230\uff01\u0020");
        return false
    }

    function setTipStyle() {
        if ($('head').length > 0) {
            $('head').append('<style type="text/css">.tool_tip{background-color:rgba(0,0,0,.9);padding:4px 8px;border-radius:4px;color:#fff;font-size:12px;position:absolute;z-index:99999;max-width:100px;word-wrap:break-word}.tool_tip:before{position:absolute;content:\'\';background-color:transparent;width:0;height:0;border-width:5px;border-style:solid}.tool_tip_top:before{top:100%;left:50%;transform:translate(-50%,0);-ms-transform:translate(-50%,0);-webkit-transform:translate(-50%,0);border-color:rgba(0,0,0,.9) transparent transparent transparent}.tool_tip_right:before{top:50%;left:0;transform:translate(-100%,-50%);-ms-transform:translate(-100%,-50%);-webkit-transform:translate(-100%,-50%);border-color:transparent rgba(0,0,0,.9) transparent transparent}.tool_tip_bottom:before{top:0;left:50%;transform:translate(-50%,-100%);-ms-transform:translate(-50%,-100%);-webkit-transform:translate(-50%,-100%);border-color:transparent transparent rgba(0,0,0,.9) transparent}.tool_tip_left:before{top:50%;left:100%;transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);border-color:transparent transparent transparent rgba(0,0,0,.9)}</style>')
        } else {
            $('body').prepend('<style type="text/css">.tool_tip{background-color:rgba(0,0,0,.9);padding:4px 8px;border-radius:4px;color:#fff;font-size:12px;position:absolute;z-index:99999;max-width:100px;word-wrap:break-word}.tool_tip:before{position:absolute;content:\'\';background-color:transparent;width:0;height:0;border-width:5px;border-style:solid}.tool_tip_top:before{top:100%;left:50%;transform:translate(-50%,0);-ms-transform:translate(-50%,0);-webkit-transform:translate(-50%,0);border-color:rgba(0,0,0,.9) transparent transparent transparent}.tool_tip_right:before{top:50%;left:0;transform:translate(-100%,-50%);-ms-transform:translate(-100%,-50%);-webkit-transform:translate(-100%,-50%);border-color:transparent rgba(0,0,0,.9) transparent transparent}.tool_tip_bottom:before{top:0;left:50%;transform:translate(-50%,-100%);-ms-transform:translate(-50%,-100%);-webkit-transform:translate(-50%,-100%);border-color:transparent transparent rgba(0,0,0,.9) transparent}.tool_tip_left:before{top:50%;left:100%;transform:translate(0,-50%);-ms-transform:translate(0,-50%);-webkit-transform:translate(0,-50%);border-color:transparent transparent transparent rgba(0,0,0,.9)}</style>')
        }
    }

    function tooltip(ele, transitionObj, enterCallback, outCallback) {
        if (!ele || typeof ele !== "string") {
            console.error(new Error('The "tooltip" method requires the "class" of at least one parameter'));
            return
        }
        if (transitionObj && ({}).constructor.name === "Object") {
            var transition = transitionObj.transition || false,
                time = transitionObj.time || 200,
                timer = null
        }
        var els = document.querySelectorAll(ele),
            tipContent = document.createElement("div");
        Array.prototype.slice.call(els).forEach(function(el) {
            el.addEventListener("mouseenter", function() {
                var currenLeft = $(el).offset().left,
                    currenTop = $(el).offset().top,
                    currenWidth = el.offsetWidth,
                    currenHeight = el.offsetHeight,
                    context = el.getAttribute("data-tip"),
                    direction = el.getAttribute("data-direction") || "top";
                tipContentSetter(tipContent, context, direction);
                var tipContentWidth = tipContent.offsetWidth,
                    tipContentHeight = tipContent.offsetHeight;
                switch (direction) {
                    case "top":
                        tipContent.style.left = (currenLeft + currenWidth / 2 - tipContentWidth / 2) + "px";
                        tipContent.style.top = (currenTop - tipContentHeight - 7) + "px";
                        break;
                    case "left":
                        tipContent.style.left = (currenLeft - tipContentWidth - 7) + "px";
                        tipContent.style.top = (currenTop + currenHeight / 2 - tipContentHeight / 2) + "px";
                        break;
                    case "right":
                        tipContent.style.left = currenLeft + currenWidth + 7 + "px";
                        tipContent.style.top = currenTop + currenHeight / 2 - tipContentHeight / 2 + "px";
                        break;
                    case "bottom":
                        tipContent.style.left = currenLeft + currenWidth / 2 - tipContentWidth / 2 + "px";
                        tipContent.style.top = currenTop + currenHeight + 7 + "px"
                }
            }, false);
            deleteTipContent(el)
        });

        function deleteTipContent(el) {
            el.addEventListener("mouseleave", function() {
                setTimeout(function() {
                    var oldTipContent = document.querySelector(".tool_tip");
                    if (oldTipContent) {
                        if (transition === true) {
                            return opacityTransition(oldTipContent, "leave")
                        }
                        document.body.removeChild(oldTipContent);
                        typeof outCallback === "function" ? outCallback() : null
                    }
                }, 100)
            }, false)
        }

        function tipContentSetter(tipContent, context, direction) {
            tipContent.innerHTML = context;
            tipContent.className = "tool_tip tool_tip_" + direction;
            document.body.appendChild(tipContent);
            if (transition === true) {
                opacityTransition(tipContent, "enter");
                return
            }
            typeof enterCallback === "function" ? enterCallback() : null
        }

        function opacityTransition(ele, state) {
            timer && clearTimeout(timer);
            ele.style.setProperty("transition", "opacity " + time / 1000 + "s");
            ele.style.setProperty("-webkit-transition", "opacity " + time / 1000 + "s");
            if (state === "enter") {
                ele.style.opacity = 0;
                timer = setTimeout(function() {
                    ele.style.opacity = 1;
                    typeof enterCallback === "function" ? enterCallback() : null
                }, 0)
            } else {
                if (state === "leave") {
                    ele.style.opacity = 0;
                    typeof outCallback === "function" ? outCallback() : null;
                    timer = setTimeout(function() {
                        try {
                            document.body.removeChild(ele)
                        } catch (e) {}
                    }, time)
                }
            }
        }
    }

    function runTip() {
        var len = $('[data-tip]').length;
        tooltip('[data-tip]', {
            transition: true,
            time: 200
        }, null, null);
        console.log("\u5438\u9644\u63d0\u793a\u63d2\u4ef6\u5c01\u88c5\u0020\u0042\u0079\u0020\u6c89\u68a6\uff01\u5171" + len + "\u4e2a")
    }
    setTipStyle();
    window.runTip = runTip;
    window.tooltip = tooltip;
    $(document).ready(function() {
        runTip()
    })
})(window, typeof $ == 'object' ? $ : jQuery);