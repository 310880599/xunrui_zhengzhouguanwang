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
     * 诊断日志文件
     */
    const TRACE_LOG_FILE = WRITEPATH.'logs/contentsync_trace.log';

    /**
     * 钩子回调入口
     *
     * @param array $data
     * @param array $old
     */
    public function handleModuleContentAfter($data, $old = []) {
        self::trace('service_enter', [
            'time' => date('Y-m-d H:i:s'),
        ]);

        $module = \Phpcmf\Service::C()->module;
        $service_dirname_raw = is_array($module) && isset($module['dirname']) ? (string)$module['dirname'] : '';
        $dirname = strtolower($service_dirname_raw);
        $mod_dir_defined = defined('MOD_DIR');
        $mod_dir_value = $mod_dir_defined ? (string)MOD_DIR : '';

        self::trace('module_detect', [
            'mod_dir_defined' => $mod_dir_defined,
            'mod_dir' => $mod_dir_value,
            'service_module_is_array' => is_array($module),
            'service_module_has_dirname' => is_array($module) && isset($module['dirname']),
            'service_module_dirname' => $service_dirname_raw,
            'dirname' => $dirname,
        ]);

        // 当前阶段只处理新闻中心模块
        if ($dirname !== 'xinwenzhongxin') {
            self::trace('return_module_mismatch', [
                'dirname' => $dirname,
            ]);
            return;
        }

        $main = isset($data[1]) && is_array($data[1]) ? $data[1] : [];
        $extend = isset($data[0]) && is_array($data[0]) ? $data[0] : [];
        self::trace('data_parsed', [
            'main_id' => isset($main['id']) ? (int)$main['id'] : null,
            'main_status' => isset($main['status']) ? (int)$main['status'] : null,
            'main_title_empty' => empty($main['title']),
        ]);
        if (!$main || empty($main['id'])) {
            self::trace('return_invalid_main', [
                'main_exists' => !empty($main),
                'main_id_exists' => isset($main['id']),
            ]);
            return;
        }

        // 仅在正式发布状态下发送
        if ((int)($main['status'] ?? 0) !== 9) {
            self::trace('return_status', [
                'status' => isset($main['status']) ? (int)$main['status'] : null,
            ]);
            return;
        }

        // 仅在新增发布时发送，修改内容不发送（兼容审核通过新增）
        self::trace('new_check_before', [
            'old_empty' => empty($old),
            'old_is_array' => is_array($old),
            'old_keys' => is_array($old) ? array_keys($old) : [],
            'old_id_exists' => is_array($old) && array_key_exists('id', $old),
            'old_id' => is_array($old) && array_key_exists('id', $old) ? $old['id'] : null,
            'old_verify_isnew_exists' => is_array($old) && isset($old['verify']) && is_array($old['verify']) && array_key_exists('isnew', $old['verify']),
            'old_verify_isnew' => is_array($old) && isset($old['verify']) && is_array($old['verify']) && array_key_exists('isnew', $old['verify']) ? $old['verify']['isnew'] : null,
        ]);
        $is_new = $this->isNewPublishedContent($old);
        self::trace('new_check_result', [
            'is_new' => $is_new,
        ]);
        if (!$is_new) {
            self::trace('return_not_new', []);
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
        $payload['thumb_data'] = $this->getThumbAttachmentData($main['thumb'] ?? '');
        self::trace('payload_ready', [
            'content_id' => (int)$main['id'],
            'title' => (string)($main['title'] ?? ''),
            'catid' => (int)($main['catid'] ?? 0),
            'content_length' => strlen((string)$payload['content']),
        ]);

        $sites = $this->getEnabledSites();
        self::trace('sites_loaded', [
            'enabled_site_count' => is_array($sites) ? count($sites) : 0,
        ]);
        if (!$sites) {
            self::trace('return_no_sites', []);
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
                $site_id = isset($site['id']) ? (int)$site['id'] : 0;
                self::trace('site_enter', [
                    'site_id' => $site_id,
                    'site_name' => $site_name,
                    'api_url' => $site_url,
                ]);

                // 最小化防重复：同内容在同站点已有成功记录时，不再重复发送
                $has_success = $this->hasSuccessfulSync((int)$main['id'], $dirname, $site_name);
                self::trace('duplicate_check', [
                    'content_id' => (int)$main['id'],
                    'site_name' => $site_name,
                    'has_success' => $has_success,
                ]);
                if ($has_success) {
                    self::trace('return_duplicate_site', [
                        'content_id' => (int)$main['id'],
                        'site_name' => $site_name,
                    ]);
                    continue;
                }

                self::trace('http_before', [
                    'site_name' => $site_name,
                    'api_url' => $site_url,
                ]);
                $response = $client->postJson($site_url, $payload, $headers, 10);
                $status = !empty($response['success']) ? 1 : 0;
                $safe_headers = $this->maskHeadersForLog($headers);
                $safe_response_body = $this->sanitizeTextForLog((string)($response['body'] ?? ''));
                $safe_error = $this->sanitizeTextForLog((string)($response['error'] ?? ''));
                self::trace('http_after', [
                    'site_name' => $site_name,
                    'success' => !empty($response['success']),
                    'http_code' => isset($response['http_code']) ? (int)$response['http_code'] : 0,
                    'error' => $safe_error,
                ]);

                self::trace('sync_log_before', [
                    'site_name' => $site_name,
                    'content_id' => (int)$main['id'],
                    'status' => $status,
                ]);
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
                self::trace('sync_log_after', [
                    'site_name' => $site_name,
                    'content_id' => (int)$main['id'],
                    'status' => $status,
                ]);
            } catch (\Throwable $e) {
                // 单站点异常隔离，不影响其他站点
                self::trace('http_after', [
                    'site_name' => (string)($site['name'] ?? ''),
                    'success' => false,
                    'http_code' => 0,
                    'error' => $this->sanitizeTextForLog($e->getMessage()),
                ]);
                self::trace('sync_log_before', [
                    'site_name' => (string)($site['name'] ?? ''),
                    'content_id' => (int)$main['id'],
                    'status' => 0,
                ]);
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
                self::trace('sync_log_after', [
                    'site_name' => (string)($site['name'] ?? ''),
                    'content_id' => (int)$main['id'],
                    'status' => 0,
                ]);
            }
        }
    }

    /**
     * 写入临时诊断日志（失败不影响主流程）
     *
     * @param string $stage
     * @param array  $context
     *
     * @return void
     */
    public static function trace($stage, array $context = []) {
        try {
            $record = [
                'time' => date('Y-m-d H:i:s'),
                'stage' => (string)$stage,
                'context' => self::normalizeTraceContext($context),
            ];
            $line = json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($line === false) {
                return;
            }
            @file_put_contents(self::TRACE_LOG_FILE, $line.PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // 临时诊断日志失败时静默处理，避免影响发布流程
        }
    }

    /**
     * 诊断上下文最小化与脱敏
     *
     * @param array $context
     *
     * @return array
     */
    private static function normalizeTraceContext(array $context) {
        $safe = [];
        foreach ($context as $key => $value) {
            $k = strtolower((string)$key);
            if (strpos($k, 'api_key') !== false || strpos($k, 'authorization') !== false || strpos($k, 'cookie') !== false) {
                continue;
            }
            if ($k === 'content') {
                continue;
            }

            if (is_string($value)) {
                $safe[$key] = self::sanitizeTraceText($value);
            } elseif (is_array($value)) {
                $safe[$key] = self::normalizeTraceContext($value);
            } elseif (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
                $safe[$key] = $value;
            } else {
                $safe[$key] = (string)$value;
            }
        }

        return $safe;
    }

    /**
     * 诊断文本脱敏
     *
     * @param string $text
     *
     * @return string
     */
    private static function sanitizeTraceText($text) {
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

        return (string)$text;
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
     * 根据缩略图附件ID提取附件信息
     *
     * @param mixed $thumb
     *
     * @return array|null
     */
    private function getThumbAttachmentData($thumb) {
        $thumb_value = trim((string)$thumb);
        if ($thumb_value === '' || !ctype_digit($thumb_value)) {
            return null;
        }

        $thumb_id = (int)$thumb_value;
        if ($thumb_id <= 0) {
            return null;
        }

        try {
            $table = \Phpcmf\Service::M()->dbprefix('attachment_data');
            $row = \Phpcmf\Service::M()->db->table($table)
                ->select('id,filename,fileext,filesize,attachment,remote,attachinfo,inputtime')
                ->where('id', $thumb_id)
                ->get()
                ->getRowArray();
        } catch (\Throwable $e) {
            return null;
        }

        if (!$row) {
            return null;
        }

        $attachment = isset($row['attachment']) ? (string)$row['attachment'] : '';
        $attachment_url = '';
        if ($attachment !== '') {
            if (function_exists('dr_get_file_url')) {
                $attachment_url = (string)dr_get_file_url($row);
            } elseif (function_exists('dr_get_file')) {
                $attachment_url = (string)dr_get_file($thumb_id, 1);
            } elseif (defined('SYS_UPLOAD_URL')) {
                $attachment_url = rtrim((string)SYS_UPLOAD_URL, '/').'/'.ltrim($attachment, '/');
            } elseif (defined('SITE_URL')) {
                $attachment_url = rtrim((string)SITE_URL, '/').'/uploadfile/'.ltrim($attachment, '/');
            }
        }

        return [
            'id' => isset($row['id']) ? (int)$row['id'] : 0,
            'filename' => isset($row['filename']) ? (string)$row['filename'] : '',
            'fileext' => isset($row['fileext']) ? (string)$row['fileext'] : '',
            'filesize' => isset($row['filesize']) ? (int)$row['filesize'] : 0,
            'attachment' => $attachment,
            'url' => $attachment_url,
            'remote' => isset($row['remote']) ? (int)$row['remote'] : 0,
            'attachinfo' => isset($row['attachinfo']) ? (string)$row['attachinfo'] : '',
            'inputtime' => isset($row['inputtime']) ? (int)$row['inputtime'] : 0,
        ];
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
