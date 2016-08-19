
<form id="web_payment_form" method="post" action="<?=$gateway?>">
	<? 
	if (isset($fields) && isset($data)){ 
		foreach ($fields as $f){
	?>
	<input type="hidden" id="<?=$f?>" name="<?=$f?>" value="<?=(isset($data[$f]))? $data[$f] : ''?>">
	<?
		}
	}
	?>	
</form>