<?php namespace Phpcmf\Controllers\Admin;

class Site extends \Phpcmf\App
{

    public function index() {

        $field = \Phpcmf\Service::M('field')->get_mysite_field(SITE_ID);

        $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'site_'.SITE_ID)->getRow();
        if ($row) {
            $data = dr_string2array($row['value']);
        } else {
            \Phpcmf\Service::M()->table('api_config')->insert([
                'name' => 'site_'.SITE_ID,
                'value' => '',
            ]);
            $row = \Phpcmf\Service::M()->table('api_config')->where('name', 'site_'.SITE_ID)->getRow();
            $data = [];
        }

        if (IS_AJAX_POST) {
            $post = \Phpcmf\Service::L('input')->post('data');
            if ($post['field']) {
                $fobj = \Phpcmf\Service::L('Function_module', 'httpapi');
                foreach ($post['field'] as $t) {
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
                if ($post['code'] != $data['code']) {
                    $this->_json(0, dr_lang('需要在index.php中开启IS_EDIT_TPL参数'));
                }
            }
            \Phpcmf\Service::M()->table('api_config')->update($row['id'], [
                'value' => dr_array2string($post),
            ]);
            \Phpcmf\Service::M('http', 'httpapi')->cache();
            \Phpcmf\Service::M('cache')->update_data_cache();
            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'menu' =>  \Phpcmf\Service::M('auth')->_admin_menu([
                '项目信息接口' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-cogs'],
                'help' => [1147],
            ]),
            'data' => $data,
            'field' => $field,
        ]);
        \Phpcmf\Service::V()->display('site.html');
    }



    // 调用代码
    public function test_edit() {

        $auth = \Phpcmf\Service::M()->table('api_auth')->where('disabled', 0)->getRow();
        if (!$auth) {
            exit('没有配置接口密钥');
        }
        $url = SITE_URL.'index.php?s=httpapi&m=site&appid='.$auth['id'].'&appsecret='.$auth['secret'];

        $rt = dr_string2array(file_get_contents($url));

        echo SITE_URL.'index.php?s=httpapi&m=site&appid=需要填写值&appsecret=需要填写值';
        if ($rt) {
            echo '<hr>';
            echo '<pre>';
            print_r($rt);
        }
        exit;
    }

}
