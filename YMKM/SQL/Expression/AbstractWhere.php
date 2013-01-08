<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann@ymkm.org>
 */


require_once(__DIR__.'/Abstract.php');
require_once(__DIR__.'/../Domain.php');
require_once(__DIR__.'/../ParseException.php');
require_once(__DIR__.'/../Iface/WhereAware.php');


/**
 * Abstract class which defines an SQL expression to be evaluated inside WHERE/JOIN conditions
 *
 * A WhereExpression is a container for sub-expressions which can be of any type
 * including other WhereExpression.
 *
 * WhereExpression can be used as WHERE or JOIN conditions.
 *
 * @package ymkm-sql
 */
abstract class YMKM_SQL_Expression_AbstractWhere extends YMKM_SQL_Expression_Abstract
                                              implements YMKM_SQL_Iface_WhereAware
{
    /**
     * Constructor
     *
     * Initialize this expression with the given list of sub-expressions
     *
     * @param array $exprs the list of sub-expressions
     */
    public function __construct(array $exprs=array())
    {
        $this->addSubExpressions($exprs);
    }

    /**
     * Add specified list of expressions as sub-expressions of current instance
     *
     * @param array $subs list of s-expressions
     */
    final public function addSubExpressions(array $subs)
    {
        $this->doAddSubExpressions($subs);
    }

    /**
     * Returns sub-expressions assigned to current instance
     *
     * @return array s-expressions
     */
    final public function subExpressions()
    {
        return $this->doSubExpressions();
    }


    /**
     * @see YMKM_SQL_Expression_Abstract
     *
     * Map/reduce all s-exprs using subclass provided lambda functions.
     */
    final protected function doParse(YMKM_SQL_Domain $domain)
    {
        $out = '';
        // For use as a closure variable
        $self = $this;
        // Calls _doParse with lambda function as first parameter :
        // It map/reds the stored sub-expressions in this expression
        // Using subclass provided map/reduce functions and initial value.
        $out .= $this->_doParse(
                  function($m, $r, $i=null) use ($self) {
                    return array_reduce(
                           array_map($m, $self->subExpressions()),
                           $r, $i);
                  }, $domain);
        return $out;
    }


    /// Abstract method to be implemented by subclasses. ///

    abstract protected function doAddSubExpressions(array $subs);
    abstract protected function doSubExpressions();

    /**
     * Parses SQL part using the lambda function supplied as first parameter
     *
     * The supplied $exprsFn should be called within the subclass _doParse
     * and takes 3 parameters :
     * - Closure $m : map function : called on each s-expr of the expression instance
     * - Closure $r : reduce function : called on generated pairs from previous map function
     * - mixed $i : initial value to apply to reduce function when needed.
     *
     * Note : calling the $exprsFn is not required, and only the return value
     * of _doParse becomes the result of this expression's parsing.
     * It just offers a structure that should be useable for most where expressions
     * constructs.
     *
     * @param Closure $entitiesFn the lambda function performing map/reduce on expressions.
     * @param YMKM_SQL_Domain $domain the column and table definitions set on this query
     * @return string the parsed SQL expression
     * @throw YMKM_SQL_ParseException if parsing fails
     */
    abstract protected function _doParse($exprsFn, YMKM_SQL_Domain $domain);
}
