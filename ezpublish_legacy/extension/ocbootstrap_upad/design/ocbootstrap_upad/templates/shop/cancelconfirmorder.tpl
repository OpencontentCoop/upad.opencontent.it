<div class="container">
<form method="post" action={"/shop/confirmorder/"|ezurl}>

<div class="message-error">
<h2>{$error_header}</h2>
<ul>
{foreach $error_list as $error}
    <li>{$error|wash}</li>
{/foreach}
</ul>
</div>

<div class="buttonblock">
<input class="button" type="submit" name="CancelButton" value="{'Back'|i18n('design/standard/shop')}" /> &nbsp;
</div>

</form>
</div>