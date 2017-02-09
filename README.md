# blockCrypto_SJJ1310_PHP
## 说明
1. 基于SJJ1310型号密码机部分定制指令封装的一套PHP语言API接口程序。
2. 接口内不提供Socket连接与维护的实现，需调用者自行完成TCP/IP Socket维护。
3. 供应用调用的方法位于cmds类中

## DEMO
``` PHP
<?php
require __DIR__ .'/vendor/autoload.php';
//include("vendor/autoload.php");
//include("Bytes.php");
//include("cmds.php");
use cmds as cmds;
use Bytes as Bytes;

$srvIp = "192.168.19.21";
$srvPort = 8018;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
var_dump($socket);
$result=socket_connect($socket,$srvIp,$srvPort);
if($result){
    echo "连接成功\n";
	$key = "RAD2F4FC1E7898465AD2F4FC1E7898465";
	//$key = 1;
            
    //明文数据
    $dataArray = array("Message1 to be Encrypted", "第二个待加密数据", "\x00\x01\x02\x03");
            
    $tmp1 = "一个测试程序。";
    echo "字符串长：".strlen($tmp1)."\n";
    $tmp2 = Bytes::getBytes($tmp1);
    echo "数组长：".count($tmp2)."\n";
            
           
    try{
		// 此方法为多块数据加密
		$rsp3 = cmds::SW_blocksEncrypt($socket, 0, 0x00A, $key, null, 0, null, 0, null, $dataArray);
		echo "rsp3 ArrayLength=".count($rsp3)."\n";
		for($i = 0; $i<count($rsp3); $i++){
			echo "\nCipher:".$i."\n";
			var_dump(Bytes::getBytes($rsp3[$i]));    //加密后数据密文转为字节数组查看
		}
    }catch(Exception $e){   //失败时抛出异常，如密码机报错则异常信息中包含错误码
        echo 'Encrypt Failed, Message: ' .$e->getMessage();
    }

    try{
        // 此方法为多块数据解密
		$rsp4 = cmds::SW_blocksDecrypt($socket, 0, 0x00A, $key, null, 0, null, 0, null, $rsp3);
		echo "rsp4 ArrayLength=".count($rsp4)."\n";
        for($i = 0; $i<count($rsp4); $i++){
            echo "\nPlain:".$i.$rsp4[$i]."\n";  //以字符串形式打印解密后的明文信息
            var_dump(Bytes::getBytes($rsp4[$i]));   //解密后数据密文转为字节数组查看
        }
    }catch(Exception $e){   //失败时抛出异常，如密码机报错则异常信息中包含错误码
        echo 'Decrypt Failed, Message: ' .$e->getMessage();
    }
}
socket_close($socket);
?>
```

## 使用指定密钥分别加密多组数据
``` PHP
public static function SW_blocksEncrypt(
    $socket,
    $encFlag,
    $keyTpe,
    $key,
    $deriveFactor,
    $sessionKeyFlag,
    $sessionKeyFactor,
    $paddingFlag,
    $iv,
    $dataArray);
```
- 参数说明

数据类型|参数名|说明
---|---|---
socket|$socket|与密码机建立的连接句柄
int|$encFlag|加密模式标识（0,ECB; 1,CBC; 2,CFB; 3.OFB）
int|$keyType|密钥类型标识
int/string|$key|密钥索引或LMK加密的密钥密文值
string|$deriveFactor|子密钥分散因子
int|$sessionKeyFlag|会话密钥标识
string|$sessionKeyFactor|会话密钥因子
int|$paddingFlag|填充算法标识
string|$iv|初始向量
array|$dataArray|多个明文数据段组成的数组

- 返回值
 多个密文数据段组成的数据，成员为string类型的array

- 异常信息
 Exception: 执行失败时抛出异常

## 使用指定密钥分别解密一组数据
``` PHP
public static function SW_blocksDecrypt(
    $socket,
    $encFlag,
    $keyType,
    $key,
    $deriveFactor,
    $sessionKeyFlag,
    $sessionKeyFactor,
    $paddingFlag,
    $iv,
    $dataArray){
```

- 参数说明

数据类型|参数名|说明
---|---|---
socket|$socket|与密码机建立的连接句柄
int|$encFlag|加密模式标识（0,ECB; 1,CBC; 2,CFB; 3.OFB）
int|$keyType|密钥类型标识
int/string|$key|密钥索引或LMK加密的密钥密文值
string|$deriveFactor|子密钥分散因子
int|$sessionKeyFlag|会话密钥标识
string|$sessionKeyFactor|会话密钥因子
int|$paddingFlag|填充算法标识
string|$iv|初始向量
array|$dataArray|多个密文数据段组成的数组

- 返回值
 多个数据段组成的数据，成员为string类型的array

- 异常信息
 Exception: 执行失败时抛出异常

