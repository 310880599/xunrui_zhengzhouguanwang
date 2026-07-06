<?php namespace Phpcmf\Model\Httpapi;

// api
class Http extends \Phpcmf\Model
{

    private $_fobj;
    private $_field;
    private $_myfield;

    /**
     * 验证码http请求授权
     */
    public function check_auth() {

        $v = (string)\Phpcmf\Service::L('input')->request('v');
        if ($v == '2') {
            // v2版本的验证方式
            $appid = (int)\Phpcmf\Service::L('input')->request('appid');
            $signature = (string)\Phpcmf\Service::L('input')->request('signature');
            $timestamp = (string)\Phpcmf\Service::L('input')->request('timestamp');

            // 格式验证
            if (!$appid) {
                \Phpcmf\Service::C()->_json(0, 'appid值为空');
            } elseif (!$signature) {
                \Phpcmf\Service::C()->_json(0, 'signature值为空');
            } elseif (!$timestamp) {
                \Phpcmf\Service::C()->_json(0, 'timestamp值为空');
            } elseif (!is_numeric($timestamp)) {
                \Phpcmf\Service::C()->_json(0, 'timestamp值不规范');
            } elseif (SYS_TIME - $timestamp > 600) {
                \Phpcmf\Service::C()->_json(0, 'timestamp值不匹配');
            }

            define('IS_API_HTTP_CODE', md5($appid.$signature));

            $appsecret = \Phpcmf\Service::C()->get_cache('api_auth', $appid);
            $string = md5($appid.$appsecret.$timestamp);
            if ($string != $signature) {
                \Phpcmf\Service::C()->_json(0, 'signature值不匹配');
            }

        } else {
            // 老版本的验证方式
            $appid = (int)\Phpcmf\Service::L('input')->request('appid');
            $appsecret = (string)\Phpcmf\Service::L('input')->request('appsecret');

            define('IS_API_HTTP_CODE', md5($appid.$appsecret));

            // 格式验证
            if (!$appid || !$appsecret) {
                \Phpcmf\Service::C()->_json(0, 'AppID和AppSecret值为空');
            }
            $cache = \Phpcmf\Service::C()->get_cache('api_auth', $appid);
            if (!$cache) {
                \Phpcmf\Service::C()->_json(0, 'AppID不存在');
            } elseif (strtoupper($cache) != strtoupper($appsecret)) {
                \Phpcmf\Service::C()->_json(0, 'AppID和AppSecret值不匹配');
            }
        }

        // 限IP地址
        $config = \Phpcmf\Service::C()->get_cache('api_db_auth', $appid);
        if (isset($config['ips']) && $config['ips']) {
            $rs = 0;
            $arr = explode(',', $config['ips']);
            $myip = \Phpcmf\Service::L('input')->ip_address();
            foreach ($arr as $ip) {
                if ($ip == $myip) {
                    $rs = 1;
                }
            }
            if (!$rs) {
                \Phpcmf\Service::C()->_json(0, 'AppID限制你的IP地址请求', $myip);
            }
        }

        // 验证账号授权并登录
        $auth = \Phpcmf\Service::L('input')->request('api_auth_code');  // 获取当前的登录记录
        if ($auth) {
            // 通过接口的post认证
            $uid = (int)\Phpcmf\Service::L('input')->get('api_auth_uid');
            if ($uid) {
                $member = \Phpcmf\Service::M('member')->get_member($uid);
                // 表示登录成功
                if (!$member) {
                    // 不存在的账号
                    \Phpcmf\Service::C()->_json(0, dr_lang('api_auth_uid 账号不存在'));
                } elseif (md5($member['password'].$member['salt']) != $auth) {
                    \Phpcmf\Service::C()->_json(0, dr_lang('登录超时，请重新登录'));
                }

                \Phpcmf\Service::C()->uid = $uid;
                \Phpcmf\Service::C()->member = $member;
            } else {
                \Phpcmf\Service::C()->uid = 0;
                \Phpcmf\Service::C()->member = [];
            }
        } else {
            \Phpcmf\Service::C()->uid = 0;
            \Phpcmf\Service::C()->member = [];
        }

        if (IS_POST && !$_POST) {
            $param = file_get_contents('php://input');
            if ($param) {
                $_POST = json_decode($param, true);
            }
        }
    }


