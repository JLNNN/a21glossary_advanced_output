<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Tim Lochmueller <webmaster@fruit-lab.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Plugin 'A21glossary Advanced Output' for the 'a21glossary_advanced_output' extension.
 *
 * @author        Tim Lochmueller <webmaster@fruit-lab.de>
 * @package       TYPO3
 * @subpackage    tx_a21glossaryadvancedoutput
 */
class tx_a21glossaryadvancedoutput_pi1 extends TYPO3\CMS\Frontend\Plugin\AbstractPlugin {

	/**
	 * Extension Prefix ID
	 *
	 * @var String
	 */
	var $prefixId = 'tx_a21glossaryadvancedoutput_pi1';

	/**
	 * Relative Pfad zum Script
	 *
	 * @var string
	 */
	var $scriptRelPath = 'pi1/class.tx_a21glossaryadvancedoutput_pi1.php';

	/**
	 * Der Extension Key
	 *
	 * @var string
	 */
	var $extKey = 'a21glossary_advanced_output';

	/**
	 * @param $int
	 *
	 * @return bool
	 */
	function testInt($int) {
		if (class_exists('t3lib_utility_Math')) {
			return t3lib_utility_Math::canBeInterpretedAsInteger($int);
		}
		return \TYPO3\CMS\Core\Utility\MathUtility::canBeInterpretedAsInteger($int);
	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param    string $content : The PlugIn content
	 * @param    array  $conf    : The PlugIn configuration
	 *
	 * @return    The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 0;
		$this->pi_checkCHash = TRUE;
		$this->alphabet = array(
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		);

		// Fix Wrong Links
		if (isset($_GET['tx_a21glossary']['uid']) && $this->testInt($_GET['tx_a21glossary']['uid'])) {
			$this->piVars['uid'] = $_GET['tx_a21glossary']['uid'];
		}
		if (!$this->testInt($this->piVars['uid'])) {
			unset($this->piVars['uid']);
		}

		if (isset($_GET['tx_a21glossary']['back'])) {
			$this->piVars['back'] = $_GET['tx_a21glossary']['back'];
		}

		$this->cObj = new TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer();
		$this->orig_templateCode = $this->cObj->fileResource($this->conf['templateFile']);

		$mainTemplateCode = $this->cObj->getSubpart($this->orig_templateCode, '###VIEW_GLOSSARY###');
		$markerArray = array(
			'###SHOW_GLOSSARY_RESULT###' => $this->getGlossaryResult($this->cObj->getSubpart($this->orig_templateCode, '###VIEW_GLOSSARY_RESULT###')),
			'###SHOW_SEARCH_FORM###'     => $this->getSearchForm($this->cObj->getSubpart($this->orig_templateCode, '###VIEW_SEARCH_FORM###')),
			'###SHOW_NAV_BAR###'         => $this->getNavBar($this->cObj->getSubpart($this->orig_templateCode, '###VIEW_NAV_BAR###')),
			'###SHOW_SELECTED_CHAR###'   => $this->getSelectedChar($this->cObj->getSubpart($this->orig_templateCode, '###VIEW_SELECTED_CHAR###')),
			'###SHOW_BROWSE_LINKS###'    => ($this->conf['disablePageBrowser']) ? '' : $this->getBrowseResults(((isset($this->piVars['char']) && trim($this->piVars['char']) == '') ? FALSE : $this->conf['showResultCount']), '', $this->conf['spacer']),
		);


		$content = $this->cObj->substituteMarkerArray($mainTemplateCode, $markerArray);
		return $this->pi_wrapInBaseClass($content);
	} // function - main

	/**
	 * Gibt den gewählten Buchstaben zurück
	 *
	 * @param String $tpl
	 *
	 * @return String
	 */
	function getSelectedChar($tpl) {
		if (isset($this->piVars['search']) AND $this->conf['hideNavAtSearch']) {
			return '';
		}

		if (isset($this->piVars['char']) && ($this->piVars['char'] != "all")) {
			$marker = array();
			$marker['###SHOW_CHAR###'] = htmlspecialchars($this->piVars['char']);
			$tpl = $this->cObj->substituteMarkerArray($tpl, $marker, $wrap = '', $uppercase = 0);
			return $tpl;
		}
	}

	/**
	 * Get the Search Form Template
	 *
	 * @param    string $tpl : The function Template
	 *
	 * @return The content that is displayed for the search form
	 */
	function getSearchForm($tpl) {
		$marker = array();

		// build Cache Link
		$cache_link = $this->pi_linkTP('-', array(), 0, $GLOBALS['TSFE']->id);
		$cache_teile = explode('"', $cache_link);
		$link = $cache_teile[1];

		$marker = array(
			'###FORM_START###'       => '<form action="' . $link . '" method="post" id="glossaryform">',
			'###FORM_END###'         => '</form>',
			'###LL_SEARCH###'        => $this->pi_getLL('search'),
			'###LL_SEARCH_BUTTON###' => $this->pi_getLL('search_button'),
		);

		return $this->cObj->substituteMarkerArray($tpl, $marker);

	} // function - getSearchForm

