<?php


namespace app\group\controller;


use app\api\model\GroupAnnounceModel;
use app\group\common\GroupBaseController;

class Notification extends GroupBaseController {

    public function show_group_notifications() {
        $this->assign('nav', 'notifications');

        $notifications = GroupAnnounceModel::all(['group_id' => $this->group->id]);
        $this->assign('notifications', $notifications);
        return view($this->theme_root . '/group-notifications');
    }
}
