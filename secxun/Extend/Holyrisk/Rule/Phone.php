<?php


namespace Secxun\Extend\Holyrisk\Rule;


class Phone
{
    /**
     * @description 手机验证
     * @author Holyrisk
     * @date 2020/3/27 16:47
     * @param $value
     * @return bool
     */
    public function isMobile($value)
    {
        //var regexs = new RegExp('^[1][3,4,5,6,7,8,9][0-9]{9}$');
        $rule = '/^0?(13|14|15|16|17|18|19)[0-9]{9}$/';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}