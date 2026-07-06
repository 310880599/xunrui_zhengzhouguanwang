<?php namespace Phpcmf\Controllers\Admin;
/* *
 *
 * 本Demo的语法参考： http://help.xunruicms.com/445.html
 *
 * */
class {cname} extends \Phpcmf\Table
{

    public function __construct()
    {
        parent::__construct();
        // 表单显示名称
        $this->name = dr_lang('{fname}');
        // 模板前缀(避免混淆)
        $this->tpl_prefix = '{tpl_prefix}';

        // 采用ajax列表请求
        $this->is_ajax_list = {is_ajax};
        // 是否启用回收站
        $this->is_recycle = {is_recycle};
        // 是否启用弹窗编辑窗口
        $this->is_iframe_post = {is_iframe};
        // 弹窗编辑窗口宽高设置
        $this->iframe_post_area = ['', ''];

        // 用于表储存的字段，后台可修改的表字段，设置字段类别参考：http://help.xunruicms.com/1138.html
        $field = {field};

        // 用于列表显示的字段
        $list_field = {list_field};
        /*
         *array (
                    'use' => '1', // 1是显示，0是不显示
                    'name' => '', //显示名称
                    'width' => '', // 显示宽度
                    'func' => '', // 回调函数见：http://help.xunruicms.com/463.html
                    'center' => '0', // 1是居中，0是默认
                )
         * */

        $json_file = str_replace('.php', '.json', __FILE__);
        if (is_file($json_file)) {
            $json = dr_string2array(file_get_contents($json_file));
            if ($json) {
                if ($json['field']) {
                    $field = $json['field'];
                }
                if ($json['list']) {
                    $list_field = $json['list'];
                }
            }
        }

        // 初始化数据表
        $this->_init([
            'table' => '{table}',  // （不带前缀的）表名字
            'field' => $field, // 可查询的字段
            'list_field' => $list_field,
            'order_by' => 'id desc', // 列表排序，默认的排序方式
            'date_field' => '', // 按时间段搜索字段，没有时间字段留空
        ]);

        // 把公共变量传入模板
        \Phpcmf\Service::V()->assign([
            // 搜索字段
            'field' => $field,
            'is_time_where' => $this->init['date_field'],
            'is_iframe_post' => $this->is_iframe_post,
            // 后台的菜单
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    $this->name => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-code'],
                    '添加' => [($this->is_iframe_post ? 'add:' : '').APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/add', 'fa fa-plus', $this->iframe_post_area[0], $this->iframe_post_area[1]],
                    '修改' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                ])
        ]);
    }

    // 查看列表
    public function index() {
        list($tpl) = $this->_List();
        \Phpcmf\Service::V()->display($tpl);
    }

    // 添加内容
    public function add() {
        list($tpl) = $this->_Post(0);
        \Phpcmf\Service::V()->display($tpl);
    }

    // 修改内容
    public function edit() {
        list($tpl) = $this->_Post(intval(\Phpcmf\Service::L('input')->get('id')));
        \Phpcmf\Service::V()->display($tpl);
    }

    // 删除内容
    public function del() {
        $this->_Del(
            \Phpcmf\Service::L('Input')->get_post_ids(),
            function($rows) {
                // 删除前的验证
                return dr_return_data(1, 'ok', $rows);
            },
            function($rows) {
                // 删除后的处理
                return dr_return_data(1, 'ok');
            },
            \Phpcmf\Service::M()->dbprefix($this->init['table'])
        );
    }

    // 回收站列表
    public function recycle_del() {
        $this->_Recycle_List();
    }

    // 恢复指定回收站数据
    public function recycle_restore_del() {
        $this->_Recycle_Restore(\Phpcmf\Service::L('Input')->get_post_ids());
    }

    // 删除回收站数据
    public function recycle_all_del() {
        $this->_Recycle_Init();
        $this->del();
    }

    // 回收站内容显示
    public function recycle_show_del() {
        $this->_Recycle_Init();
        list($tpl) = $this->_Show(intval(\Phpcmf\Service::L('input')->get('id')));
        \Phpcmf\Service::V()->display($tpl);
    }

    // 清空回收站
    public function recycle_clear_del() {
        $this->_Recycle_Clear();
        $this->del();
    }
    /**
     * 获取内容
     * $id      内容id,新增为0
     * */
    protected function _Data($id = 0) {
        $row = parent::_Data($id);
        // 这里可以对内容进行格式化显示操处理
        return $row;
    }

    // 格式化保存数据
    protected function _Format_Data($id, $data, $old) {
        if (!$id) {
            // 当提交新数据时，把当前时间插入进去
            //$data[1]['inputtime'] = SYS_TIME;
        }
        return $data;
    }


    // 保存内容
    protected function _Save($id = 0, $data = [], $old = [], $func = null, $func2 = null) {
        return parent::_Save($id, $data, $old, function($id, $data, $old){
            // 验证数据
            /*
            if (!$data[1]['title']) {
                return dr_return_data(0, '标题不能为空！', ['field' => 'title']);
            }*/
            // 保存之前执行的函数，并返回新的数据
            if (!$id) {
                // 当提交新数据时，把当前时间插入进去
                //$data[1]['inputtime'] = SYS_TIME;
            }

            return dr_return_data(1, null, $data);
        }, function ($id, $data, $old) {
            // 保存之后执行的动作
        });
    }

}
