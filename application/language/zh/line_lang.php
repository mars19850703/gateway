<?php
/**
 *    Line Pay
 */

/**
 *    付款
 */
$lang['LINEPAY_0000']           = 'LINEPAY_AUTH_00000'; // 付款成功
$lang['LINEPAY_AUTHCHECK_0000'] = 'LINEPAY_AUTHCHECK_00000'; // 授權成功等待 user 確認付款
$lang['LINEPAY_AUTH_TIMEOUT']   = 'LINEPAY_AUTH_TIMEOUT';
$lang['LINEPAY_REFUND_0000']    = 'LINEPAY_REFUND_00000';
$lang['LINEPAY_REFUND_TIMEOUT'] = 'LINEPAY_REFUND_TIMEOUT';
$lang['LINEPAY_CHECK_FAIL']     = 'BANK_40003'; // 驗證 sign 錯誤
$lang['LINEPAY_1101']           = 'LINEPAY_1101'; // 買家不是 LINE Pay 會員
$lang['LINEPAY_1102']           = 'LINEPAY_1102'; // 買方被停止交易
$lang['LINEPAY_1104']           = 'LINEPAY_1104'; // 找不到商家
$lang['LINEPAY_1105']           = 'LINEPAY_1105'; // 此商家無法使用 LINE Pay
$lang['LINEPAY_1106']           = 'LINEPAY_1106'; // 標頭資訊錯誤
$lang['LINEPAY_1110']           = 'LINEPAY_1110'; // 無法使用信用卡
$lang['LINEPAY_1124']           = 'LINEPAY_1124'; // 金額錯誤(scale)
$lang['LINEPAY_1133']           = 'LINEPAY_1133'; // 非有效之 oneTimeKey
$lang['LINEPAY_1141']           = 'LINEPAY_1141'; // 付款帳戶狀態錯誤
$lang['LINEPAY_1142']           = 'LINEPAY_1142'; // 餘額不足
$lang['LINEPAY_1145']           = 'LINEPAY_1145'; // 正在進行付款
$lang['LINEPAY_1150']           = 'LINEPAY_1150'; // 找不到交易紀錄
$lang['LINEPAY_1152']           = 'LINEPAY_1152'; // 已有既存付款
$lang['LINEPAY_1153']           = 'LINEPAY_1153'; // 付款 reserve 時的金額與申請的金額不一致
$lang['LINEPAY_1155']           = 'LINEPAY_1155'; // 交易編號不符合退款資格
$lang['LINEPAY_1159']           = 'LINEPAY_1159'; // 無付款申請資訊
$lang['LINEPAY_1163']           = 'LINEPAY_1163'; // 可退款日期已過無法退款
$lang['LINEPAY_1164']           = 'LINEPAY_1164'; // 超過退款額度
$lang['LINEPAY_1165']           = 'LINEPAY_1165'; // 交易已進行退款
$lang['LINEPAY_1169']           = 'LINEPAY_1169'; // 付款 confirm 所需要資訊錯誤
$lang['LINEPAY_1170']           = 'LINEPAY_1170'; // 使用者帳號的餘額有變動
$lang['LINEPAY_1172']           = 'LINEPAY_1172'; // 已有同一訂單編號的交易履歷
$lang['LINEPAY_1177']           = 'LINEPAY_1177'; // 超過允許擷取的交易數目 (100)
$lang['LINEPAY_1178']           = 'LINEPAY_1178'; // 不支援的貨幣
$lang['LINEPAY_1179']           = 'LINEPAY_1179'; // 無法處理狀態
$lang['LINEPAY_1183']           = 'LINEPAY_1183'; // 付款金額必須大於 0
$lang['LINEPAY_1184']           = 'LINEPAY_1184'; // 付款金額比付款申請時候的金額還大
$lang['LINEPAY_1194']           = 'LINEPAY_1194'; // This Merchant cannot use Payment.
$lang['LINEPAY_1198']           = 'LINEPAY_1198'; // 正在處理請求
$lang['LINEPAY_1199']           = 'LINEPAY_1199'; // 內部請求錯誤
$lang['LINEPAY_1280']           = 'LINEPAY_1280'; // 信用卡付款時候發生了臨時錯誤
$lang['LINEPAY_1281']           = 'LINEPAY_1281'; // 信用卡付款錯誤
$lang['LINEPAY_1282']           = 'LINEPAY_1282'; // 信用卡授權錯誤
$lang['LINEPAY_1283']           = 'LINEPAY_1283'; // 因疑似詐騙，拒絕付款
$lang['LINEPAY_1284']           = 'LINEPAY_1284'; // 暫時無法以信用卡付款
$lang['LINEPAY_1285']           = 'LINEPAY_1285'; // 信用卡資訊不完整
$lang['LINEPAY_1286']           = 'LINEPAY_1286'; // 信用卡付款資訊不完整
$lang['LINEPAY_1287']           = 'LINEPAY_1287'; // 信用卡已過期
$lang['LINEPAY_1288']           = 'LINEPAY_1288'; // 信用卡的額度不足
$lang['LINEPAY_1289']           = 'LINEPAY_1289'; // 超過信用卡付款金額上限
$lang['LINEPAY_1290']           = 'LINEPAY_1290'; // 超過一次性付款的額度
$lang['LINEPAY_1291']           = 'LINEPAY_1291'; // 此信用卡已被掛失
$lang['LINEPAY_1292']           = 'LINEPAY_1292'; // 此信用卡已被停卡
$lang['LINEPAY_1293']           = 'LINEPAY_1293'; // 信用卡驗證碼 (CVN) 無效
$lang['LINEPAY_1294']           = 'LINEPAY_1294'; // 此信用卡已被列入黑名單
$lang['LINEPAY_1295']           = 'LINEPAY_1295'; // 信用卡號無效
$lang['LINEPAY_1296']           = 'LINEPAY_1296'; // 無效的金額
$lang['LINEPAY_1298']           = 'LINEPAY_1298'; // 信用卡付款遭拒
$lang['LINEPAY_1900']           = 'LINEPAY_1900'; // 暫時錯誤，請稍後再試
$lang['LINEPAY_1901']           = 'LINEPAY_1901'; // 暫時錯誤，請稍後再試
$lang['LINEPAY_1902']           = 'LINEPAY_1902'; // 暫時錯誤，請稍後再試
$lang['LINEPAY_1903']           = 'LINEPAY_1903'; // 暫時錯誤，請稍後再試
$lang['LINEPAY_1999']           = 'LINEPAY_1999'; // 嘗試呼叫的資訊與前一次的資訊不符
$lang['LINEPAY_2101']           = 'LINEPAY_2101'; // 參數錯誤
$lang['LINEPAY_2102']           = 'LINEPAY_2102'; // JSON 資料格式錯誤
$lang['LINEPAY_2103']           = 'LINEPAY_2103'; // 錯誤請求。請確認 returnMessage
$lang['LINEPAY_2104']           = 'LINEPAY_2104'; // 錯誤請求。請確認 returnMessage
$lang['LINEPAY_9000']           = 'LINEPAY_9000'; // 內部錯誤
