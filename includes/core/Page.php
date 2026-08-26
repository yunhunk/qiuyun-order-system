<?php
// +----------------------------------------------------------------------
// | Author: 星河云Plus 
// +----------------------------------------------------------------------
// | Date: 2019/10/16
// +----------------------------------------------------------------------
namespace core;

class Page
{
    private $saveGet; //是否保留GET参数
    private $total; //总记录
    private $pageSize; //每页显示多少条
    private $limit; //limit
    private $pageBtn; //自定义页码按钮
    private $page; //当前页码
    private $pageNum; //总页码
    private $link; //Get参数
    private $url; //地址
    private $bothNum; //两边保持数字分页的量

    //构造方法初始化
    public function __construct($_total, $_pageSize, $_pageBtn = false, $_link = "", $_saveGet = true)
    {
        $this->saveGet  = $_saveGet;
        $this->total    = $_total > 0 ? $_total : 1;
        $this->pageSize = $_pageSize;
        $this->pageBtn  = $_pageBtn;
        $this->link     = $_link;
        $this->pageNum  = ceil($this->total / $this->pageSize);
        $this->page     = $this->setPage();
        $this->limit    = " LIMIT " . ($this->page - 1) * $this->pageSize . "," . $this->pageSize;
        $this->url      = $this->setUrl();
        $this->bothNum  = 1;
    }

    //拦截器
    public function __get($_key)
    {
        return $this->$_key;
    }

    //设置兼容样式
    private function setStyle()
    {
        return '
        <style>
        ul.pagination .pageSize{display:inline-block;float:left;margin-left: 5px;line-height: 28px;}
        ul.pagination .pageInput{float: left;}
        @media screen and (max-width: 767px){
            .pagination{
                font-size: 12px;
                font-size: 1.2rem;
            }
            ul.pagination>li{margin-bottom:6px;}

            .pageInput{max-width: 25px;margin-left: 5px;margin-top: 3px;}
            .pagination>li>a, .pagination>li>span {
                position: relative;
                float: left;
                padding: 4px 6px;
                margin-left: -1px;
                line-height: 1.42857143;
                color: #337ab7;
                text-decoration: none;
                background-color: #fff;
                border: 1px solid #ddd;
            }
        }

        @media screen and (min-width: 768px){
            .pagination{
                font-size: 14px;
                font-size: 1.4rem;
            }
            ul.pagination>li{margin-bottom:6px;}
            .pageSize{display:inline-block;float:left;margin-left: 5px;line-height: 220%}
            ul.pagination>li>input,.pageInput{display:inline-block;float:left;max-width: 80px;margin-left: 10px;}

        }
        </style>
        ';
    }

    //获取当前页码
    private function setPage()
    {
        if (!empty($_GET['page'])) {
            if ($_GET['page'] > 0) {
                if ($_GET['page'] > $this->pageNum) {
                    return $this->pageNum;
                } else {
                    return $_GET['page'];
                }
            } else {
                return 1;
            }
        } else {
            return 1;
        }
    }

    //获取地址
    private function setUrl()
    {
        $_url = $_SERVER["REQUEST_URI"];
        $_par = parse_url($_url);
        if ($this->saveGet && isset($_par['query'])) {
            parse_str($_par['query'], $_query);
            unset($_query['page']);
            $_url = $_par['path'] . '?' . http_build_query($_query) . '&';
        } else {
            $_url = $_par['path'] . '?';
        }
        return $_url;
    } //数字目录

    private function pageList()
    {
        $_pageList = null;
        for ($i = $this->bothNum; $i >= 1; $i--) {
            $_page = $this->page - $i;
            if ($_page < 1) {
                continue;
            }

            if ($this->pageBtn) {
                $_pageList .= '<li><a onclick="listTable(\'page=' . $_page . $this->link . '\')">' . $_page . '</a></li>';
            } else {
                $_pageList .= ' <li><a href="' . $this->url . 'page=' . $_page . '">' . $_page . '</a></li>';
            }
        }
        $_pageList .= ' <li class="active"><a >' . $this->page . '</a></li> ';
        for ($i = 1; $i <= $this->bothNum; $i++) {
            $_page = $this->page + $i;
            if ($_page > $this->pageNum) {
                break;
            }

            if ($this->pageBtn) {

                $_pageList .= '<li><a onclick="listTable(\'page=' . $_page . $this->link . '\')">' . $_page . '</a></li>';
            } else {
                $_pageList .= '<li><a href="' . $this->url . 'page=' . $_page . '">' . $_page . '</a></li>';
            }
        }
        return $_pageList;
    }

