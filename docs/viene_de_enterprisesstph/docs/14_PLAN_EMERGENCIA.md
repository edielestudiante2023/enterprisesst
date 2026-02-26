# 14 - Plan de Emergencia (Fase 10 — Documento Maestro)

## Resumen

El Plan de Emergencia es el **documento final y mas complejo** del sistema de inspecciones. Consolida datos propios del formulario (~80 columnas, 19 fotos, 8 ENUMs) con datos de TODAS las inspecciones previas del mismo cliente, generando un PDF de ~30+ paginas.

**Patron:** Documento Maestro Consolidado (ver `15_PATRON_DOCUMENTO_MAESTRO.md`)

---

## Archivos

```
app/SQL/migrate_plan_emergencia.php                              — 1 tabla, ~80 columnas
app/Models/PlanEmergenciaModel.php                               — 1 modelo
app/Controllers/Inspecciones/PlanEmergenciaController.php        — controlador CRUD+PDF (~608 lineas)
app/Views/inspecciones/plan-emergencia/list.php                  — listado cards
app/Views/inspecciones/plan-emergencia/form.php                  — formulario largo (~26 secciones, 19 fotos)
app/Views/inspecciones/plan-emergencia/view.php                  — vista read-only
app/Views/inspecciones/plan-emergencia/pdf.php                   — template DOMPDF (~800+ lineas, texto estatico + datos)
```

Total: 7 archivos nuevos + 3 archivos modificados (Routes.php, dashboard.php, InspeccionesController.php)

---

## Tabla: `tbl_plan_emergencia`

### Campos ENUM (8)

| Campo DB | Valores | Uso |
|----------|---------|-----|
| `casas_o_apartamentos` | `casas`, `apartamentos` | Tipo de inmueble |
| `tiene_gabinetes_hidraulico` | `si`, `no` | Condiciona validacion gabinetes |
| `hay_parqueadero_privado` | `si`, `no` | Parqueaderos privados |
| `tiene_oficina_admin` | `si`, `no` | Condiciona foto oficina |
| `registro_visitantes_emergencia` | `si`, `no` | Control visitantes en emergencia |
| `cuenta_megafono` | `si`, `no` | Megafono disponible |
| `ciudad` | `bogota`, `soacha` | Determina telefonos de emergencia |
| `empresa_aseo` | `urbaser_soacha`, `bogota_limpia`, `promoambiental`, `ciudad_limpia`, `area_limpia`, `lime` | Empresa recolectora |

### Campos foto (19)

```
foto_fachada, foto_panorama, foto_torres_1, foto_torres_2,
foto_parqueaderos_carros, foto_parqueaderos_motos, foto_oficina_admin,
foto_circulacion_vehicular, foto_circulacion_peatonal_1, foto_circulacion_peatonal_2,
foto_salida_emergencia_1, foto_salida_emergencia_2, foto_ingresos_peatonales,
foto_acceso_vehicular_1, foto_acceso_vehicular_2,
foto_ruta_evacuacion_1, foto_ruta_evacuacion_2,
foto_punto_encuentro_1, foto_punto_encuentro_2
```

### Otros campos notables

| Tipo | Campos |
|------|--------|
| SMALLINT (6) | `anio_construccion`, `numero_torres`, `numero_unidades_habitacionales`, `parqueaderos_carros_residentes`, `parqueaderos_carros_visitantes`, `parqueaderos_motos_residentes`, `parqueaderos_motos_visitantes`, `cantidad_salones_comunales`, `cantidad_locales_comerciales` |
| TEXT (~20) | `tanque_agua`, `planta_electrica`, `circulacion_vehicular`, `circulacion_peatonal`, `salidas_emergencia`, `ingresos_peatonales`, `accesos_vehiculares`, `concepto_entradas_salidas`, `hidrantes`, `otros_proveedores`, `registro_visitantes_forma`, `ruta_evacuacion`, `mapa_evacuacion`, `puntos_encuentro`, `sistema_alarma`, `codigos_alerta`, `energia_emergencia`, `deteccion_fuego`, `vias_transito`, `personal_aseo`, `personal_vigilancia`, `ruta_residuos_solidos`, `servicios_sanitarios`, `detalle_mascotas`, `detalle_dependencias`, `observaciones` |
| VARCHAR (~8) | `sismo_resistente`, `casas_pisos`, `cai_cercano`, `bomberos_cercanos`, `proveedor_vigilancia`, `proveedor_aseo`, `nombre_administrador`, `horarios_administracion`, `frecuencia_basura`, `cuadrante` |

