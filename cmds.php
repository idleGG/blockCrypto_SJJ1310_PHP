<?php

//use Exception;

/**
 * 指令报文封装
 */
class cmds {
    
    /**
     * 使用指定密钥分别加密多组数据
     * @param socket $socket    与密码机建立的连接句柄
     * @param int $encFlag  加密模式标识（0,ECB; 1,CBC; 2,CFB; 3.OFB）
     * @param int $keyType  密钥类型标识
     * @param int/string $key   密钥索引或LMK加密的密钥密文值
     * @param string $deriveFactor  子密钥分散因子
     * @param int $sessionKeyFlag   会话密钥标识
     * @param string $sessionKeyFactor  会话密钥因子
     * @param int $paddingFlag  填充算法标识
     * @param string $iv    初始向量
     * @param array $dataArray  多个明文数据段组成的数组
     * @return array    多个密文数据段组成的数组，成员为string类型
     * @throws Exception
     */
    public static function SW_blocksEncrypt( $socket,
            $encFlag,
            $keyType,
            $key,
            $deriveFactor,
            $sessionKeyFlag,
            $sessionKeyFactor,
            $paddingFlag,
            $iv,
            $dataArray){
        $str = "SW0";
        if(is_int($encFlag) && $encFlag >= 0){ $str = $str.sprintf("%02X", $encFlag); }
		else{
			throw new Exception("Invalid argument \$encFlag=$encFlag");
		}
        if(is_int($keyType) & $keyType >= 0){ $str = $str.sprintf("%03X", $keyType); }
        else{ throw new Exception("Invalid argument \$keyType=$keyType"); }
        if(is_int($key) && $key > 0 && $key <= 9999){ $str = $str.sprintf("K%04d", $key); }
        else if(!is_null($key) && is_string($key) && (strlen($key) == 16 || (strlen($key)-1)%32 == 0)){ $str = $str.$key; }
        else{ throw new Exception("Invalid argument \$key=$key"); }
        if(!is_null($deriveFactor) && is_string($deriveFactor)){
            $str = $str.sprintf("%02X", strlen($deriveFactor)/32);
            $str = $str.$deriveFactor;
        }
        else if(is_null($deriveFactor)){
            $str = $str."00";
        }
        else{
            throw new Exception("Invalid argument \$deriveFactor=$deriveFactor");
        }
        if(is_int($sessionKeyFlag) && $sessionKeyFlag >= 0){
            $str = $str.sprintf("%02X", $sessionKeyFlag);
        }
        if(!is_null($sessionKeyFactor)){
            $str = $str.$sessionKeyFactor;
        }
        if(is_int($paddingFlag) && $paddingFlag >= 0){
            $str = $str.sprintf("%02X", $paddingFlag);
        }
        else {
            throw new Exception("Invalid argument \$paddingFlag=$paddingFlag");
        }
        
        if(!is_null($dataArray) && is_array($dataArray)){
            $str = $str.sprintf("%02X", count($dataArray));
        }
        foreach ($dataArray as $data){
            $str = $str.sprintf("%04X", strlen($data));
            $str = $str.$data;
        }
        if(!is_null($iv)){
            $str = $str.$iv;
        }
        $len = strlen($str);
        $buff = Bytes::shortToBytesBigEnd(intval($len));
        $str = Bytes::toStr($buff).$str;
        socket_write($socket, $str, strlen($str));
        $rsp=socket_read($socket,2);
        if($rsp == FALSE){
            throw new Exception(socket_strerror(socket_last_error()));
        }
        $len = Bytes::bytesToShortBigEnd(Bytes::getBytes($rsp), 0);
        $rsp = socket_read($socket, $len);
        if($rsp == FALSE){
            throw new Exception(socket_strerror(socket_last_error()));
        }

		$rv = intval(substr($rsp, 2, 2));
		if($rv !== 0){
			throw new Exception("Failed with returnCode [".$rv."].\n");
		}

        
        $ret = array();
        $offset = 4;
        while($offset < $len){
            $l = intval(substr($rsp, $offset, 4), 16);
            $offset += 4;
            $ret[] = substr($rsp, $offset,  $l);
            $offset += $l;
        }
        return $ret;
    }
    
