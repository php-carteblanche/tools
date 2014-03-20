<?php
/**
 * This file is part of the CarteBlanche PHP framework
 * (c) Pierre Cassat and contributors
 * 
 * Sources <http://github.com/php-carteblanche/tools>
 *
 * License Apache-2.0
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tool;

use \CarteBlanche\CarteBlanche;
use \CarteBlanche\Abstracts\AbstractToolStorageEngineAware;

/**
 * Build a search request based on a string using logical operators
 *
 * -   "one two"      will return fields containing the entire string
 * -   one two        will return fields containing "one" AND "two"
 * -   one OR two     will return fields containing "one" OR "two"
 * -   on*            will return fields containing "one", "only" etc
 * -   parenthesis can be used to isolate a rule
 *
 * Full example:
 *     "one two" thr* (apple OR pie)
 * will search fields containing the string "one two" AND a string beginning
 * with "thr" AND the string "apple" OR the string "pie".
 *
 * If a fields list is set (to $field), to search in a specific field, write :
 *     field_name: my search
 *
 * If a tables list is set (to $table), to search in a specific table, write :
 *     table_name:[field_name:] my search
 */
class AdvancedSearch extends AbstractToolStorageEngineAware
{
	static $dbg=false;
	var $view='';

	var $field;
	var $table;
	var $search_str;
	var $query_search_str;

	protected $_search_tables=null;
	protected $_search_fields=null;
	protected $_search_fields_by_tables=null;
	protected $_search_keywords=null;
	protected $_search_parts=array();
	protected $_cleaned_search_str;
	protected $i=1;
	protected $db;
	protected $search_stacks = array();
	protected $isNumeric=0;
	protected $isProcessed=0;

	var $default_operator = 'and';
	var $and_operator_mask = '.* .*';
	var $and_operator_spliter = ' ';
	var $or_operator_mask = '.* OR .*';
	var $or_operator_spliter = 'OR';
	var $full_string_operator_mask = '\".*\"';
	var $group_operator_mask = '\(.*\)';
	var $completion_sign = '*';
	var $escaped_characters = "*()\":";
	var $field_name_mask = '^([A-z]*):(.+)';
	var $keywords = array( 'follow' );

	public function buildViewParams()
	{
		return array(
			'query_search_str'=>$this->getQuerySearchString(),
			'search_str'=>$this->search_str
		);
	}

// ------------------
// PUBLIC GETTERS
// ------------------

	/**
	 * Get the tables list where to search in
	 */
	public function getSearchTables()
	{
		return 1===$this->isProcessed ? $this->_search_tables : null;
	}

	/**
	 * Get the fields list where to search in
	 */
	public function getSearchFields()
	{
		return 1===$this->isProcessed ? $this->_search_fields : null;
	}

	/**
	 * Get the fields list where to search in
	 */
	public function getSearchKeywords()
	{
		return 1===$this->isProcessed ? $this->_search_keywords : null;
	}

	/**
	 * Get the search string after init parsing
	 */
	public function getCleanedSearchString()
	{
		return 1===$this->isProcessed ? $this->_cleaned_search_str : null;
	}

	/**
	 * Get the search query built by the class
	 */
	public function getSearchQuery()
	{
		return 1===$this->isProcessed ? $this->query_search_str : null;
	}

	/**
	 * Construct and get the search query string
	 */
	public function getQuerySearchString( $field=null )
	{
		if (!empty($field)) {
			$this->field = $field;
			$this->query_search_str='';
		}

		if (empty($this->query_search_str)) 
		{
			if (!isset($this->search_stacks[$this->search_str]))
			{
				$this->isProcessed=0;
				$this->buildAdvancedSearch();
				$this->buildQueryString();
			}
			else
			{
				$this->_search_parts = $this->search_stacks[$this->search_str];
				$this->buildQueryString();
			}
		}

		return $this->query_search_str;
	}

// ------------------
// WHOLE BUILDER
// ------------------

