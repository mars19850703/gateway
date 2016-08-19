<?php
/**
 *    智付寶
 */
$lang['SUCCESS']    = 'PAYMENT_00000';
$lang['CHECK_FAIL'] = 'BANK_40003';

/**
 *    信用卡幕後授權
 */
$lang['CREDIT_SUCCESS']    = 'PAYMENT_00000'; // SUCCESS
$lang['CREDIT_CHECK_FAIL'] = 'BANK_40003'; // 自己驗證跟智付寶的驗證code有錯誤
$lang['CREDIT_MEM40012']   = 'SYS_60002'; // 資料傳遞錯誤, 提示不齊全欄位
$lang['CREDIT_MEM40013']   = 'SYS_60002'; // 資料不齊全, 提示空白欄位
$lang['CREDIT_MEM40008']   = 'SYS_60002'; // 資料空白
$lang['CREDIT_MEM40014']   = 'SYS_70000'; // Time_Out
$lang['CREDIT_TRA10001']   = 'MER_10000'; // 商店代號錯誤
$lang['CREDIT_TRA10003']   = 'TRA_20002'; // 金額格式錯誤
$lang['CREDIT_TRA10008']   = 'SYS_60001'; // 資料加密錯誤
$lang['CREDIT_TRA10009']   = 'MER_10002'; // 商店代號空白
$lang['CREDIT_TRA10012']   = 'MER_10001'; // 商店代號停用
$lang['CREDIT_TRA10013']   = 'CREDIT_30003'; // 信用卡資格停用
$lang['CREDIT_TRA10016']   = 'BANK_40000'; // 信用卡授權失敗
$lang['CREDIT_TRA10037']   = 'TRA_20003'; // 商店訂單編號錯誤
$lang['CREDIT_TRA10054']   = 'SYS_60000'; // 檢查碼(CheckValue)有誤
$lang['CREDIT_TRA10075']   = 'TRA_20004'; // 商店訂單編號重覆
$lang['CREDIT_TRA20004']   = 'TRA_20004'; // 商店訂單編號重覆

/**
 *    Invoice
 */
$lang['INVOICE_ISSUE_SUCCESS']     = 'INVOICE_ISSUE_00000';
$lang['INVOICE_TOUCH_SUCCESS']     = 'INVOICE_TOUCH_00000';
$lang['INVOICE_INVALID_SUCCESS']   = 'INVOICE_INVALID_00000';
$lang['INVOICE_ALLOWANCE_SUCCESS'] = 'INVOICE_ALLOWANCE_00000';
$lang['INVOICE_SEARCH_SUCCESS']    = 'INVOICE_SEARCH_00000';
$lang['INVOICE_KEY10002']          = 'SYS_60001'; // 資料解密錯誤
$lang['INVOICE_KEY10004']          = 'SYS_60002'; // 資料不齊全
$lang['INVOICE_KEY10006']          = 'MER_10005'; // 商店未申請啟用電子發票
$lang['INVOICE_KEY10007']          = 'DEFAULT_00001'; // 頁面停留超過 30 分鐘
$lang['INVOICE_KEY10010']          = 'MER_10002'; // 商店代號空白
$lang['INVOICE_KEY10011']          = 'SYS_60002'; // PostData_欄位空白
$lang['INVOICE_KEY10012']          = 'SYS_60003'; // 資料傳遞錯誤
$lang['INVOICE_KEY10013']          = 'SYS_60002'; // 資料空白
$lang['INVOICE_KEY10014']          = 'SYS_70000'; // TimeOut
$lang['INVOICE_KEY10015']          = 'INVOICE_60013'; // 發票金額格式錯誤
$lang['INVOICE_INV10003']          = 'INVOICE_60023'; // 商品資訊格式錯誤或缺少資料
$lang['INVOICE_INV10004']          = 'INVOICE_60022'; // 商品資訊的商品小計計算錯誤
$lang['INVOICE_INV10006']          = 'INVOICE_60011'; // 稅率格式錯誤
$lang['INVOICE_INV10012']          = 'INVOICE_60013'; // 發票金額驗證錯誤
$lang['INVOICE_INV10013']          = 'INVOICE_60024'; // 發票欄位資料不齊全或格式錯誤
$lang['INVOICE_INV10014']          = 'TRA_20003'; // 自訂編號格式錯誤
$lang['INVOICE_INV10015']          = 'INVOICE_60013'; // 無未稅金額
$lang['INVOICE_INV10016']          = 'INVOICE_60012'; // 無稅金
$lang['INVOICE_INV20006']          = 'INVOICE_60025'; // 查無發票資料
$lang['INVOICE_INV70001']          = 'INVOICE_60024'; // 欄位資料格式錯誤
$lang['INVOICE_INV90005']          = 'INVOICE_60026'; // 未簽定合約或合約已到期
$lang['INVOICE_INV90006']          = 'INVOICE_60027'; // 可開立張數已用罄
$lang['INVOICE_NOR10001']          = 'INVOICE_60028'; // 網路連線異常
$lang['INVOICE_LIB10003']          = 'TRA_20004'; // 商店自訂編號重覆
$lang['INVOICE_LIB10005']          = 'INVOICE_60029'; // 發票已作廢過
$lang['INVOICE_LIB10007']          = 'INVOICE_60030'; // 無法作廢
$lang['INVOICE_LIB10008']          = 'INVOICE_60031'; // 超過可作廢期限
$lang['INVOICE_LIB10009']          = 'INVOICE_60032'; // 發票已開立，但未上傳至財政部，無法作廢

