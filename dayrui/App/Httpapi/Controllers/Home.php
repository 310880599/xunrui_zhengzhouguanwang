<?php namespace Phpcmf\Controllers;

// Http接口处理
class Home extends \Phpcmf\App
{

    // 接口权限验证
    private function _api_auth() {
        define('IS_API_HTTP', 1);
        \Phpcmf\Service::M('http', 'httpapi')->check_auth();
    }

    // 数据库操作权限
    private function _db_auth($table, $action) {

        $appid = (int)\Phpcmf\Service::L('input')->request('appid');
        $config = \Phpcmf\Service::C()->get_cache('api_db_auth', $appid);
        if (!$config) {
            \Phpcmf\Service::C()->_json(0, 'AppID不存在[db_auth]');
        } elseif (!$config['action']) {
            \Phpcmf\Service::C()->_json(0, '你没有设置AppID的数据库操作权限[db_auth]');
        } elseif (!$config['table']) {
            \Phpcmf\Service::C()->_json(0, '你没有设置AppID的数据表权限[db_auth]');
        } elseif (!dr_in_array($action, $config['action'])) {
            \Phpcmf\Service::C()->_json(0, 'AppID的没有数据库操作权限['.$action.']');
        } elseif (!dr_in_array($table, $config['table'])) {
            \Phpcmf\Service::C()->_json(0, 'AppID的没有数据库表['.$table.']的操作权限');
        }
    }

    /**
     * 文件删除接口
     */
    public function delete_file() {

        $this->_api_auth();
        if (!$this->uid) {
            \Phpcmf\Service::C()->_json(0, '没有授权登录账号');
        }

        $id = (int)\Phpcmf\Service::L('input')->request('id');
        if (!$id) {
            \Phpcmf\Service::C()->_json(0, '没有传入文件id号');
        }

        $data = \Phpcmf\Service::M()->db->table('attachment')->where('id', $id)->get()->getRowArray();
        if (!$data) {
            \Phpcmf\Service::C()->_json(0, '此文件id（'.$id.'）不存在');
        } elseif ($data['uid'] != $this->uid) {
            \Phpcmf\Service::C()->_json(0, '此文件id（'.$id.'）不是当前账号上传的');
        }

        // 删除文件
        $rt = \Phpcmf\Service::M('attachment')->file_delete($this->member, $id);
        $this->_json($rt['code'], $rt['msg']);
    }

    /**
     * 调用后台接口
     */
    public function index() {

        $this->_api_auth();

        $id = intval(\Phpcmf\Service::L('input')->request('id'));
        if (!$id) {
            $this->_json(0, '未获取到接口id');
        }

        $data = $this->get_cache('api_http', $id);
        if (!$data) {
            $this->_json(0, '接口数据缓存【'.$id.'】不存在');
        }

        $rt = \Phpcmf\Service::M('http', APP_DIR)->get_api_data($data);
        $this->_json($rt['code'], $rt['msg'], $rt['data']);

        exit;
    }

    /**
     * 删除数据接口
     */
    public function delete() {

        $this->_api_auth();

        $id = (int)\Phpcmf\Service::L('input')->post('id');
        if (!$id) {
            $this->_json(0, '主键id参数不存在');
        }

        $table = dr_safe_filename(\Phpcmf\Service::L('input')->post('table'));
        if (!$table) {
            $this->_json(0, dr_lang('数据表table参数不存在'));
        } elseif (!\Phpcmf\Service::M()->db->tableExists(\Phpcmf\Service::M()->dbprefix($table))) {
            $this->_json(0, dr_lang('数据表[%s]不存在', \Phpcmf\Service::M()->dbprefix($table)));
        }

        $this->_db_auth($table, 'delete');

        // 删除数据接口
        $rt = \Phpcmf\Service::M()->table($table)->delete($id);
        if (!$rt['code']) {
            $this->_json(0, $rt['code']);
        }

        $this->_json($id, 'ok');
    }

