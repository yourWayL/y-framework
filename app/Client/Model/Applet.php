<?php
/**
 * @category: Client\Api\Wechat
 * @description: 小程序相关接口
 * @author yourway <lyw@secxiun.com>
 * @copyright 深圳安巽科技有限公司 <https://www.secxun.com>
 * @create: 2020 - 06 - 09
 */

namespace App\Wechat\Http\Api;

use App\Wechat\Http\Domain\Applet as AppletServer;
use App\Wechat\Model\WechatPhoneUserLog as WechatPhoneUserLog;

class Applet
{
    /**
     * 小程序登录授权
     * @param $request
     * @return bool|false|string
     */
    public function login($request)
    {
        if (empty($request->post['code'])) {
            return anJson('必穿项不能为空', 404, '');
        }
        $code = $request->post['code'];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=wx158cde5a9948742b&secret=dddeb6e8d8f73bf7078ceccd6900fb7c&js_code=' . $code . '&grant_type=authorization_code';
        //初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

    /**
     * 数据解密
     * @param $request
     * @return false|string
     */
    public function getPhoneNumber($request)
    {
        $sessionKey = $request->post['session_key'];
        $encryptedData = $request->post['encryptedData'];
        $iv = $request->post['iv'];
        $data = '';
        if (empty($request->post['session_key']) or empty($request->post['encryptedData'])) {
            return anJson('sessionKey encryptedData 不能为空', 404, '');
        }
        $AppletServer = new AppletServer;
        $data = $AppletServer->decryptData($encryptedData, $iv, $data, $sessionKey);
        if ($data < 0) {
            return anJson('获取手机失败', 404, $data);
        } else {
            return anJson('获取手机成功', 200, $data);
        }

    }

    /**
     * 分布表单第一步
     * @param $request
     * @return mixed
     */
    public function addUserInfo($request)
    {
        $addData['nickname'] = $request->post['nickname'];
        $addData['unionid'] = $request->post['unionid'];
        $addData['openid'] = $request->post['openid'];
        $addData['phone'] = $request->post['phone'];
        $addData['img'] = $request->post['img'];
        if (empty($request->post['unionid']) or empty($request->post['openid'])) {
            return anJson('必传字段 不能为空', 404, '');
        }
        $AppletServer = new AppletServer;
        $AppletServer->handlePostData($addData);
        return anJson('数据添加成功', 200, '');
    }

    /**
     * 获取用户缓存信息
     * @param $request
     * @return mixed
     */
    public function getUserInfo($request)
    {
        $unionid = $request->post['unionid'];
        if (empty($request->post['unionid'])) {
            return anJson('必传字段 不能为空', 404, '');
        }
        $AppletServer = new AppletServer;
        $result = $AppletServer->getUserInfo($unionid);
        return anJson('数据返回成功', 200, $result);
    }

    /**
     * 处理Jzz用户注册数据
     * @param $request
     * @return mixed
     */
    public function addJzzUserInfo($request)
    {

        @$data['phone'] = $request->post['phone'];
        @$data['name'] = $request->post['name'];
        @$data['occupation'] = $request->post['occupation'];
        @$data['province'] = $request->post['province'];
        @$data['city'] = $request->post['city'];
        @$data['county'] = $request->post['county'];
        @$data['street'] = $request->post['street'];
        @$data['street_number'] = $request->post['street_number'];
        @$data['address_choice'] = $request->post['address_choice'];
        @$data['unionid'] = $request->post['unionid'];
        @$data['weixin_picture'] = $request->post['headimgurl'];
        @$data['unit'] = $request->post['unit'];
        @$data['unit_path'] = $request->post['unit_path'];
        @$data['unit_str'] = $request->post['unit_str'];
        @$data['lng'] = $request->post['lng'];
        @$data['lat'] = $request->post['lat'];
        @$data['openid'] = $request->post['openid'];
        @$data['create_time'] = date('Y-m-d H:i:s',time());

        if(empty($request->post['unionid'])){
            return anJson('必传项不能为空', 404, '');
        }
        $AppletServer = new AppletServer;

        $checkQrcodeInfo = $AppletServer->checkQrcodeInfo($data['unionid']);

        if(!empty($checkQrcodeInfo) and $checkQrcodeInfo['result']['0']['subscribe'] > 0 ){
            $data['qrcode_user_id'] = $checkQrcodeInfo['result']['0']['qrcode_id'];
            $AppletServer->updateMenberInfo($data['unionid']);

        }else{
            $AppletServer->addMemberInfo($data);
        }

        $result = $AppletServer->handleJzzPostData($data);
        if($result){
            //添加注册日志
            $addLog['unionid'] = $data['unionid'];
            $addLog['unit_id'] = $data['unit'];
            $addLog['old_unit_id'] = 0;
            $addLog['create_time'] = time();
            $phoneUserLogModel = new WechatPhoneUserLog();
            $phoneUserLogModel->addLog($addLog);
            return anJson('数据添加成功', 200, '');
        }else{
            return anJson('系统出现错误,该错误请记录!', 500, '');
        }
    }

    public function checkRegister($request){
        $unionid = $request->post['unionid'];
        if (empty($request->post['unionid'])) {
            return anJson('必传字段 不能为空', 404, '');
        }
        $AppletServer = new AppletServer;
        $result = $AppletServer->checkUserRegister($unionid);
        if($result){
            return anJson('用户已注册金钟罩', 200, '');
        }else{
            return anJson('用户未注册金钟罩', 404, '');
        }

    }

    /**
     * 暂时废弃
     * @param $request
     * @return mixed
     */
    public function getUserUnionid($request)
    {
        $sessionKey = $request->post['session_key'];
        $encryptedData = $request->post['encryptedData'];
        $iv = $request->post['iv'];
        $data = '';
        if (empty($request->post['session_key']) or empty($request->post['encryptedData'])) {
            return anJson('sessionKey encryptedData 不能为空', 404, '');
        }
        $AppletServer = new AppletServer;
        $data = $AppletServer->decryptData($encryptedData, $iv, $data, $sessionKey);
        if ($data < 0) {
            return anJson('数据解析失败', 404, $data);
        } else {
            return anJson('数据解析成功', 200, $data);
        }

    }


}