---

## Mapeo AppSheet → DB (Barrido completo)

### Columnas MIGRADAS (62 de 98)

Todas las columnas de AppSheet que contenian datos reales del consultor fueron migradas 1:1 a la tabla `tbl_plan_emergencia`.

### Columnas NO almacenadas con justificacion (20 de 98)

| Categoria | Columnas | Reemplazo |
|-----------|----------|-----------|
| **FK normalizada** (4) | LOGO, NOMBRE CLIENTE, NOMBRE CONSULTOR, DIRECCION | `id_cliente` → `tbl_clientes`, `id_consultor` → `tbl_consultor` |
| **Helper AppSheet** (2) | cuentadecampos, VER FALTANTES | No aplica (eran virtuales sin datos) |
| **Validacion programatica** (9) | 8x "OK REALIZADA..." + GENERAR INFORME | `verificarInspeccionesCompletas()` en PHP + boton Finalizar |
| **Telefonos constantes** (8) | 8x "R TELEFONO..." | `ciudad` ENUM + constante PHP `TELEFONOS` |

**Nota:** Los numeros de emergencia nacionales (116, 119, 132, 144, 165, 123) son iguales en Bogota y Soacha. Solo difieren Secretaria de Salud y Secretaria de Movilidad.

### Columnas NUEVAS agregadas (7)

| Campo | Razon |
|-------|-------|
| `id_cliente` | FK normalizada |
| `id_consultor` | FK normalizada |
| `ciudad` | Reemplaza 8 columnas de telefonos |
| `ruta_pdf` | Path al PDF generado |
| `estado` | ENUM('borrador','completo') |
| `created_at` | Timestamp automatico |
| `updated_at` | Timestamp automatico |

---

## Telefonos de Emergencia (constante PHP)

En lugar de almacenar 8 columnas de telefono por registro, se guarda solo `ciudad` y los numeros se resuelven desde una constante en el controlador:

```php
public const TELEFONOS = [
    'bogota' => [
        'acueducto' => '116',
        'bomberos' => '119',
        'cruz_roja' => '132',
        'defensa_civil' => '144',
        'gaula' => '165',
        'policia' => '123',
        'secretaria_salud' => '(601) 364-9090',
        'secretaria_movilidad' => '#797',
    ],
    'soacha' => [
        'acueducto' => '116',
        'bomberos' => '119',
        'cruz_roja' => '132',
        'defensa_civil' => '144',
        'gaula' => '165',
        'policia' => '123',
        'secretaria_salud' => '(601) 730-5500',
        'secretaria_movilidad' => '(601) 840-0223',
    ],
];
```

El formulario auto-llena la tabla de telefonos via JS al seleccionar la ciudad.

---

## Empresas de Aseo (constante PHP)

```php
public const EMPRESAS_ASEO = [
    'urbaser_soacha' => 'Urbaser Soacha S.A. E.S.P.',
    'bogota_limpia' => 'Bogota Limpia',
    'promoambiental' => 'Promoambiental Distrito',
    'ciudad_limpia' => 'Ciudad Limpia',
    'area_limpia' => 'Area Limpia',
    'lime' => 'LIME',
];
```

---

## Validacion de Inspecciones Completas

Antes de permitir **finalizar**, el controlador verifica que existan inspecciones con `estado='completo'` para el mismo cliente:

