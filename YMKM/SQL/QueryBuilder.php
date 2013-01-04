<?php
/**
 *
 * @package ymkm-sql
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Helper class to build SQL query using YMKM_SQL_Query object
 *
 * This wrapper allows to create queries without explicitely referring to objects,
 * and try to keep the amount of code needed as small as possible.
 * Its main data handled are strings and arrays (which can be nested).
 * Those in turn map to actual objects depending on the content/how they are structured.
 *
 * A new query can be created using the static function newQuery.
 * Upon return, the object can be chain called with the helper functions
 * addCol, addFrom, addLeftJoin, addRightJoin, addJoin, addWhere, addGroupBy, addOrder, setLimit.
 * All of these are fluent : calls can be chained.
 *
 * In the format description, the following data values are defined :
 *
 * -  =expression : Refers to a SQL valid arithmetic expression (ie. which does not contain
 * any column reference). The value is printed as is by the parser.
 * -  ?expression : Refers to a bind parameter to an arithmetic expression (see above).
 * The difference is that the value printed will always become "?", while its actual
 * value will be stored in the query object in the list of bind parameters in the same
 * order they were processed.
 * -  table_def : A table name as a string.
 * -  table_ref : can be either a table name, or a previously defined table alias.
      A table reference must refer to a single existing table_def.
 * -  alias : an alias which disambiguates any duplicate defined entry.
 * -  col_def : can be [table_ref.]column_name , =expression
 * -  col_ref : [table_ref.]column_name|[table_ref.]column_alias|#column_position
 *
 * The format they accept is the following :
 * - addFrom : table_def, [alias]
 *
 * - addCol : 1.    col_def, [alias, Closure]
 *            2.    table_ref, array(col_def|col_def=>array([alias=>alias], [fn=>Closure]))
 *      1. Definition of a single column.
 *      2. Definition of multiple columns assigned to the same table_ref table reference.
 *
 * - add{Left|Right|''}Join : table_ref, [alias, array(where_cond)]
 *
 * - addWhere : where_cond* ...
 *      where_cond first argument is always a Closure referring to the operation to perform on
 *      the remaining arguments.
 *      All other arguments can be =expression, ?expression, object responding to the method
 *      parse(YMKM_SQL_Domain $domain) (e.g. this class itself, for subqueries), array(where_cond*),
 *      col_def, array('col'=>col_def, 'fn'=>Closure).
 *      Closure argument can customize the condition expression. q->addWhere()->addWhere()
 *      will add two two ANDed conditions by default.
 *
 * - addGroupBy : col_ref, array(col_ref)
 *
 * Bind parameters can be retrieved using getBindParams.
 * the query can be parsed using parse.
 *
 * @package ymkm-sql
 */
final class YMKM_SQL_QueryBuilder
{
    /**
     * Character prefix for position type columns. Valid on GROUP BY, ORDER
     * Deprecated syntax
     */
    const COL_POS_PREFIX = '#';
    /**
     * Character prefix for arithmetic expressions type columns definitions/references
     */
    const COL_EXP_PREFIX = '=';
    /**
     * Character prefix for arithmetic expressions that needs to be bound (prepared stmts)
     * Applies to col references only (WHERE statement)
     */
    const COL_BIND_PREFIX = '?';

    /**
     * Separator bw table and column names
     */
    const TBL_COL_SEPARATOR = '.';


    /**
     * Query instance to build
     */
    private $_query = null;


    private $_parsedStr = null;
    private $_boundParams = null;


