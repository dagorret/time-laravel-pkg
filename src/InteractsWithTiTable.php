<?php

namespace Time\Laravel;

use Illuminate\Http\Request;

trait InteractsWithTiTable
{
    /**
     * Responde con formato TiTable.
     * $config es una función opcional para añadir ->searchable() o ->only()
     */
    protected function tiTableResponse($query, Request $request, callable $config = null)
    {
        $instance = TimeTable::query($query);

        if ($config) {
            $config($instance);
        }

        return $instance->apiHandle($request)->toTiTable();
    }
}