```php
private function verificarInspeccionesCompletas(int $idCliente): array
{
    $faltantes = [];
    $models = [
        'Inspeccion Locativa' => new InspeccionLocativaModel(),
        'Matriz de Vulnerabilidad' => new MatrizVulnerabilidadModel(),
        'Probabilidad de Peligros' => new ProbabilidadPeligrosModel(),
        'Revision de Extintores' => new InspeccionExtintoresModel(),
        'Revision de Botiquines' => new InspeccionBotiquinModel(),
        'Recursos de Seguridad' => new InspeccionRecursosSeguridadModel(),
        'Equipos de Comunicaciones' => new InspeccionComunicacionModel(),
    ];
    foreach ($models as $nombre => $model) {
        $existe = $model->where('id_cliente', $idCliente)
            ->where('estado', 'completo')->first();
        if (!$existe) $faltantes[] = $nombre;
    }
    // Gabinetes es condicional
    return $faltantes;
}
```

**Gabinetes** solo se requiere si `tiene_gabinetes_hidraulico = 'si'`.

Tambien se expone via endpoint AJAX `/inspecciones/plan-emergencia/check-inspecciones/{idCliente}` para validacion frontend.

---

## Estructura del PDF (~30+ paginas)

El PDF es el documento mas extenso del sistema. Combina texto estatico (de `z_plandeemergencia.txt`) con datos dinamicos del formulario y de TODAS las inspecciones previas.

### Secciones del PDF

1. **Portada** — Logo + nombre cliente + foto fachada
2. **Header corporativo** — SG-SST / FT-SST-001 / V001 / Fecha
3. **Introduccion** (texto estatico)
4. **Justificacion** (texto estatico con datos del cliente)
5. **Objetivos** (texto estatico)
6. **Alcance** (texto estatico)
7. **Conceptos/Glosario** (~23 definiciones, texto estatico)
8. **Informacion General del Conjunto** (datos formulario + fotos)
9. **Circulaciones y Accesos** (datos formulario + fotos)
10. **Entorno y Proveedores** (datos formulario)
11. **Legislacion** (texto estatico ~2 paginas)
12. **Analisis de Riesgos**:
    - Identificacion de peligros (tabla estatica)
    - **Probabilidad de Ocurrencia** → `tbl_probabilidad_peligros`
    - Descripciones por peligro (texto estatico)
    - Porcentajes consolidados
13. **Carga Combustible** (texto estatico)
14. **PON Codigo 7** (texto estatico - falla ascensor)
15. **Anexos - Evaluaciones**:
    - Inspeccion Locativa → `tbl_hallazgos_locativa`
    - Matriz Vulnerabilidad → `tbl_matriz_vulnerabilidad`
    - Extintores → `tbl_inspeccion_extintores`
    - Botiquin → `tbl_inspeccion_botiquin`
    - Recursos Seguridad → `tbl_inspeccion_recursos_seguridad`
    - Comunicaciones → `tbl_inspeccion_comunicacion`
    - Gabinetes → `tbl_inspeccion_gabinete` (si aplica)
16. **Telefonos de Emergencia** (segun ciudad)
17. **Administracion y Personal**
18. **Servicios Generales**
19. **Observaciones y Recomendaciones**

### Datos de inspecciones previas cargados en el PDF

```php
$ultimaLocativa = (new InspeccionLocativaModel())->where(...)->first();
$hallazgosLocativa = (new HallazgoLocativoModel())->where(...)->findAll();
$ultimaMatriz = (new MatrizVulnerabilidadModel())->where(...)->first();
$ultimaProb = (new ProbabilidadPeligrosModel())->where(...)->first();
$ultimaExt = (new InspeccionExtintoresModel())->where(...)->first();
$ultimaBot = (new InspeccionBotiquinModel())->where(...)->first();
$ultimaRec = (new InspeccionRecursosSeguridadModel())->where(...)->first();
$ultimaCom = (new InspeccionComunicacionModel())->where(...)->first();
$ultimaGab = (new InspeccionGabineteModel())->where(...)->first(); // si aplica
```

---

## Formulario — Secciones (26)

