<?php

$lang['Group']                   = '複合式交易';
$lang['PAYMENT_00000']           = '交易完成';
$lang['PAYMENT_00001']           = '交易失敗';
$lang['PAYMENT_QUERY_00000']     = '完成查詢';
$lang['DEFAULT_00001']           = '失敗';
$lang['REPORT_00000']            = '回報完成';
$lang['REPORT_00001']            = '回報完成';
$lang['SETTING_00000']           = '取得設定檔成功';
$lang['SETTING_00001']           = '取得設定檔失敗';
$lang['HANDOVERS_00000']         = '交班成功';
$lang['HANDOVERS_00001']         = '交班失敗';
$lang['INVOICE_ISSUE_00000']     = '開立發票成功';
$lang['INVOICE_TOUCH_00000']     = '觸發開立發票成功';
$lang['INVOICE_INVALID_00000']   = '作廢發票成功';
$lang['INVOICE_ALLOWANCE_00000'] = '折讓發票成功';
$lang['INVOICE_SEARCH_00000']    = '查詢發票成功';
$lang['INVOICE_ISSUE_00001']     = '開立發票失敗';
$lang['REPRINT_00000']           = '重印成功';

/**
 *    商店類別：首位代碼 1
 */
$lang['MER_10000'] = '商店代號錯誤';
$lang['MER_10001'] = '商店代號停用';
$lang['MER_10002'] = '商店代號空白';
$lang['MER_10003'] = '商店代號未開通';
$lang['MER_10004'] = '商店代號審核中';
$lang['MER_10005'] = '商店未申請啟用電子發票';

/**
 *    交易類別：首位代碼 2
 */
$lang['TRA_20000'] = '交易類別錯誤';
$lang['TRA_20001'] = '付款金額幣別錯誤';
$lang['TRA_20002'] = '付款金額錯誤';
$lang['TRA_20003'] = '商店訂單編號錯誤';
$lang['TRA_20004'] = '商店訂單編號重覆';
$lang['TRA_20005'] = '金流商代號錯誤';
$lang['TRA_20006'] = '未開通 %s 服務';
$lang['TRA_20007'] = '設定幣別錯誤';
$lang['TRA_20008'] = '分期付款期別錯誤';
$lang['TRA_20009'] = '商店訂單編號空白';
$lang['TRA_20010'] = '重印類別錯誤';
$lang['TRA_20011'] = '商品名稱空白';
$lang['TRA_20012'] = '商品備註空白';
$lang['TRA_20013'] = '訂單查詢類型空白';
$lang['TRA_20014'] = '訂單查詢類型錯誤';
// 取消授權
$lang['TRA_22000'] = '取消授權資料錯誤';
$lang['TRA_22001'] = '取消授權金額錯誤';
$lang['TRA_22002'] = '此訂單已取消授權';
// 請款
$lang['TRA_22100'] = '請款資料錯誤';
$lang['TRA_22101'] = '請款金額錯誤';
$lang['TRA_22102'] = '此訂單已請過款';
// 退款
$lang['TRA_22200'] = '退款資料錯誤';
$lang['TRA_22201'] = '退款金額錯誤';
$lang['TRA_22202'] = '此訂單已不能退款';
// 查詢
$lang['TRA_22300'] = '查詢金額錯誤';
$lang['TRA_22301'] = '查無此訂單';

/**
 *    信用卡類別：首位代碼 3
 */
$lang['CREDIT_30000'] = '信用卡卡號錯誤';
$lang['CREDIT_30001'] = '信用卡到期日錯誤格式YYYYMM';
$lang['CREDIT_30002'] = '信用卡末3碼錯誤';
$lang['CREDIT_30003'] = '信用卡資格停用';

/**
 *    收單機構類別：首位代碼 4
 */
$lang['BANK_40000'] = '銀行授權失敗';
$lang['BANK_40001'] = '銀行交易失敗';
$lang['BANK_40002'] = '銀行分期付款失敗';
$lang['BANK_40003'] = '銀行授權資料驗證錯誤';

/**
 *    發票類別：首位代碼 6
 */
