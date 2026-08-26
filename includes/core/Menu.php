<?php
namespace core;

class Menu
{
    private $options;
    private $html = '';
    /**
     * 构造函数 初始化菜单
     */
    public function __construct()
    {
        global $DB, $isLogin;
        if ($isLogin === 1) {
            $this->options = [];
            $rs            = $DB->select("SELECT * FROM `pre_menu` where `upid`=0 AND `status`=1 ORDER BY `sort` ASC,`id` ASC");
            if (is_array($rs)) {
                foreach ($rs as $key => $row) {
                    $item = [
                        'id'      => $row['id'],
                        'name'    => $row['name'],
                        'icon'    => $row['icon'],
                        'iconnav' => $row['iconnav'],
                        'wherein' => $row['wherein'],
                        'url'     => $row['url'],
                        'label'   => $row['label'],
                        'sublist' => [],
                    ];
                    $list = $DB->select("SELECT * FROM `pre_menu` where `upid`=? AND `status`=1 ORDER BY sort ASC,id ASC", [$row['id']]);
                    if (is_array($list)) {
                        $item['sublist'] = $list;
                    }
                    $this->options[$key] = $item;
                }
            }
        }
        return true;
    }

    /**
     * 菜单排序
     * @param  integer $id   菜单ID
     * @param  integer $type 排序类型
     */
    public function sort($id = 0, $type = 0)
    {
        global $DB;
        $row  = $DB->get_row("SELECT * FROM `pre_menu` where `id`=:id", [':id' => $id]);
        $sort = $row['sort'];
        if (!$row) {
            $result = ['code' => -1, 'msg' => '当前记录不存在！', 'data' => []];
        } else {
            if ($type == 0) {
                //到顶部
                $row2 = $DB->get_row("SELECT * FROM `pre_menu` where `fixed`=0 ORDER BY `sort` ASC LIMIT 1");
                $sql1 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row2['sort'] . "' where `id`='{$id}'");
                $sql2 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row['sort'] . "' where `id`='" . $row2['id'] . "'");
            } elseif ($type == 1) {
                //上一行
                $row2 = $DB->get_row("SELECT * FROM `pre_menu` where `fixed`=0 AND `sort`<{$sort} ORDER BY `sort` DESC LIMIT 1");
                $sql1 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row2['sort'] . "' where `id`='{$id}'");
                $sql2 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row['sort'] . "' where `id`='" . $row2['id'] . "'");
            } elseif ($type == 2) {
                //下一行
                $row2 = $DB->get_row("SELECT * FROM `pre_menu` where `fixed`=0 AND `sort`>{$sort} ORDER BY `sort` ASC LIMIT 1");
                $sql1 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row2['sort'] . "' where `id`='{$id}'");
                $sql2 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row['sort'] . "' where `id`='" . $row2['id'] . "'");
            } else {
                //到底部
                $row2 = $DB->get_row("SELECT * FROM `pre_menu` where `fixed`=0 ORDER BY `sort` DESC LIMIT 1");
                $sql1 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row2['sort'] . "' where `id`='{$id}'");
                $sql2 = $DB->query("UPDATE `pre_menu` SET `sort`='" . $row['sort'] . "' where `id`='" . $row2['id'] . "'");
            }
            if ($sql1 && $sql2) {
                $result = ['code' => 0, 'msg' => '操作成功~'];
            } else {
                $result = ['code' => -1, 'msg' => '操作失败，' . $DB->error(), 'data' => []];
            }
        }
        return $result;
    }

    private function getAllWherein($sublist = [])
    {
        $wherein = '';
        foreach ($sublist as $key => $value) {
            $wherein .= $value['wherein'] . ',';
        }
        return trim($wherein, ',');
    }

    /**
     * 输出菜单列表
     */
    public function showList()
    {
        $this->html = '<ul class="nav">';

        foreach ($this->options as $key => $item) {
            if ($item['url'] == 'auto' && count($item['sublist']) > 0) {
                //处理带子菜单的
                $wherein = $this->getAllWherein($item['sublist']);
                $navHtml = '
                        <li class="' . checkIfActive($wherein) . '" id="li_' . $item['id'] . '">
                            <a href class="auto" id="a_' . $item['id'] . '">
                              <span class="pull-right text-muted">
                                <i class="fa fa-fw fa-angle-right text"></i>
                                <i class="fa fa-fw fa-angle-down text-active"></i>
                              </span>
                            <i class="';
                $navHtml .= $item['icon'];
                $navHtml .= '"></i>
                            <span>';
                $navHtml .= $item['name'];
                if ($item['iconnav'] && is_file($item['iconnav'])) {
                    $navHtml .= '<img src="' . $item['iconnav'] . '" class="nav-icon">';
                }
                $navHtml .= '</span>
                           </a>';
                $navHtml .= '
                           <ul class="nav nav-sub dk">';
                foreach ($item['sublist'] as $key => $sub) {
                    $navHtml .= '<li class="' . checkIfActive($sub['wherein']) . '" id="li_' . $item['id'] . '_' . $sub['id'] . '">
                                <a href="' . $sub['url'] . '" id="a_' . $item['id'] . '_' . $sub['id'] . '">
                                  <span>' . $sub['name'] . '</span>
                                </a>
                              </li>';
                }
                $navHtml .= '
                           </ul>
                        </li>';
            } else {
                if ($item['label'] == 1) {
                    //处理不带链接的提示标签菜单
                    $navHtml = '<li class="hidden-folded padder m-t m-b-sm text-muted text-xs" id="' . $item['id'] . '">
                                <span>' . $item['name'] . '</span>
                               </li>';
                } else {
                    //普通链接
                    $navHtml = '<li class="' . checkIfActive($item['wherein']) . '" id="li_' . $item['id'] . '">
                                    <a href="' . $item['url'] . '" id="a_' . $item['id'] . '">
                                    <i class="' . $item['icon'] . '"></i>
                                    <span>' . $item['name'];
                    if ($item['iconnav'] && is_file($item['iconnav'])) {
                        $navHtml .= '<img src="' . $item['iconnav'] . '" class="nav-icon">';
                    }
                    $navHtml .= '  </span>
                                  </a>
                               </li>';
                }
            }
            $this->html .= $navHtml;
        }
        $this->html .= '</ul>';
        return $this->html;
    }
}
