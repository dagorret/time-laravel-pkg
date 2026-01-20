<?php

namespace Time\Laravel;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

class TimeTable
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public static function query($query)
    {
        return new static($query);
    }

    /**
     * Procesa la Request y aplica filtros/orden dinámicamente
     */
    public function apiHandle(Request $request)
    {
        // 1. Filtros por columna (soporta GET y POST)
        $filters = $request->isMethod('post') 
            ? $request->json('filters', []) 
            : $request->get('filters', []);

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $this->applyDynamicFilter($column, $value);
            }
        }

        // 2. Ordenamiento (Adaptado a TiTableLazy)
        $sortField = $request->get('sortField', 'id');
        $sortOrder = $request->get('sortOrder', 'desc'); 
        $direction = ($sortOrder == -1 || $sortOrder === 'desc') ? 'desc' : 'asc';
        
        $this->query->orderBy($sortField, $direction);

        return $this;
    }

    protected function applyDynamicFilter($column, $value)
    {
        // Traductor de palabras clave a rangos reales
        switch ($value) {
            case 'today':
                $this->query->whereDate($column, Carbon::today());
                return;
            case 'yesterday':
                $this->query->whereDate($column, Carbon::yesterday());
                return;
            case 'this_week':
                $this->query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                return;
            case 'this_month':
                $this->query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                return;
        }

        // Si no es palabra clave, procesamos normal (rango con coma o valor simple)
        if (is_string($value) && str_contains($value, ',')) {
            $range = explode(',', $value);
            $this->query->whereBetween($column, $range);
        } else {
            $this->query->where($column, $value);
        }
    }

    /**
     * Formatea la respuesta EXACTA para el componente
     */
    public function toTiTable()
    {
        // Obtenemos la paginación estándar de Laravel
        $paginated = $this->query->paginate(request('rows', 15));

        return response()->json([
            'success' => true,
            'data'    => $paginated->items(), // Los registros
            'total'   => $paginated->total(), // Total para el paginador
            'page'    => $paginated->currentPage(),
            'rows'    => $paginated->perPage(),
        ]);
    }
}