	/**
	 * Gibt die Navigationsbar zurück
	 *
	 * @param String $tpl
	 *
	 * @return String
	 */
	function getNavBar($tpl) {
		if (isset($this->piVars['search']) AND $this->conf['hideNavAtSearch']) {
			return '';
		}

		$alphabet = $this->alphabet;

		if ($this->conf['useAreaNavigation']) {
			$alphabet = explode(',', $this->conf['areaNavigation']);

		}
		#[useAreaNavigation] => 1 [areaNavigation] => a-d,e-m,n-z )

		// Char List generator
		$subcontent = '';
		$subtpl = $this->cObj->getSubpart($this->orig_templateCode, '###LIST_CHARS###');
		foreach ($alphabet as $char) {
			if ($this->conf['hideEmptyNavItems'] && !$this->conf['useAreaNavigation']) {
				$sub_sql = $this->buildSelectionQuery($char, TRUE);
				$res_count = $GLOBALS['TYPO3_DB']->sql_query($sub_sql);
				if ($GLOBALS['TYPO3_DB']->sql_num_rows($res_count) < 1) {
					continue;
				}
			}

			$subMarker = array();
			$parms = array($this->prefixId . '[char]' => str_replace(' ', '', strtolower($char)));
			$link = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $parms);

			// build Cache Link
			$cache_link = $this->pi_linkTP('-', $parms, 1, $GLOBALS['TSFE']->id);
			$cache_teile = explode('"', $cache_link);
			$link = $cache_teile[1];


			$subMarker['###LINK_TO_CHAR###'] = $link;
			$subMarker['###SHOW_CHAR###'] = $char;
			$subMarker['###SHOW_CHAR_SMALL###'] = strtolower($char);
			$subMarker['###AKTIV_CHAR_CLASS###'] = (isset($this->piVars['char']) AND $this->piVars['char'] == str_replace(' ', '', strtolower($char))) ? ' class="aktiv"' : '';
			$subcontent .= $this->cObj->substituteMarkerArray($subtpl, $subMarker);
		} // foreach
		$tpl = $this->cObj->substituteSubpart($tpl, 'LIST_CHARS', $subcontent);

