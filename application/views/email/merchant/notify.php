<table width="650" border="1" cellspacing="5" cellpadding="0">
    <tbody>
        <tr>
            <td align="center"><font style="font-family:微軟正黑體">商店代號</font></td>
            <td><?php echo $merchant['merchant_id']; ?></td>
        </tr>
        <tr>
            <td align="center"><font style="font-family:微軟正黑體">商店名稱</font></td>
            <td><?php echo $merchant['merchant_name']; ?></td>
        </tr>
        <tr>
            <td align="center"><font style="font-family:微軟正黑體">啟用項目</font></td>
            <td>
                <?php if($postData['UseInfo'] === 'ON'): ?>
                    一次付清：啟用<br />
                <?php else: ?>
                    一次付清：不啟用<br />
                <?php endif; ?>
                <?php if($postData['CreditInst'] === 'ON'): ?>
                    分期：啟用<br />
                <?php else: ?>
                    分期：不啟用<br />
                <?php endif; ?>
                <?php if($postData['CreditRed'] === 'ON'): ?>
                    紅利：啟用<br />
                <?php else: ?>
                    紅利：不啟用<br />
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td align="center"><font style="font-family:微軟正黑體">終端代碼</font></td>
            <td>
                <?php foreach($terminal as $index => $t): ?>
                    <?php echo $t['terminal_code']; ?>
                    <?php if((count($terminal) - 1) > $index): ?>
                        <?php echo '、'; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </td>
        </tr>
    </tbody>
</table>