    /**
     * Adds on or several columns to the SELECT list of this query
     *
     * First and second arguments vary depending on what is expected :
     * - First and second parameter == string : "[table.]col", ["alias"]
     *   -> Add one column with given alias.
     *      Optional table reference can be added before column, separated with a dot.
     * - First parameter == string, second parameter == array : "table", array(col=>array(['alias'=>alias], ['fn'=>fn]),...)
     *   -> Add several non arithmetical columns at once.
     *      Table reference is mandatory and is the 1st argument.
     *
     * Third argument $fn is only relevant with the first form (single column).
     * Otherwise, each column can supply its own decorator function in their
     * attribute array which has the following format (all optional) :
     * colname:string => array('alias' => alias:string, 'fn' => fn:Closure) ...
     *
     * The first character of the column reference has a special meaning
     * depending on the character used :
     * - COL_EXP_PREFIX = : What follows is a calculated column (not an actual column)
     * - Else : default, assume the whole value is a column name/alias
     * For the default case, table reference can be optionally specified, if the value
     * for the column reference is its *name*; if it is an alias, the table reference will be
     * ignored.
     *
     * @param string $tcref Table or Table+Column name reference
     * @param string|array alias or list of column definitions to add
     * @param Closure $fn optional closure to decorate column with
     * @return $this for chaining
     * @throw YMKM_Exception if input arguments are of wrong type
     */
    public function addCol($tcref, $tcrefsOrAlias=null, $fn=null)
    {
        $tref  = $tcref;
        $crefs = $tcrefsOrAlias;
        if (is_string($tcref)) {
            if (!is_array($tcrefsOrAlias)) {
                $crefs = array($tcref => array('alias'=>$tcrefsOrAlias, 'fn' => $fn));
                $tref = null;
            }
        }
        else {
            throw new YMKM_Exception('First argument is of wrong type : expected string');
        }

        $self = $this;
        $query = & $this->_query;
        $out = // Needs intermediate var for array_walk expects ref.
          array_map(
            function($cref, $cattrs) use ($tref, $self) {
              $alias = null;
              $fn = null;
              if (is_array($cattrs)) {
                $alias = (isset($cattrs['alias'])?$cattrs['alias']:$alias);
                $fn    = (isset($cattrs['fn'])?$cattrs['fn']:$fn);
              }
              else {
                $alias = $cattrs;
              }
              return $self->colDefinition($cref, $tref, $alias, $fn);
            },
            array_keys($crefs),
            array_values($crefs));
        array_walk($out,
          function($col, $i) use(&$query) {
            $query->addCol(new YMKM_SQL_Entity_Select($col));
          });

        return $this;
    }

    /**
     * Replaces an existing column entity with another
     *
     * Parameters expected follow the same structure as addCol,
     * for the single column addition case.
     *
     * @see YMKM_SQL_QueryBuilder::addCol()
     * @see YMKM_SQL_Query::replaceCol()
     */
    public function replaceCol(YMKM_SQL_Entity_Select $o, $tcref, $alias=null, $fn=null)
    {
        $n = $this->colDefinition($tcref, null, $alias, $fn);
        $this->_query->replaceCol($o, new YMKM_SQL_Entity_Select($n));
        return $this;
    }

    /**
     * @see YMKM_SQL_Query::removeCols($cols=null)
     */
    public function removeCols($cols=null)
    {
        $this->_query->removeCols($cols);
        return $this;
    }

    /**
     * Adds a new table to the FROM list of this query
     *
     * If table is directly referred by its name, then $tref
     * should be that name.
     *
     * @param string $tref table name|table object name
     * @param string $alias the optional table alias
     * @return $this for chaining
     */
    public function addFrom($tref, $alias=null)
    {
        $tblDef = $this->tableDefinition($tref, $alias);
        $this->_query->addFrom(new YMKM_SQL_Entity_From($tblDef));
        return $this;
    }

    /**
     * Adds a LEFT JOIN on given target table with optional alias and join conditions
     *
     * $jConds array contains the same list of parameters as addWhere.
     *
     * @see addFrom for the format $tref can take
     * @see addWhere for the format of $jConds
     *
     * @param string $tref table name|table object name to perform join on
     * @param string $trefAlias the optional alias name
     * @param array $jConds an array of optional join conditions
     * @return $this for chaining
     */
    public function addLeftJoin($tref, $trefAlias=null, $jConds=array())
    {
        return $this->_addJoin($tref, $trefAlias, 'LEFT', $jConds);
    }

    /**
     * Adds a RIGHT JOIN on given target table with optional alias and join conditions
     *
     * $jConds array contains the same list of parameters as addWhere.
     *
     * @see addFrom for the format $tref can take
     * @see addWhere for the format of $jConds
     *
     * @param string $tref table name|table object name to perform join on
     * @param string $trefAlias the optional alias name
     * @param array $jConds an array of optional join conditions
     * @return $this for chaining
     */
    public function addRightJoin($tref, $trefAlias=null, $jConds=array())
    {
        return $this->_addJoin($tref, $trefAlias, 'RIGHT', $jConds);
    }