	/**
	 * Process the analyze of the search string
	 */
	public function buildAdvancedSearch()
	{
		if (!empty($this->search_str)) 
		{
			$this->_cleaned_search_str = $this->search_str;
if (self::$dbg) {
	echo '<pre><hr />';
	echo '<br />string is : '.$this->search_str;
	echo '<br />fields are : ';var_export($this->field);
	echo '<br />tables are : ';var_export($this->table);
}

			// first search string treatments
			$this->buildSearchString();
if (self::$dbg) {echo '<br /><br />init parts : ';var_export($this->_search_parts); }

			// strip escaped characters
			$this->buildEscapedCharacters();
if (self::$dbg) {echo '<br /><br />buildEscapedCharacters parts : ';var_export($this->_search_parts); }

			// build keywords prefix
			$this->buildKeywordsPrefix();
if (self::$dbg) { echo '<br /><br />buildKeywordsPrefix parts : ';var_export($this->_search_parts); }

			// build table name prefix
			$this->buildTablenamePrefix();
if (self::$dbg) { echo '<br /><br />buildTablenamePrefix parts : ';var_export($this->_search_parts); }

			// build field name prefix
			$this->buildFieldnamePrefix();
if (self::$dbg) { echo '<br /><br />buildFieldnamePrefix parts : ';var_export($this->_search_parts); }

			// strip escaped characters
			$this->buildSearchType();
if (self::$dbg) {
	echo '<br /><br />search type is : '.(0===$this->isNumeric ? 'string' : 'numeric');
	echo '<br />search fields are : ';var_export($this->_search_fields);
	echo '<br />search tables are : ';var_export($this->_search_tables);
	echo '<br />search keywords are : ';var_export($this->_search_keywords);
}

			// construct string search if so
			if (0===$this->isNumeric)
			{
				// transforming * to %
				$this->buildCompletionStrings();
if (self::$dbg) {echo '<br /><br />buildCompletionStrings parts : ';var_export($this->_search_parts); }
	
				// isolation of quoted strings
				$this->buildFullStrings();
if (self::$dbg) {echo '<br /><br />buildFullStrings parts : ';var_export($this->_search_parts); }
	
				// isolation of strings between parenthesis
				$this->buildGroupedStrings();
if (self::$dbg) {echo '<br /><br />buildGroupedStrings parts : ';var_export($this->_search_parts); }
	
				if ('and'===$this->default_operator)
				{
					// searching OR in all parts
					$this->buildOrOperator();
if (self::$dbg) {echo '<br /><br />buildOrOperator parts : ';var_export($this->_search_parts); }
	
					// searching AND in all parts
					$this->buildAndOperator();
if (self::$dbg) {echo '<br /><br />buildAndOperator parts : ';var_export($this->_search_parts); }
				}
				elseif ('or'===$this->default_operator)
				{
					// searching AND in all parts
					$this->buildAndOperator();
if (self::$dbg) {echo '<br /><br />buildAndOperator parts : ';var_export($this->_search_parts); }
	
					// searching OR in all parts
					$this->buildOrOperator();
if (self::$dbg) {echo '<br /><br />buildOrOperator parts : ';var_export($this->_search_parts); }
				}
			}

			$this->search_stacks[$this->search_str] = $this->_search_parts;
if (self::$dbg) {
	echo '<br /><br />FINALLY :';
	echo '<br />search type is : '.(0===$this->isNumeric ? 'string' : 'numeric');
	echo '<br />query parts are : ';var_export($this->_search_parts); 
	echo '<br />search fields are : ';var_export($this->_search_fields);
	echo '<br />search tables are : ';var_export($this->_search_tables);
	echo '<br />search keywords are : ';var_export($this->_search_keywords);
	echo '</pre><hr />';
}

			$this->isProcessed=1;
		}
	}

// ------------------
// STRING ANALYZE
// ------------------

	/**
	 * First treatment on the string
	 * => trim extra spaces
	 * => transform html entities
	 * => replace double backslashes
	 */
	protected function buildSearchString()
	{
		$str = trim($this->search_str);
		$str = html_entity_decode($str);
		$this->_search_parts[0] = preg_replace('/\\\{2,}/', '\\', $str);
	}