| # | Seccion | Campos | Fotos |
|---|---------|--------|-------|
| 1 | Datos Generales | cliente Select2, fecha | 0 |
| 2 | Fachada y Panorama | — | 2 |
| 3 | Descripcion del Inmueble | casas/aptos, sismo, ano, torres, unidades, pisos | 2 |
| 4 | Parqueaderos | 4 numericos + privado SI/NO | 2 |
| 5 | Areas Comunes | salones, locales, oficina SI/NO | 1 (condicional) |
| 6 | Servicios del Conjunto | tanque agua, planta electrica | 0 |
| 7 | Circulacion Vehicular | textarea | 1 |
| 8 | Circulacion Peatonal | textarea | 2 |
| 9 | Salidas de Emergencia | textarea | 2 |
| 10 | Ingresos Peatonales | textarea | 1 |
| 11 | Accesos Vehiculares | textarea | 2 |
| 12 | Concepto del Consultor | entradas/salidas, hidrantes | 0 |
| 13 | Entorno | CAI, bomberos | 0 |
| 14 | Proveedores | vigilancia, aseo, otros | 0 |
| 15 | Control de Visitantes | forma, emergencia SI/NO | 0 |
| 16 | Megafono | megafono SI/NO | 0 |
| 17 | Ruta de Evacuacion | descripcion, mapa | 2 |
| 18 | Puntos de Encuentro | descripcion | 2 |
| 19 | Sistema de Alarma | alarma, codigos | 0 |
| 20 | Sistemas de Emergencia | energia, deteccion fuego | 0 |
| 21 | Vias de Transito | textarea | 0 |
| 22 | Administracion | nombre, horarios, aseo, vigilancia | 0 |
| 23 | Telefonos de Emergencia | ciudad select + tabla auto + cuadrante | 0 |
| 24 | Gabinetes | tiene SI/NO | 0 |
| 25 | Servicios Generales | residuos, empresa, sanitarios, basura, mascotas, dependencias | 0 |
| 26 | Observaciones | textarea libre | 0 |

**Total:** ~62 campos editables + 19 fotos = ~81 inputs

---

## JS Features del Formulario

- **Autoguardado localStorage** — key `plan_emg_draft_{id|new}`, cada 30s + 2s debounce on change (solo texto, no fotos)
- **Condicional casas/apartamentos** — muestra/oculta torres vs pisos segun seleccion
- **Condicional oficina admin** — muestra/oculta campo foto si tiene_oficina_admin='si'
- **Tabla telefonos auto-llenada** — JS reacciona al cambio de ciudad y llena la tabla
- **Camera/Gallery buttons** — patron dual para cada foto (ver `11_INPUT_FILE_CAMARA_GALERIA.md`)
- **FileReader preview** — preview inline de cada foto seleccionada

---

## Rutas (11)

```php
// Plan de Emergencia
$routes->get('plan-emergencia', 'PlanEmergenciaController::list');
$routes->get('plan-emergencia/create', 'PlanEmergenciaController::create');
$routes->get('plan-emergencia/create/(:num)', 'PlanEmergenciaController::create/$1');
$routes->post('plan-emergencia/store', 'PlanEmergenciaController::store');
$routes->get('plan-emergencia/edit/(:num)', 'PlanEmergenciaController::edit/$1');
$routes->post('plan-emergencia/update/(:num)', 'PlanEmergenciaController::update/$1');
$routes->get('plan-emergencia/view/(:num)', 'PlanEmergenciaController::view/$1');
$routes->get('plan-emergencia/pdf/(:num)', 'PlanEmergenciaController::generatePdf/$1');
$routes->post('plan-emergencia/finalizar/(:num)', 'PlanEmergenciaController::finalizar/$1');
$routes->get('plan-emergencia/delete/(:num)', 'PlanEmergenciaController::delete/$1');
$routes->get('plan-emergencia/check-inspecciones/(:num)', 'PlanEmergenciaController::checkInspeccionesCompletas/$1');
```

---

## Upload a Reportes

| Campo | Valor |
|-------|-------|
| `id_report_type` | 6 |
| `id_detailreport` | 19 |
| `tag` | `plan_emg_id:{id}` |
| Dir PDF | `uploads/inspecciones/plan-emergencia/pdfs/` |
| Dir Fotos | `uploads/inspecciones/plan-emergencia/` |

---

## Dashboard

- **Card:** icono `fa-file-medical`, label "Plan de Emergencia"
- **Pendientes:** borradores despues de Matriz Vulnerabilidad
- **Conteo:** `totalPlanEmergencia` (estado=completo)
- **Nota:** usa `fecha_visita` (no `fecha_inspeccion` como otros modulos)