    /**
     * Adds a (NATURAL) JOIN on given target table with optional alias and join conditions
     *
     * $jConds array contains the same list of parameters as addWhere.
     *
     * @see addFrom for the format $tref can take
     * @see addWhere for the format of $jConds
     *
     * @param string $tref table name|table object name to perform join on
     * @param string $trefAlias the optional alias name
     * @param array $jConds an array of optional join conditions
     * @return $this for chaining
     */
    public function addJoin($tref, $trefAlias=null, $jConds=array())
    {
        return $this->_addJoin($tref, $trefAlias, null, $jConds);
    }

    /**
     * Adds JOIN conditions to an existing join
     *
     * @param string $trefOrAlias table reference|alias
     * @param array $jConds additional join conditions
     * @return $this for chaining
     */
    public function addWhereJoin($trefOrAlias, $jConds)
    {
        return $this->_addWhereJoin($trefOrAlias, $jConds);
    }


    /**
     * Underlying method common to any join type
     *
     * @see addLeftJoin
     * @see addRightJoin
     * @see addJoin
     *
     * Additional parameter is $jType, which is a string telling what join type
     * it is, ie. "LEFT", "RIGHT", "" for left, right and natural join resp.
     */
    private function _addJoin($tref, $trefAlias=null, $jtype=null, $jConds=array())
    {
        assert(!is_null($tref));

        $tJoinRef = (!is_null($trefAlias)?$trefAlias:$tref);
        $fn = $this->_getWhereParseClosure();
        $conds = new YMKM_SQL_Entity_Where($fn(array_values($jConds)));

        $tDef = $this->tableDefinition($tref, $trefAlias);
        $this->_query->addJoin(new YMKM_SQL_Entity_Join($tDef, $jtype, $conds));
        return $this;
    }

    /**
     * @see addWhereJoin
     */
    private function _addWhereJoin($trefOrAlias, array $jConds)
    {
        $fn = $this->_getWhereParseClosure();
        $conds = new YMKM_SQL_Entity_Where($fn(array_values($jConds)));
        $this->_query->addWhereJoin($trefOrAlias, $conds);
        return $this;
    }

    /**
     * Adds an expression part of the WHERE list of this query
     *
     * The method takes any number of arguments.
     * The operator (Closure) which tells how the where conditions should be
     * linked is always the first argument at any nesting level.
     *
     * @param mixed variable number of args matching the grammar that follows :
     *    args              ::= <list_exprs>
     *    <list_exprs>      ::= <op_fn>, <s_exprs>
     *    <s_exprs>         ::= <s_expr>*
     *    <s_expr>          ::= <arithmetic_expr> | <col_expr> | <col_param> | <s_query>
     *    <arithmetic_expr> ::= =<arithm_value>
     *    <col_expr>        ::= <col_def> | array('col'=><col_def> [,'fn'=><col_fn>])
     *    <col_param>       ::= ?<col_param_value>
     *    <col_def>         ::= [<table_ref>.]string ; Column name/alias
     *    <table_ref>       ::= string ; Table name/alias
     *    <op_fn>           ::= Closure ; function ($expr-1, $expr-2) ... function ($expr-n-1, $expr-n)
     *    <col_fn>          ::= Closure ; function ($domain, $col)
     *    <arithm_value>    ::= string ; Valid self-evaluated SQL expression
     *    <col_param_value> ::= mixed ; must reduce to a string when processed in the closure
     *    <s_query>         ::= object ; Object having a callable parse() method
     *
     * Default behavior is to AND where conditions added through addWhere
     */
    public function addWhere()
    {
        $args = func_get_args();
        $fn = $this->_getWhereParseClosure();
        $this->_query->addWhere(new YMKM_SQL_Entity_Where($fn($args)));
        return $this;
    }