		$marker = array();
		$parms = array($this->prefixId . '[char]' => 'all');
		$link = $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $parms);

		// build Cache Link
		$cache_link = $this->pi_linkTP('-', $parms, 1, $GLOBALS['TSFE']->id);
		$cache_teile = explode('"', $cache_link);
		$link = $cache_teile[1];

		$marker['###LINK_TO_ALL###'] = $link;
		$marker['###SHOW_ALL_LABEL###'] = $this->pi_getLL('all_chars');
		$marker['###AKTIV_CHAR_CLASS###'] = (isset($this->piVars['char']) AND $this->piVars['char'] == 'all') ? ' class="aktiv"' : '';

		$tpl = $this->cObj->substituteMarkerArray($tpl, $marker);
		return $tpl;
	}

	/**
	 * Baut die Auswahl Query zusammen
	 *
	 * @param String $char
	 *
	 * @return String
	 */
	function buildSelectionQuery($char = FALSE, $returnQueryOnly = FALSE) {
		// Build SQL
		if (isset($this->piVars['char'])) {
			$this->piVars['char'] = $this->piVars['char'];
		}
		if (isset($char)) {
			$char = $char;
		}

		$sql = "SELECT * FROM tx_a21glossary_main WHERE exclude=0 AND ";

		if (trim($this->conf['recordsStorageAt']) != '' AND trim($this->conf['recordsStorageAt']) != '0') {
			$sql .= "tx_a21glossary_main.pid IN (" . $this->pi_getPidList(trim($this->conf['recordsStorageAt']), intval($this->conf['recordsStorageAtRecursiveDeep'])) . ") AND ";
		}

		if (isset($this->piVars['uid'])) {
			$sql .= "uid=" . intval($this->piVars['uid']);
		} else if ($char && in_array(strtoupper($char), $this->alphabet)) {
			$sql .= "short LIKE " . $GLOBALS['TYPO3_DB']->fullQuoteStr((($char) ? $char : $this->piVars['char']) . "%", 'tx_a21glossary_main');
			#$sql .= "short LIKE '".mysql_real_escape_string(($char)?$char:$this->piVars['char'])."%'";
		} else if (isset($this->piVars['search'])) {
			$sql .= "(short LIKE " . $GLOBALS['TYPO3_DB']->fullQuoteStr("%" . $this->piVars['search'] . "%", 'tx_a21glossary_main') . " OR ";
			$sql .= "description LIKE " . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['search'] . "%", 'tx_a21glossary_main') . ")";
			#$sql .= "(short LIKE '%".mysql_real_escape_string($this->piVars['search'])."%' OR description LIKE '%".mysql_real_escape_string($this->piVars['search'])."%')";
		} else if (isset($this->piVars['char']) AND $this->piVars['char'] == 'all') {
			$sql .= "1=1";
		} else if (isset($this->piVars['char']) OR $char) {
			if ($this->conf['useAreaNavigation']) {
				$alphabet = explode(',', $this->conf['areaNavigation']);
				$OK = FALSE;
				foreach ($alphabet as $a) {
					if (str_replace(' ', '', strtolower($a)) == $this->piVars['char']) {
						$OK = TRUE;
						continue;
					}
				}

				if ($OK) {
					$chars = explode('-', strtoupper($this->piVars['char']));
					$sql .= "(";
					$startChars = FALSE;
					foreach ($this->alphabet as $c) {
						$check = $c == $chars[0];
						if ($check || $startChars) {
							$startChars = TRUE;
							if ($c != $chars[0]) {
								$sql .= " OR ";
							}
							$sql .= "short LIKE " . $GLOBALS['TYPO3_DB']->fullQuoteStr($c . "%", 'tx_a21glossary_main');
						}
						if ($c == $chars[1]) {
							break;
						}
					}
					$sql .= ")";
				} elseif (isset($this->piVars['char']) && $this->piVars['char'] != '') {
					\TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Wrong Parameter set on ' . $this->prefixId . ' call!', 'a21glossary_advanced_output');
				}

			} else {
				$sql .= "short LIKE " . $GLOBALS['TYPO3_DB']->fullQuoteStr((($char) ? $char : $this->piVars['char']) . "%", 'tx_a21glossary_main');
			}
		} else {
			$this->piVars['char'] = trim($this->conf['startChar']);

			if ($this->piVars['char'] == '') {
				$sql .= '1=0';
			} else {
				return $this->buildSelectionQuery($char);
			}
		} // if

		$sql .= $this->cObj->enableFields('tx_a21glossary_main') . ' ORDER BY short';

		if ($returnQueryOnly) {
			return $sql;
		}

		// Für den Pagebrowser alle Einträge zählen und Abfrage mit LIMIT eingenzen
		// Anzahl der Datensätze für Pagebrowser ermitteln
		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
		$this->internal['res_count'] = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

		// Query um Limit erweitern für Pagebowser
		if ($this->piVars[pointer] > '') {
			$start = $this->conf['results_per_page'] * $this->piVars[pointer];
		} else {
			$start = 0;
		}
		$sql .= ' LIMIT ' . $start . ',' . $this->conf['results_per_page'];

		return $sql;
	}

	/**
	 * Gibt das Suchergebnis zurück
	 *
	 * @param String $tpl
	 *
	 * @return String
	 */
	function getGlossaryResult($tpl) {
		$sql = $this->buildSelectionQuery();
		$alphaSection = array();

		// SELECT Records
		$subcontent = '';
		$subtpl = $this->cObj->getSubpart($this->orig_templateCode, '###LIST_RESULT###');
		$res = $GLOBALS['TYPO3_DB']->sql_query($sql);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			$char = strtolower($row['short'][0]);
			$char = (in_array($char, array(
				'a',
				'b',
				'c',
				'd',
				'e',
				'f',
				'g',
				'h',
				'i',
				'j',
				'k',
				'l',
				'm',
				'n',
				'o',
				'p',
				'q',
				'r',
				's',
				't',
				'u',
				'v',
				'w',
				'x',
				'y',
				'z'
			))) ? $char : 'a';

			/*if($GLOBALS['TSFE']->sys_language_content){
				$OLmode = ($GLOBALS['TSFE']->sys_language_mode == 'strict'?'hideNonTranslated':'');
				$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_a21glossary_main', $row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
				if(!is_array($row)) continue;
			}*/

			$subMarker = array();
			$subMarker['###SHORT_WORD###'] = $row['short'];

			$subMarker['###SHORT_TYPE###'] = '<span class="type">' . $this->pi_getLL('type.' . $row['shorttype']) . '</span>';
			$subMarker['###LONG_VERSION###'] = $row['longversion'];

			$subMarker['###LONG_DESCRIPTION###'] = $this->pi_RTEcssText($row['description']);
			$subMarker['###SECTION###'] = ($row['uid'] == $this->piVars['uid']) ? 'goToSelected' : 'entry';
			$subMarker['###ALPHA_SECTION###'] = (in_array($char, $alphaSection)) ? substr(md5($row['short']), 0, 5) : $char;
			$alphaSection[] = $char;
			$subcontent .= $this->cObj->substituteMarkerArray($subtpl, $subMarker);

		} // while


		$tpl = $this->cObj->substituteSubpart($tpl, 'LIST_RESULT', $subcontent);

		return $tpl;

	}

	function intInRange($int, $start, $end) {
		if (class_exists('t3lib_utility_Math')) {
			return t3lib_utility_Math::forceIntegerInRange($int, $start, $end);
		}
		return \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($int, $start, $end);
	}

	/**
	 *  Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "pointer" will be pointing to the "result page" to show.
	 * Using $this->piVars['pointer'] as pointer to the page to display
	 * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
	 *
	 * @param    boolean        If set (default) the text "Displaying results..." will be show, otherwise not.
	 * @param    string         Attributes for the div tag which is wrapped around the table cells containing the browse links
	 * @param    string         If given, the passed string is used to seperate the links
	 *
	 * @return    string        Output HTML, wrapped in <div>-tags with a class attribute
	 */
	function getBrowseResults($showResultCount = 1, $divParams = '', $spacer = FALSE) {
		$pointer = intval($this->piVars['pointer']);
		$count = $this->internal['res_count'];
		$results_at_a_time = $this->intInRange($this->internal['results_at_a_time'], 1, 1000);
		$results_at_a_time = (int)$this->conf['results_per_page'];
		$maxPages = $this->intInRange($this->internal['maxPages'], 1, 100);
		$max = $this->intInRange(ceil($count / $results_at_a_time), 1, $maxPages);
		$links = array();

		$differ = (!isset($this->conf['pageBrowserRange'])) ? 4 : (int)$this->conf['pageBrowserRange'];

		// Make previous link
		if ($this->pi_alwaysPrev >= 0) {
			if ($pointer > 0) {
				$links[] = $this->pi_linkTP_keepPIvars($this->pi_getLL('browseresults.prev', '< Previous', TRUE), array('pointer' => ($pointer - 1 ? $pointer - 1 : '')), 1);
			} elseif ($this->pi_alwaysPrev) {
				$links[] = $this->pi_getLL('browseresults.prev', '< Previous', TRUE);
			}
		}

		// Make page links
		if ($max > 1) {
			for ($i = $pointer - $differ; $i < $pointer + $differ + 1; $i++) {
				if ($i < 0) {
					continue;
				}
				if ($i >= $max) {
					continue;
				}

				if ($pointer == $i) {
					$links[] = '<span ' . $this->pi_classParam('browsebox-SCell') . '><strong>' . $this->pi_linkTP_keepPIvars(trim($this->pi_getLL('browseresults.page', 'Page', TRUE) . ' ' . ($i + 1)), array('pointer' => $i), 1) . '</strong></span>';
				} else {
					$links[] = $this->pi_linkTP_keepPIvars(trim($this->pi_getLL('browseresults.page', 'Page', TRUE) . ' ' . ($i + 1)), array('pointer' => $i), 1);
				}
			}
		}

		// Make next link
		if ($pointer < ceil($count / $results_at_a_time) - 1) {
			$links[] = $this->pi_linkTP_keepPIvars($this->pi_getLL('browseresults.next', 'Next >', TRUE), array('pointer' => $pointer + 1), 1);
		}


		// Browsing box
		$pR1 = $pointer * $results_at_a_time + 1;
		$pR2 = $pointer * $results_at_a_time + $results_at_a_time;
		$sBox = '<div' . $this->pi_classParam('browsebox') . '>';

		$sBox .= ($showResultCount ? '<p>' . ($this->internal['res_count'] ? sprintf($this->pi_getLL('browseresults.displays', 'Displaying results %s to %s out of %s'), $this->internal['res_count'] > 0 ? $pR1 : 0, min(array(
				$this->internal['res_count'],
				$pR2
			)), $this->internal['res_count']) : $this->pi_getLL('browseresults.noResult', 'Sorry, no items were found.')) . '</p>' : '');
		$sBox .= '<' . trim('p ' . $divParams) . '>' . implode($spacer, $links) . '</p>';
		$sBox .= '</div>';

		return $sBox;
	}

} // class - tx_a21glossaryadvancedoutput_pi1

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/a21glossary_advanced_output/pi1/class.tx_a21glossaryadvancedoutput_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/a21glossary_advanced_output/pi1/class.tx_a21glossaryadvancedoutput_pi1.php']);
}
?>
