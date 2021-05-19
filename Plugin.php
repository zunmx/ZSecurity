<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 自用的文章版权添加以及防调试能力，并且添加了多种特效。
 *
 * @package ZSecurity
 * @author Zunmx
 * @version 1.0.1
 * @link https://www.zunmx.top
 */
class ZSecurity_Plugin implements Typecho_Plugin_Interface
{
    const STATIC_DIR = '/usr/plugins/ZSecurity/static';

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
<script>
setInterval(function () { 
$("html").append("<scr"+"ipt>function devToolNotice() { try{antiDebug_Clear();}catch{}   $.message({        title: '检测到异常行为或指令',        message: '建站不易，趴站可耻。感谢配合。鞠躬',        type: 'error',        time: '3000'    });}</scr"+"ipt>")
var t1 = new Date().getTime(); debugger; var t2 = new Date().getTime(); if ((t2 - t1 > 1)||(window.outerWidth - window.innerWidth > 160 )||  (window.outerHeight - window.innerHeight > 160)) { devToolNotice() }console.clear(); }, 520); // 叼毛，不要调试我。  
 

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
    if (window.console && window.console.log) {
    devToolNotice();
    }
    
</script>
EOF;

        /** 分类名称 */
        $form->addInput(new My_Title('btnTitle', NULL, NULL, _t('插件设置'), NULL));
        $name = new Typecho_Widget_Helper_Form_Element_Radio('tip_switch', array(0 => _t('不显示'), 1 => _t('显示')), 1, _t('管理页面是否显示顶部提示 <span style="color:blue;font-weight:bold;">点击跳转到设置页面</span>'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Text('word', NULL, '[√] ZSecurity', _t('顶部提示'));
        $form->addInput($name);

        $form->addInput(new My_Title('btnTitle', NULL, NULL, _t('反盗版'), NULL));
        $name = new Typecho_Widget_Helper_Form_Element_Radio('antiDebug_switch', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('简单阻止开发者工具 <span style="color:red;font-weight:bold;"> 这里的js不进行转义</span>'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Radio('antiDebug_Clear', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('当发现开发者工具进行页面清空 <span style="color:red;font-weight:bold;">不建议开启，可能存在误判</span>'));
        $form->addInput($name);

        $name = new Typecho_Widget_Helper_Form_Element_Textarea('antiDevtool', NULL, $defaultAntiDev, _t('脚本内容'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Radio('copyPlus', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('页面复制加版权信息'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Text('copyText',NULL, "尊暮萧", _t('版权作者'));
        $form->addInput($name);

        $form->addInput(new My_Title('btnTitle', NULL, NULL, _t('外观设置'), NULL));
        $name = new Typecho_Widget_Helper_Form_Element_Radio('clickStyle', array(0 => _t('禁用'), 1 => _t('emoji') ,2 =>'爆炸气泡'), 1, _t('鼠标点击特效 <span style="color:red;font-weight:bold;">爆炸特效不建议开启</span>'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Radio('grayStyle', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('公祭日页面灰度'));
        $form->addInput($name);
        $name = new Typecho_Widget_Helper_Form_Element_Radio('commentStyle', array(0 => _t('禁用'), 1 => _t('启动')), 1, _t('评论框打字特效'));
        $form->addInput($name);
        // 鼠标样式
        $dir = self::STATIC_DIR . '/image';
        $options = [
            'none' => _t('默认'),
            'dew' => "<img src='{$dir}/dew/normal.cur'><img src='{$dir}/dew/link.cur'>",
            'sketch' => "<img src='{$dir}/sketch/normal.cur'><img src='{$dir}/sketch/link.cur'>",
            'black' => "<img src='{$dir}/black/normal.cur'><img src='{$dir}/black/link.cur'>",
            'star' => "<img src='{$dir}/star/normal.cur'><img src='{$dir}/star/link.cur'>",
        ];
        $bubbleType = new Typecho_Widget_Helper_Form_Element_Radio('mouseType', $options, 'dew', _t('鼠标样式'));
        $form->addInput($bubbleType);





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

            echo $myself->antiDevtool ;
            if($myself->antiDebug_Clear == "1"){
                echo '<script>'.<<<EOF
        function antiDebug_Clear(){
            $("html").html("");
        }
EOF;
                echo "</script>";
            }
        }
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
        if ($myself->clickStyle == "2") { // 鼠标样式
            echo <<<EOF
<canvas id="fireworks" style="position:fixed;left:0;top:0;pointer-events:none;z-index: 999999"></canvas>
EOF;
            echo "<script type='text/javascript' src='".self::STATIC_DIR."/js/anime2.2.0.min.js'></script>";
            echo "<script type='text/javascript' src='".self::STATIC_DIR."/js/fireworks.js'></script>";

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
        var htmlData = '著作权归作者所有。<br/>商业转载请联系作者获得授权，非商业转载请注明出处。<br/>作者：$myself->copyText<br/>链接：' + window.location.href + '<br/>来源：'+window.location.host+'/<br/><br/>'+ window.getSelection().toString();
        clipboardData.setData('text/plain',htmlData.replaceAll("<br/>","\\r\\n"));
    }
}
EOF
                . "</script>";
        }

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
        if ($myself->commentStyle == "1") {
            echo <<<EOF
<script>
(function webpackUniversalModuleDefinition(a,b){if(typeof exports==="object"&&typeof module==="object"){module.exports=b()}else{if(typeof define==="function"&&define.amd){define([],b)}else{if(typeof exports==="object"){exports["POWERMODE"]=b()}else{a["POWERMODE"]=b()}}}})(this,function(){return(function(a){var b={};function c(e){if(b[e]){return b[e].exports}var d=b[e]={exports:{},id:e,loaded:false};a[e].call(d.exports,d,d.exports,c);d.loaded=true;return d.exports}c.m=a;c.c=b;c.p="";return c(0)})([function(c,g,b){var d=document.createElement("canvas");d.width=window.innerWidth;d.height=window.innerHeight;d.style.cssText="position:fixed;top:0;left:0;pointer-events:none;z-index:999999";window.addEventListener("resize",function(){d.width=window.innerWidth;d.height=window.innerHeight});document.body.appendChild(d);var a=d.getContext("2d");var n=[];var j=0;var k=120;var f=k;var p=false;o.shake=true;function l(r,q){return Math.random()*(q-r)+r}function m(r){if(o.colorful){var q=l(0,360);return"hsla("+l(q-10,q+10)+", 100%, "+l(50,80)+"%, "+1+")"}else{return window.getComputedStyle(r).color}}function e(){var t=document.activeElement;var v;if(t.tagName==="TEXTAREA"||(t.tagName==="INPUT"&&t.getAttribute("type")==="text")){var u=b(1)(t,t.selectionStart);v=t.getBoundingClientRect();return{x:u.left+v.left,y:u.top+v.top,color:m(t)}}var s=window.getSelection();if(s.rangeCount){var q=s.getRangeAt(0);var r=q.startContainer;if(r.nodeType===document.TEXT_NODE){r=r.parentNode}v=q.getBoundingClientRect();return{x:v.left,y:v.top,color:m(r)}}return{x:0,y:0,color:"transparent"}}function h(q,s,r){return{x:q,y:s,alpha:1,color:r,velocity:{x:-1+Math.random()*2,y:-3.5+Math.random()*2}}}function o(){var t=e();var s=5+Math.round(Math.random()*10);while(s--){n[j]=h(t.x,t.y,t.color);j=(j+1)%500}f=k;if(!p){requestAnimationFrame(i)}if(o.shake){var r=1+2*Math.random();var q=r*(Math.random()>0.5?-1:1);var u=r*(Math.random()>0.5?-1:1);document.body.style.marginLeft=q+"px";document.body.style.marginTop=u+"px";setTimeout(function(){document.body.style.marginLeft="";document.body.style.marginTop=""},75)}}o.colorful=false;function i(){if(f>0){requestAnimationFrame(i);f--;p=true}else{p=false}a.clearRect(0,0,d.width,d.height);for(var q=0;q<n.length;++q){var r=n[q];if(r.alpha<=0.1){continue}r.velocity.y+=0.075;r.x+=r.velocity.x;r.y+=r.velocity.y;r.alpha*=0.96;a.globalAlpha=r.alpha;a.fillStyle=r.color;a.fillRect(Math.round(r.x-1.5),Math.round(r.y-1.5),3,3)}}requestAnimationFrame(i);c.exports=o},function(b,a){(function(){var d=["direction","boxSizing","width","height","overflowX","overflowY","borderTopWidth","borderRightWidth","borderBottomWidth","borderLeftWidth","borderStyle","paddingTop","paddingRight","paddingBottom","paddingLeft","fontStyle","fontVariant","fontWeight","fontStretch","fontSize","fontSizeAdjust","lineHeight","fontFamily","textAlign","textTransform","textIndent","textDecoration","letterSpacing","wordSpacing","tabSize","MozTabSize"];var e=window.mozInnerScreenX!=null;function c(k,l,o){var h=o&&o.debug||false;if(h){var i=document.querySelector("#input-textarea-caret-position-mirror-div");if(i){i.parentNode.removeChild(i)}}var f=document.createElement("div");f.id="input-textarea-caret-position-mirror-div";document.body.appendChild(f);var g=f.style;var j=window.getComputedStyle?getComputedStyle(k):k.currentStyle;g.whiteSpace="pre-wrap";if(k.nodeName!=="INPUT"){g.wordWrap="break-word"}g.position="absolute";if(!h){g.visibility="hidden"}d.forEach(function(p){g[p]=j[p]});if(e){if(k.scrollHeight>parseInt(j.height)){g.overflowY="scroll"}}else{g.overflow="hidden"}f.textContent=k.value.substring(0,l);if(k.nodeName==="INPUT"){f.textContent=f.textContent.replace(/\s/g,"\u00a0")}var n=document.createElement("span");n.textContent=k.value.substring(l)||".";f.appendChild(n);var m={top:n.offsetTop+parseInt(j["borderTopWidth"]),left:n.offsetLeft+parseInt(j["borderLeftWidth"])};if(h){n.style.backgroundColor="#aaa"}else{document.body.removeChild(f)}return m}if(typeof b!="undefined"&&typeof b.exports!="undefined"){b.exports=c}else{window.getCaretCoordinates=c}}())}])});
POWERMODE.colorful=true;POWERMODE.shake=false;document.body.addEventListener("input",POWERMODE);
</script>
EOF;

        }

        $mouseType = $myself->mouseType;
        $imageDir = self::STATIC_DIR . '/image';
        if ($mouseType != 'none') {
            echo <<<EOF
<script>
$("body").css("cursor", "url('{$imageDir}/{$mouseType}/normal.cur'), default");
$("a").css("cursor", "url('{$imageDir}/{$mouseType}/link.cur'), pointer");
</script>;
EOF;
        }
    }



}
class My_Title extends Typecho_Widget_Helper_Form_Element
{

    public function label($value)
    {
        /** 创建标题元素 */
        if (empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', array('class' => 'typecho-label', 'style' => 'font-size: 1.5em;border-bottom: 1px #ddd solid;padding-top:1em;'));
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }

    public function input($name = NULL, array $options = NULL)
    {
        $input = new Typecho_Widget_Helper_Layout('p', array());
        $this->container($input);
        $this->inputs[] = $input;
        return $input;
    }

    protected function _value($value)
    {
    }


}