	/**
	 * Extract the keywords if the string is prefixed by one
	 */
	protected function buildKeywordsPrefix()
	{
		if (!empty($this->keywords) && is_array($this->keywords) && count($this->keywords)>0 && 
			0!==preg_match('/'.$this->field_name_mask.'/i', $this->_search_parts[0], $matches))
		{
//var_export($matches);echo '<br />';
			if (!empty($matches[1]) && in_array($matches[1], $this->keywords))
			{
				if (!is_array($this->_search_keywords)) $this->_search_keywords = array();
				$this->_search_keywords[$matches[1]] = true;
				$this->_search_parts[0] = trim( $matches[2] );
				$this->cleanSearchString( $matches[1].':' );
			}
		}
	}

	/**
	 * Extract the table name if the string is prefixed by one
	 */
	protected function buildTablenamePrefix()
	{
		if (!empty($this->table) && is_array($this->table) && count($this->table)>0 && 
			0!==preg_match('/'.$this->field_name_mask.'/i', $this->_search_parts[0], $matches))
		{
//var_export($matches);echo '<br />';
			if (!empty($matches[1]))
			{
				$table = $this->getTable( $matches[1] );
				$table_fields = $this->getFieldsByTable( $matches[1] );
				if ($table && $table_fields)
				{
					$this->_search_tables = array( $table );
					$this->_search_fields_by_tables = $table_fields;
					$this->_search_parts[0] = trim( $matches[2] );
					$this->cleanSearchString( $matches[1].':' );
				}
			}
		}
	}

	/**
	 * Extract the field name if the string is prefixed by one
	 */
	protected function buildFieldnamePrefix()
	{
		if (0!==preg_match('/'.$this->field_name_mask.'/i', $this->_search_parts[0], $matches))
		{
//var_export($matches);echo '<br />';
			if (!empty($matches[1]))
			{
				$this->_search_fields = array( $this->getField( $matches[1] ) );
				$this->_search_parts[0] = trim( $matches[2] );
				$this->cleanSearchString( $matches[1].':' );
			}
		}
	}

	/**
	 * Build the search based on search string type : numeric or string
	 */
	protected function buildSearchType()
	{
		$search_type = 'str';
		if (is_numeric($this->_search_parts[0]))
		{
			$num = $this->_search_parts[0];
			$search_type = 'num';
			$this->isNumeric=1;
			$this->_search_parts[0] = '{'.$this->i.'}';
			$this->_search_parts[$this->i] = array();
			$this->_search_parts[$this->i]['='] = $num;
			$this->i++;
		}
		if (is_null($this->_search_fields))
		{
			$fieldslist = $this->field;
			if (!empty($this->_search_fields_by_tables))
			{
				$fieldslist = $this->_search_fields_by_tables;
			}
			if (isset($fieldslist[$search_type]))
				$this->_search_fields = $fieldslist[$search_type];
			else
				$this->_search_fields = $fieldslist;
		}
	}

	/**
	 * Replacement of escaped characters 
	 */
	protected function buildEscapedCharacters()
	{
		$matches = preg_split('{(\\\\['.preg_quote($this->escaped_characters).'])}xi', $this->_search_parts[0], -1, PREG_SPLIT_DELIM_CAPTURE);
		if (count($matches)>1)
		{
//var_export($matches);echo '<br />';
			$matched=array();
			foreach($matches as $match)
			{
				if (!in_array($match, $matched) && 
					0!==preg_match('{(\\\\['.preg_quote($this->escaped_characters).'])}xi', $match))
				{
					$this->_search_parts[0] = str_replace($match, '{'.$this->i.'}', $this->_search_parts[0]);
					$this->_search_parts[$this->i] = array();
					$this->_search_parts[$this->i]['esc'] = $match;
					$this->i++;
				}
				$matched[] = $match;
			}
		}
	}

	/**
	 * Completion of asterisk's strings
	 */
	protected function buildCompletionStrings()
	{
		$parts = explode(' ', $this->_search_parts[0]);
		foreach ($parts as $_part) {
			if (false!==strpos($_part, $this->completion_sign)) {
//var_export($matches);echo '<br />';
				$this->_search_parts[0] = str_replace($_part, '{'.$this->i.'}', $this->_search_parts[0]);
				$this->_search_parts[$this->i] = array();
				$this->_search_parts[$this->i]['%'] = str_replace($this->completion_sign, '%', $_part);
				$this->i++;
			}
		}
	}

