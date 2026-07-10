<?php namespace Phpcmf\App\Contentsync\Libraries;

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
            $scheme = strtolower((string)parse_url((string)$url, PHP_URL_SCHEME));
            $is_https = $scheme === 'https';
            $host = strtolower((string)parse_url((string)$url, PHP_URL_HOST));
            $port = (int)parse_url((string)$url, PHP_URL_PORT);
            if (!$port) {
                $port = $is_https ? 443 : 80;
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
                if ($is_https) {
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                }
                if (
                    $host === 'www.hnyugong.com'
                    && $is_https
                    && defined('CURLOPT_RESOLVE')
                ) {
                    curl_setopt($ch, CURLOPT_RESOLVE, [
                        $host.':'.$port.':172.17.163.109',
                    ]);
                }

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
                if ($is_https) {
                    $opts['ssl'] = [
                        'verify_peer' => true,
                        'verify_peer_name' => true,
                        'allow_self_signed' => false,
                    ];
                }
                $context = stream_context_create($opts);
                $body = @file_get_contents($url, false, $context);
                $result['body'] = is_string($body) ? $body : '';
                $result['http_code'] = $this->parseHttpCodeFromHeaders(isset($http_response_header) ? $http_response_header : []);
                $result['success'] = $body !== false
                    && ($result['http_code'] === 0 || ($result['http_code'] >= 200 && $result['http_code'] < 300));
                if ($body === false) {
                    $error = error_get_last();
                    $result['error'] = isset($error['message']) ? (string)$error['message'] : '请求失败，且当前环境不支持curl扩展';
                }
            }
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * 从响应头解析HTTP状态码
     *
     * @param array $headers
     *
     * @return int
     */
    private function parseHttpCodeFromHeaders(array $headers) {
        if (!$headers) {
            return 0;
        }

        $http_code = 0;
        foreach ($headers as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#i', (string)$line, $match)) {
                $http_code = (int)$match[1];
            }
        }

        return $http_code;
    }
}
