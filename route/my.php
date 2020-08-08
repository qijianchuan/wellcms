<?php
/*
 * Copyright (C) www.wellcms.cn
*/
!defined('DEBUG') AND exit('Access Denied.');

// 检查是否登录
user_login_check();

$action = param(1);

// hook my_start.php

// 从全局拉取$user
$header['mobile_title'] = '';
$header['mobile_linke'] = '';
list($member_navs, $member_menus) = nav_member();

// hook my_before.php

switch ($action) {
    // hook my_case_start.php
    case 'avatar':
        if ('GET' == $method) {

            // hook my_avatar_get_start.php
            $safe_token = well_token_set($uid);

            include _include(theme_load('my_avatar'));

        } elseif ('POST' == $method) {

            // 验证token
            $safe_token = param('safe_token');
            well_token_set($uid);
            FALSE === well_token_verify($uid, $safe_token, 60) AND message(1, lang('illegal_operation'));

            // hook my_avatar_post_start.php

            $width = param('width');
            $height = param('height');
            $data = param('data', '', FALSE);

            // hook my_avatar_post_save_before.php

            empty($data) AND message(-1, lang('data_is_empty'));
            $data = base64_decode_file_data($data);
            $size = strlen($data);
            $size > 40000 AND message(-1, lang('filesize_too_large', array('maxsize' => '40K', 'size' => $size)));

            // hook my_avatar_post_save_center.php

            $filename = "$uid.png";
            $dir = substr(sprintf("%09d", $uid), 0, 3) . '/';
            $path = $conf['upload_path'] . 'avatar/' . $dir;
            $url = file_path() . 'avatar/' . $dir . $filename;
            !is_dir($path) AND (mkdir($path, 0777, TRUE) OR message(-2, lang('directory_create_failed')));

            // hook my_avatar_post_save_middle.php

            file_put_contents($path . $filename, $data) OR message(-1, lang('write_to_file_failed'));
            // hook my_avatar_post_save_after.php
            user_update($uid, array('avatar' => $time));

            // hook my_avatar_post_end.php

            message(0, array('url' => $url));
        }
        break;
    case 'password':
        if ('GET' == $method) {

            // hook my_password_get_start.php

            include _include(theme_load('my_password'));

        } elseif ('POST' == $method) {

            // hook my_password_post_start.php

            $password_old = param('password_old');
            $password_new = param('password_new');
            $password_new_repeat = param('password_new_repeat');
            $password_new_repeat != $password_new AND message(-1, lang('repeat_password_incorrect'));
            md5($password_old . $user['salt']) != $user['password'] AND message('password_old', lang('old_password_incorrect'));
            $password_new = md5($password_new . $user['salt']);
            FALSE === user_update($uid, array('password' => $password_new)) AND message(-1, lang('password_modify_failed'));

            // hook my_password_post_end.php

            message(0, lang('password_modify_successfully'));

        }
        break;
    // hook my_case_end.php
    default:
        $header['title'] = lang('my_home');
        include _include(theme_load('my'));
        break;
}

// hook my_end.php

?>