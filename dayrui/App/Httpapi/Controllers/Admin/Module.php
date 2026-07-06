<?php namespace Phpcmf\Controllers\Admin;

class Module extends \Phpcmf\App
{
    public function __construct() {
        parent::__construct();

        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));

        $menu = [
            '内容模块' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-cogs'],
            '内容页接口' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/show_edit{dir='.$dir.'}', 'fa fa-code'],
            '列表页接口' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/list_edit{dir='.$dir.'}', 'fa fa-code'],
            'help' => [1054],
        ];

        \Phpcmf\Service::V()->assign('menu', \Phpcmf\Service::M('auth')->_admin_menu($menu));
    }

    public function index() {

        $modules = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        if (!$modules) {
            $this->_admin_msg(0, dr_lang('未安装任何内容模块'));
        }

        \Phpcmf\Service::V()->assign([
            'module' => $modules,
        ]);
        \Phpcmf\Service::V()->display('module_list.html');
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

        $url = SITE_URL.'index.php?s='.$dir.'&c=show&id='.$id.'&api_call_function=module_show&appid='.$auth['id'].'&appsecret='.$auth['secret'];

        $rt = dr_string2array(file_get_contents($url));

        echo SITE_URL.'index.php?s='.$dir.'&c=show&id='.$id.'&api_call_function=module_show&appid=需要填写值&appsecret=需要填写值';
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

        $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'module_show')->getRow();
        if ($row) {
            $config = dr_string2array($row['value']);
        } else {
            \Phpcmf\Service::M()->table('api_config')->insert([
                'name' => 'module_show',
                'value' => '',
            ]);
            $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'module_show')->getRow();
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

        $table = \Phpcmf\Service::M()->dbprefix(SITE_ID.'_'.$dir);
        $field = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'`')->getResultArray();
        $field_data = [
            'catname' => [
                'name' => '栏目名称',
                'type' => ''
            ],
        ];
        foreach ($field as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['field'][$t['Field']] ? $module['field'][$t['Field']]['fieldtype'] : '',
            ];
        }
        $field2 = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'_data_0`')->getResultArray();
        foreach ($field2 as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['field'][$t['Field']] ? $module['field'][$t['Field']]['fieldtype'] : '',
            ];
        }
        $field3 = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'_category_data`')->getResultArray();
        foreach ($field3 as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['category_data_field'][$t['Field']] ? $module['category_data_field'][$t['Field']]['fieldtype'] : '',
            ];
        }

        \Phpcmf\Service::V()->assign([
            'page' => 0,
            'data' => $config[$dir],
            'form' => dr_form_hidden(['page' => 0]),
            'module' => $module,
            'field_data' => $field_data,
        ]);
        \Phpcmf\Service::V()->display('module_show.html');
    }


    // 列表页接口
    public function list_edit() {

        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));
        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-'.$dir);
        if (!$module) {
            $this->_admin_msg(0, dr_lang('当前站点没有安装此模块[%s]', $dir));
        }

        $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'module_list')->getRow();
        if ($row) {
            $config = dr_string2array($row['value']);
        } else {
            \Phpcmf\Service::M()->table('api_config')->insert([
                'name' => 'module_list',
                'value' => '',
            ]);
            $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'module_list')->getRow();
            $config = [];
        }

        if (IS_AJAX_POST) {
            $code = $config[$dir]['code'];
            $config[$dir] = \Phpcmf\Service::L('input')->post('data');
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

        $table = \Phpcmf\Service::M()->dbprefix(SITE_ID.'_'.$dir);
        $field = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'`')->getResultArray();
        $field_data = [
            'catname' => [
                'name' => '栏目名称',
                'type' => ''
            ],
        ];
        foreach ($field as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['field'][$t['Field']] ? $module['field'][$t['Field']]['fieldtype'] : '',
            ];
        }

        $field3 = \Phpcmf\Service::M()->db->query('SHOW FULL COLUMNS FROM `'.$table.'_category_data`')->getResultArray();
        foreach ($field3 as $t) {
            $field_data[$t['Field']] = [
                'name' => $t['Comment'] ? $t['Comment'] : $t['Field'],
                'type' => $module['category_data_field'][$t['Field']] ? $module['category_data_field'][$t['Field']]['fieldtype'] : '',
            ];
        }

        \Phpcmf\Service::V()->assign([
            'page' => 0,
            'data' => $config[$dir],
            'form' => dr_form_hidden(['page' => 0]),
            'module' => $module,
            'field_data' => $field_data,
        ]);
        \Phpcmf\Service::V()->display('module_show_list.html');
    }

    // 调用代码
    public function test_list_edit() {

        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));

        $auth = \Phpcmf\Service::M()->table('api_auth')->where('disabled', 0)->getRow();
        if (!$auth) {
            exit('没有配置接口密钥');
        }
        $url = SITE_URL.'index.php?s='.$dir.'&c=search&more=1&api_call_function=module_list&appid='.$auth['id'].'&appsecret='.$auth['secret'];

        $rt = dr_string2array(file_get_contents($url));

        echo SITE_URL.'index.php?s='.$dir.'&c=search&api_call_function=module_list&appid=需要填写值&appsecret=需要填写值(注意，需要显示栏目模型字段时加上参数&more=1)&page=当前页码&pagesize=每页显示条数';
        if ($rt) {
            echo '<hr>';
            echo '<pre>';
            print_r($rt);
        }
        exit;
    }

}
