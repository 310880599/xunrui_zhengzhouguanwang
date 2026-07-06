<?php

if (IS_USE_MODULE && !is_file(dr_get_app_dir('module').'Libraries/Category.php')) {
    $this->_admin_msg(0, '内容系统插件需要升级到最新版本');
}