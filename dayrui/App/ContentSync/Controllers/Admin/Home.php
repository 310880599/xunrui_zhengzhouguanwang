<?php namespace Phpcmf\Controllers\Admin;

/**
 * 内容同步插件后台管理
 */
class Home extends \Phpcmf\App
{
    /**
     * 配置首页
     */
    public function index() {

        if (IS_AJAX_POST) {
            $this->save_site();
        }

        $db = \Phpcmf\Service::M()->db;
        $site_table = \Phpcmf\Service::M()->dbprefix('content_sync_site');
        $log_table = \Phpcmf\Service::M()->dbprefix('content_sync_log');

        $sites = $db->table($site_table)->orderBy('id desc')->get()->getResultArray();
        $logs = $db->table($log_table)->orderBy('id desc')->limit(20)->get()->getResultArray();

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'sites' => $sites,
            'logs' => $logs,
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu([
                '内容同步' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-send'],
            ]),
        ]);
        \Phpcmf\Service::V()->display('config.html');
    }

    /**
     * 保存目标站点
     */
    private function save_site() {
        $post = \Phpcmf\Service::L('input')->post('data');

        $name = trim((string)$post['name']);
        $api_url = trim((string)$post['api_url']);
        $api_key = trim((string)$post['api_key']);
        $status = isset($post['status']) ? 1 : 0;

        if (!$name) {
            $this->_json(0, '网站名称不能为空');
        } elseif (!$api_url) {
            $this->_json(0, '接口地址不能为空');
        } elseif (!preg_match('/^https?:\/\//i', $api_url)) {
            $this->_json(0, '接口地址必须以http://或https://开头');
        } elseif (!$api_key) {
            $this->_json(0, 'API KEY不能为空');
        }

        $data = [
            'name' => $name,
            'api_url' => $api_url,
            'api_key' => $api_key,
            'status' => $status,
            'create_time' => SYS_TIME,
        ];

        $db = \Phpcmf\Service::M()->db;
        $site_table = \Phpcmf\Service::M()->dbprefix('content_sync_site');
        $rt = $db->table($site_table)->insert($data);
        if (!$rt) {
            $this->_json(0, '保存失败，请检查数据库配置');
        }

        $this->_json(1, '保存成功');
    }

    /**
     * 删除目标站点
     */
    public function delete() {
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        if (!$id) {
            $this->_json(0, '参数错误');
        }

        $site_table = \Phpcmf\Service::M()->dbprefix('content_sync_site');
        \Phpcmf\Service::M()->db->table($site_table)->where('id', $id)->delete();
        $this->_json(1, '删除成功');
    }

    /**
     * 切换启用状态
     */
    public function status_edit() {
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        if (!$id) {
            $this->_json(0, '参数错误');
        }

        $db = \Phpcmf\Service::M()->db;
        $site_table = \Phpcmf\Service::M()->dbprefix('content_sync_site');
        $row = $db->table($site_table)->where('id', $id)->get()->getRowArray();
        if (!$row) {
            $this->_json(0, '站点不存在');
        }

        $status = $row['status'] ? 0 : 1;
        $db->table($site_table)->where('id', $id)->update([
            'status' => $status,
        ]);

        $this->_json(1, $status ? '已启用' : '已禁用');
    }
}
