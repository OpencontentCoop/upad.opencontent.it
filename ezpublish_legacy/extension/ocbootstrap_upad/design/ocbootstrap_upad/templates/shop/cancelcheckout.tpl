<div class="container">
{if eq($ErrorCode, "NO_CALLBACK")}
<div class="warning">
<h2>{"We did not get a confirmation from the payment server." | i18n("design/standard/shop")} </h2>
</div>
{"Please contact the owner of the webshop and provide your order ID" | i18n("design/standard/shop")}:<b>{$OrderID}</b><br/>
{else}
<div class="warning">
{"Payment was canceled for an unknown reason. Please try to buy again."|i18n("design/standard/shop")}
</div>
{/if}
</div>