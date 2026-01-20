Este es el documento que define la identidad de tu paquete. Está diseñado para que cualquier desarrollador (o tú mismo en el futuro) entienda que este paquete es el **puente inteligente** entre Laravel y las tablas dinámicas.

---

# Time Laravel Package ⏱️

Este paquete es un motor de transformación de consultas para Laravel. Permite convertir peticiones de frontend (filtros, ordenamiento y paginación) directamente en consultas de base de datos optimizadas, con un enfoque especial en el **manejo inteligente de rangos de tiempo**.

Está diseñado específicamente para alimentar componentes de tablas dinámicas como **TiTableLazy**.

## 🚀 Características

- **Filtros en Lenguaje Natural:** Soporta `today`, `yesterday`, `this_week`, `this_month`.

- **Filtros Dinámicos:** Detecta automáticamente rangos (separados por coma) o valores exactos.

- **Paginación Inteligente:** Adaptada al formato de respuesta que esperan los componentes modernos.

- **Trait de Integración:** Incluye un Trait para limpiar tus controladores.

---

## 🛠️ Instalación

### En Desarrollo (Local)

Para trabajar en tu laboratorio (`time-lab`) vinculando el paquete que tienes en la carpeta de al lado, añade esto al `composer.json` de tu proyecto principal:

JSON

```
"repositories": [
    {
        "type": "path",
        "url": "../time-laravel-pkg"
    }
],
"require": {
    "time/laravel": "dev-main"
}
```

Luego ejecuta:

Bash

```
composer update
```

### En Producción

Una vez que subas el paquete a un repositorio privado o público (GitHub/GitLab):

Bash

```
composer require time/laravel
```

---

## 🛰️ Cómo armar la API

El paquete hace que tus controladores pasen de tener 50 líneas a solo 2.

### 1. Usando el Trait en el Controlador

Importa `InteractsWithTiTable` para habilitar el método `tiTableResponse`.

PHP

```
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Log; // Tu modelo
use Time\Laravel\InteractsWithTiTable;

class LogController extends Controller
{
    use InteractsWithTiTable;

    public function index(Request $request)    {
        // Puedes pasar un Modelo, un Query Builder o un DB::table
        $query = Log::query(); 

        // El paquete procesa filtros, orden y devuelve el JSON
        return $this->tiTableResponse($query, $request);
    }
}
```

### 2. Definición de la Ruta

En tu archivo `routes/api.php`:

PHP

```
use App\Http\Controllers\Api\LogController;

Route::get('/logs', [LogController::class, 'index']);
```

---

## 🔍 Uso de la Query desde el Frontend

El API responderá dinámicamente según los parámetros que reciba en la URL:

| Parámetro           | Ejemplo                             | Resultado SQL                     |
| ------------------- | ----------------------------------- | --------------------------------- |
| **Filtro Simple**   | `filters[status]=error`             | `WHERE status = 'error'`          |
| **Rango de Fechas** | `filters[at]=2026-01-01,2026-01-10` | `WHERE at BETWEEN ...`            |
| **Atajo de Tiempo** | `filters[at]=this_month`            | `WHERE at` (rango del mes actual) |
| **Orden**           | `sortField=id&sortOrder=-1`         | `ORDER BY id DESC`                |
| **Paginación**      | `rows=50&page=2`                    | `LIMIT 50 OFFSET 50`              |

Exportar a Hojas de cálculo

---

## 🏗️ Estructura de Consulta en el Modelo

Si necesitas que la consulta tenga filtros base (por ejemplo, solo logs del usuario autenticado) antes de que el paquete aplique los filtros de la tabla, hazlo así:

PHP

```
public function index(Request $request)
{
    $query = Log::where('user_id', auth()->id())
                ->where('active', true);

    // El paquete respetará tus where anteriores y anidará los nuevos
    return $this->tiTableResponse($query, $request);
}
```


