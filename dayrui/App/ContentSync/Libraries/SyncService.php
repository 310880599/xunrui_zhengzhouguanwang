<?php namespace Phpcmf\App\Contentsync\Libraries;

require_once dr_get_app_dir('contentsync').'Libraries/HttpClient.php';
require_once dr_get_app_dir('contentsync').'Models/SyncLog.php';

use Phpcmf\App\Contentsync\Models\SyncLog;

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

        $main = isset($data[1]) && is_array($data[1]) ? $data[1] : [];
        $extend = isset($data[0]) && is_array($data[0]) ? $data[0] : [];
        if (!$main || empty($main['id'])) {
            return;
        }

        // 仅在正式发布状态下发送
        if ((int)($main['status'] ?? 0) !== 9) {
            return;
        }

        // 仅在新增发布时发送，修改内容不发送（兼容审核通过新增）
        if (!$this->isNewPublishedContent($old)) {
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
            try {
                $site_name = (string)($site['name'] ?? '');
                $site_url = (string)($site['api_url'] ?? '');
                $headers = [
                    'X-API-KEY' => (string)($site['api_key'] ?? ''),
                ];

                // 最小化防重复：同内容在同站点已有成功记录时，不再重复发送
                if ($this->hasSuccessfulSync((int)$main['id'], $dirname, $site_name)) {
                    continue;
                }

                $response = $client->postJson($site_url, $payload, $headers, 10);
                $status = !empty($response['success']) ? 1 : 0;
                $safe_headers = $this->maskHeadersForLog($headers);
                $safe_response_body = $this->sanitizeTextForLog((string)($response['body'] ?? ''));
                $safe_error = $this->sanitizeTextForLog((string)($response['error'] ?? ''));

                $this->writeSyncLog($log_model, [
                    'content_id' => (int)$main['id'],
                    'module' => $dirname,
                    'title' => (string)$main['title'],
                    'target_site' => $site_name,
                    'status' => $status,
                    'request_data' => $this->jsonEncode([
                        'url' => $site_url,
                        'headers' => $safe_headers,
                        'payload' => $payload,
                    ]),
                    'response_data' => $safe_response_body,
                    'error_message' => $safe_error,
                    'create_time' => SYS_TIME,
                ]);
            } catch (\Throwable $e) {
                // 单站点异常隔离，不影响其他站点
                $this->writeSyncLog($log_model, [
                    'content_id' => (int)$main['id'],
                    'module' => $dirname,
                    'title' => (string)$main['title'],
                    'target_site' => (string)($site['name'] ?? ''),
                    'status' => 0,
                    'request_data' => $this->jsonEncode([
                        'url' => (string)($site['api_url'] ?? ''),
                        'headers' => $this->maskHeadersForLog([
                            'X-API-KEY' => (string)($site['api_key'] ?? ''),
                        ]),
                        'payload' => $payload,
                    ]),
                    'response_data' => '',
                    'error_message' => $this->sanitizeTextForLog($e->getMessage()),
                    'create_time' => SYS_TIME,
                ]);
            }
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
     * 判断当前回调是否属于新增内容发布
     *
     * @param mixed $old
     *
     * @return bool
     */
    private function isNewPublishedContent($old) {
        if (!$old || !is_array($old)) {
            return true;
        }

        // 审核通过新增内容：old中携带 verify.isnew
        if (isset($old['verify']['isnew'])) {
            return (bool)$old['verify']['isnew'];
        }

        return empty($old['id']);
    }

    /**
     * 判断是否已有成功发送记录
     *
     * @param int    $content_id
     * @param string $module
     * @param string $target_site
     *
     * @return bool
     */
    private function hasSuccessfulSync($content_id, $module, $target_site) {
        $table = \Phpcmf\Service::M()->dbprefix('content_sync_log');
        $count = \Phpcmf\Service::M()->db->table($table)
            ->where('content_id', (int)$content_id)
            ->where('module', (string)$module)
            ->where('target_site', (string)$target_site)
            ->where('status', 1)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * 日志写入并处理失败回退
     *
     * @param SyncLog $log_model
     * @param array   $data
     *
     * @return void
     */
    private function writeSyncLog(SyncLog $log_model, array $data) {
        $rt = false;
        try {
            $rt = $log_model->add($data);
        } catch (\Throwable $e) {
            log_message('error', '[contentsync] sync log exception: '.$e->getMessage());
        }

        if (!$rt) {
            log_message(
                'error',
                '[contentsync] sync log insert failed, content_id='.(int)($data['content_id'] ?? 0).', target='.(string)($data['target_site'] ?? '')
            );
        }
    }

    /**
     * Header日志脱敏
     *
     * @param array $headers
     *
     * @return array
     */
    private function maskHeadersForLog(array $headers) {
        $safe_headers = [];
        foreach ($headers as $name => $value) {
            $header_name = (string)$name;
            $safe_headers[$header_name] = $this->isSensitiveHeaderName($header_name)
                ? '***'
                : (string)$value;
        }

        return $safe_headers;
    }

    /**
     * 判断是否为敏感Header
     *
     * @param string $name
     *
     * @return bool
     */
    private function isSensitiveHeaderName($name) {
        static $sensitive = [
            'x-api-key',
            'authorization',
            'proxy-authorization',
            'x-auth-token',
            'x-access-token',
        ];

        return in_array(strtolower((string)$name), $sensitive, true);
    }

    /**
     * 文本日志脱敏
     *
     * @param string $text
     *
     * @return string
     */
    private function sanitizeTextForLog($text) {
        if ($text === '') {
            return '';
        }

        $text = preg_replace(
            '/(X-API-KEY|Authorization|Proxy-Authorization|X-Auth-Token|X-Access-Token)\s*[:=]\s*([^\r\n]+)/i',
            '$1: ***',
            $text
        );
        $text = preg_replace(
            '/(Cookie|Set-Cookie)\s*[:=]\s*([^\r\n]+)/i',
            '$1: [MASKED]',
            $text
        );
        $text = preg_replace('/[A-Za-z]:\\\\[^\s"\']+/u', '[PATH]', $text);
        $text = preg_replace('/\/(?:home|var|www|data|usr|opt)\/[^\s"\']+/u', '[PATH]', $text);

        return (string)$text;
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