    /**
     * 更新数据接口
     */
    public function update() {

        $this->_api_auth();

        $id = (int)\Phpcmf\Service::L('input')->post('id');
        if (!$id) {
            $this->_json(0, '主键id参数不存在');
        }

        $table = dr_safe_filename(\Phpcmf\Service::L('input')->post('table'));
        if (!$table) {
            $this->_json(0, dr_lang('数据表table参数不存在'));
        } elseif (!\Phpcmf\Service::M()->db->tableExists(\Phpcmf\Service::M()->dbprefix($table))) {
            $this->_json(0, dr_lang('数据表[%s]不存在', \Phpcmf\Service::M()->dbprefix($table)));
        }

        $this->_db_auth($table, 'update');

        $value = \Phpcmf\Service::L('input')->post('value', false);
        if (!$value) {
            $this->_json(0, dr_lang('value参数不存在'));
        } elseif (!is_array($value)) {
            $this->_json(0, dr_lang('value参数必须是一个数组'));
        }

        // 格式化数据
        $call = \Phpcmf\Service::L('input')->request('call');
        if ($call) {
            // 回调函数
            if (method_exists(\Phpcmf\Service::L('http'), $call)) {
                $value = \Phpcmf\Service::L('http')->$call($id, $value);
            } elseif (function_exists($call)) {
                $value = call_user_func($call, $id, $value);
            } else {
                $this->_json(0, '回调方法【'.$call.'】未定义');
            }
        }

        $save = [];
        $field = \Phpcmf\Service::M()->db->getFieldNames(\Phpcmf\Service::M()->dbprefix($table));
        foreach ($value as $name => $val) {
            if (!dr_in_array($name, $field)) {
                $this->_json(0, dr_lang('数据表[%s]的字段[%s]不存在', \Phpcmf\Service::M()->dbprefix($table), $name));
            }
            $save[$name] = is_array($val) ? dr_array2string($val) : $val;
        }

        // 存储数据
        $rt = \Phpcmf\Service::M()->table($table)->update($id, $save);
        if (!$rt['code']) {
            $this->_json(0, $rt['code']);
        }

        $this->_json($id, 'ok');
    }

    /**
     * 插入数据接口
     */
    public function insert() {

        $this->_api_auth();

        $table = dr_safe_filename(\Phpcmf\Service::L('input')->post('table'));
        if (!$table) {
            $this->_json(0, dr_lang('数据表table参数不存在'));
        } elseif (!\Phpcmf\Service::M()->db->tableExists(\Phpcmf\Service::M()->dbprefix($table))) {
            $this->_json(0, dr_lang('数据表[%s]不存在', \Phpcmf\Service::M()->dbprefix($table)));
        }

        $this->_db_auth($table, 'insert');

        $value = \Phpcmf\Service::L('input')->post('value', false);
        if (!$value) {
            $this->_json(0, dr_lang('value参数不存在'));
        } elseif (!is_array($value)) {
            $this->_json(0, dr_lang('value参数必须是一个数组'));
        }

        // 格式化数据
        $call = \Phpcmf\Service::L('input')->request('call');
        if ($call) {
            // 回调函数
            if (method_exists(\Phpcmf\Service::L('http'), $call)) {
                $value = \Phpcmf\Service::L('http')->$call(0, $value);
            } elseif (function_exists($call)) {
                $value = call_user_func($call, 0, $value);
            } else {
                $this->_json(0, '回调方法【'.$call.'】未定义');
            }
        }

        $save = [];
        $field = \Phpcmf\Service::M()->db->getFieldNames(\Phpcmf\Service::M()->dbprefix($table));
        foreach ($value as $name => $val) {
            if (!dr_in_array($name, $field)) {
                $this->_json(0, dr_lang('数据表[%s]的字段[%s]不存在', \Phpcmf\Service::M()->dbprefix($table), $name));
            }
            $save[$name] = is_array($val) ? dr_array2string($val) : $val;
        }

        // 存储数据
        $rt = \Phpcmf\Service::M()->table($table)->insert($save);
        if (!$rt['code']) {
            $this->_json(0, $rt['code']);
        }

        $this->_json($rt['code'], 'ok');
    }

    /**
     * 用户基本信息接口
     */
    public function member() {

        $this->_api_auth();

        $uid = intval($_GET['uid']);
        if (!$uid) {
            $this->_json(0, 'uid参数不能为空');
        }

        $member = dr_member_info($uid);
        if (!$member) {
            $this->_json(0, '此用户不存在');
        }

        $this->_json(1, 'ok', $member);
    }

    /**
     * 网站信息接口
     */
    public function site() {

        $this->_api_auth();
        $data = \Phpcmf\Service::M('Site')->config(SITE_ID);
        $rt = \Phpcmf\Service::M('http', 'httpapi')->json('site', 1, '', $data);
        $this->_json(1, 'ok', $rt);
    }

    /**
     * 栏目接口
     */
    public function category() {

        $this->_api_auth();

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $pid = intval(\Phpcmf\Service::L('input')->get('pid'));
        $dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('mid'));
        $this->module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-'.$dir);
        if (!$this->module) {
            $this->_json(0, dr_lang('当前站点没有安装此模块[%s]', $dir));
        }

        if ($id) {
            $data = [
                dr_cat_value($this->module['mid'], $id),
            ];
        } else {
            $data = [];
            $child = \Phpcmf\Service::L('category', 'module')->get_child($this->module['mid'] ? $this->module['mid'] : $dir, $pid);
            if ($child) {
                foreach ($child as $i) {
                    $cat = dr_cat_value($this->module['mid'], $i);
                    if ($cat) {
                        $data[$i] = $cat;
                    }
                }
            }
        }
        $this->module['field'] = [];
        $rt = \Phpcmf\Service::M('http', 'httpapi')->json('category', 1, '', $data);
        $this->_json(1, 'ok', $rt);
    }

    /**
     * 接口测试
     */
    public function test() {
        $this->_api_auth();
        $this->_json(1, 'ok');
    }

}
