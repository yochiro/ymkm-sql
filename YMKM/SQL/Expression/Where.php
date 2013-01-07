<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */

/**
 * Stateful class which defines an SQL expression to be evaluated inside WHERE/JOIN conditions
 *
 * A WhereExpression is a container for sub-expressions which can be of any type
 * including other WhereExpression.
 *
 * WhereExpression can be used as WHERE or JOIN conditions.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_Expression_Where extends YMKM_SQL_Expression_AbstractWhere
{
    /**
     * List of sub-expressions
     * @var array(YMKM_SQL_Expression_AbstractWhere)
     */
    private $_subExprs = array();

    /**
     * Map function to call on each sub-expression
     * @var Closure
     */
    private $_mapFn = null;

    /**
     * Reduce function to call to produce the parsed content
     * @var Closure
     */
    private $_redFn = null;

    /**
     * Initial value to supply to the reduce fn if needed
     * @var mixed
     */
    private $_init = null;


    /**
     * Constructor
     *
     * Constructs a list of sub-expressions using supplied map/reduce functions,
     * if available.
     */
    public function __construct(array $exprs=array(), $mapFn=null, $redFn=null, $init=null)
    {
        parent::__construct($exprs);
        $this->_mapFn = $mapFn;
        $this->_redFn = $redFn;
        $this->_init = $init;
    }


    /**
     * @see YMKM_SQL_Expression_AbstractWhere
     */
    protected function doAddSubExpressions(array $subs)
    {
        $this->_subExprs = array_merge($this->_subExprs, $subs);
    }

    /**
     * @see YMKM_SQL_Expression_AbstractWhere
     */
    protected function doSubExpressions()
    {
        return $this->_subExprs;
    }

    /**
     * @see YMKM_SQL_Expression_AbstractWhere
     *
     * The supplied $exprsFn takes 3 parameters :
     * - Closure $m : map function : called on each s-expr of the expression instance
     * - Closure $r : reduce function : called on generated pairs from previous map function
     * - mixed $i : initial value to apply to reduce function when needed.
     *
     * map/reduce function which are given are defined here :
     * - map function : parses each sub-expression and passes the result to
     *                  the map function stored in this instance if defined.
     *                  Otherwise, return the parsed result unmodified.
     * - reduce function : Checks nullness of first arg, then passes on to
     *                     the reduce function stored in this instance if defined.
     *                     Otherwise, keep the supplied content as is.
     *                     Finally, if non empty, encloses it around (<content>)
     *                     and return the final result.
     * Parenthesis do not affect the SQL syntax and allow to separate where
     * sub-expressions correctly.
     * Note/Warning :
     * With no reduce function defined on the instance, the reduce function
     * described above will return (<subexpr-1>)(<subexpr-2>)...(<subexpr-n>)
     * which is not valid SQL, so while it is technically possible to not specify
     * a reduce function, it should be passed if more than 1 sub-expr is
     * defined. For 1 sub-expr only, this is not required.
     */
    protected function _doParse($exprFn, YMKM_SQL_Domain $domain)
    {
        $mapFn = $this->_mapFn;
        $redFn = $this->_redFn;
        // Map function
        $m = function ($e) use($domain, $mapFn) {
               $out = $e->parse($domain);
               if (!is_null($mapFn)) {
                 $out = $mapFn($out);
               }
               return $out;
             };
        // Reduce function
        $r = function ($e, $f) use($redFn) {
               $out = '';
               if (is_null($e)) {
                 $out = $f;
               }
               elseif(!is_null($redFn)) {
                 $out = $redFn($e, $f);
               }
               return $out;
             };
        $rendered = $exprFn($m, $r, $this->_init);
        return (!is_null($rendered) && '' !== $rendered)?'('.$rendered.')':'';
    }
}
