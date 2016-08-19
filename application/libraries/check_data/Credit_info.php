<?php

class Credit_info extends BaseLibrary
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 *	判斷信卡卡號類型
	 */
    public function getCardType($cardNo, $length = null)
    {
        if (is_null($length)) {
            $length = strlen($cardNo);
        }
        $first = substr($cardNo, 0, 1);

        $length = intval($length);
        if ($length === 15) {
            switch ($first) {
                case '1':
                    $card4No = substr($cardNo, 0, 4);
                    if ($card4No === '1800') {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '2':
                    $card4No = substr($cardNo, 0, 4);
                    if ($card4No === '2131') {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '3':
                    $card3No = intval(substr($cardNo, 0, 3));
                    if ($card3No >= 340 && $card3No <= 379) {
                        $type = 'AMEX CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                default:
                    $type = 'UNDEFINED';
                    break;
            }
        } else if ($length === 16) {
            switch ($first) {
                case '3':
                    $card3No = intval(substr($cardNo, 0, 3));
                    if ($card3No >= 300 && $card3No <= 399) {
                        $type = 'JCB CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                case '4':
                    $type = 'VISA CARD';
                    break;
                case '5':
                    $second = intval(substr($cardNo, 1, 1));
                    if ($second > 0 && $second < 6) {
                        $type = 'MASTER CARD';
                    } else {
                        $type = 'UNDEFINED';
                    }
                    break;
                default:
                    $type = 'UNDEFINED';
                    break;
            }
        }
        return $type;
    }
}