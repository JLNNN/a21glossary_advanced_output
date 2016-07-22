<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "a21glossary_advanced_output".
 *
 * Auto generated 22-07-2016 13:27
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'A21glossary Advanced Output',
  'description' => 'A extended Output for the A21glossary. SEO friendly incl. RealURL support & search function',
  'category' => 'fe',
  'version' => '0.2.2',
  'state' => 'beta',
  'author' => 'Tim Lochmueller, Thomas Buss',
  'author_email' => 'webmaster@fruit-lab.de',
  'author_company' => 'www.fruit-lab.de',
  'constraints' => 
  array (
    'depends' =>
    array (
      'typo3' => '7.6.0-7.6.99',
      'a21glossary' => '7.6.0-7.6.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
  'uploadfolder' => false,
  'createDirs' => NULL,
  'clearcacheonload' => false,
);
