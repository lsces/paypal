{* $Header: /cvsroot/bitweaver/_bit_paypal/templates/paypal.tpl,v 1.1 2008/08/27 16:20:01 lsces Exp $ *}

<div class="floaticon">
{if $gContent->hasAdminPermission()}
  <a href="{$gBitLoc.PAYPAL_PKG_URL}admin/index.php"><img class="icon" src="{$gBitLoc.LIBERTY_PKG_URL}icons/config.gif"  alt="{tr}admin{/tr}" /></a>
{/if}
</div>

<div class="display paypal">
<div class="header">
<h1><a href="{$gBitLoc.PAYPAL_PKG_URL}index.php?view={$view}">{tr}Paypal List{/tr}</a></h1>
</div>