$lang['INVOICE_60000'] = '發票買受人姓名空白';
$lang['INVOICE_60001'] = '發票買受人姓名錯誤';
$lang['INVOICE_60002'] = '發票買受人統編錯誤';
$lang['INVOICE_60003'] = '發票買受人電子信箱空白';
$lang['INVOICE_60004'] = '發票買受人電子信箱錯誤';
$lang['INVOICE_60005'] = '開立發票方式空白';
$lang['INVOICE_60006'] = '索取紙本發票空白';
$lang['INVOICE_60007'] = '索取紙本發票錯誤';
$lang['INVOICE_60008'] = '發票稅別空白';
$lang['INVOICE_60009'] = '發票稅別錯誤';
$lang['INVOICE_60010'] = '發票稅率空白';
$lang['INVOICE_60011'] = '發票稅率錯誤';
$lang['INVOICE_60012'] = '發票稅額錯誤';
$lang['INVOICE_60013'] = '發票金額錯誤';
$lang['INVOICE_60014'] = '發票商品名稱空白';
$lang['INVOICE_60015'] = '發票商品名稱長度不得超過 30 個字';
$lang['INVOICE_60016'] = '發票商品數量空白';
$lang['INVOICE_60017'] = '發票商品數量錯誤';
$lang['INVOICE_60018'] = '發票商品單位空白';
$lang['INVOICE_60019'] = '發票商品單位錯誤';
$lang['INVOICE_60020'] = '發票商品單價空白';
$lang['INVOICE_60021'] = '發票商品單價錯誤';
$lang['INVOICE_60022'] = '商品小記金額錯誤';
$lang['INVOICE_60023'] = '商品資訊格式錯誤或缺少資料';
$lang['INVOICE_60024'] = '發票欄位資料不齊全或格式錯誤';
$lang['INVOICE_60025'] = '查無發票資料';
$lang['INVOICE_60026'] = '未簽定合約或合約已到期';
$lang['INVOICE_60027'] = '可開立張數已用罄';
$lang['INVOICE_60028'] = '網路連線異常';
$lang['INVOICE_60029'] = '發票已作廢過';
$lang['INVOICE_60030'] = '無法作廢';
$lang['INVOICE_60031'] = '超過可作廢期限';
$lang['INVOICE_60032'] = '發票已開立，但未上傳至財政部，無法作廢';
$lang['INVOICE_60033'] = '觸發開立發票資料錯誤';
// 作廢發票
$lang['INVOICE_61000'] = '發票號碼錯誤';
$lang['INVOICE_61001'] = '發票號碼空白';
$lang['INVOICE_61002'] = '作廢發票資料錯誤';
$lang['INVOICE_61003'] = '作廢發票理由字數上限為 20 個字';
// 折讓發票
$lang['INVOICE_62000'] = '商品稅額金額錯誤';
$lang['INVOICE_62001'] = '折讓發票金額錯誤';
$lang['INVOICE_62002'] = '折讓發票資料錯誤';

/**
 *    系統訊息類別：首位代碼 7
 */
$lang['SYS_60000'] = '資料空白';
$lang['SYS_60001'] = '資料加密格式錯誤';
$lang['SYS_60002'] = '資料不齊全';
$lang['SYS_60003'] = '資料格式錯誤';
$lang['SYS_60004'] = '加密 Key 過期';
$lang['SYS_70000'] = '連線逾時';
$lang['SYS_70001'] = '回傳類別錯誤';
$lang['SYS_70002'] = '商店金流商設定錯誤'; // 是商店跟金流商的 key
$lang['SYS_70003'] = '服務代碼空白';
$lang['SYS_70004'] = '服務未開通';
$lang['SYS_70005'] = '服務代碼錯誤';
$lang['SYS_70006'] = '服務審核中';
$lang['SYS_70007'] = '自動請款空白';
$lang['SYS_70008'] = '自動請款參數錯誤';
$lang['SYS_70009'] = '回傳網址錯誤';
$lang['SYS_70010'] = 'Gateway 版本錯誤，請更新您的應用程式';
$lang['SYS_70011'] = '更新回報錯誤，查無此更新';
$lang['SYS_70012'] = '功能服務代碼錯誤';
$lang['SYS_70013'] = '服務審核未過';
$lang['SYS_70014'] = '服務已遭停用';
$lang['SYS_70015'] = '商店金流商商店代號空白';
$lang['SYS_70016'] = '商店金流商商店代號錯誤';
$lang['SYS_70017'] = '商店金流商商店無此訂單';
$lang['SYS_70018'] = '時間錯誤';
$lang['SYS_70019'] = '檢查碼 CheckValue 有錯誤';
$lang['SYS_20009'] = '版本號錯誤';
$lang['SYS_20010'] = '版本號空白';

