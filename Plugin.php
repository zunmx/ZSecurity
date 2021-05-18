<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 自用的文章版权添加以及防调试能力，并且引入了一个鼠标小特效。
 *
 * @package ZSecurity
 * @author Zunmx
 * @version 1.0.0
 * @link https://www.zunmx.top
 */
class ZSecurity_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {

        Typecho_Plugin::factory('admin/menu.php')->navBar = array('ZSecurity_Plugin', 'render');
        Typecho_Plugin::factory('Widget_Archive')->header = array(__CLASS__, 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'footer');

    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $defaultAntiDev = <<<EOF
function devToolNotice() {
    $.message({
        title: "检测到异常行为或指令",
        message: "建站不易，趴站可耻。感谢配合。鞠躬",
        type: 'error',
        time: '3000'
    });
}// notice窗
setInterval(function () { var t1 = new Date().getTime(); debugger; var t2 = new Date().getTime(); if (t2 - t1 > 1) { devToolNotice() }console.clear(); }, 520); // 叼毛，不要调试我。
let element = new Image();
Object.defineProperty(element, 'id', function () {
    devToolNotice();
}) // 开发者工具遍历元素提示
document.onkeydown = function () {
    if ((event.ctrlKey || event.metaKey) && event.keyCode === 73) {
        event.preventDefault();
        devToolNotice();
        return false;
    }
    if (window.event && window.event.keyCode == 123) {
        event.keyCode = 0;
        event.returnValue = false;
        devToolNotice();
        return false;
    }
};// 键盘事件，你要是改我也没办法。
window.oncontextmenu = function (e) {
try{
    emojiMouse(e)
}catch{
}
return false;
} // 右键特效，并且屏蔽右键
EOF;

        /** 分类名称 */
        $name = new Typecho_Widget_Helper_Form_Element_Radio('tip_switch', array(0 => _t('不显示'), 1 => _t('显示')), 1, _t('管理页面是否显示顶部提示'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Text('word', NULL, '[√] ZSecurity', _t('顶部提示'));
        $form->addInput($name);

        $name = new Typecho_Widget_Helper_Form_Element_Radio('antiDebug_switch', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('简单阻止开发者工具'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Textarea('antiDevtool', NULL, $defaultAntiDev, _t('脚本内容'));
        $form->addInput($name);

        $name = new Typecho_Widget_Helper_Form_Element_Radio('clickStyle', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('鼠标点击特效'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Radio('grayStyle', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('公祭日页面灰度'));
        $form->addInput($name);

        $name = new Typecho_Widget_Helper_Form_Element_Radio('copyPlus', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('页面复制加版权信息'));
        $form->addInput($name);

    }


    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {

    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
        $myself = Helper::options()->plugin('ZSecurity');
        if ($myself->tip_switch == "1")  // 标识
            echo '<a class="message warning" href="';
        Helper::options()->adminUrl();
        echo 'options-plugin.php?config=ZSecurity'
            . '">'
            . htmlspecialchars(Typecho_Widget::widget('Widget_Options')->plugin('ZSecurity')->word)
            . '</a>';
    }

    public static function header()
    {
        $myself = Helper::options()->plugin('ZSecurity');
        if ($myself->antiDebug_switch == "1") {  // 禁止调试

            echo '<script>'
                . $myself->antiDevtool
                . '</script>';
        }
        /*********************/
        $myself = Helper::options()->plugin('ZSecurity');
        if ($myself->clickStyle == "1") { // 鼠标样式
            echo <<<EOF
<script>
var a = new Array("🙂", "🙋‍", "😀", "😃", "😄", "😁", "😆", "😅", "🤣", "😂", "🙂", "🙃", "😉", "😊", "😇", "🥰", "😍", "🤩", "😘", "😗", "😚", "😙", "😋", "😛", "😜", "🤪", "😝", "🤑", "🤗", "🤭", "🤔", "🤐", "🤨", "😐", "😑", "😶", "😏", "😒", "🙄", "😬", "🤥", "😌", "😔", "😪", "🤤", "😴", "😷", "🤒", "🤕", "🤢", "🤮", "🤧", "🥵", "🥶", "🥴", "😵", "🤯", "🤠", "🥳", "😎", "🤓", "🧐", "😕", "😟", "🙁", "☹️", "😮", "😯", "😲", "😳", "🥺", "😦", "😧", "😨", "😰", "😥", "😢", "😭", "😱", "😖", "😣", "😞", "😓", "😩", "😫", "🥱", "😤", "😡", "😠", "🤬", "💋", "💍", "🌈", "👽", "💘", "💓", "💔", "💕", "💖", "💗", "💙", "💚", "💛", "💜", "💝", "💞", "💟");
    function emojiMouse(e) {
        var a_idx = parseInt((Math.random() * 1000)) % a.length;
        var sp = $("<span/>").text(a[a_idx]);
        var x = e.pageX, y = e.pageY;
        sp.css({ "z-index": 9999999999999, "top": y - 20, "left": x, "position": "absolute", "font-weight": "bold", "color": "#ff6651" });
        $("body").append(sp);
        sp.animate({ "top": y - 100, "opacity": 0 }, 1000, function () {
            sp.remove();
        });
    } // 鼠标特效主入口

        document.onclick=(function (e) {
            emojiMouse(e)
        });
 //启动事件，鼠标特效
</script>

EOF;
        }
    }

    public static function footer()
    {
        $myself = Helper::options()->plugin('ZSecurity');
        if ($myself->copyPlus == "1") { // 复制版权
            echo "<script>" . <<<EOF

$(function() {
  document.body.addEventListener('copy', function (e) {
    if (window.getSelection().toString() && window.getSelection().toString().length > 10) {
        setClipboardText(e);
    }
}); 
})
function setClipboardText(event) {
    var clipboardData = event.clipboardData || window.clipboardData;
    if (clipboardData) {
        event.preventDefault();
        var htmlData = '著作权归作者所有。<br/>商业转载请联系作者获得授权，非商业转载请注明出处。<br/>作者：zmx<br/>链接：' + window.location.href + '<br/>来源：https://www.zunmx.top/<br/><br/>'+ window.getSelection().toString();
        clipboardData.setData('text/plain',htmlData.replaceAll("<br/>","\\r\\n"));
    }
}
EOF
                . "</script>";
        }
        /*********************/
        if ($myself->grayStyle == "1") { // 公祭日
            echo <<<EOF
<script>
            $(function(){
                var flag = false;
            var dt = new Date();
            var dt2 = dt.getMonth() + 1 + "" + dt.getDate()
            if (dt2 == "918" || dt2 == "1213" ) {flag = true;}

            if (flag) {
                $("html").css({
                    "filter": "gray !important",
                    "filter": "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)",
                    "filter": "grayscale(100%)",
                    "-webkit-filter": "grayscale(100%)",
                    "-moz-filter": "grayscale(100%)",
                    "-ms-filter": "grayscale(100%)",
                    "-o-filter": "grayscale(100%)"
                });
            }
            })
</script>
EOF;
        }
    }


}

