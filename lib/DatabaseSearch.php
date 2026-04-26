<?php
/**
 * CATS
 * Database Search Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: DatabaseSearch.php 3592 2007-11-13 17:30:46Z brian $
 */

/**
 *	Database Search Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class DatabaseSearch
{
    private $_simpleReplaceHash = array(
        '+' => '_rPLUSr',
        '#' => '_rPOUNDr',
        '&' => '_rANDr',
        '@' => '_rATr'
    );


    /**
     * Translates a traditional AND/OR/NOT boolean query string into a
     * Sphinx-compatible +/-/! query string.
     *
     * @param string input query
     * @return string translated boolean query
     */
    public static function humanToSphinxBoolean($string)
    {
        /* Remove double operators. */
        $regexSearch = array(
            '/\bAND(?:\s+AND)+\b/i',
            '/\bOR(?:\s+OR)+\b/i',
            '/\bAND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i'
        );
        $regexReplace = array(
            'AND',
            'OR',
            'NOT',
            'NOT',
            'NOT'
        );
        $string = preg_replace($regexSearch, $regexReplace, $string);

        /* Translate AND/OR/NOT to +/-/!. */
        $stringSearch  = array(' AND ', ' NOT ', ' OR ', ',');
        $stringReplace = array(' +',    ' !',    ' | ',  ' | ');
        $string = str_ireplace($stringSearch, $stringReplace,  $string);

        return $string;
    }

    /**
     * Converts a simple human boolean string into a MySQL fulltext boolean
     * query string.
     *
     * @param string input query
     * @return string boolean query
     */
    public static function humanToMySQLBoolean($string)
    {
        $string = trim($string);
        if ($string == '')
        {
            return '';
        }

        $string = str_replace(',', ' OR ', $string);
        $regexSearch = array(
            '/\bAND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bAND(?:\s+AND)+\b/i',
            '/\bOR(?:\s+OR)+\b/i',
            '/\bNOT(?:\s+NOT)+\b/i'
        );
        $regexReplace = array(
            'AND NOT',
            'AND',
            'OR',
            'NOT'
        );
        $string = preg_replace($regexSearch, $regexReplace, $string);
        $string = preg_replace('/\s+/', ' ', trim($string));

        $tokens = preg_split('/\s+/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $parts = array();
        $joinOperator = 'OR';
        $negateNext = false;

        foreach ($tokens as $token)
        {
            $upperToken = strtoupper($token);

            if ($upperToken == 'AND')
            {
                self::markLastBooleanPartRequired($parts);
                $joinOperator = 'AND';
                $negateNext = false;
                continue;
            }

            if ($upperToken == 'OR')
            {
                $joinOperator = 'OR';
                $negateNext = false;
                continue;
            }

            if ($upperToken == 'NOT')
            {
                if ($joinOperator == 'AND')
                {
                    self::markLastBooleanPartRequired($parts);
                }
                $negateNext = true;
                continue;
            }

            $parts[] = self::buildBooleanPart(
                $token,
                $joinOperator,
                $negateNext
            );
            $joinOperator = 'OR';
            $negateNext = false;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Marks the last built boolean part as required for AND semantics.
     *
     * @param array query parts
     * @return void
     */
    private static function markLastBooleanPartRequired(&$parts)
    {
        $lastIndex = count($parts) - 1;
        if ($lastIndex < 0)
        {
            return;
        }

        $lastPart = $parts[$lastIndex];
        if ($lastPart == '' || $lastPart[0] == '+' || $lastPart[0] == '-')
        {
            return;
        }

        $parts[$lastIndex] = '+' . $lastPart;
    }

    /**
     * Builds one MySQL boolean query term.
     *
     * @param string token
     * @param string join operator
     * @param boolean negate next token
     * @return string
     */
    private static function buildBooleanPart($token, $joinOperator, $negateNext)
    {
        $token = trim($token);
        if ($token == '')
        {
            return '';
        }

        $token = ltrim($token, '+-');
        if ($token == '')
        {
            return '';
        }

        if ($negateNext)
        {
            return '-' . $token;
        }

        if ($joinOperator == 'AND')
        {
            return '+' . $token;
        }

        return $token;
    }

    /**
     * Converts the advanced search parser payload into a MySQL fulltext
     * boolean query string.
     *
     * @param string advanced parser payload
     * @param string fallback wildcard string
     * @return string boolean query
     */
    public static function advancedToMySQLBoolean($advancedSearchParser,
        $wildCardString)
    {
        $advancedSearchParser = trim($advancedSearchParser);
        if ($advancedSearchParser == '')
        {
            return self::humanToMySQLBoolean($wildCardString);
        }

        $tokens = self::tokenizeAdvancedSearchParser($advancedSearchParser);
        if (empty($tokens))
        {
            return self::humanToMySQLBoolean($wildCardString);
        }

        $offset = 0;
        $tree = self::parseAdvancedBooleanExpression($tokens, $offset);
        if ($tree === false)
        {
            return self::humanToMySQLBoolean($wildCardString);
        }

        return trim(self::renderAdvancedBooleanExpression($tree, false));
    }

    /**
     * Returns true if the query contains special tokens that are commonly
     * problematic for MySQL fulltext parsing and should fall back to the
     * legacy boolean SQL parser.
     *
     * @param string query string
     * @return boolean
     */
    public static function requiresFulltextFallback($string)
    {
        return (boolean) preg_match('/(^|[\s\(])(?:\.[^\s\)]+|[^\s\)]*[#@+][^\s\)]*)(?=$|[\s\)])/', $string);
    }

    /**
     * Returns whether an index exists on a table.
     *
     * @param DatabaseConnection database connection
     * @param string table name
     * @param string index name
     * @return boolean
     */
    public static function hasIndex($db, $tableName, $indexName)
    {
        static $indexCache = array();

        $cacheKey = $tableName . ':' . $indexName;
        if (isset($indexCache[$cacheKey]))
        {
            return $indexCache[$cacheKey];
        }

        $tableName = str_replace('`', '``', $tableName);
        $indexNameSQL = $db->makeQueryString($indexName);
        $sql = sprintf(
            "SHOW INDEX FROM `%s` WHERE Key_name = %s",
            $tableName,
            $indexNameSQL
        );

        $indexCache[$cacheKey] = (boolean) $db->getAssoc($sql);

        return $indexCache[$cacheKey];
    }

    /**
     * Tokenizes advanced search parser payload.
     *
     * @param string advanced parser payload
     * @return array tokens
     */
    private static function tokenizeAdvancedSearchParser($advancedSearchParser)
    {
        $stuff = explode('{[+', $advancedSearchParser);
        $tokens = array();

        foreach ($stuff as $entry)
        {
            $innerStuff = explode('[|]', $entry);
            $value = '';
            $node = '';

            if (isset($innerStuff[0]))
            {
                $value = trim($innerStuff[0]);
            }
            if (isset($innerStuff[1]))
            {
                $node = $innerStuff[1];
            }

            if ($value != '')
            {
                $tokens[] = array(
                    'type'    => 'TERM',
                    'value'   => $value,
                    'partial' => false
                );
            }

            switch ($node)
            {
                case '* ':
                    if (!empty($tokens))
                    {
                        $tokens[count($tokens) - 1]['partial'] = true;
                    }
                    break;

                case ' AND ':
                    $tokens[] = array('type' => 'AND');
                    break;

                case ' OR ':
                    $tokens[] = array('type' => 'OR');
                    break;

                case ' AND NOT ':
                    $tokens[] = array('type' => 'ANDNOT');
                    break;

                case ' AND (':
                    $tokens[] = array('type' => 'AND');
                    $tokens[] = array('type' => 'LPAREN');
                    break;

                case ' OR (':
                    $tokens[] = array('type' => 'OR');
                    $tokens[] = array('type' => 'LPAREN');
                    break;

                case ' AND NOT (':
                    $tokens[] = array('type' => 'ANDNOT');
                    $tokens[] = array('type' => 'LPAREN');
                    break;

                case ')':
                    $tokens[] = array('type' => 'RPAREN');
                    break;
            }
        }

        return $tokens;
    }

    /**
     * Parses an OR-level boolean expression.
     *
     * @param array tokens
     * @param integer current offset
     * @return array parsed tree
     */
    private static function parseAdvancedBooleanExpression($tokens, &$offset)
    {
        $left = self::parseAdvancedBooleanAndExpression($tokens, $offset);
        if ($left === false)
        {
            return false;
        }

        while (isset($tokens[$offset]) && $tokens[$offset]['type'] == 'OR')
        {
            $offset++;
            $right = self::parseAdvancedBooleanAndExpression($tokens, $offset);
            if ($right === false)
            {
                break;
            }

            if (isset($left['type']) && $left['type'] == 'OR')
            {
                $left['children'][] = $right;
            }
            else
            {
                $left = array(
                    'type'     => 'OR',
                    'children' => array($left, $right)
                );
            }
        }

        return $left;
    }

    /**
     * Parses an AND-level boolean expression.
     *
     * @param array tokens
     * @param integer current offset
     * @return array parsed tree
     */
    private static function parseAdvancedBooleanAndExpression($tokens, &$offset)
    {
        $left = self::parseAdvancedBooleanPrimary($tokens, $offset);
        if ($left === false)
        {
            return false;
        }

        while (isset($tokens[$offset]) &&
            ($tokens[$offset]['type'] == 'AND' ||
             $tokens[$offset]['type'] == 'ANDNOT'))
        {
            $tokenType = $tokens[$offset]['type'];
            $offset++;

            $right = self::parseAdvancedBooleanPrimary($tokens, $offset);
            if ($right === false)
            {
                break;
            }

            if ($tokenType == 'ANDNOT')
            {
                $right = array(
                    'type'  => 'NOT',
                    'child' => $right
                );
            }

            if (isset($left['type']) && $left['type'] == 'AND')
            {
                $left['children'][] = $right;
            }
            else
            {
                $left = array(
                    'type'     => 'AND',
                    'children' => array($left, $right)
                );
            }
        }

        return $left;
    }

    /**
     * Parses a primary boolean expression.
     *
     * @param array tokens
     * @param integer current offset
     * @return array parsed tree
     */
    private static function parseAdvancedBooleanPrimary($tokens, &$offset)
    {
        if (!isset($tokens[$offset]))
        {
            return false;
        }

        if ($tokens[$offset]['type'] == 'LPAREN')
        {
            $offset++;
            $expression = self::parseAdvancedBooleanExpression($tokens, $offset);

            if (isset($tokens[$offset]) && $tokens[$offset]['type'] == 'RPAREN')
            {
                $offset++;
            }

            return $expression;
        }

        if ($tokens[$offset]['type'] == 'TERM')
        {
            $term = $tokens[$offset];
            $offset++;

            return $term;
        }

        return false;
    }

    /**
     * Renders a parsed advanced boolean expression into MySQL boolean syntax.
     *
     * @param array parsed tree
     * @param boolean whether the expression is required
     * @return string boolean query
     */
    private static function renderAdvancedBooleanExpression($tree, $required)
    {
        if (!is_array($tree) || !isset($tree['type']))
        {
            return '';
        }

        switch ($tree['type'])
        {
            case 'TERM':
                return self::renderAdvancedBooleanTerm($tree, $required);

            case 'NOT':
                return self::renderAdvancedBooleanNot($tree['child']);

            case 'OR':
                return self::renderAdvancedBooleanOr($tree['children'], $required);

            case 'AND':
                return self::renderAdvancedBooleanAnd($tree['children'], $required);
        }

        return '';
    }

    /**
     * Renders a term node.
     *
     * @param array term node
     * @param boolean required
     * @return string boolean query segment
     */
    private static function renderAdvancedBooleanTerm($tree, $required)
    {
        $term = self::normalizeBooleanModeTerm($tree['value'], $tree['partial']);
        if ($term == '')
        {
            return '';
        }

        return ($required ? '+' : '') . $term;
    }

    /**
     * Renders an AND node.
     *
     * @param array children
     * @param boolean required
     * @return string boolean query segment
     */
    private static function renderAdvancedBooleanAnd($children, $required)
    {
        $parts = array();

        foreach ($children as $child)
        {
            $part = self::renderAdvancedBooleanExpression($child, true);
            if ($part != '')
            {
                $parts[] = $part;
            }
        }

        $inner = trim(implode(' ', $parts));
        if ($inner == '')
        {
            return '';
        }

        if ($required || count($parts) <= 1)
        {
            return $inner;
        }

        return '(' . $inner . ')';
    }

    /**
     * Renders an OR node.
     *
     * @param array children
     * @param boolean required
     * @return string boolean query segment
     */
    private static function renderAdvancedBooleanOr($children, $required)
    {
        $parts = array();

        foreach ($children as $child)
        {
            $part = self::renderAdvancedBooleanExpression($child, false);
            if ($part != '')
            {
                $parts[] = $part;
            }
        }

        $inner = trim(implode(' ', $parts));
        if ($inner == '')
        {
            return '';
        }

        if (count($parts) == 1)
        {
            return ($required ? '+' : '') . $inner;
        }

        return ($required ? '+(' : '(') . $inner . ')';
    }

    /**
     * Renders a NOT node.
     *
     * @param array child node
     * @return string boolean query segment
     */
    private static function renderAdvancedBooleanNot($child)
    {
        if (!is_array($child) || !isset($child['type']))
        {
            return '';
        }

        if ($child['type'] == 'TERM')
        {
            $term = self::normalizeBooleanModeTerm(
                $child['value'],
                $child['partial']
            );

            if ($term == '')
            {
                return '';
            }

            return '-' . $term;
        }

        $inner = self::renderAdvancedBooleanExpression($child, false);
        if ($inner == '')
        {
            return '';
        }

        return '-(' . $inner . ')';
    }

    /**
     * Normalizes a term for MySQL boolean fulltext syntax.
     *
     * @param string term
     * @param boolean partial match
     * @return string normalized term
     */
    private static function normalizeBooleanModeTerm($term, $partial)
    {
        $term = trim($term);
        if ($term == '')
        {
            return '';
        }

        $term = preg_replace('/\s+/', ' ', $term);
        $term = str_replace(
            array('"', '(', ')'),
            array('', '', ''),
            $term
        );

        if ($term == '')
        {
            return '';
        }

        if (strpos($term, ' ') !== false)
        {
            if ($partial)
            {
                $words = explode(' ', $term);
                foreach ($words as $index => $word)
                {
                    $words[$index] = $word . '*';
                }

                return implode(' ', $words);
            }

            return '"' . $term . '"';
        }

        if ($partial)
        {
            $term .= '*';
        }

        return $term;
    }

    /**
     * Makes a string searchable via REGEXP in a MySQL query.
     * note that it produces double slashes rather than single
     * slashes because mysql interprets backslashes twice, once
     * in SQL and 2nd time in REGEXP.
     *
     * @param string text to escape
     * @return string REGEXP parameter of an sql query
     */
    public static function makeREGEXPString($string)
    {
         /* FIXME: Test this! */
        $search  = array(
            '\\',   '+',   '.',   '*',   '(',   ')',   '[',   ']',   '?',   '^',   '$'
        );
        $replace = array(
            '\\\\', '\\+', '\\.', '\\*', '\\(', '\\)', '\\[', '\\]', '\\?', '\\^', '\\$'
        );

        return str_replace($search, $replace, $string);
    }

    /**
     * Changes commas and spaces (normally delimiters) in quoted string with
     * _QCOMMAQ_ and _QSPACEQ_ respectively.
     *
     * @param string text to escape
     * @return string marked up string
     */
    public function markUpQuotes($string)
    {
        while (strpos($string, '"') !== false)
        {
            /* Find the first quote. */
            $quoteStart = strpos($string, '"');
            $string = substr_replace($string, '', $quoteStart, 1);

            /* Find the second quote; if there isn't one, break out. */
            $quoteEnd = strpos($string, '"');
            if ($quoteEnd === false)
            {
                break;
            }

            /* Remove the second quote. */
            $string = substr_replace($string, '', $quoteEnd, 1);

            /* Grab the string that was inside the quotes. */
            $quoted = substr($string, $quoteStart, $quoteEnd - 1);

            /* Mark up the string that was inside the quotes. */
            $quoted = str_replace(
                array(' ', ','), array('_QSPACEQ_', '_QCOMMAQ_'), $quoted
            );

            /* Replace the string that was inside the quotes with the marked-up string. */
            $string = trim(
                substr_replace($string, $quoted, $quoteStart, $quoteEnd - 1)
            );
        }

        return $string;
    }

    /**
     * Removes _QCOMMAQ_ and _QSPACEQ_ from a string that was 
     * created with markUpQuotes.
     *
     * @param string text to unescape
     * @return string marked up string
     */
    public function unMarkUpQuotes($string)
    {
        return str_replace(
            array('_QSPACEQ_', '_QCOMMAQ_'), array(' ', ','), $string
        );
    }
    
    /**
     * Returns true if for every ) we don't have an (, or vice versa.
     *
     * @param string string to evaluate
     * @return boolean parenthesis are unmatched
     */
    public function containsUnmatchedParenthesis($string)
    {
        /* Counters for open and close paranthesis. */
        $open  = 0;
        $close = 0;
        
        /* Loop through each character of the string and ensure all paranthesis
         * are matched.
         */
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i)
        {
            /* Open paranthesis. */
            if ($string[$i] == '(')
            {
                ++$open;
            }
            
            /* Close paranthesis. */
            if ($string[$i] == ')')
            {
                /* If we found a ')' without any unclosed '(' before it... */
                if ($open < 1)
                {
                    return true;
                }
                
                ++$close;
            }
        }
        
        /* If we don't have the same number of ('s as )'s, fail. */
        if ($open != $close)
        {
            return true;
        }
        
        return false;
    }

    /**
     * Parses a query string into a series of SQL statments.
     *
     * @param string Search query string.
     * @param DatabaseConnection Database connection object.
     * @param string Field name in query to search.
     * @return string SQL WHERE clause.
     */
    public static function makeBooleanSQLWhere($string, $databaseConnection,
        $tableField)
    {
        /* Empty string handling. This makes the query "WHERE 0", thus no
         * results are returned.
         */
        $string = trim($string);
        if (empty($string))
        {
            return '0';
        }

        /* Mark up quoted strings with filler characters (no white space). */
        $string = self::markUpQuotes($string);

        /* Clean up ()'s. */
        $string = preg_replace('/\(\s*\)/', '', $string);
        if (self::containsUnmatchedParenthesis($string))
        {
            return '0';
        }
        
        /* Add spaces to the input string to make things easier. */
        $string = ' ' . $string . ' ';

        /* Special character handling. */
        $stringSearch  = array(
            ' -',
            ' !',
            ',',
            '|',
            '(',
            ')',
            '%'
        );
        $stringReplace = array(
            ' NOT ',
            ' NOT ',
            ' OR ',
            ' OR ',
            ' OOOPENPARENTH ',
            ' CCCLOSEPARENTH ',
            ''
        );
        $string = str_replace($stringSearch, $stringReplace, $string);

        /* Remove double operators and filter query. */
        $regexSearch = array(
            '/\bAND(?:\s+AND)+\b/i',
            '/\b(?:AND|OR)(?:\s+OR)+\b/i',
            '/\bOR(?:\s+AND)+\b/i',
            '/\b(?:OR\s+)*AND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bOR\s+NOT\b/i',
            '/\b(?:AND\s+)?NOT(?:\s+OR)+\b/i'
        );
        $regexReplace = array(
            'AND',
            'OR',
            'OR',
            'NOT',
            'NOT',
            'NOT',
            'NOT',
            ' '
        );
        $string = preg_replace($regexSearch, $regexReplace, $string);

        /* Clean up extra spaces. */
        while (strpos($string, '  ') !== false)
        {
            $string = str_replace('  ', ' ', $string);
        }

        /* Mark up symbols so we can search propely. */
        $string = self::makeREGEXPString($string);

        /* Make the string database safe. */
        $string = $databaseConnection->escapeString($string);

        /* Everything that is a symbol gets translated into something else. */
        $string = urlencode($string);
        $string = str_replace('%5C%5C%2A', '*', $string);
        $string = str_replace('%', 'PPPERCENTTT', $string);
        $string = urldecode($string);
        
        /* Convert normal boolean operators to shortened syntax. */
        /* Translate AND/OR/NOT to +/,/-. */
        $stringSearch  = array(' AND ', ' NOT ', ' OR ');
        $stringReplace = array(' +',    ' -',    ',');
        $string = str_ireplace($stringSearch, $stringReplace,  $string);

        /* Strip excessive whitespace. */
        $string = str_replace('OOOPENPARENTH', '(', $string);
        $string = str_replace('CCCLOSEPARENTH', ')', $string);
        $string = str_replace('( ', '(', $string);
        $string = str_replace(' )', ')', $string);
        $string = str_replace(', ', ',', $string);
        $string = str_replace(' ,', ',', $string);
        $string = str_replace('- ', '-', $string);

        /* Mark-up words. */
        $string = preg_replace(
            '/([A-Za-z0-9_]+[A-Za-z0-9\._-]*)/',
            'word[(\'\\0\')]full',
            $string
        );

        /* Remove leading and trailing whitespace from $string. */
        $string = trim($string);

        /* Strip empty or erroneous atoms. */
        $string = str_replace('word[(\'\')]full', '', $string);
        $string = str_replace('word[(\'-\')]full', '-', $string);

        /* Add needed space. */
        $string = str_replace(')word[(', ') word[(', $string);
        $string = str_replace(')]full(', ')]full (', $string);

        /* Deal with asterisks. */
        $string = str_replace(')]full*', ')]wild ', $string);
        $string = str_replace('*word[(', 'wild[(', $string);
        $string = str_replace('*', '', $string);

        /* Clean up extra spaces again. */
        while (strpos($string, '  ') !== false)
        {
            $string = str_replace('  ', ' ', $string);
        }

        /* Dispatch symbols. */
        $string = str_replace(' ',  ' AND ', $string);
        $string = str_replace(',',  ' OR ', $string);
        $string = str_replace(' -', ' NOT ', $string);
        $string = preg_replace('/^-/', 'NOT ', $string);

        /* At this point:
         * in:  c++ and java or linux and not basic
         * out: word[('cPPPERCENTTT2BPPPERCENTTT2B')]full AND word[('java')]full OR word[('linux')]full NOT word[('basic')]full
         */

        $string = str_replace('PPPERCENTTT', '%', $string);
        $string = urldecode($string);

        /* Word searches. */
        /* ".NET", "C#" are not searched with [[:<:]] [[:>:]]
        $string = preg_replace(
            "/word\[\(\'([^\)]+)\'\)\]full/",
            '(' . $tableField . ' REGEXP \'[[:<:]]\\1[[:>:]]\')',
            $string
        );
        */
        $string = preg_replace(
            "/word\[\(\'([^\)]+)\'\)\]full/",
            '(' . $tableField . ' REGEXP \'\\1\')',
            $string
        );

        /* Wildcard searches. */
        $search = array(
            '/(?:word|wild)\[\(\'(.+?)\'\)\]wild/',
            '/wild\[\(\'(.+?)\'\)\]full/'
        );
        $string = preg_replace(
            $search, '(' . $tableField . ' LIKE \'%\\1%\')', $string
        );

        /* WHERE clauses cannot start with NOT. */
        if (preg_match('/^\s*NOT/i', $string))
        {
            return '0';
        }

        /* WHERE clauses cannot start with AND or OR. */
        $string = preg_replace('/^\s*(?:(?:AND|OR)\s+)+/', ' ', $string);
        
        /* WHERE clauses cannot end with AND or OR. */
        $string = preg_replace('/\s*(?:(?:AND|OR|NOT|AND\s+NOT)\s*)+$/', ' ', $string);

        /* Move around NOT. */
        $array = explode(' ', $string);
        $count = count($array);
        for ($i = 0; $i < ($count - 1); $i++)
        {
            if ($array[$i] == 'NOT' && isset($array[$i + 2]) &&
                trim($array[$i + 2]) == 'LIKE')
            {
                $array[$i] = $array[$i + 1];
                $array[$i + 1] = 'NOT';
                $i++;
            }
        }
        $string = implode(' ', $array);

        /* Make quoted strings work again. */
        $string = self::unMarkUpQuotes($string);

        /* Empty string handling. This makes the query "WHERE 0", thus no
         * results are returned.
         */
        $string = trim($string);
        if (empty($string))
        {
            return '0';
        }

        return '(' . $string . ')';
    }

    /**
     * Parses a query string into SQL statements across multiple fields while
     * preserving the existing boolean parser behavior.
     *
     * @param string Search query string.
     * @param DatabaseConnection Database connection object.
     * @param array Field names in query to search.
     * @return string SQL WHERE clause.
     */
    public static function makeBooleanSQLWhereMultiField($string,
        $databaseConnection, $tableFields)
    {
        if (empty($tableFields))
        {
            return '0';
        }

        $placeholder = '__CATS_SEARCH_FIELD__';
        $where = self::makeBooleanSQLWhere(
            $string,
            $databaseConnection,
            $placeholder
        );

        if ($where === '0')
        {
            return '0';
        }

        $where = self::expandBooleanWherePlaceholder(
            $where,
            $placeholder,
            $tableFields,
            'REGEXP'
        );
        $where = self::expandBooleanWherePlaceholder(
            $where,
            $placeholder,
            $tableFields,
            'LIKE'
        );

        return $where;
    }

    /**
     * Expands one placeholder clause into OR conditions across fields.
     *
     * @param string SQL WHERE clause
     * @param string placeholder token
     * @param array searchable fields
     * @param string operator SQL operator
     * @return string SQL WHERE clause
     */
    private static function expandBooleanWherePlaceholder($where, $placeholder,
        $tableFields, $operator)
    {
        $pattern = '/\(' . $placeholder . ' ' . $operator
            . ' \'((?:\\\\.|[^\'])*)\'\)/';

        if (!preg_match_all($pattern, $where, $matches, PREG_SET_ORDER))
        {
            return $where;
        }

        foreach ($matches as $match)
        {
            $where = str_replace(
                $match[0],
                self::makeExpandedBooleanFieldClause(
                    $tableFields,
                    $operator,
                    $match[1]
                ),
                $where
            );
        }

        return $where;
    }

    /**
     * Builds an OR clause for the same search term across multiple fields.
     *
     * @param array searchable fields
     * @param string operator SQL operator
     * @param string escaped search term
     * @return string SQL clause
     */
    private static function makeExpandedBooleanFieldClause($tableFields,
        $operator, $value)
    {
        $expandedClauses = array();

        foreach ($tableFields as $tableField)
        {
            $expandedClauses[] = '(' . $tableField . ' ' . $operator
                . ' \'' . $value . '\')';
        }

        return '(' . implode(' OR ', $expandedClauses) . ')';
    }

    /**
     * Encodes a string of text so that MySQL 's FULLTEXT searching will
     * correctly operate on certain special characters. This should be run
     * before the text is INSERTed or UPDATEd in the database. When SELECTing
     * text from the field later on, use the fulltextDecode() function to
     * reverse the encoding.
     *
     * @param string text to encode
     * @return string encoded text
     */
    public function fulltextEncode($text)
    {
        $_simpleReplaceHash = array(
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr'
        );

        foreach ($_simpleReplaceHash as $find => $replace)
        {
            $text = str_replace($find, $replace, $text);
        }

        return preg_replace('/\.([^\s])/', '_rDOTr${1}', $text);
    }

    /**
     * Reverses the operations of fulltextEncode().
     *
     * @param string text to decode
     * @return string decoded text
     */
    public function fulltextDecode($text)
    {
        $_simpleReplaceHash = array(
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr'
        );

        foreach ($_simpleReplaceHash as $replace => $find)
        {
            $text = str_replace($find, $replace, $text);
        }

        return str_replace('_rDOTr', '.', $text);
    }
}

?>