    /**
     * Adds on or several columns to the ORDER BY section of this query
     *
     * First and second arguments vary depending on what is expected :
     * - First and second parameter == string : "[table.]col", ["ASC|DESC"]
     *   -> Order one column using given ASC|DESC direction
     *      Optional table reference can be added before column, separated with a dot.
     * - First parameter == array, second parameter == N/A : array([table.]col=>["ASC|DESC"]])
     *   -> Order several column references at once.
     *      Sole argument is an array of column as the key and its order direction as the value.
     *      Optional table reference can be added before each column entry.
     *
     * The first character of the column reference has a special meaning
     * depending on the character used :
     * - COL_POS_PREFIX # : What follows is a column position (ie. positive integer)
     * - Else : default, assume the whole value is a column name/alias
     * For the default case, table reference can be optionally specified, if the value
     * for the column reference is its *name*; if it is an alias, the table reference will be
     * ignored.
     *
     * @param string|array $tcref Single Table+Column, or array of Table+Col=>Dir
     * @param string $dir order direction if $tcref is a string
     * @return $this for chaining
     * @throw YMKM_Exception if input arguments are of wrong type
     */
    public function addOrder($tcref, $dir='ASC')
    {
        $crefs = $tcref;
        if (is_string($tcref)) {
            if (is_string($dir)) {
                $crefs = array($tcref => $dir);
            }
            else {
                throw new YMKM_Exception('Second argument is of wrong type : expected string');
            }
        }
        elseif (!is_array($tcref)) {
            throw new YMKM_Exception('First argument is of wrong type : expected string or array');
        }

        $self = $this;
        $query = & $this->_query;
        array_walk($crefs, function($dir, $col) use($self, &$query) {
                             $query->addOrder(new YMKM_SQL_Entity_Order(
                                              $self->orderColRef($col), $dir));
                           });
        return $this;
    }

    /**
     * Adds one or several columns to the GROUP BY section of this query
     *
     * Argument may be a string or an array :
     * - String : "[table.]col"
     * - Array : array([table.]col)
     *   -> Groups several column references at once.
     *      Optional table reference can be added before each column entry.
     *
     * The first character of the column reference has a special meaning
     * depending on the character used :
     * - COL_POS_PREFIX # : What follows is a column position (ie. positive integer)
     * - Else : default, assume the whole value is a column name/alias
     * For the default case, table reference can be optionally specified, if the value
     * for the column reference is its *name*; if it is an alias, the table reference will be
     * ignored.
     *
     * @param string|array $tcref Single Table+Column, or array of Table+Col
     * @return $this for chaining
     * @throw YMKM_Exception if input arguments are of wrong type
     */
    public function addGroupBy($tcref) {
        $crefs = $tcref;
        if (is_string($tcref)) {
            $crefs = array($tcref);
        }
        elseif (!is_array($tcref)) {
            throw new YMKM_Exception('First argument is of wrong type : expected string or array');
        }

        $self = $this;
        $query = & $this->_query;
        array_walk($crefs, function($col) use($self, &$query) {
                             $query->addGroup(new YMKM_SQL_Entity_Group(
                                              $self->orderColRef($col)));
                           });
        return $this;
    }

    /**
     * Sets the number/offset (limit) of this query
     *
     * @param int|string $nb the max. number of records to return
     * @param int|string $offset the position at which to start returning records
     * @return $this for chaining
     */
    public function setLimit($nb, $offset)
    {
        $this->_query->setLimit(new YMKM_SQL_Entity_Limit($nb, $offset));
        return $this;
    }

    /**
     * Returns all bound parameters of this query
     *
     * @return array bound params
     */
    public function getBindParams()
    {
        if (is_null($this->_boundParams)) {
            $this->parse();
        }
        return $this->_boundParams;
    }

    /**
     * Parses this query
     *
     * If func_get_args returns a non empty list of argument, then
     * assume there is one being the query domain.
     * As a user, the call should be done without any parameter passed.
     * However, instances of this class can be used as subquery expressions
     * inside Where statements, which then require the domain of the parent query.
     * If such a parameter is found, the resulting parsing value is returned
     * enclosed within parenthesis (as this is handled as a subquery).
     *
     * @return string the parsed query
     * @throw YMKM_SQL_ParseException if the parsing failed
     */
    public function parse()
    {
        $args   = func_get_args();
        $domain = null;
        $rendered = '%s';
        if (0 < func_num_args()) {
            $domain = array_shift($args);
            $rendered = '('.$rendered.')';
        }
        elseif (!is_null($this->_parsedStr)) {
            return $this->_parsedStr;
        }
        if (is_null($domain)) {
            $domain = new YMKM_SQL_Domain();
        }
        if (($domain instanceof YMKM_SQL_Domain) &&
            !$domain->has('boundParams')) {
            $domain->set('boundParams', array());
        }

        $out = sprintf($rendered, $this->_query->parse($domain));
        if (0 === func_num_args()) {
            $this->_parsedStr   = $out;
            $this->_boundParams = $domain->boundParams;
        }
        return $out;
    }

