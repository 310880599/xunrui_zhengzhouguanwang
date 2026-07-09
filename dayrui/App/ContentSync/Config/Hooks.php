<?php

try {
    require_once dr_get_app_dir('contentsync').'Libraries/SyncService.php';
    \Phpcmf\App\Contentsync\Libraries\SyncService::trace('hooks_file_loaded', [
        'time' => date('Y-m-d H:i:s'),
        'app' => 'contentsync',
    ]);
} catch (\Throwable $e) {
    file_put_contents(
        WRITEPATH.'logs/contentsync_debug.log',
        date('Y-m-d H:i:s').' '.$e->getMessage().PHP_EOL.$e->getTraceAsString().PHP_EOL,
        FILE_APPEND
    );
    return;
}


// 内容发布完成后触发同步发送
\Phpcmf\Hooks::app_on('contentsync', 'module_content_after', function ($data, $old = []) {
    try {
        \Phpcmf\App\Contentsync\Libraries\SyncService::trace('hook_enter', [
            'time' => date('Y-m-d H:i:s'),
            'mod_dir_defined' => defined('MOD_DIR'),
            'mod_dir' => defined('MOD_DIR') ? (string)MOD_DIR : '',
            'data_is_array' => is_array($data),
            'data_0_exists' => is_array($data) && array_key_exists(0, $data),
            'data_0_is_array' => is_array($data) && isset($data[0]) && is_array($data[0]),
            'data_1_exists' => is_array($data) && array_key_exists(1, $data),
            'data_1_is_array' => is_array($data) && isset($data[1]) && is_array($data[1]),
            'data_1_id' => is_array($data) && isset($data[1]) && is_array($data[1]) && array_key_exists('id', $data[1]) ? $data[1]['id'] : null,
            'data_1_status' => is_array($data) && isset($data[1]) && is_array($data[1]) && array_key_exists('status', $data[1]) ? $data[1]['status'] : null,
            'data_1_title' => is_array($data) && isset($data[1]) && is_array($data[1]) && array_key_exists('title', $data[1]) ? (string)$data[1]['title'] : '',
            'old_is_array' => is_array($old),
            'old_keys' => is_array($old) ? array_keys($old) : [],
        ]);

        $service = new \Phpcmf\App\Contentsync\Libraries\SyncService();
        $service->handleModuleContentAfter($data, $old);
    } catch (\Throwable $e) {
        // 失败仅记录日志，不能影响内容发布流程
        log_message('error', '[contentsync] hook error: '.$e->getMessage());
    }
});