    /**
     * 使用指定密钥分别解密一组数据
     * @param socket $socket    与密码机建立的连接句柄
     * @param int $encFlag  加密模式标识（0,ECB; 1,CBC; 2,CFB; 3.OFB）
     * @param int $keyType  密钥类型标识
     * @param int/string $key   密钥索引或LMK加密的密钥密文值
     * @param string $deriveFactor  子密钥分散因子
     * @param int $sessionKeyFlag   会话密钥标识
     * @param string $sessionKeyFactor  会话密钥因子
     * @param int $paddingFlag  填充算法标识
     * @param string $iv    初始向量
     * @param array $dataArray  多个密文数据段组成的数组
     * @return array    多个明文数据段组成的数组，成员为string类型
     * @throws Exception
     */
     public static function SW_blocksDecrypt( $socket,
            $encFlag,
            $keyType,
            $key,
            $deriveFactor,
            $sessionKeyFlag,
            $sessionKeyFactor,
            $paddingFlag,
            $iv,
            $dataArray){
        $str = "SW1";
        if(is_int($encFlag) && $encFlag >= 0){
            $str = $str.sprintf("%02X", $encFlag);
        }
        else{
            throw new Exception("Invalid argument \$encFlag=$encFlag");
        }
        if(is_int($keyType) & $keyType >= 0){
            $str = $str.sprintf("%03X", $keyType);
        }
        else{
            throw new Exception("Invalid argument \$keyType=$keyType");
        }
        if(is_int($key) && $key > 0 && $key <= 9999){
            $str = $str.sprintf("K%04d", $key); 
        }
        else if(!is_null($key) && is_string($key) && (strlen($key) == 16 || (strlen($key)-1)%32 == 0)){
            $str = $str.$key;
        }
        else{
            throw new Exception("Invalid argument \$key=$key"); 
        }
        if(!is_null($deriveFactor) && is_string($deriveFactor)){
            $str = $str.sprintf("%02X", strlen($deriveFactor)/32);
            $str = $str.$deriveFactor;
        }
        else if(is_null($deriveFactor)){
            $str = $str."00";
        }
        else{
            throw new Exception("Invalid argument \$deriveFactor=$deriveFactor");
        }
        if(is_int($sessionKeyFlag) && $sessionKeyFlag >= 0){
            $str = $str.sprintf("%02X", $sessionKeyFlag);
        }
        if(!is_null($sessionKeyFactor)){
            $str = $str.$sessionKeyFactor;
        }
        if(is_int($paddingFlag) && $paddingFlag >= 0){
            $str = $str.sprintf("%02X", $paddingFlag);
        }
        else {
            throw new Exception("Invalid argument \$paddingFlag=$paddingFlag");
        }
            if(!is_null($dataArray) && is_array($dataArray)){
            $str = $str.sprintf("%02X", count($dataArray));
        }
        foreach ($dataArray as $data){
            $str = $str.sprintf("%04X", strlen($data));
            $str = $str.$data;
        }
        if(!is_null($iv)){
            $str = $str.$iv;
        }
        $len = strlen($str);
        $buff = Bytes::shortToBytesBigEnd(intval($len));
        $str = Bytes::toStr($buff).$str;
        socket_write($socket, $str, strlen($str));
        $rsp=socket_read($socket,2);
        if($rsp == FALSE){
            throw new Exception(socket_strerror());
        }
        $len = Bytes::bytesToShortBigEnd(Bytes::getBytes($rsp), 0);
        $rsp = socket_read($socket, $len);
        if($rsp == FALSE){
            throw new Exception(socket_strerror());
        }

		$rv = intval(substr($rsp, 2, 2));
		if($rv !== 0){
			throw new Exception("Failed with returnCode [".$rv."].\n");
		}

        $ret = array();
        $offset = 4;
        while($offset < $len){
            $l = intval(substr($rsp, $offset, 4), 16);
            $offset += 4;
            $ret[] = substr($rsp, $offset,  $l);
            $offset += $l;
        }
        return $ret;
    }
    
