<?php
tslib_eidtools::connectDB();

$q = $_GET['q'];

$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
	'*',
	'tx_a21glossary_main',
	"exclude=0 AND (short LIKE ".$GLOBALS['TYPO3_DB']->fullQuoteStr("%".$q."%", 'tx_a21glossary_main')." OR
		longversion LIKE ".$GLOBALS['TYPO3_DB']->fullQuoteStr("%".$q."%", 'tx_a21glossary_main')." OR 
		description LIKE ".$GLOBALS['TYPO3_DB']->fullQuoteStr("%".$q."%", 'tx_a21glossary_main').")",
	'',
	'',
	100
);

while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
	echo $row['short']."|".$row['short']."\n";
}
?> 