	/**
	 * Isolate quoted strings
	 */
	protected function buildFullStrings()
	{
		$init_count = count($this->_search_parts);
		foreach ($this->_search_parts as $j=>$_part) {
			if ($j<=$init_count) {
				if (0!==preg_match('{'.$this->full_string_operator_mask.'}i', $_part, $matches)) {
//var_export($matches);echo '<br />';
					$this->_search_parts[0] = str_replace($matches[0], '{'.$this->i.'}', $this->_search_parts[0]);
					$this->_search_parts[$this->i] = array();
					$this->_search_parts[$this->i]['%'] = str_replace('"', '', '%'.$matches[0].'%');
					$this->i++;
				}
			}
		}
	}

	/**
	 * Isolate strings between parenthesis
	 */
	protected function buildGroupedStrings()
	{
		$init_count = count($this->_search_parts);
		foreach ($this->_search_parts as $j=>$_part) {
			if ($j<=$init_count) {
				if (0!==preg_match('/'.$this->group_operator_mask.'/i', $_part, $matches)) {
//var_export($matches);echo '<br />';
					$this->_search_parts[0] = str_replace($matches[0], '{'.$this->i.'}', $this->_search_parts[0]);
					$this->_search_parts[$this->i] = array();
					$this->_search_parts[$this->i]['group'] = str_replace('(', '', str_replace(')', '', $matches[0]));
					$this->i++;
				}
			}
		}
	}

	/**
	 * Searching OR in all parts
	 */
	protected function buildOrOperator()
	{
		$this->buildOperator( 
			$this->or_operator_mask,
			$this->or_operator_spliter,
			'or'
		);
	}

	/**
	 * Searching AND in all parts
	 */
	protected function buildAndOperator()
	{
		$this->buildOperator( 
			$this->and_operator_mask,
			$this->and_operator_spliter,
			'and'
		);
	}

	/**
	 * Searching an operator in all parts
	 * @param string $mask The PCRE mask to match
	 * @param string $spliter The PCRE split mask used to separate strings parts
	 * @param string $index The index used in the parts stack
	 * @return void
	 */
	protected function buildOperator( $mask=null, $spliter=null, $index=null )
	{
		foreach($this->_search_parts as $j=>$_part)
		{
			if (is_string($_part))
			{
				if (0!==preg_match('/'.$mask.'/i', $_part, $matches))
				{
//var_export($matches);echo '<br />';
					$this->_search_parts[$j] = str_replace($matches[0], '{'.$this->i.'}', $this->_search_parts[$j]);
					$this->_search_parts[$this->i] = array();
					$explode = preg_split('/'.$spliter.'/i', $_part);
					array_walk($explode, create_function('&$value,$key', '$value = trim($value);'));
					$this->_search_parts[$this->i][$index] = $explode;
					$this->i++;
				}
			}
			elseif (is_array($_part))
			{
				foreach($_part as $k=>$_subpart)
				{
					if ($k!='%' && $k!='esc' && is_string($_subpart))
					{
						if (0!==preg_match('/'.$mask.'/i', $_subpart, $matches))
						{
//var_export($matches);echo '<br />';
							$this->_search_parts[$j][$k] = str_replace($matches[0], '{'.$this->i.'}', $this->_search_parts[$j][$k]);
							$this->_search_parts[$this->i] = array();
							$explode = preg_split('/'.$spliter.'/i', $_subpart);
							array_walk($explode, create_function('&$value,$key', '$value = trim($value);'));
							$this->_search_parts[$this->i][$index] = $explode;
							$this->i++;
						}
					}
					elseif (is_array($_subpart))
					{
						foreach($_subpart as $l=>$_subsubpart)
						{
							if (0!==preg_match('/'.$mask.'/i', $_subsubpart, $matches))
							{
//var_export($matches);echo '<br />';
								$this->_search_parts[$j][$k][$l] = str_replace($matches[0], '{'.$this->i.'}', $this->_search_parts[$j][$k][$l]);
								$this->_search_parts[$this->i] = array();
								$explode = preg_split('/'.$spliter.'/i', $_subsubpart);
								array_walk($explode, create_function('&$value,$key', '$value = trim($value);'));
								$this->_search_parts[$this->i][$index] = $explode;
								$this->i++;
							}
						}
					}
				}
			}
		}
	}

// ------------------
// QUERY BUILDER
// ------------------

