<?php

namespace Time\Laravel;

use Illuminate\Http\Request;

trait InteractsWithTiTable
{
    /**
     * Responde con el formato JSON necesario para TiTableLazy
     */
    protected function tiTableResponse($query, Request $request)
    {
        return TimeTable::query($query)
            ->apiHandle($request)
            ->toTiTable();
    }
}
