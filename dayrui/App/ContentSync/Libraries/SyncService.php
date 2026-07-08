<?php namespace Phpcmf\App\ContentSync\Libraries;

require_once dr_get_app_dir('ContentSync').'Libraries/HttpClient.php';
require_once dr_get_app_dir('ContentSync').'Models/SyncLog.php';

use Phpcmf\App\ContentSync\Models\SyncLog;

/**
 * 内容同步发送服务
 */
class SyncService
{
    /**
     * 钩子回调入口
     *
     * @param array $data
     * @param array $old
     */
    public function handleModuleContentAfter($data, $old = []) {

        $module = \Phpcmf\Service::C()->module;
        $dirname = is_array($module) && isset($module['dirname']) ? strtolower((string)$module['dirname']) : '';

        // 当前阶段只处理新闻中心模块
        if ($dirname !== 'xinwenzhongxin') {
            return;
        }

        // 仅在新增发布时发送，修改内容不发送
        if ($old && is_array($old) && !empty($old['id'])) {
            return;
        }

        $main = isset($data[1]) && is_array($data[1]) ? $data[1] : [];
        $extend = isset($data[0]) && is_array($data[0]) ? $data[0] : [];
        if (!$main || empty($main['id'])) {
            return;
        }

        $payload = [
            'content_id' => (string)$main['id'],
            'title' => (string)$main['title'],
            'content' => (string)(isset($extend['content']) ? $extend['content'] : (isset($main['content']) ? $main['content'] : '')),
            'thumb' => (string)(isset($main['thumb']) ? $main['thumb'] : ''),
            'catid' => (string)(isset($main['catid']) ? $main['catid'] : 0),
            'inputtime' => (string)(isset($main['inputtime']) ? $main['inputtime'] : 0),
            'seo_title' => (string)(isset($main['seo_title']) ? $main['seo_title'] : (isset($main['title']) ? $main['title'] : '')),
            'seo_keywords' => (string)(isset($main['seo_keywords']) ? $main['seo_keywords'] : (isset($main['keywords']) ? $main['keywords'] : '')),
            'seo_description' => (string)(isset($main['seo_description']) ? $main['seo_description'] : (isset($main['description']) ? $main['description'] : '')),
        ];

        $sites = $this->getEnabledSites();
        if (!$sites) {
            return;
        }

        $client = new HttpClient();
        $log_model = new SyncLog();

        foreach ($sites as $site) {
            $headers = [
                'X-API-KEY' => (string)$site['api_key'],
            ];

            $response = $client->postJson((string)$site['api_url'], $payload, $headers, 10);
            $status = $response['success'] ? 1 : 0;

            $log_model->add([
                'content_id' => (int)$main['id'],
                'module' => $dirname,
                'title' => (string)$main['title'],
                'target_site' => (string)$site['name'],
                'status' => $status,
                'request_data' => $this->jsonEncode([
                    'url' => (string)$site['api_url'],
                    'headers' => $headers,
                    'payload' => $payload,
                ]),
                'response_data' => (string)$response['body'],
                'error_message' => (string)$response['error'],
                'create_time' => SYS_TIME,
            ]);
        }
    }

    /**
     * 获取启用的网站配置
     *
     * @return array
     */
    private function getEnabledSites() {
        $table = \Phpcmf\Service::M()->dbprefix('content_sync_site');
        return \Phpcmf\Service::M()->db->table($table)->where('status', 1)->orderBy('id asc')->get()->getResultArray();
    }

    /**
     * 安全JSON编码
     *
     * @param mixed $data
     *
     * @return string
     */
    private function jsonEncode($data) {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? '' : $json;
    }
}