	/**
	 * Build query searching OR, putting AND by default
	 */
	protected function buildQueryString()
	{
		$this->_search_fields = array_filter($this->_search_fields);
		if (empty($this->_search_fields)) return false;

		$matches = explode(' ', $this->_search_parts[0]);
		$matches = array_filter($matches);
		$_count=0;
		foreach($matches as $j=>$_part)
		{
			$_part = trim($_part);
			if (!empty($_part))
			{
				$this->query_search_str .= $this->getParsedEntry($_part);
			}
		}
	}

	/**
	 * Retrieve and parse if needed one of the entry from the array constructed during the search string analyze
	 */
	protected function getParsedEntry( $index, $surround=true )
	{
		if (is_string($index)) $index = trim($index);
		if (empty($index)) return false;
//echo '<br />working on index '.$index;
		if (is_string($index) && 0!==preg_match('/\{(\d)\}/i', $index, $inserts))
		{
//var_export($inserts);echo '<br />';
			if (isset($this->_search_parts[$inserts[1]]))
			{
				foreach($this->_search_parts[$inserts[1]] as $type=>$val)
				{
					$ok=false;
					switch($type)
					{
						case '%': 
							$ok = $this->getParsedEntry($val, false);
							break;
						case 'esc': 
							$str = str_replace(
								$inserts[0], 
								str_replace('"', '""', stripslashes($val)),
								$index
							);
							$ok = $this->getParsedEntry($str);
							break;
						case 'or': 
							$str='';
							foreach($val as $k=>$_val)
							{
								$_val = trim($_val);
								if (!empty($_val))
								{
									if ($ok = $this->getParsedEntry($_val))
									{
										$str .= $ok.( $k==count($val)-1 ? '' : ' OR ' );
									}
								}
							}
//							$ok = $str;
							$ok = '('.$str.')';
							break;
						case 'and': 
							$str='';
							foreach($val as $k=>$_val)
							{
								$_val = trim($_val);
								if (!empty($_val))
								{
									if ($ok = $this->getParsedEntry($_val))
									{
										$str .= $ok.( $k==count($val)-1 ? '' : ' AND ' );
									}
								}
							}
//							$ok = $str;
							$ok = '('.$str.')';
							break;
						case 'group': 
							if ($ok = $this->getParsedEntry($val))
							{
								$ok = '('.$ok.')';
							}
							break;
						case '=':
							$ok = $this->buildQueryForFields($val, 'OR', '=');
							break;
						default: 
							$ok = $this->getParsedEntry($val);
							break;
					}
				}
				if ($ok)
				{
//					unset($this->_search_parts[$inserts[1]]);
//echo '<br />OK is '.$ok;
					return $ok;
				}
			}
		}
		return is_string($index) ? 
			$this->buildQueryForFields(
				'"'.(true===$surround ? '%' : '').$this->escape($index).(true===$surround ? '%' : '').'"'
			) : $index;
	}

// ------------------
// UTILITIES
// ------------------

	/**
	 * Clean the search string from substring $replace
	 */
	protected function cleanSearchString( $replace )
	{
		$this->_cleaned_search_str = str_replace($replace, '', $this->_cleaned_search_str);
	}

	/**
	 * Find a field name in the fields list
	 */
	protected function getField( $fieldname, $type=null, $table=null )
	{
		$array = empty($this->_search_fields_by_tables) ? $this->field : $this->_search_fields_by_tables;
		if (is_array($array))
		{
			foreach($array as $t=>$fields)
			{
				if (is_array($fields))
				{
					if (is_null($type) ||
						(!is_null($type) && $t==$type)
					) {
						if (in_array($fieldname, $fields))
							return $fieldname;
					}
				}
				else
				{
					if ($fields==$fieldname) return $fields;
				}
			}
		}
		elseif (is_string($array) && $array==$fieldname)
		{
			return $array;
		}
		return false;
	}

