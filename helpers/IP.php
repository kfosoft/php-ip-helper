<?php
namespace kfosoft\helpers;

/**
 * IP Helper.
 * @package kfosoft\helpers
 * @version 1.0.1
 * @copyright (c) 2014-2015 KFOSoftware Team <kfosoftware@gmail.com>
 */
class IP
{
    const V4 = 'v4';
    const V6 = 'v6';
    const ALL = 'all';

    /**
     * Return local ips. Use ifconfig.
     * @param string $ifconfig path to if config.
     * @return array ips.
     */
    public static function localIpsV4($ifconfig = '/sbin/ifconfig')
    {
        $out = explode(PHP_EOL, shell_exec($ifconfig));
        $localAddrs = [];
        $ifName = 'interface';
        $i=0;
        foreach ($out as $str) {
            $matches = [];
            if (preg_match('/^([a-z0-9]+)(:\d{1,2})?(\s)+Link/', $str, $matches)) {
                $ifName = $matches[1];
                if (strlen($matches[2]) > 0) {
                    $ifName .= $matches[2];
                }
            } elseif (preg_match('/inet addr:((?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3})\s/',
                $str, $matches)) {
                $localAddrs[$ifName==='interface'?$ifName.$i:$ifName] = $matches[1];
            } elseif (preg_match('/inet ((?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)(?:[.](?:25[0-5]|2[0-4]\d|1\d\d|[1-9]\d|\d)){3})\s/',
                $str, $matches)) {
                $localAddrs[$ifName==='interface'?$ifName.$i:$ifName] = $matches[1];
            }
            $i++;
        }

        return $localAddrs;
    }

    /**
     * Return local ip. Use ifconfig.
     * @param string $ifName name of ifconfig device.
     * @return null|string ip.
     */
    public static function localIpV4($ifName)
    {
        $ips = static::localIpsV4();

        return isset($ips[$ifName]) ? $ips[$ifName] : null;
    }

    /**
     * Validate ip.
     * @param string $ip ip for validate.
     * @param string $type see const of this class.
     * @return bool
     * @throws \Exception
     */
    public static function validate($ip, $type = self::ALL)
    {
        switch ($type) {
            case static::ALL :
                $flag = null;
                break;
            case static::V4 :
                $flag = FILTER_FLAG_IPV4;
                break;
            case static::V6 :
                $flag = FILTER_FLAG_IPV6;
                break;
            default :
                throw new \Exception('Bad type of ip validator! See class const.');
        }

        return filter_var($ip, FILTER_VALIDATE_IP, $flag) !== false;
    }

    /**
     * Validate ipv4.
     * @param string $ip ip for validate.
     * @return bool
     * @throws \Exception
     */
    public static function validateV4($ip)
    {
        return static::validate($ip, static::V4);
    }

    /**
     * Validate ipv6.
     * @param string $ip ip for validate.
     * @return bool
     * @throws \Exception
     */
    public static function validateV6($ip)
    {
        return static::validate($ip, static::V6);
    }

    /**
     * Ip v4 to long.
     * @param string $ip
     * @return int|string
     */
    public static function ipv4ToLong($ip)
    {
        $result = 0;

        if (static::validatev4($ip)) {
            $result = sprintf("%u", ip2long($ip));
        }

        return $result;
    }

    /**
     * Long to ipv4.
     * @param int $number
     * @return string
     */
    public static function longToIpv4($number)
    {
        $number = trim($number);
        $result = '0.0.0.0';

        if ($number != '0') {
            return long2ip(-(4294967295 - ($number - 1)));
        }

        return $result;
    }

    /**
     * Ip v6 to long.
     * @param string $ipv6 ipv6.
     * @return string formated ip.
     */
    public static function ipv6ToLong($ipv6)
    {
        if (!static::validatev6($ipv6)) {
            return 0;
        }
        $ipPton = inet_pton($ipv6);
        $bits = 15; // 16 x 8 bit = 128bit
        $ipv6long = '';

        while ($bits >= 0) {
            $bin = sprintf("%08b", (ord($ipPton[$bits])));
            $ipv6long = $bin . $ipv6long;
            $bits--;
        }

        return gmp_strval(gmp_init($ipv6long, 2), 10);
    }

    /**
     * Long to Ip v6.
     * @param string $long long for format in ipv6.
     * @return string
     */
    public static function longToIpv6($long)
    {
        $bin = gmp_strval(gmp_init($long, 10), 2);
        if (strlen($bin) < 128) {
            $pad = 128 - strlen($bin);
            for ($i = 1; $i <= $pad; $i++) {
                $bin = "0" . $bin;
            }
        }
        $bits = 0;
        $ipv6 = '';
        while ($bits <= 7) {
            $binPart = substr($bin, ($bits * 16), 16);
            $ipv6 .= dechex(bindec($binPart)) . ":";
            $bits++;
        }

        return inet_ntop(inet_pton(substr($ipv6, 0, -1)));
    }

    /**
     * Ip to bin.
     * @param string $ip ip for format.
     * @return bool|string
     */
    public static function ip2bin($ip)
    {
        if ($version = static::version($ip)) {
            if ($version === static::V4) {
                return base_convert(ip2long($ip), 10, 2);
            } elseif ($version === static::V6) {
                if (($ipPton = inet_pton($ip)) === false) {
                    return false;
                }
                $bits = 15; // 16 x 8 bit = 128bit (ipv6)
                $ipBin = '';
                while ($bits >= 0) {
                    $bin = sprintf("%08b", (ord($ipPton[$bits])));
                    $ipBin = $bin . $ipBin;
                    $bits--;
                }

                return $ipBin;
            }
        }

        return false;
    }

    /**
     * Bin to ip.
     * @param string $bin bin for format.
     * @return bool|string
     */
    public static function bin2ip($bin)
    {
        if (strlen($bin) <= 32) { // 32bits (ipv4)
            return long2ip(base_convert($bin, 2, 10));
        }
        if (strlen($bin) != 128) {
            return false;
        }
        $pad = 128 - strlen($bin);
        for ($i = 1; $i <= $pad; $i++) {
            $bin = "0" . $bin;
        }
        $bits = 0;
        $ipv6 = '';
        while ($bits <= 7) {
            $bin_part = substr($bin, ($bits * 16), 16);
            $ipv6 .= dechex(bindec($bin_part)) . ":";
            $bits++;
        }

        return inet_ntop(inet_pton(substr($ipv6, 0, -1)));
    }

    /**
     * Returns ip version.
     * @param string $ip ip for check.
     * @return bool|string
     */
    public static function version($ip)
    {
        if (static::validateV4($ip)) {
            return static::V4;
        } elseif (static::validateV6($ip)) {
            return static::V6;
        } else {
            return false;
        }
    }
}