/**
 *    ATM
 */
$lang['ATM_SUCCESS']    = 'PAYMENT_00000'; // SUCCESS
$lang['ATM_CHECK_FAIL'] = 'BANK_40003'; // 自己驗證跟智付寶的驗證code有錯誤
$lang['ATM_TRA10001']   = 'MER_10000'; // 商店代號錯誤
$lang['ATM_TRA10003']   = 'TRA_20002'; // 金額格式錯誤
$lang['ATM_TRA10008']   = 'SYS_60001'; // 資料加密錯誤
$lang['ATM_TRA10009']   = 'MER_10002'; // 商店代號空白
$lang['ATM_TRA10012']   = 'MER_10001'; // 商店代號停用
$lang['ATM_TRA10013']   = 'CREDIT_30003'; // 信用卡資格停用
$lang['ATM_TRA10016']   = 'BANK_40000'; // 信用卡授權失敗
$lang['ATM_TRA10037']   = 'TRA_20003'; // 商店訂單編號錯誤
$lang['ATM_TRA10054']   = 'SYS_60000'; // 檢查碼(CheckValue)有誤
$lang['ATM_TRA10075']   = 'TRA_20004'; // 商店訂單編號重覆
$lang['ATM_TRA20004']   = 'TRA_20004'; // 商店訂單編號重覆
$lang['ATM_MEM40013']   = 'SYS_60002'; // 資料不齊全, 提示空白欄位

/**
 *    MPG
 */
