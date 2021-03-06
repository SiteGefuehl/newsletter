<?php
/**
 * This is the htmlmail-opened-ping script, that detects if a user has opened the mail
 */

require ('browserrun.php');

// Record that the email was opened 
$authcode = addslashes($_REQUEST['c']);

$TYPO3_DB->sql_query("UPDATE tx_newsletter_domain_model_email SET open_time = " . time() . " WHERE open_time = 0 AND MD5(CONCAT(uid, recipient_address)) = '$authcode' LIMIT 1");

// Tell the target that he opened the email
$rs = $TYPO3_DB->sql_query("
SELECT tx_newsletter_domain_model_newsletter.recipient_list, tx_newsletter_domain_model_email.recipient_address
FROM tx_newsletter_domain_model_email
LEFT JOIN tx_newsletter_domain_model_newsletter ON (tx_newsletter_domain_model_email.newsletter = tx_newsletter_domain_model_newsletter.uid)
LEFT JOIN tx_newsletter_domain_model_recipientlist ON (tx_newsletter_domain_model_newsletter.recipient_list = tx_newsletter_domain_model_recipientlist.uid) 
WHERE MD5(CONCAT(tx_newsletter_domain_model_email.uid, tx_newsletter_domain_model_email.recipient_address)) = '$authcode' AND recipient_list IS NOT NULL
LIMIT 1");

if (list($recipientListUid, $emailAddress) = $TYPO3_DB->sql_fetch_row($rs)) {
	$recipientListRepository = t3lib_div::makeInstance('Tx_Newsletter_Domain_Repository_RecipientListRepository');
	$recipientList = $recipientListRepository->findByUid($recipientListUid);
	if ($recipientList)
	{
		$recipientList->registerOpen($emailAddress);
	}
}

header('Content-type: image/gif');
readfile('clear.gif');
