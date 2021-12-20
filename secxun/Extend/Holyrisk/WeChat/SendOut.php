<?php
/**
 * @description 微信交互  本项目 发送消息给 微信公众号
 * @author Holyrisk
 * @date 2019/12/25 15:05
 */

namespace Secxun\Extend\Holyrisk\WeChat;


class SendOut
{
    /**
     * @description  发送消息给 公众号 | 关注 取消等 事件 | 文本消息
     * @author Holyrisk
     * @date 2019/12/25 15:05
     * @param $object
     * @param $content
     * @return string
     */
    public function transmitText($object, $content)
    {
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    /**
     * @description 发送消息给 公众号 | 关注 取消等 事件 | 图片消息
     * @author Holyrisk
     * @date 2019/12/25 16:43
     * @param $object
     * @param $imageArray
     * @return string
     */
    public function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";
        $item_str = sprintf($itemTpl, $imageArray['MediaId']);
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    public function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";
        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";
        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    /**
     * @description 发送图文消息
     * @author Holyrisk
     * @date 2020/1/14 14:42
     * @param $object
     * @param $newsArray
     * @return string|void
     */
    public function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $newsTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<Content><![CDATA[]]></Content>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";
        $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    /**
     * @description 发送音乐
     * @author Holyrisk
     * @date 2020/1/14 14:43
     * @param $object
     * @param $musicArray
     * @return string
     */
    public function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";
        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);
        $textTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

}