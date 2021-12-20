<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: ellermister
 * Date: 2020/5/25
 * Time: 17:12
 */

namespace App\Client\Model;

use Secxun\Extend\Eller\Database\Model;

class ClientMessageTemplate extends Model
{
    /**
     * 客户端消息模板
     * @var string
     */
    protected $table = "client_message_template";

    /**
     * 创建模板
     *
     * @param array $data
     * @return bool|int
     */
    public static function createTemplate(array $data)
    {
        $template = [
            'template_name'  => $data['template_name'] ?? '',
            'disable'        => $data['disable'] ?? '',
            'logo_file'      => $data['logo_file'] ?? '',
            'reply_type'     => $data['reply_type'] ?? '',
            'reply_text'     => $data['reply_text'] ?? '',
            'replay_content' => $data['replay_content'] ?? '',
            'reply_img'      => $data['reply_img'] ?? '',
            'replay_img_url' => $data['replay_img_url'] ?? '',
            'reply_article'  => $data['reply_article'] ?? '',
            'user_id'        => $data['user_id'] ?? '',
            'created_time'   => time(),
            'updated_time'   => time(),
        ];
        $ret = self::create($template);
        return $ret;
    }

    /**
     * 获取模板列表分页
     *
     * @param array $data
     * @param int $pageSize
     * @return array|bool
     * @throws \Secxun\Extend\Eller\Database\DbException
     */
    public static function  getTemplateForPage(array $data, $pageSize = 10)
    {
        $orderBy = $data['order_by'] ?? 'desc';
        $orderByField = $data['order_by_field'] ?: 'created_time';
        unset($data['order_by']);
        unset($data['order_by_field']);
        return self::where($data)->leftJoin('client_qrcode', 'client_message_template.id', 'client_qrcode.template_id')
            ->column(['client_message_template.*', 'count(client_qrcode.id) as qrcode_count'])
            ->groupBy('client_message_template.id')
            ->orderBy("{$orderByField} {$orderBy}")
            ->paginate($pageSize);
    }

    /**
     * 删除模板
     *
     * @param $id
     * @return mixed
     * @throws \Secxun\Extend\Eller\Database\DbException
     */
    public static function deleteTemplate(int $id)
    {
        self::table('client_qrcode')->where('template_id', $id)->update(['template_id' => 0]);
        return self::where('id', $id)->delete();
    }


    /**
     * 获取模板的二维码总数
     *
     * @param $tplId
     * @return int
     * @throws \Secxun\Extend\Eller\Database\DbException
     */
    public static function getTemplateQRCodeCount(int $tplId)
    {
        return self::table('client_qrcode')->where('template_id', $tplId)->count();
    }

}