	/**
	 * Find a table name in the fields list
	 */
	protected function getTable( $tablename )
	{
		if (is_array($this->table) && in_array($tablename, $this->table))
		{
			return $tablename;
		}
		elseif (is_string($this->table) && $this->table==$tablename)
		{
			return $this->table;
		}
		return false;
	}

	/**
	 * Find a fields list by table name in the fields list
	 */
	protected function getFieldsByTable( $tablename )
	{
		if (is_array($this->field) && isset($this->field[$tablename]))
		{
			return $this->field[$tablename];
		}
		return false;
	}

	/**
	 * Build a query string on each fields where to search in
	 */
	protected function buildQueryForFields( $str, $operator='OR', $sign='LIKE' )
	{
		if (is_array($this->_search_fields))
		{
			$qstr='';
			foreach($this->_search_fields as $f=>$_field)
			{
				$qstr .= $_field.' '.$sign.' '.$str.( $f<count($this->_search_fields)-1 ? ' '.$operator.' ' : '');
			}
			return '('.$qstr.')';
		}
		else
		{
			return $this->_search_fields.' '.$sign.' '.$str;
		}
	}

	/**
	 * Escape a string for database command
	 */
	protected function escape( $str )
	{
		$db = $this->getStorageEngine();
		if (empty($db)) {
		    throw new \InvalidArgumentException(
		        'No storage engine to process advanced search!'
		    );
		}
		return $db->escape($str);
	}

}


/*
////// SET OF TESTS //////

//$search_fields = 'yo';

//$search_fields = array( 'id', 'parent_id' );

$search_fields = array(
  'num' =>  array( 'id', 'parent_id' ),
  'str' => array( 'title', 'content' )
);

$search_tables = array( 'article', 'rubrique' );

$search_fields_by_tables = array(
	'article'=>array(
		'num' =>  array( 'id', 'rubrique_id' ),
		'str' => array( 'title', 'content' )
	),
	'rubrique'=>array(
		'num' =>  array( 'id', 'parent_id' ),
		'str' => array( 'title', 'content' )
	),
);

$a = new \Tool\AdvancedSearch(array(
	'search_str' 	=> '"one two" ',
	'field'			=> $search_fields
));
echo '<br /><br />query : '.var_export($a->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$b = new \Tool\AdvancedSearch(array(
	'search_str' 	=> '"one two" thr* OR iu (apple OR pie)',
	'field'			=> $search_fields
));
echo '<br /><br />query : '.var_export($b->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$c = new \Tool\AdvancedSearch(array(
	'search_str' 	=> '\"one two\" thr\* OR iu \(apple OR pie\)',
	'field'			=> $search_fields
));
echo '<br /><br />query : '.var_export($c->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$d = new \Tool\AdvancedSearch(array(
	'search_str' 	=> 'id:2',
	'field'			=> $search_fields
));
echo '<br /><br />query : '.var_export($d->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$e = new \Tool\AdvancedSearch(array(
	'search_str' 	=> 'id: "mlkj jkl" (uio OR oiu)',
	'field'			=> $search_fields
));
echo '<br /><br />query : '.var_export($e->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$f = new \Tool\AdvancedSearch(array(
	'search_str' 	=> 'article:id:2',
	'field'			=> $search_fields_by_tables,
	'table'			=> $search_tables
));
echo '<br /><br />query : '.var_export($f->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$g = new \Tool\AdvancedSearch(array(
	'search_str' 	=> 'article:id: "mlkj jkl" (uio OR oiu)',
	'field'			=> $search_fields_by_tables,
	'table'			=> $search_tables
));
echo '<br /><br />query : '.var_export($g->getQuerySearchString(),1);
//echo $SQLITE->get_query();

$h = new \Tool\AdvancedSearch(array(
	'search_str' 	=> 'follow:article:id: "mlkj jkl" (uio OR oiu)',
	'field'			=> $search_fields_by_tables,
	'table'			=> $search_tables
));
echo '<br /><br />query : '.var_export($h->getQuerySearchString(),1);
//echo $SQLITE->get_query();

exit('yo');
*/

// Endfile