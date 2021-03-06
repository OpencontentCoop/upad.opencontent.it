{*
    Variables that can be set in a set-block to customize the e-mail sent :
    - subject (default is "Registration info")
    - content_type (like described in site.ini/[MailSettings].ContentType)
    - email_sender (default is site.ini/[MailSettings].AdminEmail)
    - email_receiver (default is site.ini/[UserSettings].RegistrationEmail. If not set, it is site.ini/[MailSettings].AdminEmail
*}
{set-block scope=root variable=subject}{'New user registered at %siteurl'|i18n('design/standard/user/register',,hash('%siteurl',ezini('SiteSettings','SiteURL')))}{/set-block}
{'A new user has registered.'|i18n('design/standard/user/register')}


{$user|attribute(show,1)}

{'Account information.'|i18n('design/standard/user/register')}
{'Username'|i18n('design/standard/user/register','Login name')}: {$user.login}
{'Email'|i18n('design/standard/user/register')}: {$user.email}

{'Link to user information'|i18n('design/standard/user/register')}:
http://{$hostname}{concat('content/view/full/',$object.main_node_id)|ezurl(no)}
