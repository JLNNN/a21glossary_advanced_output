<?php
if(!defined('TYPO3_MODE'))
	die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi1/class.tx_a21glossaryadvancedoutput_pi1.php','_pi1','list_type',1);

$TYPO3_CONF_VARS['FE']['eID_include']['a21glossary_advanced_output'] = 'EXT:a21glossary_advanced_output/class.tx_a21glossaryadvancedoutput_eid.php';
?>