    /**
     * 解析接口数据
     */
    public function get_api_data($data) {

        if (!$data) {
            return dr_return_data(0, dr_lang('接口数据不存在'));
        }

        switch ($data['type']) {

            case 0:
                return dr_return_data(1, 'ok', $data['data']);
                break;

            case 1:
                $rt = dr_string2array($data['data']);
                if (!$rt) {
                    return dr_return_data(0, dr_lang('接口数据内容格式必须是数组'));
                }

                return dr_return_data(1, 'ok', $rt);
                break;

            case 2:
                $return = null;
                if (is_file(dr_get_app_dir('httpapi').'Api/'.$data['file'])) {
                    require dr_get_app_dir('httpapi').'Api/'.$data['file'];
                } else {
                    return dr_return_data(0, dr_lang('接口程序文件【Api/'.$data['file'].'】不存在'));
                }
                return dr_return_data(1, 'ok', $return);
                break;

            case 3:
                $return = null;
                $param = trim(trim($data['list'], '{'), '}');
                $rt = \Phpcmf\Service::V()->list_tag($param);
                if (!$rt['return']) {
                    return dr_return_data(0, $rt['debug']);
                }

                $call = $data['call'];
                if ($call) {
                    // 回调函数
                    if (method_exists(\Phpcmf\Service::L('http'), $call)) {
                        $rt['return'] = \Phpcmf\Service::L('http')->$call($rt['return']);
                    } elseif (function_exists($call)) {
                        if (function_exists('dr_is_safe_function') && !dr_is_safe_function($call)) {
                            \Phpcmf\Service::C()->_json(0, '函数['.$call.']不安全，禁止在此处使用');
                        }
                        $rt['return'] = call_user_func($call, $rt['return']);
                    } else {
                        \Phpcmf\Service::C()->_json(0, '回调方法【'.$data['call'].'】未定义');
                    }
                }
                return dr_return_data(1, 'ok', $rt['return']);
                break;

            case 4:
                $return = null;
                if (!$data['sql']) {
                    return dr_return_data(0, 'SQL内容不存在');
                }

                $return = \Phpcmf\Service::M()->db->query($data['sql'])->getResultArray();

                $call = $data['call'];
                if ($call) {
                    // 回调函数
                    if (method_exists(\Phpcmf\Service::L('http'), $call)) {
                        $rt['return'] = \Phpcmf\Service::L('http')->$call($return);
                        return dr_return_data(1, 'ok', $rt['return']);
                    } elseif (function_exists($call)) {
                        if (function_exists('dr_is_safe_function') && !dr_is_safe_function($call)) {
                            \Phpcmf\Service::C()->_json(0, '函数['.$call.']不安全，禁止在此处使用');
                        }
                        $rt['return'] = call_user_func($call, $return);
                        return dr_return_data(1, 'ok', $rt['return']);
                    } else {
                        \Phpcmf\Service::C()->_json(0, '回调方法【'.$data['call'].'】未定义');
                    }
                }
                return dr_return_data(1, 'ok', $return);
                break;

            case 5:
                $api = [];
                $return = null;
                $param = '{php $api = [];}'.htmlspecialchars_decode($data['tpl']);
                $param = preg_replace('/\{api::([\w]+)\=(.+)\}/iU', '{php \$api[\$key][\'$1\']=$2;}', $param);
                $file = \Phpcmf\Service::V()->code2php($param);
                ob_start();
                require $file;
                ob_clean();
                return dr_return_data(1, 'ok', $api);
                break;

            case 6:
                $api = [];
                $return = null;
                $file = \Phpcmf\Service::V()->code2php('<?php '.htmlspecialchars_decode($data['php']).' ?>');
                ob_start();
                require $file;
                ob_clean();
                return dr_return_data(1, 'ok', $api);
                break;
        }

        return dr_return_data(0, dr_lang('未知接口类型'));
    }

