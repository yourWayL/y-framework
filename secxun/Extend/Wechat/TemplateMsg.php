<?php
/**
 * Note:
 * User: rcj
 * Date: 2020/7/22
 * Time: 15:15
 */

namespace Secxun\Extend\Wechat;

use App\Wechat\Http\Domain\AccessToken as Service;
use Secxun\Core\Action;

class TemplateMsg
{
    protected $access_token;
    const SEND = 'https://api.weixin.qq.com/cgi-bin/message/template/send';
    const LOG_FILE = ROOT_PATH.'/runtime/log/template/runtime.log';

    public function __construct()
    {
        //获取token
        $service = new Service();
        $result = $service->getData();
        $result = json_decode($result,true);
        $this->access_token = $result['data'];
    }

    /**
     * @Desc:
     * @param string $touser   //用户的openid
     * @param string $template_id  //使用的模板ID
     * @param array $data      //模板数据
     * @param array $param     //其他非必须参数
     * [
     *    'url'=>'',//跳转地址
     *     'miniprogram'=>[]//跳小程序所需数据，不需跳小程序可不用传该数据
     * ]
     * 详情参考接口:https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Interface.html#5
     * @return mixed
     */
    public function sendMsg(string $touser,string $template_id,array $data,array $param=[])
    {
        $postData['touser'] = $touser;
        $postData['template_id'] = $template_id;
        $postData['data'] = $data;
        if(isset($param['url'])){
            $postData['url'] = $param['url'];
        }
        if(isset($param['miniprogram'])){
            $postData['miniprogram']['appid'] = $param['miniprogram']['appid'];
            $postData['miniprogram']['pagepath'] = $param['miniprogram']['pagepath'];
        }
        $url = self::SEND.'?access_token='.$this->access_token;
        $returnData = Action::curlUrl($url,$postData);
        log_message('client','info','sendMsg:POST DATA:'.json_encode($postData).', URL:'.$url.', RETURN DATA:'.$returnData,self::LOG_FILE);
        return json_decode($returnData);
    }
    
    public function batchSendMsg(array $data)
    {
        $url = self::SEND.'?access_token='.$this->access_token;
        foreach($data as $key => $val)
        {
            $param['touser'] = $val['openid'];
            $param['template_id'] = $val['template_id'];
            $param['data'] = $val['data'];
            $param['url'] = $val['param']['url'];
            if(isset($val['miniprogram'])){
                $param['miniprogram']['appid'] = $val['miniprogram']['appid'];
                $param['miniprogram']['pagepath'] = $val['miniprogram']['pagepath'];
            }
            $postData[$key]['url'] = $url;
            $postData[$key]['data'] = $param;
        }
        $returnData = Action::multiCurlUrl($postData);
        log_message('client','info','batchSendMsg : POST DATA:'.json_encode($postData).', URL:'.$url.', RETURN DATA:'.json_encode($returnData),self::LOG_FILE);
        return $returnData;
    }

}