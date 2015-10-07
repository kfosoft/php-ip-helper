# PHP IP Helper 
## Installation

Installation with Composer

Either run
~~~
    php composer.phar require kfosoft/php-ip-helper:"*"
~~~
or add in composer.json
~~~
    "require": {
        ...
        "kfosoft/php-ip-helper":"*"
    }
~~~

Well done!

API
-------------------
    localIpsV4  Return local ips. Use ifconfig.
    localIpV4   Return local ip. Use ifconfig.
    validate    Validate ip.
    validateV4  Validate ipv4.
    validateV6  Validate ipv6.
    ipv4ToLong  Ip v4 to long.
    longToIpv4  Long to ipv4.
    ipv6ToLong  Ip v6 to long.
    longToIpv6  Long to Ip v6.
    ip2bin      Ip to bin.
    bin2ip      Bin to ip.
    version     Returns ip version.

## Example

~~~
use kfosoft\helpers\IP;

var_dump(IP::localIpsV4());
~~~

##### Result

~~~
array(2) {
  ["eth0"]=>
  string(13) "192.168.0.105"
  ["lo"]=>
  string(9) "127.0.0.1"
}
~~~

Enjoy, guys!
