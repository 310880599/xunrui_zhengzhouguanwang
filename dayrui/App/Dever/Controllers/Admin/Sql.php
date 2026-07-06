<?php namespace Phpcmf\Controllers\Admin;


class Sql extends \Phpcmf\Common
{

	public function __construct() {
		parent::__construct();
		\Phpcmf\Service::V()->assign('menu', \Phpcmf\Service::M('auth')->_admin_menu(
			[
				'执行SQL' => ['dever/sql/index', 'fa fa-code'],
			]
		));
	}

	public function index() {



        if (IS_POST) {
            $msg = '';
            $sqls = trim(\Phpcmf\Service::L('input')->post('sql'));
            $replace = [];
            $replace[0][] = '{dbprefix}';
            $replace[1][] = \Phpcmf\Service::M()->db->DBPrefix;
            $sql_data = explode(';SQL_FINECMS_EOL', trim(str_replace(array(PHP_EOL, chr(13), chr(10)), 'SQL_FINECMS_EOL', str_replace($replace[0], $replace[1], $sqls))));


            if ($sql_data) {
                foreach($sql_data as $query){
                    if (!$query) {
                        continue;
                    }
                    $ret = '';
                    $queries = explode('SQL_FINECMS_EOL', trim($query));
                    foreach($queries as $query) {
                        $ret.= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
                    }
                    $sql = trim($ret);
                    if (!$sql) {
                        continue;
                    }
                    // 执行语句
                    $db = \Phpcmf\Service::M()->db->query($sql);
                    if (!$db) {
                        $rt = \Phpcmf\Service::M()->db->error();
                        $this->_json(0, '查询错误：'.$rt['message']);
                    }
                }
                $this->_json(1, $msg ? $msg : dr_lang('执行完成'));
            } else {
                $this->_json(0, dr_lang('不存在的SQL语句'));
            }
        }

        \Phpcmf\Service::V()->assign([
            'sql_cache' => \Phpcmf\Service::L('File')->get_sql_cache(),
        ]);
        \Phpcmf\Service::V()->display('db_sql.html');
    }
}
