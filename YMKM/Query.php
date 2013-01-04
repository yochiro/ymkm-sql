<?php
/**
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */


/**
 * Builds query using the YMKM_SQL_QueryBuilder API
 *
 * @package ymkm-sql
 */
final class YMKM_Query
{

    private $_queryBuilder = null;


    /**
     * Returns a new instance of itself. Can be chained with other methods
     *
     * @return YMKM_SQL_QueryBuilder new instance of a query builder
     */
    public static function create()
    {
        return new self();
    }

    public static function distinct()  { return function($domain, $c) { return 'DISTINCT('.$c.')'; }; }
    public static function cnt()  { return function($domain, $c) { return 'COUNT('.$c.')'; }; }
    public static function max()  { return function($domain, $c) { return 'MAX('.$c.')'; }; }
    public static function sum()  { return function($domain, $c) { return 'SUM('.$c.')'; }; }
    public static function and_() { return function($e, $f) { return $e.' AND '.$f; }; }
    public static function or_()  { return function($e, $f) { return $e.' OR '.$f; }; }
    public static function in()   { return function($e, $f) { return $e.' IN '.$f; }; }
    public static function eq()   { return function($e, $f) { return $e.' = '.$f; }; }
    public static function lt()   { return function($e, $f) { return $e.' < '.$f; }; }
    public static function gt()   { return function($e, $f) { return $e.' > '.$f; }; }
    public static function le()   { return function($e, $f) { return $e.' <= '.$f; }; }
    public static function ge()   { return function($e, $f) { return $e.' >= '.$f; }; }
    public static function ne()   { return function($e, $f) { return $e.' <> '.$f; }; }
    public static function like() { return function($e, $f) { return $e.' LIKE '.$f; }; }
    public static function null() { return function($e, $f) { return $e.' IS NULL'; }; }
    public static function nnul() { return function($e, $f) { return $e.' IS NOT NULL'; }; }
    public static function pair() { return function($e, $f) { return $e.','.$f; }; }
    public static function match(){ return function($e, $f) { return 'MATCH'.$e.' AGAINST('.rtrim(ltrim($f,'('),')').' IN BOOLEAN MODE)'; }; }


    /**
     * Parses query and return generated SQL
     *
     * Explicitely overrides this method to allow future custom code before/after parsing
     */
    public function parse()
    {
        $out = call_user_func_array(array($this->_queryBuilder, 'parse'), func_get_args());
        return $out;
    }


    /**
     * Overridden to allow an array to be passed as unique arg.
     */
    public function addWhere()
    {
        $args = func_get_args();
        $num  = func_num_args();
        if (is_array($args) && $num === 1) {
            $args = array_shift($args);
        }
        call_user_func_array(array($this->_queryBuilder, 'addWhere'), $args);
        return $this;
    }

    /**
     * Helper function for WHERE IN conditions
     */
    public function addWhereIn($col, array $listOfVals)
    {
        $args = array_merge(array(YMKM_Query::pair()),
                            array_map(function($v) { return '?'.$v; }, $listOfVals));
        $this->_queryBuilder->addWhere(YMKM_Query::in(), $col, $args);
        return $this;
    }


    /**
     * Delegate all method calls to composite QueryBuilder object.
     */
    public function __call($fn, $args)
    {
        $q = call_user_func_array(array($this->_queryBuilder, $fn), $args);
        return ($q === $this->_queryBuilder)?$this:$q;
    }


    /**
     * Private ctor. only instantiable through static create() method.
     */
    private function __construct()
    {
        $this->_queryBuilder = new YMKM_SQL_QueryBuilder();
    }
}