$lang['MPG_SUCCESS']    = 'PAYMENT_00000'; // SUCCESS
$lang['MPG_CHECK_FAIL'] = 'BANK_40003'; // 自己驗證跟智付寶的驗證code有錯誤
$lang['MPG_MPG10001']   = 'BANK_40003'; // 商店代號空白
$lang['MPG_MPG10002']   = ''; // 串接程式版本參數值有誤
$lang['MPG_MPG10003']   = ''; // 回傳格式參數值錯誤
$lang['MPG_MPG10005']   = ''; // TimeStamp 錯誤
$lang['MPG_MPG10006']   = ''; // CheckValue [ 檢查碼 ]空白
$lang['MPG_MPG10007']   = ''; // 查無商店資料
$lang['MPG_MPG10008']   = ''; // CheckValue [ 檢查碼 ]驗證失敗
$lang['MPG_MPG10009']   = ''; // 商店訂單編號空白
$lang['MPG_MPG10010']   = ''; // 商店訂單編號格式錯誤，限英數字、底線，長度 20 字
$lang['MPG_MPG10012']   = ''; // 商店訂單金額空白
$lang['MPG_MPG10013']   = ''; // 金額填入非數字資訊
$lang['MPG_MPG10014']   = ''; // 訂單金額超過 9999999999
$lang['MPG_MPG10015']   = ''; // 商品資訊空白
$lang['MPG_MPG10016']   = ''; // 繳費期限日期格式錯誤
$lang['MPG_MPG10017']   = ''; // 商店狀態關閉或暫停，無法交易
$lang['MPG_MPG10018']   = ''; // 商店 Form Post 資料加密失敗
$lang['MPG_MPG10019']   = ''; // 登入智付寶會員參數空白
$lang['MPG_MPG10020']   = ''; // 回傳參數資料不存在
$lang['MPG_MPG10021']   = ''; // 回傳參數資料解密失敗
$lang['MPG_MPG10022']   = ''; // 查無商店金流設定資料
$lang['MPG_MPG10024']   = ''; // 付款人電子信箱格式錯誤
$lang['MPG_MPG10028']   = ''; // 信用卡卡號格式錯誤
$lang['MPG_MPG10029']   = ''; // 未輸入信用卡有效期
$lang['MPG_MPG10030']   = ''; // 未輸入信用卡背面末三碼
$lang['MPG_MPG10031']   = ''; // 服務未啟用
$lang['MPG_MPG10033']   = ''; // 信用卡未選擇一次刷卡或是分期交易
$lang['MPG_MPG10034']   = ''; // 信用卡支付未選擇分期期數
$lang['MPG_MPG10035']   = ''; // 信用卡支付分期交易旗標空白或參數格式錯誤
$lang['MPG_MPG10036']   = ''; // URL 非為 http 或 https 開頭
$lang['MPG_MPG10037']   = ''; // URL 格式錯誤
$lang['MPG_MPG10038']   = ''; // 禁止使用 localhost 或是 127.0.0.1 的網址格式
$lang['MPG_MPG20001']   = ''; // 驗證資料不存在
$lang['MPG_MPG20002']   = ''; // 驗證資料空白-商店代號
$lang['MPG_MPG20003']   = ''; // 驗證資料空白-訂單金額
$lang['MPG_MPG20004']   = ''; // 驗證資料空白-商店自訂訂單編號
$lang['MPG_MPG20005']   = ''; // 頁面停留超過 30 分鐘
$lang['MPG_MPG20006']   = ''; // 未選擇金融機構別
$lang['MPG_MPG20007']   = ''; // 未選擇支付方式
$lang['MPG_MPG20009']   = ''; // 未選擇超商別
$lang['MPG_TRA10003']   = ''; // 總數量格式錯誤
$lang['MPG_TRA10043']   = ''; // 信用卡到期日格式錯誤

/**
 *    查詢
 */
$lang['QUERY_SUCCESS']  = 'PAYMENT_QUERY_00000';
$lang['QUERY_MEM40013'] = 'SYS_60002'; // 資料不齊全 會提示空白欄位
$lang['QUERY_MEM40008'] = 'SYS_60000'; // 資料空白 會提示空白欄位
$lang['QUERY_MEM40014'] = 'SYS_70018'; // 傳送時間有誤
$lang['QUERY_TRA10001'] = 'SYS_70002'; // 商店代號錯誤
$lang['QUERY_TRA10003'] = 'TRA_22300'; // 金額填入非數字資訊
$lang['QUERY_TRA10009'] = 'SYS_70015'; // 商店代號空白
$lang['QUERY_TRA10012'] = 'SYS_70016'; // 商店代號停用
$lang['QUERY_TRA10021'] = 'SYS_70017'; // 查無該筆交易資訊
$lang['QUERY_TRA10036'] = 'SYS_70001'; // RespondType 欄位資料格式錯誤
$lang['QUERY_TRA10037'] = 'TRA_20003'; // 商店訂單編號錯誤
$lang['QUERY_TRA10054'] = 'SYS_70019'; // 檢查碼 CheckValue 有錯誤
