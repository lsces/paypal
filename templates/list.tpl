{strip}

<div class="floaticon">{bithelp}</div>

<div class="listing paypals">
	<div class="header">
		<h1>{tr}Paypals{/tr}</h1>
	</div>

	<div class="body">

		{include file="bitpackage:paypal/display_list_header.tpl"}

		<div class="navbar">
			<ul>
				<li>{biticon ipackage="icons" iname="emblem-symbolic-link" iexplain="sort by"}</li>
		{*		<li>{smartlink ititle="Paypal Number" isort="content_id" idefault=1 iorder=desc ihash=$listInfo.ihash}</li>		
				<li>{smartlink ititle="Forename" isort="forename" ihash=$listInfo.ihash}</li>		*}
				<li>{smartlink ititle="Surname" isort="surname" ihash=$listInfo.ihash}</li>
				<li>{smartlink ititle="Organisation" isort="organisation" ihash=$listInfo.ihash}</li>
				<li>{smartlink ititle="Address" isort="street" ihash=$listInfo.ihash}</li>
				<li>{smartlink ititle="Town" isort="town" ihash=$listInfo.ihash}</li>
				<li>{smartlink ititle="Postcode" isort="postcode" ihash=$listInfo.ihash}</li>
			</ul>
		</div>

		<ul class="clear data">
			{section name=content loop=$listpaypals}
				<li class="item {cycle values='odd,even'}">
						<a href="display_paypal.php?content_id={$listpaypals[content].content_id}" title="ci_{$listpaypals[content].content_id}">
						{$listpaypals[content].content_id}&nbsp;-&nbsp;
						{$listpaypals[content].prefix}&nbsp;
						{$listpaypals[content].forename}&nbsp;
						{$listpaypals[content].surname} 
						</a>&nbsp;&nbsp;&nbsp;
						{if isset($listpaypals[content].organisation) && ($listpaypals[content].organisation <> '') }Company: {$listpaypals[content].organisation}&nbsp;&nbsp;{/if} 
						{if isset($listpaypals[content].dob) && ($listpaypals[content].dob <> '')  }DOB: {$listpaypals[content].dob}&nbsp;&nbsp;{/if}
						{if isset($listpaypals[content].nino) && ($listpaypals[content].nino <> '') }NI: {$listpaypals[content].nino}&nbsp;&nbsp;{/if}
						
					<div class="footer">
						{if isset($listpaypals[content].uprn) && ($listpaypals[content].uprn <> '') }UPRN: {$listpaypals[content].uprn}&nbsp;&nbsp;{/if}
						{tr}Address{/tr}: 
						{if isset($listpaypals[content].sao) && ($listpaypals[content].sao <> '') }
							{$listpaypals[content].sao},&nbsp;{/if}
						{if isset($listpaypals[content].pao) && ($listpaypals[content].pao <> '') }
							{$listpaypals[content].pao},&nbsp;{/if}
						{if isset($listpaypals[content].number) && ($listpaypals[content].number <> '') }
							{$listpaypals[content].number},&nbsp;{/if}
						{if isset($listpaypals[content].street) && ($listpaypals[content].street <> '') }
							{$listpaypals[content].street},&nbsp;{/if}
						{if isset($listpaypals[content].locality) && ($listpaypals[content].locality <> '') }
							{$listpaypals[content].locality},&nbsp;{/if}
						{if isset($listpaypals[content].town) && ($listpaypals[content].town <> '') }
							{$listpaypals[content].town},&nbsp;{/if}
						{if isset($listpaypals[content].county) && ($listpaypals[content].county <> '') }
							{$listpaypals[content].county},&nbsp;{/if}
						{$listpaypals[content].postcode}&nbsp;&nbsp;
						{tr}Links{/tr}: {$listpaypals[content].links|default:0}
						{tr}Enquiries{/tr}: {$listpaypals[content].enquiries|default:0}
					</div>

					<div class="clear"></div>
				</li>
			{sectionelse}
				<li class="item norecords">
					{tr}No records found{/tr}
				</li>
			{/section}
		</ul>

		{pagination}
	</div><!-- end .body -->
</div><!-- end .irlist -->

{/strip}
