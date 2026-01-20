<?php

namespace Time\Laravel;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeTable
{
    protected $query;
    protected $searchableColumns = [];

    public function __construct($query)
    {
        $this->query = $query;
    }

    public static function query($query)
    {
        return new static($query);
    }

    /**
     * Define qué columnas se usarán en la búsqueda global
     */
    public function searchable(array $columns)
    {
        $this->searchableColumns = $columns;
        return $this;
    }

    /**
     * Limita las columnas que se traen de la base de datos
     */
    public function only(array $columns)
    {
        $this->query->select($columns);
        return $this;
    }

    /**
     * Procesa la Request y aplica filtros/orden/búsqueda
     */
    public function apiHandle(Request $request)
    {
        // 1. Búsqueda Global (si existe el parámetro 'search')
        $search = $request->get('search');
        if ($search && !empty($this->searchableColumns)) {
            $this->query->where(function ($q) use ($search) {
                foreach ($this->searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        // 2. Filtros por columna
        $filters = $request->isMethod('post') 
            ? $request->json('filters', []) 
            : $request->get('filters', []);

        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                $this->applyDynamicFilter($column, $value);
            }
        }

        // 3. Ordenamiento
        $sortField = $request->get('sortField', 'id');
        $sortOrder = $request->get('sortOrder', 'desc'); 
        $direction = ($sortOrder == -1 || $sortOrder === 'desc') ? 'desc' : 'asc';
        
        $this->query->orderBy($sortField, $direction);

        return $this;
    }

    protected function applyDynamicFilter($column, $value)
    {
        // Traductor de lenguaje natural
        switch ($value) {
            case 'today':
                return $this->query->whereDate($column, Carbon::today());
            case 'yesterday':
                return $this->query->whereDate($column, Carbon::yesterday());
            case 'this_week':
                return $this->query->whereBetween($column, [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            case 'this_month':
                return $this->query->whereBetween($column, [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }

        // Rango manual (coma) o valor exacto
        if (is_string($value) && str_contains($value, ',')) {
            $range = explode(',', $value);
            $this->query->whereBetween($column, $range);
        } else {
            $this->query->where($column, $value);
        }
    }

    public function toTiTable()
    {
        $paginated = $this->query->paginate(request('rows', 15));

        return response()->json([
            'success' => true,
            'data'    => $paginated->items(),
            'total'   => $paginated->total(),
            'page'    => $paginated->currentPage(),
            'rows'    => $paginated->perPage(),
        ]);
    }
}