    /**
     * Returns a new table definition object whose type is based on argument
     *
     * @param string $tref the table definition name to return
     * @param string $alias the optional alias
     * @return YMKM_SQL_Iface_TableDef a new table definition
     */
    public function tableDefinition($tref, $alias=null)
    {
        $tblDef = new YMKM_SQL_Expression_TableDefinition($tref, $alias);
        return $tblDef;
    }

    /**
     * Returns a new table reference object whose type is based on argument
     *
     * @param string $tref the table reference name to return
     * @return YMKM_SQL_Iface_TableRef a new table reference
     */
    public function tableReference($tref)
    {
        assert(!is_null($tref));
        $tblRef = new YMKM_SQL_Expression_TableReference($tref);
        return $tblRef;
    }

    /**
     * Returns a new column definition object whose type is based on argument list
     *
     * If $tref is null, it will first try to split $cref into $tref+$cref.
     * if $cref starts with either the column position, arithmetic or bind param prefix,
     * the related object is created.
     * Otherwise ColumnReference is used with given parameters.
     * If $fn is a non null Closure function, the created column object will be decorated
     * with a ColumnFn decorator. The result of the parsing will be that of the lambda function.
     *
     * @param string $cref table.colname | @objname.colname | colname | =expr
     * @param string $tref optional table name if not in the $cref argument
     * @param string $alias optional alias
     * @param Closure $fn the optional lambda function to apply to the parsed content of the column
     * @return YMKM_SQL_Iface_ColumnDef a new column definition
     */
    public function colDefinition($cref, $tref=null, $alias=null, $fn=null)
    {
        assert(!is_null($cref));

        $colDef = null;
        if (0 === strpos($cref, self::COL_EXP_PREFIX)) {
            $cref = substr($cref, 1);
            $colDef = new YMKM_SQL_Expression_Arithmetic($cref, $alias);
        }
        else {
            if (is_null($tref)) {
                list($cref, $tref) = $this->_splitTblCol($cref);
            }
            $tblRef = (!is_null($tref)?
                      $this->tableReference($tref):null);
            $colDef = new YMKM_SQL_Expression_ColumnDefinition($cref, $tblRef, $alias);
        }

        if (!is_null($fn) && $fn instanceof Closure) {
            $colDef = new YMKM_SQL_Expression_ColumnFn($colDef, $fn, $alias);
        }
        return $colDef;
    }

    /**
     * Returns a new column reference object whose type is based on argument list
     *
     * If $tref is null, it will first try to split $cref into $tref+$cref.
     * if $cref starts with either the column position, arithmetic or bind param prefix,
     * the related object is created.
     * Otherwise ColumnReference is used with given parameters.
     * If $fn is a non null Closure function, the created column object will be decorated
     * with a ColumnFn decorator. The result of the parsing will be that of the lambda function.
     *
     * @param string $cref table.colname | @objname.colname | colname | =expr | ?bindexpr | #colpos
     * @param string $tref optional table name if not in the $cref argument
     * @param Closure $fn the optional lambda function to apply to the parsed content of the column
     * @return YMKM_SQL_Iface_ColumnRef a new column reference
     */
    public function colReference($cref, $tref=null, $fn=null)
    {
        assert(!is_null($cref));

        $colRef = null;
        if (0 === strpos($cref, self::COL_EXP_PREFIX)) {
            $cref = substr($cref, 1);
            $colRef = new YMKM_SQL_Expression_Arithmetic($cref);
        }
        elseif (0 === strpos($cref, self::COL_BIND_PREFIX)) {
            $cref = substr($cref, 1);
            $colRef = new YMKM_SQL_Expression_ColumnFn(
                        new YMKM_SQL_Expression_Arithmetic($cref),
                            function ($domain, $c) {
                              $domain->add('boundParams', $c); return '?'; });
        }
        else {
            if (is_null($tref)) {
                list($cref, $tref) = $this->_splitTblCol($cref);
            }
            $tblRef = (!is_null($tref)?
                      $this->tableReference($tref):null);
            $colRef = new YMKM_SQL_Expression_ColumnReference($cref, $tblRef);
        }

        if (!is_null($fn) && $fn instanceof Closure) {
            $colRef = new YMKM_SQL_Expression_ColumnFn($colRef, $fn);
        }
        return $colRef;
    }

