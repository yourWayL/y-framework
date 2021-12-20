<?php


namespace Secxun\Core;


class XSSHandler
{
    private $str = '';

    public static function string_remove_xss($html) {
        preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

        $searchs[] = '<';
        $replaces[] = '&lt;';
        $searchs[] = '>';
        $replaces[] = '&gt;';

        if ($ms[1]) {
            $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
            $ms[1] = array_unique($ms[1]);
            foreach ($ms[1] as $value) {
                $searchs[] = "&lt;".$value."&gt;";

                $value = str_replace('&amp;', '_uch_tmp_str_', $value);
                $value = self::string_htmlspecialchars($value);
                $value = str_replace('_uch_tmp_str_', '&amp;', $value);

                $value = str_replace(array('\\', '/*'), array('.', '/.'), $value);
                $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                    'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                    'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                    'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                    'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                    'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                    'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                    'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                    'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
                $skipstr = implode('|', $skipkeys);
                $value = preg_replace(array("/($skipstr)/i"), '.', $value);
                if (!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                    $value = '';
                }
                $replaces[] = empty($value) ? '' : "<" . str_replace('&quot;', '"', $value) . ">";
            }
        }
        $html = str_replace($searchs, $replaces, $html);

        return $html;
    }

    public static function string_htmlspecialchars($string, $flags = null)
    {
        if (is_array($string))
        {
            foreach ($string as $key => $val)
            {
                $string[$key] = self::string_htmlspecialchars($val, $flags);
            }
        } else
        {
            if ($flags === null)
            {
                $string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
                if (strpos($string, '&amp;#') !== false)
                {
                    $string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
                }
            } else
            {
                if (PHP_VERSION < '5.4.0')
                {
                    $string = htmlspecialchars($string, $flags);
                } else
                {
                    if (!defined('CHARSET') || (strtolower(CHARSET) == 'utf-8')) {
                        $charset = 'UTF-8';
                    } else {
                        $charset = 'ISO-8859-1';
                    }
                    $string = htmlspecialchars($string, $flags, $charset);
                }
            }
        }

        return $string;
    }
}