    //首页
    private function first()
    {
        if ($this->page == $this->bothNum + 2) {
            if ($this->pageBtn) {
                return '<li><a onclick="listTable(\'page=1' . $this->link . '\')">首页</a></li>';
            }
            return '<li><a href="' . $this->url . '">首页</a></li>';
        } elseif ($this->page > $this->bothNum + 2) {
            if ($this->pageBtn) {
                return '<li><a onclick="listTable(\'page=1' . $this->link . '\')">首页</a></li>';
            }
            return '<li><a href="' . $this->url . '">首页</a></li>';
        }
    }

    //上一页
    private function prev()
    {
        if ($this->page == 1) {
            return '<li class="disabled"><span>&laquo;</span></li>&nbsp;';
        }
        if ($this->pageBtn) {
            return '<li><a onclick="listTable(\'page=' . ($this->page - 1) . $this->link . '\')"><span><</span></a></li>&nbsp;';
        }
        return '<li><a href="' . $this->url . 'page=' . ($this->page - 1) . '"><span><</span></a></li>&nbsp;';
    }

    //下一页
    private function next()
    {

        if ($this->page == $this->pageNum) {
            return '&nbsp;<li class="disabled"><span>&raquo;</span></li>';
        }
        if ($this->pageBtn) {
            return '&nbsp;<li><a onclick="listTable(\'page=' . ($this->page + 1) . $this->link . '\')"><span>></span></a></li>';
        }
        return '&nbsp;<li><a href="' . $this->url . 'page=' . ($this->page + 1) . '"><span>></span></a></li>';
    }

    //尾页
    private function last()
    {
        if ($this->pageNum - $this->page == $this->bothNum + 1) {
            if ($this->pageBtn) {
                return '<li><a onclick="listTable(\'page=' . $this->pageNum . $this->link . '\')">尾页</a></li>';
            }
            return '<li><a href="' . $this->url . 'page=' . $this->pageNum . '">尾页</a></li>';
        } elseif ($this->pageNum - $this->page > $this->bothNum + 1) {
            if ($this->pageBtn) {
                return '<li><a onclick="listTable(\'page=' . $this->pageNum . $this->link . '\')">尾页</a></li>';
            }
            return '<li><a href="' . $this->url . 'page=' . $this->pageNum . '">尾页</a></li>';
        }
    }

    //跳页
    private function jump()
    {

        if ($this->pageBtn) {
            return '<li><font class="pageSize">共' . $this->pageNum . '页</font><input id="jumpPage" class="form-inline pageInput"/><font class="pageSize">页</font>&nbsp;&nbsp;<a style="margin-left: 4px;" onclick="var page=$(\'#jumpPage\').val();var maxpage=' . $this->pageNum . ';if(Number(page)>maxpage)return layer.alert(\'最多\'+maxpage+\'页\');if(Number(page)<1){return layer.alert(\'请输入正确的页码\')};listTable(\'page=\'+page+\'' . $this->link . '\')">跳转</a></li>';
        } else {
            return '<li><font class="pageSize">共' . $this->pageNum . '页</font><input id="jumpPage" class="form-inline pageInput"/><font class="pageSize">页</font>&nbsp;&nbsp;<a style="margin-left: 4px;" onclick="var page=$(\'#jumpPage\').val();var maxpage=' . $this->pageNum . ';if(Number(page)>maxpage)return layer.alert(\'最多\'+maxpage+\'页\');if(Number(page)<1){return layer.alert(\'请输入正确的页码\')};window.location.href=\'' . $this->url . 'page=\'+page+\'' . $this->link . '\'">跳转</a></li>';
        }
    }

    //分页信息
    public function showPage()
    {
        $_page = $this->setStyle();
        $_page .= '<ul class="pagination">';
        $_page .= $this->prev();
        $_page .= $this->first();
        $_page .= $this->pageList();
        $_page .= $this->last();
        $_page .= $this->next();
        $_page .= $this->jump();
        $_page .= '</ul>';
        return $_page;
    }
}