    // 接口输出
    public function json($call, $code, $msg, $data) {

        if ($code <= 0) {
            return $data;
        }

        $cache = SYS_CACHE && SYS_CACHE_LIST ? SYS_CACHE_LIST * 3600 : 0;
        if ($cache) {
            $cache_name = md5(dr_now_url().dr_array2string($data));
            $cache_data = \Phpcmf\Service::L('cache')->get_data($cache_name);
            if ($cache_data) {
                return $cache_data;
            }
        }

        switch ($call) {

            case 'module_list':
                // 模块内容列表页面
                $config = \Phpcmf\Service::C()->get_cache('api_config', 'module_list', \Phpcmf\Service::C()->module['dirname']);
                $this->_format_init(
                    $config,
                    dr_array22array(\Phpcmf\Service::C()->module['field'], \Phpcmf\Service::C()->module['category_data_field'])
                );
                $rt = [];
                foreach ($data['list'] as $i => $r) {
                    $rt[$i] = $this->_format_value($r);
                }

                break;

            case 'module_show':
                // 模块内容详情页面
                $config = \Phpcmf\Service::C()->get_cache('api_config', 'module_show', \Phpcmf\Service::C()->module['dirname']);
                if (isset($config['hits']) && $config['hits']) {
                    $data['hits'] = (int)$data['hits'] + 1;
                    // 更新主表
                    \Phpcmf\Service::M()->db->table(dr_module_table_prefix(\Phpcmf\Service::C()->module['dirname']))
                        ->where('id', $data['id'])
                        ->set('hits', $data['hits'])->update();
                }

                $this->_format_init(
                    $config,
                    dr_array22array(\Phpcmf\Service::C()->module['field'], \Phpcmf\Service::C()->module['category_data_field'])
                );
                $rt = $this->_format_value($data);

                break;

            case 'category':
                // 栏目页面
                $config = \Phpcmf\Service::C()->get_cache('api_config', 'category', \Phpcmf\Service::C()->module['dirname']);
                $this->_format_init(
                    $config,
                    \Phpcmf\Service::C()->module['field']
                );

                $rt = [];
                foreach ($data as $i => $r) {
                    $rt[$i] = $this->_format_value($r);
                }

                break;

            case 'site':
                // 网站信息接口
                $rt = [
                    'SITE_NAME' => $data['config']['SITE_NAME'],
                    'SITE_ICP' => $data['config']['SITE_ICP'],
                    'SITE_LOGO' => dr_get_file($data['config']['logo']),
                ];
                if ($data['param']) {
                    // 字段处理
                    $this->_format_init(
                        \Phpcmf\Service::C()->get_cache('api_config', 'site_'.SITE_ID),
                        \Phpcmf\Service::M('field')->get_mysite_field(SITE_ID)
                    );
                    $rt = dr_array22array($rt, $this->_format_value($data['param']));
                }

                $config = \Phpcmf\Service::C()->get_cache('api_config', 'site_'.SITE_ID);

                break;

            default:
                // 其他回调
                if (method_exists(\Phpcmf\Service::L('http'), $call)) {
                    return \Phpcmf\Service::L('http')->$call($data);
                } elseif (function_exists($call)) {
                    if (function_exists('dr_is_safe_function') && !dr_is_safe_function($call)) {
                        \Phpcmf\Service::C()->_json(0, '函数['.$call.']不安全，禁止在此处使用');
                    }
                    return call_user_func($call, $data);
                } else {
                    echo dr_array2string(dr_return_data(0, '回调方法(' . $call . ')未定义'));exit;
                }
                break;
        }

        // 处理数据
        if ($config['code']) {
            extract($data, EXTR_SKIP);
            $api = $rt;
            $file = \Phpcmf\Service::V()->code2php(htmlspecialchars_decode($config['code']));
            require $file;
            $rt = $api;
            ob_clean();
        }
        if ($config['call']) {
            $call2 = $config['call'];
            // 回调函数
            if (method_exists(\Phpcmf\Service::L('http'), $call2)) {
                $rt = \Phpcmf\Service::L('http')->$call2($rt, $data);
            } elseif (function_exists($call2)) {
                if (function_exists('dr_is_safe_function') && !dr_is_safe_function($call2)) {
                    \Phpcmf\Service::C()->_json(0, '函数['.$call2.']不安全，禁止在此处使用');
                }
                $rt = call_user_func($call2, $rt, $data);
            } else {
                \Phpcmf\Service::C()->_json(0, '回调方法【'.$call2.'】未定义');
            }
        }

        if ($cache) {
            \Phpcmf\Service::L('cache')->set_data($cache_name, $rt, $cache);
        }

        return $rt;
    }

    private function _format_init($config, $field) {
        $this->_fobj = \Phpcmf\Service::L('Function_module', 'httpapi');
        $this->_field = $field;
        if (!$config) {
            $this->_myfield = [];
        } else {
            $this->_myfield = $config['field'];
        }
    }

