<?php namespace Phpcmf\App\Contentsync\Models;

/**
 * 内容同步日志模型
 */
class SyncLog extends \Phpcmf\Model
{
    /**
     * 写入同步日志
     *
     * @param array $data
     *
     * @return bool
     */
    public function add($data) {
        $table = $this->dbprefix('content_sync_log');
        return (bool)$this->db->table($table)->insert($data);
    }
}
