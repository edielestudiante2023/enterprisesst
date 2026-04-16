# BORRADO OMEGA - Limpieza del cliente de pruebas para videos TikTok

## Contexto

- **Cliente:** `EMPRESA OMEGA`
- **ID:** `19`
- **NIT:** `123456`
- **Uso:** Cliente de pruebas dedicado a grabar videos de TikTok. Se re-llena y re-limpia periodicamente.
- **Script:** [cli_limpiar_cliente_omega.php](cli_limpiar_cliente_omega.php)
- **Doc del cliente real (cliente 11):** el mismo procedimiento se aplico primero al cliente 11 (CYCLOID TALENT SAS) como caso real; OMEGA es la version reutilizable.

## Que hace el script

Deja al cliente **virgen** de toda data operativa/documental, **preservando** solo las tablas que contienen informacion maestra del cliente (contexto, responsables, contrato). Asi no hay que re-crear el cliente ni re-cargar su contexto SST antes de cada video.

### TABLAS QUE SE BORRAN

Data documental:
- `tbl_documentos_sst`, `tbl_doc_versiones_sst`, `tbl_doc_carpetas`, `tbl_cliente_estandares`
- `tbl_doc_firma_solicitudes`, `tbl_doc_firma_evidencias`, `tbl_doc_firma_audit_log`

Data operativa SG-SST:
- `tbl_pta_cliente`, `tbl_cronog_capacitacion`, `tbl_indicadores_sst`
- `tbl_reporte`, `tbl_client_kpi`, `tbl_pendientes`
- `tbl_presupuesto_sst`, `tbl_presupuesto_items`, `tbl_presupuesto_detalle`
- `tbl_vigias`

Actas y COPASST:
- `tbl_actas`, `tbl_actas_notificaciones`, `tbl_actas_tokens`, `tbl_acta_compromisos`, `tbl_acta_asistentes`
- `tbl_acta_visita`, `tbl_acta_visita_integrantes`, `tbl_acta_visita_temas`, `tbl_acta_visita_fotos`, `tbl_acta_visita_pta`
- `tbl_comites`, `tbl_comite_miembros`, `tbl_miembros_comite`

Procesos electorales:
- `tbl_procesos_electorales`, `tbl_candidatos_comite`, `tbl_votos_comite`, `tbl_votantes_proceso`

### TABLAS QUE SE PRESERVAN

- `tbl_cliente` (legacy)
- `tbl_clientes` (extendida)
- `tbl_cliente_contexto_sst`
- `tbl_cliente_contexto_historial`
- `tbl_cliente_responsables_sst`
- `tbl_contratos`

## Proteccion de seguridad

El script **aborta** si el `nombre_cliente` del ID 19 no contiene "OMEGA". Esto evita limpiar por error un cliente real si alguien cambiara el ID en el codigo.

## Uso

### LOCAL
```bash
cd c:/xampp/htdocs/enterprisesst
C:/xampp/php/php.exe cli_limpiar_cliente_omega.php local --dry-run
C:/xampp/php/php.exe cli_limpiar_cliente_omega.php local --apply
```
> Nota: LOCAL normalmente no tiene cliente 19. El script detecta el caso y sale sin hacer nada.

### PRODUCCION
```bash
cd c:/xampp/htdocs/enterprisesst
C:/xampp/php/php.exe cli_limpiar_cliente_omega.php prod --dry-run
C:/xampp/php/php.exe cli_limpiar_cliente_omega.php prod --apply
```

### Flujo recomendado (antes de grabar un video)

1. `prod --dry-run` -> ver que conteos salen (sanity check)
2. `prod --apply`   -> borrar en transaccion, commit en un solo paso
3. Ir a [/documentacion/19](https://dashboard.cycloidtalent.com/documentacion/19) y re-inicializar las carpetas
4. Crear/ajustar lo que necesites para el video (PTA, indicadores, actas, etc.)
5. Grabar
6. Al terminar, volver a correr el script para dejar OMEGA limpio para la proxima

## Verificacion post-limpieza

El script imprime al final el conteo de las 6 tablas preservadas. Tras `--apply`, deberias ver:

```
tbl_cliente                              : 1 filas
tbl_clientes                             : 1 filas
tbl_cliente_contexto_sst                 : 0 o 1 filas (segun hayas cargado contexto)
tbl_cliente_contexto_historial           : N filas (no se tocan)
tbl_cliente_responsables_sst             : N filas (no se tocan)
tbl_contratos                            : 0 o 1 filas (segun hayas firmado contrato)
```

Y todas las demas tablas del cliente 19 deben quedar en 0.

## Historial

- **2026-04-14:** Primera ejecucion. 12 filas eliminadas en PROD (1 acta_visita + 2 integrantes + 2 temas + 1 documento + 1 version + 3 indicadores + 1 reporte + 1 pendiente).

## Si aparece data nueva que el script no borra

Si en algun momento el cliente OMEGA queda con data residual despues de correr el script, significa que existe una tabla nueva no contemplada. Pasos:

1. Correr `cli_descubrir_tablas_cliente.php prod 19` para ver que tablas siguen con filas
2. Si son hijas sin `id_cliente` directo, usar `cli_inspect_hijas_cliente11.php` como referencia para descubrir la relacion
3. Agregar la tabla al array `$plan` o `$padres` en [cli_limpiar_cliente_omega.php](cli_limpiar_cliente_omega.php)
4. Actualizar este archivo con la nueva tabla en la seccion "TABLAS QUE SE BORRAN"
