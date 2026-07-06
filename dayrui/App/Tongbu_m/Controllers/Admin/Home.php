<?php namespace Phpcmf\Controllers\Admin;

class Home extends \Phpcmf\App
{

    public function index() {

        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        if (!$module) {
            $this->_admin_msg(0, dr_lang('未安装任何内容模块'));
        }

        $data = \Phpcmf\Service::M('app')->get_config(APP_DIR);

        if (IS_AJAX_POST) {

            $post = \Phpcmf\Service::L('input')->post('data');
            \Phpcmf\Service::M('app')->save_config(APP_DIR, $post);

            $this->_json(1, dr_lang('操作成功'));
        }

        $page = intval(\Phpcmf\Service::L('input')->get('page'));

        \Phpcmf\Service::V()->assign([
            'page' => $page,
            'data' => $data,
            'form' => dr_form_hidden(['page' => $page]),
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '插件设置' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-cog'],
                ]
            ),
            'module' => $module
        ]);
        \Phpcmf\Service::V()->display('config.html');
    }

    // 同步模块
    public function add() {
        $ids = explode(',',$_GET['ids']);
        $mid = dr_safe_filename($_GET['mid']);
        $key = dr_safe_filename($_GET['key']);
        $list = \Phpcmf\Service::L('cache')->get_auth_data($key);
        if (!$list) {
            $this->_html_msg(0, '缓存过期');
        }
        $page = intval($_GET['page']);

        $arr = array_slice($list, $page, 1, true);
        if (!$arr) {
            $this->_html_msg(1, '同步完成');
        }

        $siteid = SITE_ID;
        $this->_module_init($mid, SITE_ID);

        foreach ($ids as $id) {
            $this->content_model->_init($mid, SITE_ID, $this->module['share']);
            $data = $this->content_model->get_data($id);
            foreach ($arr as $dir => $cats) {
                if ($cats) {
                    // 初始化站点模块
                    $this->content_model->_init($dir, $siteid, $this->module['share']);

                    $fields = [];
                    // 主表字段
                    $fields[1] = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$dir));
                    $cache = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$dir.'_category_data'));
                    $cache && $fields[1] = array_merge($fields[1], $cache);

                    // 附表字段
                    $fields[0] = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$dir.'_data_0'));
                    $cache = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$dir.'_category_data_0'));
                    $cache && $fields[0] = array_merge($fields[0], $cache);

                    // 去重复
                    $fields[0] = array_unique($fields[0]);
                    $fields[1] = array_unique($fields[1]);

                    $save = [];

                    // 主表附表归类
                    foreach ($fields as $ismain => $field) {
                        foreach ($field as $name) {
                            isset($data[$name]) && $save[$ismain][$name] = $data[$name];
                        }
                    }

                    $save[1]['uid'] = $save[0]['uid'] = $data['uid'];

                    $save[1]['url'] = '';
                    $save[1]['status'] = 9; //9表示正常发布，1表示审核里面
                    $save[1]['hits'] = 0;
                    $save[1]['displayorder'] = 0;
                    $save[1]['link_id'] = 0;
                    $save[1]['inputtime'] = $save[1]['updatetime'] = SYS_TIME;
                    $save[1]['inputip'] = '127.0.0.1';

                    foreach ($cats as $catid) {
                        $save[1]['catid'] = $save[0]['catid'] = $catid;
                        $rt = $this->content_model->save_content(0, $save);
                        if ($rt['code']) {
                            $ct++;
                        }
                    }
                }
            }
        }


        $page++;
        $this->_html_msg(1, '本次同步'.$ct.'篇，正在同步中（'.($page).'/'.count($list).'）', dr_url('tongbu_m/home/add', ['mid'=>$mid, 'ids'=>$_GET['ids'], 'key'=>$key, 'page'=>$page]));
    }

    // 同步模块
    public function edit() {

        $mid = dr_safe_filename($_GET['mid']);
        $row = \Phpcmf\Service::M('Module')->table('module')->where('dirname', $mid)->getRow();
        if (!$row) {
            $this->_json(0, dr_lang('此模块[%s]未安装', $mid));
        }

        $ids = \Phpcmf\Service::L('input')->get('ids');
        if (!$ids) {
            $this->_json(0, dr_lang('所选内容不存在'));
        }

        $cats = [];
        foreach ($ids as $id) {
            if ($id) {
                $data = \Phpcmf\Service::M()->table_site($mid)->get($id);
                if ($data) {
                    $cat = dr_cat_value($mid, $data['catid'], 'name');
                    if ($cat) {
                        $cats[] = 'name = "'.$cat.'"';
                    }
                }
            }
        }

        $all = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-'.$mid);
        $config = \Phpcmf\Service::M('app')->get_config('tongbu_m');


        // 计算可用站点信息
        $list = [];
        foreach ($all as $t) {
            $my = [];
            if (dr_in_array($t['dirname'], $config['module'])) {

                $list[$t['dirname']] = [
                    'name' => $t['name'],
                    'select' => \Phpcmf\Service::L('tree')->select_category(
                        \Phpcmf\Service::L('category', 'module')->get_category($t['dirname'], SITE_ID),
                        $my,
                        'name="data['.$t['dirname'].'][catid][]"  multiple="multiple" data-actions-box="true"',
                        '',
                        1, 1
                    ),
                ];
            }
        }

        if (IS_POST) {

            //$key = SYS_TIME.$this->uid;
            //\Phpcmf\Service::L('cache')->set_auth_data($key, $list);
            //$this->_json(1, dr_lang('即将同步到其他站点'), ['url' => dr_url('tongbu/home/add', ['mid'=>$mid, 'key'=>$key, 'ids'=>implode(',', $ids)])]);

            $ct = 0;
            $post = \Phpcmf\Service::L('input')->post('data');
            $this->_module_init($mid, SITE_ID);
            foreach ($ids as $id) {
                $this->content_model->_init($mid, SITE_ID, $this->module['share']);
                $data = $this->content_model->get_data($id);
                foreach ($list as $dir => $t) {
                    if ($post[$dir]['catid']) {
                        // 初始化站点模块
                        $this->module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-'.$dir);
                        $this->content_model->_init($dir, SITE_ID, $this->module['share']);

                        $fields = [];
                        // 主表字段
                        $fields[1] = $this->get_cache('table-'.SITE_ID, $this->content_model->dbprefix(SITE_ID.'_'.$dir));
                        $cache = $this->get_cache('table-'.SITE_ID, $this->content_model->dbprefix(SITE_ID.'_'.$dir.'_category_data'));
                        $cache && $fields[1] = array_merge($fields[1], $cache);

                        // 附表字段
                        $fields[0] = $this->get_cache('table-'.SITE_ID, $this->content_model->dbprefix(SITE_ID.'_'.$dir.'_data_0'));
                        $cache = $this->get_cache('table-'.SITE_ID, $this->content_model->dbprefix(SITE_ID.'_'.$dir.'_category_data_0'));
                        $cache && $fields[0] = array_merge($fields[0], $cache);

                        // 去重复
                        $fields[0] = array_unique($fields[0]);
                        $fields[1] = array_unique($fields[1]);

                        $save = [];

                        // 主表附表归类
                        foreach ($fields as $ismain => $field) {
                            foreach ($field as $name) {
                                isset($data[$name]) && $save[$ismain][$name] = $data[$name];
                            }
                        }

                        $save[1]['uid'] = $save[0]['uid'] = $data['uid'];

                        $save[1]['url'] = '';
                        $save[1]['status'] = 9; //9表示正常发布，1表示审核里面
                        $save[1]['hits'] = 0;
                        $save[1]['displayorder'] = 0;
                        $save[1]['link_id'] = 0;
                        $save[1]['inputtime'] = $save[1]['updatetime'] = SYS_TIME;
                        $save[1]['inputip'] = '127.0.0.1';

                        $cats = dr_string2array($post[$dir]['catid']);
                        foreach ($cats as $catid) {
                            $save[1]['catid'] = $save[0]['catid'] = $catid;
                            $rt = $this->content_model->save_content(0, $save);
                            if ($rt['code']) {
                                $ct++;
                            }
                        }

                    }
                }
            }
            $this->_json(1, dr_lang('本次同步%s条数据', $ct));
            exit;
        }

        \Phpcmf\Service::V()->assign([
            'ids' => $ids,
            'list' => $list,
            'form' => dr_form_hidden(),
        ]);
        \Phpcmf\Service::V()->display('sync.html');exit;
    }

}
