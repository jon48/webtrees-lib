<?php
 /**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hook
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Hook\HookInterfaces;

/**
 * Interface for WT_Module for modules implementing custom tags.
 * Support hook <strong>hHtmlSimpleTagDisplay</strong>,<strong>hHtmlSimpleTagEditor</strong>,
 * <strong>hAddSimpleTag</strong>, <strong>hHasHelpTextTag</strong>, <string>hGetHelpTextTag</strong> and <strong>hGetExpectedTags</strong>.
 */
interface CustomSimpleTagManager {


    /**
     * Returns the list of expected tags, classified by type of records.
     *
     * @return array List of expected tags
     */
    public function hGetExpectedTags();
    
	/**
	 * Return the HTML code to be display for this tag.
	 * 
	 * @param string $tag Tag
	 * @param string $value Value of the tag
	 * @param string $context Context of the tag
	 * @param string $contextid Context ID of the tag, if it exists
	 * @return string HTML code to display
	 */
	public function hHtmlSimpleTagDisplay($tag, $value, $context = null, $contextid = null);
	
	/**
	 * Returns HTML code for editing the custom tag.
	 * 
	 * @param string $tag Tag
	 * @param string $value Value of the tag
	 * @param string $element_id Element id from the edit interface, used fr jQuery
	 * @param string $element_name Element name from the edit interface, used to POST values for update
	 * @param string $context Tag context
	 * @param string $contextid Id of tag context
	 */
	public function hHtmlSimpleTagEditor($tag, $value = null, $element_id = '', $element_name = '', $context = null, $contextid = null);
	
	/**
	 * Print all tags edit field for the context specified.
	 * 
	 * @param string $context Context of the edition
	 * @param int $level Level to which add the tags
	 */
	public function hAddSimpleTag($context, $level);

	/**
	 * Returns whether the tag has any help text
	 * 
	 * @param string $tag Tag
	 * @return bool True is help text, False otherwise 
	 */
	public function hHasHelpTextTag($tag);
	
	/**
	 * Returns $title and $text to display help text for the specified tag.
	 * 
	 * @param string $tag Tag
	 * @return array Help title and text
	 */
	public function hGetHelpTextTag($tag);
	
}

?>