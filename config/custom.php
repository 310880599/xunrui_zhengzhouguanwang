 <?php

/**
 * 开发者自定义函数文件
 */


/**
 * 字符串安照标点符号进行截取。
 */
function cut_str_by_punctuation($string, $length, $dot='...') {
    // 匹配句号、叹号或问号
    $pattern = '/[。！？]/u';
    
    // 执行正则匹配,查找第一个标点符号的位置
    if (preg_match($pattern, $string, $matches, PREG_OFFSET_CAPTURE)) {
        $position = $matches[0][1];
        // 在标点符号处截取字符串
        $result = substr($string, 0, $position + 3);
    } else {
        // 如果没有找到标点符号,则将整个字符串作为结果
        $result = $string;
    }
    
    // 如果截取后的字符串长度超过指定长度,则进行二次截取
    if (mb_strlen($result, 'UTF-8') > $length) {
        $result = mb_substr($result, 0, $length, 'UTF-8') . $dot;
    }
    
    return $result;
}