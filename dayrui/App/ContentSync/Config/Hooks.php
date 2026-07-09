<?php

require_once dr_get_app_dir('contentsync').'Libraries/SyncService.php';

// 内容发布完成后触发同步发送
\Phpcmf\Hooks::app_on('contentsync', 'module_content_after', function ($data, $old = []) {
    try {
        $service = new \Phpcmf\App\Contentsync\Libraries\SyncService();
        $service->handleModuleContentAfter($data, $old);
    } catch (\Throwable $e) {
        // 失败仅记录日志，不能影响内容发布流程
        log_message('error', '[contentsync] hook error: '.$e->getMessage());
    }
});
