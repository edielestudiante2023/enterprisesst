# Plan Maestro — Otto Chat (EnterpriseSST)

> Fecha: 2026-03-15
> Estado: En progreso
> Referencia gemelo: `enterprisesstph` → `app/SQL/create_views_otto.sql` + `portal_cliente_chat.md`

---

## Fase 1 — Variables de entorno OpenAI ✅ LISTA PARA EJECUTAR

**Problema actual:** `.env` tiene `OPENAI_MODEL=gpt-4o` (global). `AgenteChatService` hereda ese valor aunque su fallback sea `gpt-4o-mini`. Otto corre sobre gpt-4o y explota en TPM con el system prompt grande.

**Solución:** Separar en dos variables independientes.

### Cambios requeridos

**`.env`:**
```
OPENAI_MODEL=gpt-4o        # documentos SST (alta calidad, baja frecuencia)
OTTO_MODEL=gpt-4o-mini     # chat Otto (rápido, barato, alta frecuencia por sesión)
```

**`app/Services/AgenteChatService.php`:**
```php
// Antes:
$this->model = env('OPENAI_MODEL', 'gpt-4o-mini');

// Después:
$this->model = env('OTTO_MODEL', 'gpt-4o-mini');
```

### Por qué dos modelos

| Módulo | Modelo | Justificación |
|--------|--------|---------------|
| Generación documentos SST | `gpt-4o` | 1 llamada por documento, necesita calidad redacción |
| Otto chat | `gpt-4o-mini` | N llamadas por sesión, TPM crítico, SQL no necesita creatividad |

---

## Fase 2 — Inventario de tablas + Vistas SQL

### 2.1 Inventario completo

Clasificar las 149 tablas de `empresas_sst` en estas categorías:

| Categoría | Criterio | Acción |
|-----------|----------|--------|
| **Negocio con FK** | Tiene `id_cliente` u otras FK de negocio | Crear vista `v_*` |
| **Lookup / Catálogo** | Sin FK o solo contiene maestros globales | Sin vista, mapear directo |
| **Sistema / Seguridad** | `tbl_usuarios`, `tbl_roles`, `tbl_sesiones_*`, etc. | Excluir de Otto |
| **Obsoleta** | `_old`, `prueba`, `tbl_tests`, sistema KPI viejo | Excluir, documentar |
| **Log / Auditoría** | `tbl_*_audit`, `tbl_agente_chat_log`, `tbl_auditoria` | Excluir de lectura Otto |

### 2.2 Reglas para crear vistas

- `CREATE OR REPLACE VIEW v_{nombre} AS ...` — idempotente, seguro re-ejecutar
- Cada vista desnormaliza los JOINs más comunes (nombre_cliente, nombre_consultor, etc.)
- Las vistas son **solo lectura** — INSERT/UPDATE/DELETE siempre a `tbl_*`
- Alias obligatorios para evitar colisiones:
  - Si tabla base ya tiene `nombre_cliente`: usar `c.nombre_cliente AS nombre_cliente_join`
  - `tbl_inspeccion_senalizacion` → alias `ins` (no `is`, es palabra reservada MySQL)
  - `tbl_presupuesto_sst.anio` en JOIN → alias `anio_presupuesto`

### 2.3 Script de salida

```
app/SQL/
  create_views_otto.sql     ← todas las CREATE OR REPLACE VIEW
  apply_views_otto.php      ← script CLI para aplicar local y producción
```

### 2.4 Grupos de vistas a crear

