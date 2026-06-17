<?php

namespace App\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\SqlServerGrammar;

/**
 * SQL Server 2008 compatible query grammar.
 *
 * SQL Server 2012 introduced the ANSI-standard OFFSET/FETCH syntax. The
 * default Laravel grammar uses that syntax, which causes a syntax error on
 * SQL Server 2008 / 2008 R2 installations. This grammar replaces the
 * OFFSET/FETCH approach with a ROW_NUMBER() OVER (...) sub-query wrapper
 * that works on SQL Server 2005, 2008 and 2008 R2.
 *
 * Only the pagination-related overrides are needed; everything else delegates
 * to the standard SqlServerGrammar.
 */
class SqlServer2008Grammar extends SqlServerGrammar
{
    /**
     * Compile a select query into SQL.
     *
     * When an offset is present we wrap the query with a ROW_NUMBER() CTE so
     * that the result can be filtered by row number rather than using the
     * SQL Server 2012+ OFFSET/FETCH syntax.
     *
     * @param  Builder  $query
     * @return string
     */
    public function compileSelect(Builder $query): string
    {
        if (! $query->offset || $query->offset <= 0) {
            // No offset — delegate directly to the parent (which will use TOP
            // for a limit-only query, or a plain SELECT otherwise).
            return parent::compileSelect($query);
        }

        // The ROW_NUMBER() expression needs an ORDER BY. If the query has none,
        // add a neutral sort identical to what the parent does.
        if (empty($query->orders)) {
            $query->orders[] = ['sql' => '(SELECT 0)'];
        }

        $offset = (int) $query->offset;
        $limit = is_numeric($query->limit) && $query->limit > 0
            ? (int) $query->limit
            : null;

        // Derive the ORDER BY expression for ROW_NUMBER() BEFORE blanking orders.
        $orderBy = $this->compileOrdersForRowNumber($query);

        // Blank out the offset, limit AND orders so the inner SELECT emitted by
        // the parent has neither OFFSET/FETCH nor ORDER BY (SQL Server does not
        // allow ORDER BY in a derived table/subquery unless TOP is also present).
        $originalOffset = $query->offset;
        $originalLimit = $query->limit;
        $originalOrders = $query->orders;
        $query->offset = null;
        $query->limit = null;
        $query->orders = [];

        // Let the parent build the core SELECT (no offset, no limit, no order).
        $innerSql = parent::compileSelect($query);

        // Restore so the builder object is not mutated permanently.
        $query->offset = $originalOffset;
        $query->limit = $originalLimit;
        $query->orders = $originalOrders;

        // Wrap with ROW_NUMBER().
        $start = $offset + 1;
        $end = $limit !== null ? $offset + $limit : null;

        $rowNumberExpr = "row_number() over ({$orderBy})";

        $wrappedSql = "select * from (select row_num = {$rowNumberExpr}, inner_query.* from ({$innerSql}) as inner_query) as rn_query";

        if ($end !== null) {
            $wrappedSql .= " where rn_query.row_num between {$start} and {$end}";
        } else {
            $wrappedSql .= " where rn_query.row_num >= {$start}";
        }

        return $wrappedSql;
    }

    /**
     * Compile the ORDER BY expression used inside the ROW_NUMBER() window.
     *
     * @param  Builder  $query
     * @return string
     */
    protected function compileOrdersForRowNumber(Builder $query): string
    {
        if (empty($query->orders)) {
            return 'order by (select 0)';
        }

        return 'order by '.implode(', ', $this->compileOrdersToArray($query, $query->orders));
    }

    /**
     * Compile the "offset" clause.
     *
     * We handle offsets in compileSelect via ROW_NUMBER(), so this method
     * returns an empty string to prevent the OFFSET x ROWS fragment from
     * being emitted by the parent component loop.
     *
     * @param  Builder  $query
     * @param  int      $offset
     * @return string
     */
    protected function compileOffset(Builder $query, $offset): string
    {
        // Suppressed — handled in compileSelect via ROW_NUMBER() wrapping.
        return '';
    }

    /**
     * Compile the "limit" clause.
     *
     * When an offset is present the limit is baked into the ROW_NUMBER()
     * WHERE clause in compileSelect, so we only need to emit FETCH NEXT for
     * limit-only queries — but since we are targeting SQL Server 2008 which
     * does not support FETCH NEXT at all, we suppress it entirely and rely on
     * the TOP clause emitted by compileColumns for limit-only queries.
     *
     * @param  Builder  $query
     * @param  int      $limit
     * @return string
     */
    protected function compileLimit(Builder $query, $limit): string
    {
        // Suppressed — TOP is emitted by compileColumns when offset == 0.
        // When offset > 0 the limit is embedded in the ROW_NUMBER() wrapper.
        return '';
    }
}
