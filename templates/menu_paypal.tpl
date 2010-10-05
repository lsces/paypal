{strip}
<ul>
	<li><a class="item" href="{$smarty.const.PAYPAL_PKG_URL}list.php">{tr}List Paypals{/tr}</a></li>
	{if $gBitUser->isAdmin() or $gBitUser->hasPermission( 'bit_p_edit_irlist' ) }
		<li><a class="item" href="{$smarty.const.PAYPAL_PKG_URL}edit.php">{biticon ipackage="icons" iname="document-new" iexplain="create paypal" iforce="icon"} {tr}Create/Edit a Paypal{/tr}</a></li>
	{/if}
	{if $gBitUser->hasPermission('p_paypal_admin')}
		<li><a class="item" href="{$smarty.const.PAYPAL_PKG_URL}load_golden.php">{tr}Load Paypal Index Dump{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.PAYPAL_PKG_URL}load_golden.php?update=1">{tr}Load Paypal Index Update{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.PAYPAL_PKG_URL}load_history.php">{tr}Load History Dump{/tr}</a></li>
		<li><a class="item" href="{$smarty.const.KERNEL_PKG_URL}admin/index.php?page=paypal">{tr}Admin paypals{/tr}</a></li>
	{/if}
</ul>
{/strip}