| Grupo | Tablas base | Vista |
|-------|-------------|-------|
| Plan de trabajo | `tbl_pta_cliente` + `tbl_clientes` + `tbl_consultor` | `v_pta_cliente` |
| Pendientes | `tbl_pendientes` + `tbl_clientes` | `v_pendientes` |
| Capacitaciones | `tbl_cronog_capacitacion` + `capacitaciones_sst` + `tbl_clientes` | `v_cronog_capacitacion` |
| Indicadores | `tbl_indicadores_sst` + `tbl_clientes` | `v_indicadores_sst` |
| Indicadores mediciones | `tbl_indicadores_sst_mediciones` + `tbl_indicadores_sst` | `v_indicadores_mediciones` |
| Documentos SST | `tbl_documentos_sst` + `tbl_clientes` | `v_documentos_sst` |
| Versiones docs | `tbl_doc_versiones_sst` + `tbl_documentos_sst` | `v_doc_versiones_sst` |
| Evaluación inicial | `evaluacion_inicial_sst` + `tbl_clientes` | `v_evaluacion_inicial` |
| Estándares cliente | `tbl_cliente_estandares` + `estandares` + `tbl_clientes` | `v_cliente_estandares` |
| Inspecciones botiquín | `tbl_inspeccion_botiquin` + `tbl_clientes` | `v_inspeccion_botiquin` |
| Inspecciones extintores | `tbl_inspeccion_extintores` + `tbl_clientes` | `v_inspeccion_extintores` |
| Inspecciones locativas | `tbl_inspeccion_locativa` + `tbl_clientes` | `v_inspeccion_locativa` |
| Inspecciones señalización | `tbl_inspeccion_senalizacion` + `tbl_clientes` | `v_inspeccion_senalizacion` |
| Actas visita | `tbl_acta_visita` + `tbl_clientes` + `tbl_consultor` | `v_acta_visita` |
| Mantenimientos | `tbl_vencimientos_mantenimientos` + `tbl_mantenimientos` + `tbl_clientes` | `v_vencimientos_mantenimientos` |
| Matrices | `tbl_matrices` + `tbl_clientes` | `v_matrices` |
| Presupuesto | `tbl_presupuesto_sst` + `tbl_presupuesto_items` + `tbl_presupuesto_detalle` | `v_presupuesto` |
| Contratos | `tbl_contratos` + `tbl_clientes` + `tbl_consultor` | `v_contratos` |
| Actas comité | `tbl_actas` + `tbl_comites` + `tbl_tipos_comite` + `tbl_clientes` | `v_actas_comite` |
| Compromisos acta | `tbl_acta_compromisos` + `tbl_actas` + `tbl_clientes` | `v_acta_compromisos` |
| Procesos electorales | `tbl_procesos_electorales` + `tbl_clientes` | `v_procesos_electorales` |
| Candidatos | `tbl_candidatos_comite` + `tbl_procesos_electorales` | `v_candidatos_comite` |
| Hallazgos ACC | `tbl_acc_hallazgos` + `tbl_clientes` | `v_acc_hallazgos` |
| Acciones correctivas | `tbl_acc_acciones` + `tbl_acc_hallazgos` | `v_acc_acciones` |
| Reportes | `tbl_reporte` + `tbl_clientes` + `detail_report` + `report_type_table` | `v_reportes` |
| Contexto cliente | `tbl_cliente_contexto_sst` + `tbl_clientes` | `v_cliente_contexto` |
| Responsables SST | `tbl_cliente_responsables_sst` + `tbl_clientes` | `v_responsables_sst` |
| Miembros comité | `tbl_comite_miembros` + `tbl_comites` + `tbl_clientes` | `v_comite_miembros` |
| Vigías | `tbl_vigias` + `tbl_clientes` | `v_vigias` |
| Inducción etapas | `tbl_induccion_etapas` + `tbl_clientes` | `v_induccion_etapas` |
| Looker Studio | `tbl_lookerstudio` + `tbl_clientes` | `v_lookerstudio` |

---

## Fase 3 — Aplicar los 30 aprendizajes al código

### 3.1 System prompt (AgenteChatService)

- [ ] **Inyectar `$now` y `$year`** desde PHP en `buildSystemPrompt()` y `buildSystemPromptCliente()`
- [ ] **Agregar regla "NUNCA muestres SQL"** en sección REGLAS ESTRICTAS de ambos prompts
- [ ] **Reformatear OttoTableMap** a una línea por tabla: `tabla(col1,col2,col3) — descripción`
- [ ] **Referenciar vistas** en OttoTableMap con estrategia dual:
  ```
  v_pta_cliente(SELECT) / tbl_pta_cliente(WRITE)
  ```

### 3.2 ENUMs explícitos en el prompt

Agregar en el prompt los ENUMs reales para evitar alucinaciones:
```
tbl_pta_cliente.estado_actividad: ABIERTA|GESTIONANDO|CERRADA|CERRADA SIN EJECUCIÓN|CERRADA POR FIN CONTRATO
tbl_pendientes.estado: ABIERTA|CERRADA|SIN RESPUESTA DEL CLIENTE|CERRADA POR FIN CONTRATO
tbl_vencimientos_mantenimientos.estado_actividad: sin ejecutar|ejecutado|CERRADA|CERRADA POR FIN CONTRATO
```

