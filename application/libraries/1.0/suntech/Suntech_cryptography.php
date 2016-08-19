<?php

class Suntech_cryptography
{
    /**
     *  智付寶送資料的加密機制
     *
     *  @param $key string 加密 key
     *  @param $iv string 加密 iv
     *  @param $data array 需要加密的資料
     *  @return string 加密字串
     */
    public function encryption($coyProStr, $secret)
    {
        $key   = $this->getKey($secret);
        $size  = mcrypt_get_block_size(MCRYPT_TRIPLEDES, 'ecb');
        // $input = $this->pkcs5_pad($coyProStr, $size);
        $td    = mcrypt_module_open(MCRYPT_TRIPLEDES, '', 'ecb', '');
        $iv    = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $coyProStr);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data);
        $data = urlencode($data);

        return $data;
    }

    /**
     *  將資料加上垃圾字串
     *
     *  @param $text string 資料字串
     *  @param $blockszie string 加垃圾的 szie
     *  @return string 加完垃圾的資料字串
     */
    private function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function getKey($secret)
    {
        if (strlen($secret) >= 8) {
            return '1234567890' . substr($secret, 0, 8) . '123456';
        } else {
            return '1234567890' . str_pad($secret, 8, '0', STR_PAD_RIGHT) . '123456';
        }
    }
}