/**
 *    終端代碼類別：首位代碼 8
 */
$lang['TERMINAL_80000'] = '終端代碼空白';
$lang['TERMINAL_80001'] = '終端代碼錯誤';
$lang['TERMINAL_80002'] = '終端停用';
$lang['TERMINAL_80003'] = '終端使用期限到期，請洽客服';

/**
 *    EDC 類別：首位代碼 9
 */
$lang['EDC_90000'] = 'EDC 代碼空白';
$lang['EDC_90001'] = 'EDC mac 空白';
$lang['EDC_90002'] = 'EDC 代碼錯誤';
$lang['EDC_90003'] = 'EDC mac 錯誤';
$lang['EDC_90004'] = 'EDC 未開通';
$lang['EDC_90005'] = 'EDC 停用';
$lang['EDC_90006'] = 'EDC 經度錯誤';
$lang['EDC_90007'] = 'EDC 緯度錯誤';
$lang['EDC_90008'] = 'EDC 應用程式名稱錯誤';
$lang['EDC_90009'] = 'EDC 應用程式名稱空白';
$lang['EDC_90010'] = 'EDC 網路環境參數錯誤';
$lang['EDC_90011'] = 'EDC 更新設定參數錯誤';

/**
 *    支付寶類別
 */
$lang['ALIPAY_00000'] = '交易成功';
$lang['ALIPAY_00001'] = '交易失敗';
$lang['ALIPAY_10000'] = 'BarCode 空白';
$lang['ALIPAY_10001'] = 'BarCode 錯誤';
$lang['ALIPAY_10002'] = '台新支付寶閘道錯誤';
$lang['ALIPAY_10003'] = '台新支付寶特店代號錯誤';
$lang['ALIPAY_10004'] = '交易回傳 XML 格式錯誤';
$lang['ALIPAY_10005'] = '台新支付寶商店代號錯誤';
$lang['ALIPAY_10006'] = '台新支付寶終端機代號錯誤';
$lang['ALIPAY_10007'] = '台新支付寶訂單編號錯誤';
$lang['ALIPAY_10008'] = '台新支付寶交易時間異常';
$lang['ALIPAY_10009'] = '台新支付寶簽章異常';
$lang['ALIPAY_10010'] = '台新支付寶發生錯誤，請聯絡台新';
$lang['ALIPAY_10011'] = '台新支付寶查無此訂單';

/**
 *    LINE 類別
 */
