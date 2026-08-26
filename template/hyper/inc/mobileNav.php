<ul class="nav nav-pills nav-justified form-wizard-header mb-2" id="mobileNav">
    <li class="nav-item">
        <a href="./" _action="index" class="nav-link rounded-0 pt-2 pb-2"> 
            <i class="fas fa-home mr-1"></i>
            <span>首页</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="./?mod=query" _action="query" class="nav-link rounded-0 pt-2 pb-2">
            <i class="fas fa-search mr-1"></i>
            <span>查单</span>
        </a>
    </li>
    <li class="nav-item">
        <a href="./?mod=more" _action="more" class="nav-link rounded-0 pt-2 pb-2">
            <span>更多</span>
            <i class="fas fa-angle-double-right mr-1"></i>
        </a>
    </li>
</ul>
<script type="text/javascript">
    $(function(){
    <?php if($action == 'index' || $action == 'query'): ?>
        $("a[_action=<?php echo $action; ?>]").addClass("active");
    <?php else: ?>
        $("a[_action=more]").addClass("active");
    <?php endif; ?>
    })
</script>
<?php
//加载插件代码
hook('header');
?>