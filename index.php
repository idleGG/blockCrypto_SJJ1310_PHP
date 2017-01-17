<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <?php
        include("Bytes.php");
        include("cmds.php");

        $srvIp = "192.168.19.51";
        $srvPort = 8018;
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        var_dump($socket);
        $result=socket_connect($socket,$srvIp,$srvPort);
        if($result){
            echo "连接成功<br />";
            
            //明文数据
            $dataArray = array("Message1 to be Encrypted", "第二个待加密数据", "\x00\x01\x02\x03");
            
            $tmp1 = "一个测试程序。";
            echo "字符串长：".strlen($tmp1)."<br />";
            $tmp2 = Bytes::getBytes($tmp1);
            echo "数组长：".count($tmp2)."<br />";
            
            try{
//                $rsp2 = cmds::S3_Encrypt($socket, 0, 0x00A, 1, null, 0, null, 0, null, $dataArray[2]);
//                var_dump($rsp2);
//                $val = Bytes::bytesToHexstring(Bytes::getBytes($rsp2));
//                echo "Cipher:".$val."<br />";
           
                // 此方法为多块数据加密
                $rsp3 = cmds::SW_blocksEncrypt($socket, 0, 0x00A, 1, null, 0, null, 0, null, $dataArray);
                for($i = 0; $i<count($dataArray); $i++){
                    echo "<br />Cipher:".$i.$rsp3[$i]."<br />";
                    var_dump(Bytes::getBytes($rsp3[$i]));    //加密后数据密文转为字节数组查看
                }

                // 此方法为多块数据解密
                $rsp4 = cmds::SW_blocksDecrypt($socket, 0, 0x00A, 1, null, 0, null, 0, null, $rsp3);
                for($i = 0; $i<count($dataArray); $i++){
                    echo "<br />Plain:".$i.$rsp4[$i]."<br />";  //以字符串形式打印解密后的明文信息
                    var_dump(Bytes::getBytes($rsp4[$i]));   //解密后数据密文转为字节数组查看
                }
            }catch(Exception $e){   //失败时抛出异常，如密码机报错则异常信息中包含错误码
                echo 'Message: ' .$e->getMessage();
            }
        }
        socket_close($socket);
        
        ?>
    </body>
</html>
