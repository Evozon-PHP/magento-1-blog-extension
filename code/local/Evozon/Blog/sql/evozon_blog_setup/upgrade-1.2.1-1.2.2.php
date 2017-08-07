<?php
/**
 * Create transactional email for comment notification
 */

$installer = $this;

$installer->startSetup();

$code = 'comment_notifications';
$subject = '{{var data.subject}}';
$content = '{{template config_path="design/email/header"}}
{{inlinecss file="email-inline.css"}}

<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td class="action-content">
            <h1>Hello {{var data.comment.author.name}}.</h1>
            <p>You have a new reply on your comment.</p>
            <p>If you want to see the reply click<a href="{{var data.post.post_url}}"> HERE</a> and you will be redirected to the post page.</p>

            <p>
                If you have any questions, please feel free to contact us at
                <a href="mailto:{{var store_email}}">{{var store_email}}</a>
                {{depend store_phone}} or by phone at <a href="tel:{{var phone}}">{{var store_phone}}</a>{{/depend}}.
            </p>
        </td>
    </tr>
</table>

{{template config_path="design/email/footer"}}';

$template = Mage::getModel('adminhtml/email_template');

$template->setTemplateSubject($subject)
    ->setTemplateCode($code)
    ->setTemplateText($content)
    ->setModifiedAt(Mage::getSingleton('core/date')->gmtDate())
    ->setAddedAt(Mage::getSingleton('core/date')->gmtDate())
    ->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML);

$template->save();

$installer->endSetup();