$lang['LINEPAY_AUTH_00000']             = 'LINE Pay 交易成功';
$lang['LINEPAY_AUTH_00001']             = 'LINE Pay 交易失敗';
$lang['LINEPAY_AUTHCHECK_00000']        = '授權成功，等待 user 確認付款';
$lang['LINEPAY_AUTH_TIMEOUT']           = 'LINE Pay 授權逾時，請再次確認訂單狀態';
$lang['LINEPAY_REFUND_00000']           = 'LINE Pay 退款成功';
$lang['LINEPAY_REFUND_TIMEOUT']         = 'LINE Pay 退款逾時，請查詢確認訂單狀態';
$lang['LINEPAY_CONFIRM_00000']          = 'LINE Pay 訂單確認成功';
$lang['LINEPAY_CONFIRM_COMPLETE_00000'] = 'LINE Pay 確認訂單付款成功';
$lang['LINEPAY_CONFIRM_FAIL_00000']     = 'LINE Pay 確認訂單付款失敗';
$lang['LINEPAY_CONFIRM_CANCEL_00000']   = 'LINE Pay 確認訂單付款已取消';
$lang['LINEPAY_TIMEOUT']                = 'LINE Pay 連線逾時';
$lang['LINEPAY_1194']                   = 'LINE Pay 商店不允許使用付款';
$lang['LINEPAY_1101']                   = '買家不是 LINE Pay 會員';
$lang['LINEPAY_1102']                   = '買方被停止交易';
$lang['LINEPAY_1104']                   = '找不到商家';
$lang['LINEPAY_1105']                   = '此商家無法使用 LINE Pay';
$lang['LINEPAY_1106']                   = '標頭資訊錯誤';
$lang['LINEPAY_1110']                   = '無法使用信用卡';
$lang['LINEPAY_1124']                   = '金額錯誤(scale)';
$lang['LINEPAY_1133']                   = '非有效之 oneTimeKey';
$lang['LINEPAY_1141']                   = '付款帳戶狀態錯誤';
$lang['LINEPAY_1142']                   = '餘額不足';
$lang['LINEPAY_1145']                   = '正在進行付款';
$lang['LINEPAY_1150']                   = '找不到交易紀錄';
$lang['LINEPAY_1152']                   = '已有既存付款';
$lang['LINEPAY_1153']                   = '付款 reserve 時的金額與申請的金額不一致';
$lang['LINEPAY_1155']                   = '交易編號不符合退款資格';
$lang['LINEPAY_1159']                   = '無付款申請資訊';
$lang['LINEPAY_1163']                   = '可退款日期已過無法退款';
$lang['LINEPAY_1164']                   = '超過退款額度';
$lang['LINEPAY_1165']                   = '交易已進行退款';
$lang['LINEPAY_1169']                   = '付款 confirm 所需要資訊錯誤';
$lang['LINEPAY_1170']                   = '使用者帳號的餘額有變動';
$lang['LINEPAY_1172']                   = '已有同一訂單編號的交易履歷';
$lang['LINEPAY_1177']                   = '超過允許擷取的交易數目 (100)';
$lang['LINEPAY_1178']                   = '不支援的貨幣';
$lang['LINEPAY_1179']                   = '無法處理狀態';
$lang['LINEPAY_1183']                   = '付款金額必須大於 0';
$lang['LINEPAY_1184']                   = '付款金額比付款申請時候的金額還大';
$lang['LINEPAY_1198']                   = '正在處理請求';
$lang['LINEPAY_1199']                   = '內部請求錯誤';
$lang['LINEPAY_1280']                   = '信用卡付款時候發生了臨時錯誤';
$lang['LINEPAY_1281']                   = '信用卡付款錯誤';
$lang['LINEPAY_1282']                   = '信用卡授權錯誤';
$lang['LINEPAY_1283']                   = '因疑似詐騙，拒絕付款';
$lang['LINEPAY_1284']                   = '暫時無法以信用卡付款';
$lang['LINEPAY_1285']                   = '信用卡資訊不完整';
$lang['LINEPAY_1286']                   = '信用卡付款資訊不完整';
$lang['LINEPAY_1287']                   = '信用卡已過期';
$lang['LINEPAY_1288']                   = '信用卡的額度不足';
$lang['LINEPAY_1289']                   = '超過信用卡付款金額上限';
$lang['LINEPAY_1290']                   = '超過一次性付款的額度';
$lang['LINEPAY_1291']                   = '此信用卡已被掛失';
$lang['LINEPAY_1292']                   = '此信用卡已被停卡';
$lang['LINEPAY_1293']                   = '信用卡驗證碼 (CVN) 無效';
$lang['LINEPAY_1294']                   = '此信用卡已被列入黑名單';
$lang['LINEPAY_1295']                   = '信用卡號無效';
$lang['LINEPAY_1296']                   = '無效的金額';
$lang['LINEPAY_1298']                   = '信用卡付款遭拒';
$lang['LINEPAY_1900']                   = '暫時錯誤，請稍後再試';
$lang['LINEPAY_1901']                   = '暫時錯誤，請稍後再試';
$lang['LINEPAY_1902']                   = '暫時錯誤，請稍後再試';
$lang['LINEPAY_1903']                   = '暫時錯誤，請稍後再試';
$lang['LINEPAY_1999']                   = '嘗試呼叫的資訊與前一次的資訊不符';
$lang['LINEPAY_2101']                   = '參數錯誤';
$lang['LINEPAY_2102']                   = 'JSON 資料格式錯誤';
$lang['LINEPAY_2103']                   = '錯誤請求。請確認 returnMessage';
$lang['LINEPAY_2104']                   = '錯誤請求。請確認 returnMessage';
$lang['LINEPAY_9000']                   = '內部錯誤';
