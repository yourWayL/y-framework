<?php
/**
 * @description 登录业务
 * @author Holyrisk
 * @date 2020/4/16 16:28
 */

namespace App\Client\Http\Domain;

use App\Client\Model\Login as Model;
use App\Client\Model\Cliuser as CliuserModel;
use App\Client\Model\Organization as OrganizationModel;
use Secxun\Extend\Holyrisk\Handle\Rsa;

class Login
{

    /**
     * @description 登录校验
     * @author Holyrisk
     * @date 2020/4/16 16:39
     * @param $paramArr
     * @return false|string
     */
    public function login($paramArr)
    {
        $result = false;
        $messages = "添加失败";
        $code = 400;
        $model = new Model();
        $CliuserModel = new CliuserModel();
        $OrganizationModel = new OrganizationModel();
        $username = isset($paramArr['username']) ? $paramArr['username'] : false;
        $password = isset($paramArr['password']) ? $paramArr['password'] : false;
        if (empty($username))
        {
            return anJson("账号不能为空",$code,$result);
        }
        if (empty($password))
        {
            return anJson("请填写密码",$code,$result);
        }

        try{
            $user = $model->getLogin($username,md5(md5($password)));

            $pUser = [];
            if ($user[0]['p_id']){
                $where['id'] = $user[0]['p_id'];
                $pUser = $CliuserModel->getUsername($where);
            }

            if (empty($user))
            {
                return anJson("此账号不存在、请校验是否账号密码错误",$code,$result);
            }
            //if (empty([0]['openid']))
            //{
            //    return anJson("此账号未绑定微信,请先绑定微信",$code,$result);
            //}
            if ($user[0]['status'] == 2)
            {
                return anJson("此账号已被冻结",$code,$result);
            }
            if ($user[0]['is_admin'] == 2)
            {
                return anJson("此账号暂不允许登录，请联系管理员",$code,$result);
            }


            $newTime = time();
            // 如果父过期了则不能登录
            if (!$pUser){
                if ($newTime > $user[0]['out_time']){
                    return anJson("此账号使用时间、已过期、请联系管理员",$code,$result);
                }
            }else{
//                return json_encode($pUser);
                if ($newTime > $pUser[0]['out_time'])
                {
                    return anJson("此账号使用时间、已过期、请联系管理员~",$code,$result);
                }
            }

            if ($user[0]['type'] === 3 && $user[0]['pc_open'] !== 1){
                return anJson("当前账号不允许登录,请联系上级管理员",$code,$result);
            }
            $code = 200;
            $rsaObj = new Rsa();
            $time = time();
            $tonkem_create_time = $time+5*60;
            $token = md5($user[0]['id'].$time);

            $organ['id'] = $pUser ? $pUser[0]['organ_id'] : $user[0]['organ_id'];
            $organInfo = $OrganizationModel->getOrganization($organ);
            $organ = $organInfo[0]['lng'] . ',' .$organInfo[0]['lat'];
//            $path = explode('-',$jpath);
//            $cityPath = [];
//            foreach ($path as $pathVal){
//                if (!$pathVal){
//                    continue;
//                }
//                $city = $cityModel->getIdData($pathVal);
//                array_push($cityPath,$city['name']);
//            }
//
//            $cityPath = implode('',$cityPath);

            $result = array(
                'token' => $token,
                'username' => $user[0]['username'],
                'name' => $user[0]['name'],
                'user_type' => $user[0]['type'],
                'pc_open' => $user[0]['pc_open'],
                'organ' => $organ
            );
            try{
                //$loginStatus =1;
                $model->modifyToken($user[0]['id'],$token,$tonkem_create_time);
                return anJson("登录成功",$code,$result);
            }catch (\Exception $exception)
            {
                return anJson("登录失败 ".$exception->getMessage(),$exception->getCode(),$result);
            }

        }catch (\Exception $exception)
        {
            $messages = "系统繁忙 ".$exception->getCode();
            $result = $exception->getMessage();
        }
        return anJson($messages,$code,$result);
    }


}