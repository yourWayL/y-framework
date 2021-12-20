<?php
/**
 * Note:
 * User: rcj
 * Date: 2020/7/22
 * Time: 14:31
 */

namespace Secxun\Extend\Wechat;
use App\Wechat\Http\Domain\AccessToken as Service;
use Secxun\Core\Action;

class Template
{
    protected $access_token;
    const GET_ALL_PRIVATE_TEMPLATE = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template';


    public function __construct()
    {
        //获取token
        $service = new Service();
        $result = $service->getData();
        $result = json_decode($result,true);
        $this->access_token = $result['data'];
    }

    public function get_all_private_template()
    {
         $url = self::GET_ALL_PRIVATE_TEMPLATE.'?access_token='.$this->access_token;
         $templateList = Action::curlUrl($url);
         return json_decode($templateList);
    }
}