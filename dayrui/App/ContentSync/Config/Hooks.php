<?php

@file_put_contents(
    '/tmp/contentsync_hook_registered.log',
    'contentsync hook registered'.PHP_EOL,
    FILE_APPEND
);

// 内容发布完成后触发同步发送
\Phpcmf\Hooks::app_on('contentsync', 'module_content_after', function ($data, $old = []) {
    $content_id = 0;
    if (is_array($data) && isset($data[1]) && is_array($data[1]) && isset($data[1]['id'])) {
        $content_id = (int)$data[1]['id'];
    }

    $module_dir = defined('MOD_DIR') ? (string)MOD_DIR : '';
    if ($module_dir === '') {
        $module = \Phpcmf\Service::C()->module;
        if (is_array($module) && isset($module['dirname'])) {
            $module_dir = (string)$module['dirname'];
        }
    }

    @file_put_contents(
        '/tmp/contentsync_hook_callback.log',
        date('Y-m-d H:i:s').' callback id='.$content_id.' module='.$module_dir.PHP_EOL,
        FILE_APPEND
    );

    try {
        require_once dr_get_app_dir('contentsync').'Libraries/SyncService.php';
        $service = new \Phpcmf\App\Contentsync\Libraries\SyncService();
        $service->handleModuleContentAfter($data, $old);
    } catch (\Throwable $e) {
        // 失败仅记录日志，不能影响内容发布流程
        log_message('error', '[contentsync] '.$e->getMessage());
    }
});