    /**
     * Returns a column reference usable within an ORDER BY or GROUP BY  statement.
     *
     * Can be column position (#1, #2...), or a reference to a column defined within
     * defined tables.
     *
     * @return YMKM_SQL_Iface_OrderAware a valid expression inside ORDER BY/GROUP BY statements
     */
    public function orderColRef($tcref)
    {
        $colRef = null;
        if (0 === strpos($tcref, self::COL_POS_PREFIX)) {
            $tcref = substr($tcref, 1);
            $colRef = new YMKM_SQL_Expression_ColumnPosition($tcref);
        }
        else {
            list($cref, $tref) = $this->_splitTblCol($tcref);
            $tblRef = (!is_null($tref)?
                      $this->tableReference($tref):null);
            $colRef = new YMKM_SQL_Expression_ColumnReference($cref, $tblRef);
        }
        return $colRef;
    }

    /**
     * Returns all columns defined in the query
     *
     * @return array[YMKM_SQL_Iface_ColumnDef] of columns
     */
    public function cols()
    {
        return $this->_query->cols();
    }

    /**
     * Returns all tables defined in the query
     *
     * @return array[YMKM_SQL_Iface_TableDef] of tables
     */
    public function tables()
    {
        return $this->_query->tables();
    }

    /**
     * Private Constructor
     */
    public function __construct()
    {
        $this->_query = new YMKM_SQL_Query();
    }


    /// Private interface

    /**
     * Splits input into table name / column name based on separator
     * Table and columns are separated by TBL_COL_SEPARATOR
     * Column is required, but table is not.
     *
     * @param string $tcRef string to split
     * @return array[2], with array[0] = col name, array[1] = null|table name
     */
    private function _splitTblCol($tcRef)
    {
        $ret = array_reverse(explode(self::TBL_COL_SEPARATOR, $tcRef));
        if (!isset($ret[1])) {
            $ret[1] = null;
        }
        return $ret;
    }

    /**
     * Returns a lambda function that recursively process the where s-expressions list supplied
     *
     * @return Closure
     */
    private function _getWhereParseClosure()
    {
          $self = $this;
          $fn = function ($sexprs) use (&$fn, $self) {
                  // First argument is ALWAYS a closure
                  $exprFn = array_shift($sexprs);
                  if (!(is_null($exprFn) || $exprFn instanceof Closure)) {
                      throw new YMKM_Exception('Illegal Argument passed as where closure');
                  }
                  $exprs = array_map(
                             function ($sexpr) use (&$fn, $self) {
                               // sub expression is itself an expression : recursive call
                               if (is_array($sexpr) && isset($sexpr[0]) && ($sexpr[0] instanceof Closure)) {
                                 return $fn($sexpr);
                               }
                               // col. reference with a lambda function => enclosed in an associative array
                               elseif (is_array($sexpr)) {
                                 if (!array_key_exists('col', $sexpr)) {
                                   throw new YMKM_Exception('`col\' key is required in WHERE s-expression');
                                 }
                                 $f = (array_key_exists('fn', $sexpr)?$sexpr['fn']:null);
                                 return $self->colReference($sexpr['col'], $ref, $f);
                               }
                               // sub queries or any other object that responds to the parse method
                               elseif (is_object($sexpr) && method_exists($sexpr, 'parse')) {
                                 return $sexpr;
                               }
                               // self-evaluated expression, column reference
                               elseif (is_string($sexpr) || is_numeric($sexpr)) {
                                 return $self->colReference($sexpr, null, null);
                               }
                               // Any other type is an error
                               elseif (!is_null($sexpr)) {
                                 throw new YMKM_Exception('Illegal argument passed as where list : ' . $sexpr);
                               }
                               // Wrap null values into a Null Expression object
                               return new YMKM_SQL_Expression_Null($sexpr);
                             }, $sexprs);
                  // List of sub-expressions, map Fn, reduce Fn, init value
                  return new YMKM_SQL_Expression_Where($exprs, null, $exprFn, null);
            };
        return $fn;
    }
}
