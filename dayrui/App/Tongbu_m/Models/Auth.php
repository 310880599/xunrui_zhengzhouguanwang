<?php namespace Phpcmf\Model\Tongbu_m;

// 权限验证
class Auth extends \Phpcmf\Model
{

    public function is_bottom_auth($mid) {

        $config = \Phpcmf\Service::M('app')->get_config('tongbu_m');
        if (dr_in_array($mid, $config['module'])) {
            return 1;
        }

        return 0;
    }

    public function is_link_auth($mid) {

        $config = \Phpcmf\Service::M('app')->get_config('tongbu_m');
        if (dr_in_array($mid, $config['module'])) {
            return 1;
        }

        return 0;
    }

}