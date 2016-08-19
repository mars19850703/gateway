<?php

class Spgateway_cryptography
{
    /**
     *  智付寶送資料的加密機制
     *
     *  @param $key string 加密 key
     *  @param $iv string 加密 iv
     *  @param $data array 需要加密的資料
     *  @return string 加密字串
     */
    public function encryption($key, $iv, array $data)
    {
        $str = http_build_query($data);
        $str = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $this->addPadding($str), MCRYPT_MODE_CBC, $iv)));
        return $str;
    }

    /**
     *  將資料加上垃圾字串
     *
     *  @param $string string 資料字串
     *  @param $blockszie string 加垃圾的 szie
     *  @return string 加完垃圾的資料字串
     */
    private function addPadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }
}
