# Dashboard Centralizado de Firmas Electronicas

## Fecha: 2026-02-13

## Problema

El sistema de firmas electronicas funciona por documento individual:
- `/firma/estado/10` → muestra las firmas del documento #10
- `/firma/estado/25` → muestra las firmas del documento #25

**No existe una vista centralizada** donde el consultor o admin pueda ver:
- Cuantos documentos tienen firmas pendientes
- Cuales estan firmados, cuales expirados
- Quien falta por firmar
- Acceder con un clic al estado de cualquier firma

Para llegar a `/firma/estado/{id}` hoy, hay que:
1. Ir al cliente → documentacion → buscar el documento → clic en "Solicitar Firma" → ver estado
2. O saber la URL exacta de memoria

---

## Solucion: Dashboard de Firmas (`/firma/dashboard`)

Una tabla centralizada que muestra **todos los documentos que tienen solicitudes de firma**
agrupados por documento, con resumen de estado y acceso directo.

---

## Diseño de la Vista

### Encabezado
- Titulo: "Gestion de Firmas Electronicas"
- Tarjetas resumen (cards): Total documentos | Pendientes | Firmados | Expirados

### Tabla Principal (DataTables)

| Columna | Dato | Fuente |
|---------|------|--------|
| Cliente | Nombre de la empresa | `tbl_clientes.nombre_cliente` |
| Documento | Codigo + Titulo | `tbl_documentos_sst.codigo`, `titulo` |
| Version | Numero de version | `tbl_documentos_sst.version` |
| Firmantes | Ej: "2/3 firmados" | COUNT de solicitudes por estado |
| Estado | Badge color segun progreso | Logica: todos firmados = verde, algunos pendientes = amarillo, expirados = rojo |
| Ultimo evento | Fecha de la ultima firma o solicitud | MAX fecha de `tbl_doc_firma_solicitudes` |
| Acciones | Boton "Ver Estado" → `/firma/estado/{id}` | Link directo |

### Filtros

- **Estado**: Todos | Con pendientes | Completados | Con expirados
- **Cliente**: Select2 con busqueda
- **Busqueda general**: DataTables search (codigo, titulo, cliente)

---

## Arquitectura

### Archivos a Crear/Modificar

| Archivo | Accion | Descripcion |
|---------|--------|-------------|
| `app/Models/DocFirmaModel.php` | Modificar | Agregar `getDashboardFirmas()` |
| `app/Controllers/FirmaElectronicaController.php` | Modificar | Agregar `dashboard()` |
| `app/Views/firma/dashboard.php` | **Crear** | Vista con tabla DataTables |
| `app/Config/Routes.php` | Modificar | Agregar ruta `/firma/dashboard` |
| `app/Config/Filters.php` | Verificar | Que `firma/*` este en filtro auth |
| `app/Views/consultant/dashboard.php` | Modificar | Agregar card de acceso |
| `app/Views/consultant/admindashboard.php` | Modificar | Agregar card de acceso |

---

## Implementacion

### 1. Modelo: `DocFirmaModel::getDashboardFirmas()`

Query que trae documentos con solicitudes de firma, agrupados por documento:

```php
public function getDashboardFirmas(?int $idConsultor = null): array
{
    $builder = $this->db->table('tbl_doc_firma_solicitudes s')
        ->select('
            s.id_documento,
            d.codigo,
            d.titulo,
            d.version,
            d.estado as estado_documento,
            d.tipo_documento,
            c.id_cliente,
            c.nombre_cliente,
            c.nit_cliente,
            COUNT(s.id_solicitud) as total_firmantes,
            SUM(CASE WHEN s.estado = "firmado" THEN 1 ELSE 0 END) as firmados,
            SUM(CASE WHEN s.estado = "pendiente" THEN 1 ELSE 0 END) as pendientes,
            SUM(CASE WHEN s.estado = "esperando" THEN 1 ELSE 0 END) as esperando,
            SUM(CASE WHEN s.estado = "expirado" THEN 1 ELSE 0 END) as expirados,
            SUM(CASE WHEN s.estado = "cancelado" THEN 1 ELSE 0 END) as cancelados,
            MAX(s.fecha_firma) as ultima_firma,
            MAX(s.created_at) as fecha_solicitud
        ')
        ->join('tbl_documentos_sst d', 'd.id_documento = s.id_documento')
        ->join('tbl_clientes c', 'c.id_cliente = d.id_cliente')
        ->groupBy('s.id_documento')
        ->orderBy('fecha_solicitud', 'DESC');

    if ($idConsultor) {
        $builder->where('c.id_consultor', $idConsultor);
    }

    return $builder->get()->getResultArray();
}
```

**Retorna un array donde cada fila = 1 documento** con contadores de firmantes.

