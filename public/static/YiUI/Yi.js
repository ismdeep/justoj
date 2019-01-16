/** Yi is Master Yi, very fast. ***/
(function(window,document) {
    var w = window,
        doc = document;
    var MasterYi = function(selector) {
        return new DMasterYioo.prototype.init(selector);
    }
    MasterYi.prototype = {
        constructor : MasterYi,
        length : 0,
        splice: [].splice,
        selector : '',
        init : function(selector) {//dom选择的一些判断

        }
    }
    MasterYi.prototype.init.prototype = MasterYi.prototype;

    MasterYi.ajax = function() { //直接挂载方法  可o.ajax调用
        console.log(this);
    }

    MasterYi.show = function(str){
      console.log(str);
    }

    MasterYi.randomString = function (len) {
      len = len || 32;
      var $chars = '0123456789';
      var maxPos = $chars.length;
      var pwd = '';
      for (i = 0; i < len; i++) {
          pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
      }
      return pwd;
    }

    MasterYi.showLoading = function (content) {
        var id = Yi.randomString(6);
        var loadingItem = document.createElement('div');
        loadingItem.id = 'loadingToast-' + id;
        loadingItem.innerHTML = '<div class="yiui-mask_transparent"></div><div class="yiui-toast"><i class="yiui-loading yiui-icon_toast"></i><p class="yiui-toast__content">'+content+'</p></div>';
        document.body.appendChild(loadingItem);
        return loadingItem.id;
    }

    MasterYi.closeLoading = function (loading_id) {
        document.body.removeChild(document.getElementById(loading_id));
    }

    // var loading_id = Yi.showLoading();
    // setTimeout(function () {
    //     Yi.closeLoading(loading_id);
    // }, 500);

    window.Yi = MasterYi;
})(window,document);