    public static function S3_Encrypt($socket,
            $encFlag,
            $keyType,
            $key,
            $deriveFactor,
            $sessionKeyFlag,
            $sessionKeyFactor,
            $paddingFlag,
            $iv,
            $data) {
        $str = "S3";
        if(is_int($encFlag) || $encFlag > 0){
            $str = $str.sprintf("%02X",$encFlag);
        }
        else{
            throw new Exception("Invalid argument \$encFlag=$encFlag");
        }
        if(is_int($keyType) || $keyType > 0){
            $str = $str.sprintf("%03X",$keyType);
        }
        else{
            throw new Exception("Invalid argument \$keyType=$keyType");
        }
        if(is_int($key) && $key > 0 && $key <= 9999){
            $str = $str.sprintf("K%04d", $key); 
        }
        else if(!is_null($key) && is_string($key) && (strlen($key) == 16 || (strlen($key)-1)%32 == 0)){
            $str = $str.$key;
        }
        else{
            throw new Exception("Invalid argument \$key=$key"); 
        }
        if(!is_null($deriveFactor) && is_string($deriveFactor)){
            $str = $str.sprintf("%02X", strlen($deriveFactor)/32);
            $str = $str.$deriveFactor;
        }
        else if(is_null($deriveFactor)){
            $str = $str."00";
        }
        else{
            throw new Exception("Invalid argument \$deriveFactor=$deriveFactor");
        }
        if(is_int($sessionKeyFlag) && $sessionKeyFlag >= 0){
            $str = $str.sprintf("%02X", $sessionKeyFlag);
        }
        if(!is_null($sessionKeyFactor)){
            $str = $str.$sessionKeyFactor;
        }
        if(is_int($paddingFlag) && $paddingFlag >= 0){
            $str = $str.sprintf("%02X", $paddingFlag);
        }
        else {
            throw new Exception("Invalid argument \$paddingFlag=$paddingFlag");
        }
        if(!is_null($data)){
            $str = $str.sprintf("%04X", strlen($data));
            $str = $str.$data;
        }
        else{
            throw new Exception("Invalid argument \$data=$data");
        }
        if(!is_null($iv)){
            $str = $str.$iv;
        }
        
        $len = strlen($str);
        $buff = Bytes::shortToBytesBigEnd(intval($len));
        $str = Bytes::toStr($buff).$str;
        socket_write($socket, $str, strlen($str));
        $rsp=socket_read($socket,2);
        if($rsp == FALSE){
            throw new Exception(socket_strerror());
        }
        $len = Bytes::bytesToShortBigEnd(Bytes::getBytes($rsp), 0);
        $rsp = socket_read($socket, $len);
        if($rsp == FALSE){
            throw new Exception(socket_strerror());
        }
        $errCode = ($rsp[2]&0xF) * 10 + ($rsp[3]&0xF);
        if($errCode != 0){
            throw new Exception("Response ErrorCode $errCode from HSM.");
        }
        else {
            return substr($rsp, 4+4);
        }
    }
    
    public static function NC_GetHsmInfo( $socket){
        $str = "NC";
        $len1 = strlen($str);
        $buff = Bytes::shortToBytesBigEnd(intval($len1));
        $str = Bytes::toStr($buff).$str;
        socket_write($socket, $str, strlen($str));
        $rsp1=socket_read($socket,2);
        $len2 = Bytes::bytesToShortBigEnd($rsp1, 0);
        $rsp2 = socket_read($socket, $len2);
        return $rsp2;
    }
}
