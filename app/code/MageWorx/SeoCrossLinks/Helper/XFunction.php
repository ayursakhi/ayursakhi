<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinks\Helper;

class XFunction
{
    /**
     * Recursive applies the callback to the elements of the given array
     *
     * @param string $func
     * @param array $array
     * @return array
     */
    public function arrayMapRecursive($func, $array)
    {
        if (!is_array($array)) {
            $array = array();
        }

        foreach ($array as $key => $val) {
            if (is_array($array[$key])) {
                $array[$key] = $this->arrayMapRecursive($func, $array[$key]);
            } else {
                $array[$key] = call_user_func($func, $val);
            }
        }
        return $array;
    }

    /**
     * Replace once occurrence of the search string with the replacement string
     *
     * @param string $search
     * @param string $replace
     * @param string $text
     * @return string
     */
    public function strReplaceOnce($search, $replace, $text)
    {
        $pos = mb_strpos($text, $search);
        return $pos !== false ? substr_replace($text, $replace, $pos, mb_strlen($search)) : $text;
    }
}
