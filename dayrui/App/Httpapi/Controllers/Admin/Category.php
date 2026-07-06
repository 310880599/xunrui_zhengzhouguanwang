<?php namespace Phpcmf\Controllers\Admin;

class Category extends \Phpcmf\App
{
    public function __construct() {
        parent::__construct();

        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));

        $menu = [
            '网站栏目' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-cogs'],
            '栏目接口' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/show_edit{dir='.$dir.'}', 'fa fa-code'],
            'help' => [1035],
        ];

        \Phpcmf\Service::V()->assign('menu', \Phpcmf\Service::M('auth')->_admin_menu($menu));
    }

    public function index() {

        $modules = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        if (!$modules) {
            $this->_admin_msg(0, dr_lang('未安装任何内容模块'));
        }

        $list = [
            'share' => [
                'name' => '共享栏目',
                'icon' => 'fa fa-share-alt',
                'dirname' => 'share',
            ],
        ];
        foreach ($modules as $t) {
            if ($t['share']) {
                continue;
            }
            $list[$t['dirname']] = $t;
        }

        \Phpcmf\Service::V()->assign([
            'module' => $list,
        ]);
        \Phpcmf\Service::V()->display('category_list.html');
    }

    // 回调函数
    public function func_index() {

        \Phpcmf\Service::V()->assign([
            'func' => [

            ],
        ]);
        \Phpcmf\Service::V()->display('module_func.html');
    }

    // 调用代码
    public function test_show_edit() {

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));

        $auth = \Phpcmf\Service::M()->table('api_auth')->where('disabled', 0)->getRow();
        if (!$auth) {
            exit('没有配置接口密钥');
        }

        $url = SITE_URL.'index.php?s=httpapi&m=category&mid='.$dir.'&pid=0&appid='.$auth['id'].'&appsecret='.$auth['secret'];

        $rt = dr_string2array(file_get_contents($url));

        echo SITE_URL.'index.php?s=httpapi&m=category&mid='.$dir.'&pid=填写0表示顶级栏目、填写父栏目id号时循环显示下级栏目&appid=需要填写值&appsecret=需要填写值';
        if ($rt) {
            echo '<hr>';
            echo '<pre>';
            print_r($rt);
        }
        exit;
    }

    // 内容页接口
    public function show_edit() {

        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));
        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-'.$dir);
        if (!$module) {
            $this->_admin_msg(0, dr_lang('当前站点没有安装此模块[%s]', $dir));
        }

        $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'category')->getRow();
        if ($row) {
            $config = dr_string2array($row['value']);
        } else {
            \Phpcmf\Service::M()->table('api_config')->insert([
                'name' => 'category',
                'value' => '',
            ]);
            $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'category')->getRow();
            $config = [];
        }

        if (IS_AJAX_POST) {
            $code = $config[$dir]['code'];
            $config[$dir] = \Phpcmf\Service::L('input')->post('data', false);
            if ($config[$dir]['field']) {
                $fobj = \Phpcmf\Service::L('Function_module', 'httpapi');
                foreach ($config[$dir]['field'] as $t) {
                    if ($t['func']) {
                        if (method_exists($fobj, $t['func'])) {

                        } else {
                            $arr = explode('|', $t['func']);
                            foreach ($arr as $a) {
                                if (!function_exists($a)) {
                                    $this->_json(0, dr_lang('函数[%s]未定义', $t['func']));
                                }
                            }
                        }
                    }
                }
            }
            if (!IS_EDIT_TPL) {
                if ($config[$dir]['code'] != $code) {
                    $this->_json(0, dr_lang('需要在index.php中开启IS_EDIT_TPL参数'));
                }
            }
            \Phpcmf\Service::M()->table('api_config')->update($row['id'], [
                'value' => dr_array2string($config),
            ]);
            \Phpcmf\Service::M('http', 'httpapi')->cache();
            \Phpcmf\Service::M('cache')->update_data_cache();
            $this->_json(1, dr_lang('操作成功'));
        }

        $table = \Phpcmf\Service::M()->dbprefix(SITE_ID.'_'.$dir.'_category');
        $field = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'`')->getResultArray();
        $field_data = [

        ];
        foreach ($field as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['field'][$t['Field']] ? $module['field'][$t['Field']]['fieldtype'] : '',
            ];
        }

        \Phpcmf\Service::V()->assign([
            'page' => 0,
            'data' => $config[$dir],
            'form' => dr_form_hidden(['page' => 0]),
            'module' => $module,
            'field_data' => $field_data,
        ]);
        \Phpcmf\Service::V()->display('category_show.html');
    }


}
