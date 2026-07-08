<?php namespace Phpcmf\App\ContentSync\Libraries;

/**
 * 内容同步HTTP客户端
 */
class HttpClient
{
    /**
     * 发送JSON POST请求
     *
     * @param string $url
     * @param array  $data
     * @param array  $headers
     * @param int    $timeout
     *
     * @return array
     */
    public function postJson($url, $data, $headers = [], $timeout = 10) {

        $result = [
            'success' => false,
            'http_code' => 0,
            'body' => '',
            'error' => '',
        ];

        try {
            $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($payload === false) {
                $payload = '{}';
            }
            $http_headers = [
                'Content-Type: application/json; charset=utf-8',
                'Accept: application/json',
                'Content-Length: '.strlen($payload),
            ];

            foreach ($headers as $k => $v) {
                $http_headers[] = $k.': '.$v;
            }

            if (function_exists('curl_init')) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $http_headers);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                $body = curl_exec($ch);
                $error = curl_error($ch);
                $http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                $result['body'] = is_string($body) ? $body : '';
                $result['http_code'] = $http_code;

                if ($error) {
                    $result['error'] = $error;
                } else {
                    $result['success'] = ($http_code >= 200 && $http_code < 300);
                }
            } else {
                $opts = [
                    'http' => [
                        'method' => 'POST',
                        'header' => implode("\r\n", $http_headers),
                        'content' => $payload,
                        'timeout' => $timeout,
                        'ignore_errors' => true,
                    ],
                ];
                $context = stream_context_create($opts);
                $body = @file_get_contents($url, false, $context);
                $result['body'] = is_string($body) ? $body : '';
                $result['success'] = $body !== false;
                $result['http_code'] = $result['success'] ? 200 : 0;
                if ($body === false) {
                    $result['error'] = '请求失败，且当前环境不支持curl扩展';
                }
            }
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }
}
