var layer=layui.layer;

    /*! 消息组件实例 */
    $.msg = new function () {
        var self = this;
        this.shade = [0.02, '#000'];
        this.dialogIndexs = [];
        // 关闭消息框
        this.close = function (index) {
            return layer.close(index);
        };
        // 弹出警告消息框
        this.alert = function (msg, callback) {
            var index = layer.alert(msg, {end: callback, scrollbar: false});
            return this.dialogIndexs.push(index), index;
        };
        // 确认对话框
        this.confirm = function (msg, ok, no) {
            var index = layer.confirm(msg, {title: '操作确认', btn: ['确认', '取消']}, function () {
                typeof ok === 'function' && ok.call(this);
            }, function () {
                typeof no === 'function' && no.call(this);
                self.close(index);
            });
            return index;
        };
        // 显示成功类型的消息
        this.success = function (msg, time, callback) {
            var index = layer.msg(msg, {icon: 1, shade: this.shade, scrollbar: false, end: callback, time: (time || 2) * 1000, shadeClose: true});
            return this.dialogIndexs.push(index), index;
        };
        // 显示失败类型的消息
        this.error = function (msg, time, callback) {
            var index = layer.msg(msg, {icon: 2, shade: this.shade, scrollbar: false, time: (time || 3) * 1000, end: callback, shadeClose: true});
            return this.dialogIndexs.push(index), index;
        };
        // 状态消息提示
        this.tips = function (msg, time, callback) {
            var index = layer.msg(msg, {time: (time || 3) * 1000, shade: this.shade, end: callback, shadeClose: true});
            return this.dialogIndexs.push(index), index;
        };
        // 显示正在加载中的提示
        this.loading = function (msg, callback) {
            var index = msg ? layer.msg(msg, {icon: 16, scrollbar: false, shade: this.shade, time: 0, end: callback}) : layer.load(2, {time: 0, scrollbar: false, shade: this.shade, end: callback});
            return this.dialogIndexs.push(index), index;
        };
        // 自动处理显示返回的Json数据
        this.auto = function (data, time) {
            return (parseInt(data.code) === 1) ? self.success(data.msg, time, function () {
                !!data.url ? (window.location.href = data.url) : $.form.reload();
                for (var i in self.dialogIndexs) {
                    layer.close(self.dialogIndexs[i]);
                }
                self.dialogIndexs = [];
            }) : self.error(data.msg, 3, function () {
                !!data.url && (window.location.href = data.url);
            });
        };
    };


    /*! 表单自动化组件 */
    $.form = new function () {
        var self = this;
        // 默认异常提示消息
        this.errMsg = '{status}服务器繁忙，请稍候再试！';
        // 内容区域动态加载后初始化
        this.reInit = function ($container) {
            $.vali.listen(this), JPlaceHolder.init();
            $container.find('[required]').parent().prevAll('label').addClass('label-required');
        };
        // 在内容区显示视图
        this.show = function (html) {
            var $container = $('.framework-body').html(html);
            reinit.call(this), setTimeout(reinit, 500), setTimeout(reinit, 1000);

            function reinit() {
                $.form.reInit($container);
            }
        };
        // 以hash打开网页
        this.href = function (url, obj) {
            if (url !== '#') {
                window.location.href = '#' + $.menu.parseUri(url, obj);
            } else if (obj && obj.getAttribute('data-menu-node')) {
                var node = obj.getAttribute('data-menu-node');
                $('[data-menu-node^="' + node + '-"][data-open!="#"]:first').trigger('click');
            }
        };
        // 刷新当前页面
        this.reload = function () {
            window.onhashchange.call(this);
        };
        // 异步加载的数据
        this.load = function (url, data, type, callback, loading, tips, time) {
            var self = this, dialogIndex = 0;
            (loading !== false) && (dialogIndex = $.msg.loading(tips));
            (typeof Pace === 'object') && Pace.restart();
            $.ajax({
                type: type || 'GET', url: $.menu.parseUri(url), data: data || {},
                statusCode: {
                    404: function () {
                        $.msg.close(dialogIndex);
                        $.msg.tips(self.errMsg.replace('{status}', 'E404 - '));
                    },
                    500: function () {
                        $.msg.close(dialogIndex);
                        $.msg.tips(self.errMsg.replace('{status}', 'E500 - '));
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    $.msg.close(dialogIndex);
                    $.msg.tips(self.errMsg.replace('{status}', 'E' + textStatus + ' - '));
                },
                success: function (res) {
                    $.msg.close(dialogIndex);
                    if (typeof callback === 'function' && callback.call(self, res) === false) {
                        return false;
                    }
                    if (typeof (res) === 'object') {
                        return $.msg.auto(res, time || res.wait || undefined);
                    }
                    self.show(res);
                }
            });
        };
        // 加载HTML到目标位置
        this.open = function (url, data, callback, loading, tips) {
            this.load(url, data, 'get', function (res) {
                if (typeof (res) === 'object') {
                    return $.msg.auto(res);
                }
                self.show(res);
            }, loading, tips);
        };
        // 打开一个iframe窗口
        this.iframe = function (url, title) {
            return layer.open({title: title || '窗口', type: 2, area: ['800px', '530px'], fix: true, maxmin: false, content: url});
        };
        // 加载HTML到弹出层
        this.modal = function (url, data, title, callback, loading, tips) {
            this.load(url, data, 'GET', function (res) {
                if (typeof (res) === 'object') {
                    return $.msg.auto(res);
                }
                var layerIndex = layer.open({
                    type: 1, btn: false, area: "800px", content: res, title: title || '', success: function (dom, index) {
                        $(dom).find('[data-close]').off('click').on('click', function () {
                            if ($(this).attr('data-confirm')) {
                                var confirmIndex = $.msg.confirm($(this).attr('data-confirm'), function () {
                                    layer.close(confirmIndex), layer.close(index);
                                });
                                return false;
                            }
                            layer.close(index);
                        });
                        $.form.reInit($(dom));
                    }
                });
                $.msg.dialogIndexs.push(layerIndex);
                return (typeof callback === 'function') && callback.call(this);
            }, loading, tips);
        };
    };



    /*! 后台菜单辅助插件 */
    $.menu = new function () {
        // 计算URL地址中有效的URI
        this.getUri = function (uri) {
            uri = uri || window.location.href;
            uri = (uri.indexOf(window.location.host) > -1 ? uri.split(window.location.host)[1] : uri).split('?')[0];
            return (uri.indexOf('#') !== -1 ? uri.split('#')[1] : uri);
        };
        // 通过URI查询最有可能的菜单NODE
        this.queryNode = function (url) {
            var node = location.href.replace(/.*spm=([\d\-m]+).*/ig, '$1');
            if (!/^m\-/.test(node)) {
                var $menu = $('[data-menu-node][data-open*="' + url.replace(/\.html$/ig, '') + '"]');
                return $menu.size() ? $menu.get(0).getAttribute('data-menu-node') : '';
            }
            return node;
        };
        // URL转URI
        this.parseUri = function (uri, obj) {
            var params = {};
            if (uri.indexOf('?') > -1) {
                var serach = uri.split('?')[1].split('&');
                for (var i in serach) {
                    if (serach[i].indexOf('=') > -1) {
                        var arr = serach[i].split('=');
                        try {
                            params[arr[0]] = window.decodeURIComponent(window.decodeURIComponent(arr[1].replace(/%2B/ig, ' ')));
                        } catch (e) {
                            console.log([e, uri, serach, arr]);
                        }
                    }
                }
            }
            uri = this.getUri(uri);
            params.spm = obj && obj.getAttribute('data-menu-node') || this.queryNode(uri);
            delete params[""];
            var query = '?' + $.param(params);
            return uri + (query !== '?' ? query : '');
        };
        // 后台菜单动作初始化
        this.listen = function () {
            var self = this;
            // 左则二级菜单展示
            $('[data-submenu-layout]>a').on('click', function () {
                $(this).parent().toggleClass('open');
                self.syncOpenStatus(1);
            });
            // 同步二级菜单展示状态
            this.syncOpenStatus = function (mode) {
                $('[data-submenu-layout]').map(function () {
                    var node = $(this).attr('data-submenu-layout');
                    if (mode === 1) {
                        var type = (this.className || '').indexOf('open') > -1 ? 2 : 1;
                        layui.data('menu', {key: node, value: type});
                    } else {
                        var type = layui.data('menu')[node] || 2;
                        (type === 2) && $(this).addClass('open');
                    }
                });
            };
            window.onhashchange = function () {
                var hash = window.location.hash || '';
                if (hash.length < 1) {
                    return $('[data-menu-node][data-open!="#"]:first').trigger('click');
                }
                $.form.load(hash);
                self.syncOpenStatus(2);
                // 菜单选择切换
                var node = self.queryNode(self.getUri());
                if (/^m\-/.test(node)) {
                    var $all = $('a[data-menu-node]'), tmp = node.split('-'), tmpNode = tmp.shift();
                    while (tmp.length > 0) {
                        tmpNode = tmpNode + '-' + tmp.shift();
                        $all = $all.not($('a[data-menu-node="' + tmpNode + '"]').addClass('active'));
                    }
                    $all.removeClass('active');
                    // 菜单模式切换
                    if (node.split('-').length > 2) {
                        var _tmp = node.split('-'), _node = _tmp.shift() + '-' + _tmp.shift();
                        $('[data-menu-layout]').not($('[data-menu-layout="' + _node + '"]').removeClass('hide')).addClass('hide');
                        $('[data-menu-node="' + node + '"]').parent('div').parent('div').addClass('open');
                        $('body.framework').removeClass('mini');
                    } else {
                        $('body.framework').addClass('mini');
                    }
                    self.syncOpenStatus(1);
                }
            };
            // URI初始化动作
            window.onhashchange.call(this);
        };
    };



$(function(){
	$('.cpwd').on('click',function(){
		$.form.modal(controller+'/change_pwd',{'id':uid},'编辑用户信息');
	})
})