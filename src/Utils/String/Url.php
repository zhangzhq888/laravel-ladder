<?php
namespace Laravelladder\Core\Utils\String;

/**
 * Class Helper
 * Url相关的方法
 * @package Laravelladder\Core\Utils\String
 */
class Url
{
    /**
     * Base64 Url 加密
     * @param $input
     * @return string
     */
    public static function base64_url_encode($input)
    {   
        return strtr(base64_encode($input), '+/', '-_');
    }
    /**
     * Base64 Url 解密
     * @param $input
     * @return string
     */
    public static function base64_url_decode($input)
    {   
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * parse_url() 方法的反转
     * @param $parsed_url
     * @return string
     * @see parse_url()
     */
    public static function unparse_url($parsed_url)
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }
    /**
     * 给定url，获取他的domain
     * @param $url
     * @return string
     */
    public static function getDomainFromUrl($url)
    {
        if (strpos($url, 'http') === false) {
            $url = "http://$url";
        }

        $urlParts = parse_url($url);
        $domainParts = explode(".", $urlParts['host']);

        if (count($domainParts) > 2) {
            array_shift($domainParts);
        }

        return implode(".", $domainParts);
    }
}