### 3.3 OttoTableMap — tablas sin prefijo `tbl_` a agregar

```
evaluacion_inicial_sst     ← sin prefijo, 300 filas
capacitaciones_sst         ← catálogo, destino de JOIN desde tbl_cronog_capacitacion
detail_report              ← lookup, destino de JOIN desde tbl_reporte
report_type_table          ← lookup, destino de JOIN desde tbl_reporte
matriz_legal               ← catálogo global (491 filas)
```

---

## Fase 4 — Replicar arquitectura del gemelo (triple capa de seguridad)

Basado en `enterprisesstph/portal_cliente_chat.md`.

### 4.1 Usuario MySQL readonly

Crear usuario `empresas_readonly` con `GRANT SELECT` solo sobre las vistas `v_*`.

```
app/SQL/create_readonly_user.php   ← script CLI
```

Gotchas del gemelo a no repetir:
- Crear para `localhost` Y `127.0.0.1` (XAMPP usa socket vs TCP)
- `REVOKE ALL ON empresas_sst.*` (no global) para DigitalOcean
- `"no such grant"` en REVOKE de usuario nuevo → es SKIP, no error

### 4.2 Grupo de conexión `readonly` en Database.php

```php
public array $readonly = [
    'hostname' => env('readonly.hostname', 'localhost'),
    'username' => env('readonly.username', 'empresas_readonly'),
    'password' => env('readonly.password', ''),
    'database' => env('readonly.database', 'empresas_sst'),
    'port'     => (int) env('readonly.port', 3306),
    'encrypt'  => env('readonly.encrypt', false),
    // campos estándar CI4...
];
```

### 4.3 ClienteChatController — triple capa

| Capa | Implementación |
|------|---------------|
| **DB** | Conectar con grupo `readonly` (solo SELECT físico) |
| **App** | Sin endpoint `/confirmar`. Validar que SQL sea SELECT antes de ejecutar |
| **Prompt** | System prompt exige `WHERE id_cliente = {$idCliente}` en todo SELECT |

### 4.4 Validación `queryContainsClientScope()`

Método PHP que verifica que la query del cliente siempre incluya su `id_cliente` antes de ejecutar.
Si no cumple → error: *"Solo puedes consultar datos de tu empresa"*.

### 4.5 Diferencias vs consultor

| Aspecto | Consultor | Cliente |
|---------|-----------|---------|
| Conexión DB | `default` (escritura) | `readonly` (solo SELECT) |
| Tools disponibles | SELECT + INSERT + UPDATE + DELETE | Solo SELECT |
| Confirmación UI | Sí | No |
| Panel schema | Sí | No |
| Scope datos | Todos los clientes | Solo su `id_cliente` |

---

## Orden de ejecución

```
Fase 1 → Fase 2 → Fase 3 → Fase 4
  ↑           ↑         ↑         ↑
 15 min    3-4 hrs    2 hrs    2-3 hrs
```

- **Fase 1** es prerequisito para no quemar TPM durante el desarrollo
- **Fase 2** es prerequisito para Fases 3 y 4 (las vistas deben existir antes de mapearlas)
- **Fase 3** mejora Otto para consultor/admin
- **Fase 4** sube el nivel de seguridad del módulo cliente

## Archivos que se crearán/modificarán

| Archivo | Fase | Acción |
|---------|------|--------|
| `.env` | 1 | Agregar `OTTO_MODEL=gpt-4o-mini` |
| `app/Services/AgenteChatService.php` | 1, 3 | Cambiar modelo + mejorar prompts |
| `app/Libraries/OttoTableMap.php` | 2, 3 | Reformatear + agregar vistas + tablas faltantes |
| `app/SQL/create_views_otto.sql` | 2 | Crear — todas las vistas |
| `app/SQL/apply_views_otto.php` | 2 | Crear — script CLI |
| `app/SQL/create_readonly_user.php` | 4 | Crear — script CLI |
| `app/Config/Database.php` | 4 | Agregar grupo `readonly` |
| `app/Controllers/ClienteChatController.php` | 4 | Actualizar con triple capa |