### 2. Controller: `FirmaElectronicaController::dashboard()`

```php
public function dashboard()
{
    if (!session()->get('isLoggedIn')) {
        return redirect()->to('/login');
    }

    // Si es consultor, filtrar por sus clientes
    $idConsultor = null;
    if (session()->get('role') === 'consultant') {
        $idConsultor = session()->get('user_id');
    }

    $documentos = $this->firmaModel->getDashboardFirmas($idConsultor);

    // Calcular totales para cards
    $totales = [
        'total'     => count($documentos),
        'pendientes' => 0,
        'firmados'   => 0,
        'expirados'  => 0
    ];

    foreach ($documentos as $doc) {
        if ($doc['expirados'] > 0) {
            $totales['expirados']++;
        } elseif ($doc['firmados'] == $doc['total_firmantes']) {
            $totales['firmados']++;
        } else {
            $totales['pendientes']++;
        }
    }

    return view('firma/dashboard', [
        'documentos' => $documentos,
        'totales' => $totales
    ]);
}
```

### 3. Ruta

```php
// Routes.php - ANTES de las rutas con parametro
$routes->get('firma/dashboard', 'FirmaElectronicaController::dashboard');
```

**IMPORTANTE**: Esta ruta debe ir ANTES de `firma/(:any)` para que no la capture como token.

### 4. Filtro de autenticacion

Verificar en `Filters.php` que `firma/dashboard` este protegido.
Las rutas publicas son solo: `firma/firmar/*`, `firma/procesar`, `firma/confirmacion/*`, `firma/verificar/*`.

---

## Logica de Estado por Documento

Para determinar el badge/color de cada fila:

```php
function getEstadoResumen($doc) {
    if ($doc['expirados'] > 0) {
        return ['label' => 'Con expirados', 'class' => 'bg-danger'];
    }
    if ($doc['firmados'] == $doc['total_firmantes']) {
        return ['label' => 'Completado', 'class' => 'bg-success'];
    }
    if ($doc['firmados'] > 0) {
        return ['label' => 'En progreso', 'class' => 'bg-warning text-dark'];
    }
    if ($doc['pendientes'] > 0 || $doc['esperando'] > 0) {
        return ['label' => 'Pendiente', 'class' => 'bg-info'];
    }
    if ($doc['cancelados'] == $doc['total_firmantes']) {
        return ['label' => 'Cancelado', 'class' => 'bg-secondary'];
    }
    return ['label' => 'Sin firmas', 'class' => 'bg-light text-dark'];
}
```

---

## Acceso desde Dashboards

### Opcion: Card directo (sin Select2)

A diferencia del modulo de Actas que requiere seleccionar un cliente,
el Dashboard de Firmas muestra TODOS los documentos del consultor de una vez.
Por lo tanto, solo se necesita un boton/card que abra `/firma/dashboard`.

```html
<!-- En consultant/dashboard.php y admindashboard.php -->
<div class="col-lg-4 col-md-6 mb-3">
    <a href="/firma/dashboard" target="_blank" class="text-decoration-none">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 15px;">
            <div class="card-body text-center p-4"
                 style="background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 100%); color: white;">
                <i class="bi bi-pen" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3 mb-1">Firmas Electronicas</h5>
                <p class="mb-0 small opacity-75">
                    Ver estado de todas las solicitudes de firma
                </p>
            </div>
        </div>
    </a>
</div>
```

---

## Dependencias de la Vista

| Libreria | CDN | Uso |
|----------|-----|-----|
| Bootstrap 5.3 | Ya incluido globalmente | Layout y componentes |
| Bootstrap Icons | Ya incluido globalmente | Iconos |
| DataTables | `cdn.datatables.net/1.13.6` | Tabla interactiva con busqueda |
| DataTables Bootstrap 5 | `cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css` | Estilo BS5 |

---

## Vista Previa del Resultado

```
╔══════════════════════════════════════════════════════════════╗
║  Gestion de Firmas Electronicas                              ║
╠══════════════════════════════════════════════════════════════╣
║                                                              ║
║  [12 Total]  [5 Pendientes]  [6 Firmados]  [1 Expirado]    ║
║                                                              ║
║  Filtro: [Todos ▾]     Buscar: [________________]           ║
║                                                              ║
║  Cliente        | Documento         | Firmantes | Estado    ║
║  ─────────────────────────────────────────────────────────  ║
║  Empresa Omega  | RES-REP-001 v1.0  | 2/3       | En prog  ║
║  Empresa Omega  | PRG-CAP-001 v1.0  | 3/3       | Completo ║
║  Empresa Alpha  | ASG-RES-001 v2.0  | 0/2       | Pendient ║
║  Empresa Beta   | POL-SST-001 v1.0  | 1/2       | Expirado ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
```
