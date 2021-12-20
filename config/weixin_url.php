<?php
// ----------------------------------------------------------------------
// | 注意 修改配置项 | 请 重启 框架 主进程 | 不然 资源 没有加载进去
// ----------------------------------------------------------------------
return [
    //默认配置
    'default' => [
        //获取微信服务器IP地址
        'getcallbackip' => 'https://api.weixin.qq.com/cgi-bin/getcallbackip?',
        //'getcallbackip' => 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=ACCESS_TOKEN',
        //获取 access_token
        'token'=>'https://api.weixin.qq.com/cgi-bin/token?',
        //'token'=>'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET',
        //获取用户基本信息(UnionID机制
        'user_info_openid' => 'https://api.weixin.qq.com/cgi-bin/user/info?',
        //'user_info' => 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=ACCESS_TOKEN&openid=OPENID&lang=zh_CN',
        //获取用户列表
        'user_info_list' => 'https://api.weixin.qq.com/cgi-bin/user/get?',
        //'user_info_list' => 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=ACCESS_TOKEN&next_openid=NEXT_OPENID',
        //临时二维码
        //https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN POST
        /**
         * {"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}
         * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
         * {"expire_seconds": 604800, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "test"}}}
         */
        'qrcode_temp' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?',
        //永久二维码
        //https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKEN POST
        /**
         * {"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
         * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
         * {"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "test"}}}
         */
        'qrcode_long' => 'https://api.weixin.qq.com/cgi-bin/qrcode/create?',

        //新增其他类型永久素材 | 上传文件到微信服务器
        'material_uploadimg' => 'https://api.weixin.qq.com/cgi-bin/media/uploadimg?',
        //'material_add' => 'https https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=ACCESS_TOKEN&type=TYPE',
        //客服消息
        'custom' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send?',
        //'custom' => 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=ACCESS_TOKEN',
        //上传临时素材
//        'material_temp_upload' => 'https://api.weixin.qq.com/cgi-bin/media/upload?',
        'material_temp_upload' => 'https://api.weixin.qq.com/cgi-bin/material/add_material?',
//        'material_temp_upload' => 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=ACCESS_TOKEN&type=TYPE',
    ],
];