    private function _format_value($data) {
        $rt = [];
        if (!$this->_myfield) {
            foreach ($data as $n => $value) {
                $rt[$n] = $this->_format_data('auto', $n, $value);
            }
        } else {
            foreach ($this->_myfield as $n => $t) {
                if ($t['use']) {
                    $value = $data[$n];
                    if ($n == 'catname') {
                        $value = dr_cat_value(\Phpcmf\Service::C()->module['mid'], $data['catid'], 'name');
                    }
                    $rt[$n] = $this->_format_data($t['func'], $n, $value);
                }
            }
        }
        return $rt;
    }

    private function _format_data($func, $n, $value) {
        if (!$func) {
            $dfunc = [
                'Uid' => 'uid',
                'File' => 'dr_get_file',
                'Files' => 'dr_get_files',
                'Image' => 'image',
                'Score' => 'score',
                'Date' => 'datetime',
                'Radio' => 'radio_name',
                'Select' => 'select_name',
                'Checkbox' => 'checkbox_name',
                'Linkage' => 'linkage_name',
                'Linkages' => 'linkages_name',
            ];
            if (isset($this->_field[$n]['fieldtype']) && isset($dfunc[$this->_field[$n]['fieldtype']])
                && $dfunc[$this->_field[$n]['fieldtype']]) {
                $func = $dfunc[$this->_field[$n]['fieldtype']];
            } else {
                $func = '';
            }
        }
        if (isset($this->_field[$n]['fieldtype']) && $this->_field[$n]['fieldtype']
            && !is_array($value)
            && stripos($this->_field[$n]['fieldtype'], 'editor') !== false) {
            if (!$func) {
                $func = 'dr_code2html';
            }
            if (defined('SYS_API_REL') && SYS_API_REL) {
                // 相对路径
                $func.= '|dr_text_rel';
            } else {
                $func.= '|dr_text_full';
            }
        }
        if ($func) {
            if (method_exists($this->_fobj, $func)) {
                $value = call_user_func_array([$this->_fobj, $func], [$n, $value, ['field' => $this->_field]]);
            } else {
                $arr = explode('|', $func);
                $arr = array_unique($arr);
                foreach ($arr as $a) {
                    if (function_exists($a)) {
                        if (function_exists('dr_is_safe_function') && !dr_is_safe_function($a)) {
                            return '函数['.$a.']不安全，禁止在此处使用';
                        }
                        $value = call_user_func_array($a, [$value]);
                    }
                }
            }
        }
        return $value;
    }

    // 缓存
    public function cache() {

        $table = $this->dbprefix('api_config');
        if (!\Phpcmf\Service::M()->db->tableExists($table)) {
            \Phpcmf\Service::M()->query(dr_format_create_sql('CREATE TABLE IF NOT EXISTS `'.$table.'` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT \'Id\',
  `name` varchar(50) NOT NULL,
  `value` mediumtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'api属性配置表\';'));
        }

        // api 授权码
        $data = $this->table('api_auth')->where('disabled', 0)->getAll();
        $cache = $config = [];
        if ($data) {
            foreach ($data as $t) {
                $cache[$t['id']] = $t['secret'];
                $val = dr_string2array($t['setting']);
                $val['table'] = explode(',', $val['table']);
                $config[$t['id']] = $val;
            }
        }

        \Phpcmf\Service::L('cache')->set_file('api_auth', $cache);
        \Phpcmf\Service::L('cache')->set_file('api_db_auth', $config);

        // api 返回数据
        $data = $this->table('api_http')->where('disabled', 0)->getAll();
        $cache = [];
        if ($data) {
            foreach ($data as $t) {
                $cache[$t['id']] = dr_string2array($t['content']);
            }
        }

        \Phpcmf\Service::L('cache')->set_file('api_http', $cache);

        // api 配置
        $data = $this->table('api_config')->getAll();
        $cache = [];
        if ($data) {
            foreach ($data as $t) {
                $cache[$t['name']] = dr_string2array($t['value']);
            }
        }

        if (!IS_USE_MODULE) {
            $this->db->table('admin_menu')->where('uri', 'httpapi/category/index')->delete();
            $this->db->table('admin_menu')->where('uri', 'httpapi/module/index')->delete();
        }

        \Phpcmf\Service::L('cache')->set_file('api_config', $cache);

    }

}