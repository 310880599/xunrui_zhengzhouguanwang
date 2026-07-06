<?php namespace Phpcmf\Controllers;

class Home extends \Phpcmf\App
{

    public function index() {

        $keyword = dr_safe_keyword($_GET['keyword']);
        
        $different = (int)$_GET['different'];
        
        $config = \Phpcmf\Service::M('app')->get_config(APP_DIR);

        if (!defined('IS_NOT_301')
            && !IS_API_HTTP && $config['urlrule'] && strpos(FC_NOW_URL, 'index.php') !== false) {
            !$keyword && $keyword = 0;
            dr_redirect(str_replace( ['{keyword}', '{page}'], [$keyword, '[page]'], $config['urlrule']), 'auto', 301);exit;
        }

        if (!isset($config['mid']) || !$config['mid']) {
            $this->_msg(0, '没有设置可用的搜索模块');
        } elseif (!$keyword) {
            $this->_msg(0, '搜索关键词不能为空');
        }
        
        
        if($different == 1){
            
             
            
        }else if($different == 2){
            
            $config['mid'] = ["chanpinzhongxin"];
            
        }else{
            
            
            
        }
        

        \Phpcmf\Service::V()->assign([
            'mids' => implode(',', $config['mid']),
            'keyword' => $keyword,
            'urlrule' => $config['pagerule'] ? str_replace( ['{keyword}', '{page}'], [$keyword, '[page]'], $config['pagerule']) : WEB_DIR.'index.php?s=so&keyword='.$keyword.'&different='.$different.'&page=[page]',
            'meta_title' => '全站搜索',
            'meta_keywords' => $keyword,
            'meta_description' => '',
            'different' => $different,
            'app_dir' => APP_DIR,
        ]);
        \Phpcmf\Service::V()->display('so.html');
    }

}
