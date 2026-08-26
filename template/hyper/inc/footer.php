</div>
        <footer class="footer border-top border-light">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 text-center">
                        <?php echo date("Y"); ?> © <a href="./"><?php echo $conf['sitename']; ?></a> • <?php echo $conf['footer'] ?>
                    </div>
                    <!--div class="col-md-6">
                        <div class="text-md-right footer-links d-none d-md-block">
                            <a href="javascript: void(0);">About</a>
                            <a href="javascript: void(0);">Support</a>
                            <a href="javascript: void(0);">Contact Us</a>
                        </div>
                    </div-->
                </div>
            </div>
        </footer>
        </section>
        </div>
    </div>
</div>
<!-- END wrapper -->

<script src="<?php echo $cdnserver ?>assets/template/hyper/js/app.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/template/hyper/js/jquery.pjax.js"></script>
<script type="text/javascript">
var ui_tool=<?php echo $conf['ui_tool'] > 0 ? '1' : '0' ?>;
var tool_show=<?php echo $conf['tool_show'] > 0 ? '1' : '0' ?>;
var cartBuy=<?php echo $conf['shoppingcart'] > 0 ? '1' : '0' ?>;
var kf_qq='<?php echo $conf['kfqq'] ? $conf['kfqq'] : $conf['zzqq'] ?>';
var isLogin2=<?php echo $isLogin2 == 1 ? 'true' : 'false'; ?>;
var islogin=<?php echo $isLogin2 == 1 ? 'true' : 'false'; ?>;
</script>
<!-- <script src="<?php echo $cdnserver ?>assets/js/main.js?<?php echo VERSION ?>"></script> -->
<script src="<?php echo $cdnserver ?>assets/template/hyper/js/main.js?v=<?php echo VERSION; ?>"></script>
<script type="text/javascript">
var gotop = $("#top");
var mobileNav = $("#mobileNav").parent();
$(window).scroll(function () {
    if ($(window).scrollTop() > 288) {
        gotop.fadeIn(888);
    } else {
        gotop.fadeOut(588);
    }
    //导航
    function mobileNavs(){
        if ($(window).scrollTop() > 70) {
            mobileNav.css('position','fixed');
        } else {
            mobileNav.css({
                'position':'relative',
                'top':'0',
                'z-index':'888'
            });
        }
    }
});
gotop.click(function () {
    $('body,html').animate({
            scrollTop: 0
        }, 688);
18});

if(isModal == undefined) var isModal=false;
if(homepage == undefined) var homepage=false;
if(is_app == undefined) var is_app=false;
$(document).ready(function(){
    $(document).pjax('a:not(a[target=_blank],a[pjax=no])', '#pjax-container', {
        fragment:('#pjax-container'),
        timeout:5000,
        maxCacheLength:0,
        dataType:null,
    });
    $(document).on('submit', 'form', function(event) {
        $.pjax.submit(event, '#pjax-container',{
            fragment:'#pjax-container', timeout:5000
        });
    });
    $(document).on('pjax:send', function() {
        $(".loading").css("display", "block");
        //NProgress.start();
    });
    $(document).on('pjax:complete', function() {
        if(homepage == true){
            getcount();
        }
        $(".loading").css("display", "none");
        //NProgress.done();
    });
    <?php if (!$is_mobile): ?>
    $("a[_action]").removeClass('active');
    $("a[_action]").parents('li').removeClass('active');
    <?php endif?>
    $("a[_action=<?php echo $action; ?>]").parents('li').addClass("active");
    $("a[_action=<?php echo $action; ?>]").addClass("active");
    if($("a[_action=<?php echo $action; ?>]").parents('ul.collapse').length)$("a[_action=<?php echo $action; ?>]").parents('ul.collapse').addClass('in');
    $(document).on("click","a[_action]",function(){
        $("a[_action]").parents('li').removeClass('active');
        $("a[_action]").removeClass('active');
        $(this).parents('li').addClass('active');
        $(this).addClass('active');
        if(!$(this).parents('ul.collapse').length)$('ul.collapse').removeClass('in');
    });


});
</script>
<?php hook('footer_after');?>