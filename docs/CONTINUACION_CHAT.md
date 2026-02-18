# Continuacion del Chat - 2026-02-17

## Estado: SUB-CARPETA 2.5.1.1 DOCUMENTOS EXTERNOS - IMPLEMENTADA Y NIVELADA

### Que se hizo
Crear la sub-carpeta 2.5.1.1 "Listado Maestro de Documentos Externos" dentro del estandar 2.5.1 del SG-SST, con funcionalidad completa de adjuntar soportes (archivos/enlaces).

### Archivos creados (3)
1. **`app/SQL/agregar_subcarpeta_documentos_externos.sql`** - Script migracion: INSERT sub-carpetas 2.5.1.1 para clientes existentes
2. **`app/Views/documentacion/_tipos/documentos_externos.php`** - Vista tipo con modal adjuntar (archivo/enlace), campo origen/entidad emisora, tabla soportes, color info/azul
3. **`ejecutar_sql_documentos_externos.php`** + **`actualizar_sp_produccion.php`** - Scripts CLI para nivelar produccion (ejecutados exitosamente)

### Archivos modificados (4)
1. **`app/Controllers/DocumentacionController.php`**:
   - Punto A: `determinarTipoCarpetaFases()` → retorna `'documentos_externos'` si codigo='2.5.1.1' (ANTES de 2.5.1)
   - Punto B: `in_array` de `$tipoCarpetaFases` incluye `'documentos_externos'`
   - Punto C: `elseif ($tipoCarpetaFases === 'documentos_externos')` filtra `tipo_documento = 'soporte_documento_externo'`
   - Punto D: Bloque soportes adicionales consulta `tbl_documentos_sst` con `tipo_documento = 'soporte_documento_externo'`

2. **`app/Controllers/DocumentosSSTController.php`**:
   - Metodo `adjuntarSoporteDocumentoExterno()` al final del archivo (~linea 6787)
   - Guarda con `tipo_documento = 'soporte_documento_externo'`, acepta campo `origen`

3. **`app/Config/Routes.php`**:
   - `$routes->post('documentos-sst/adjuntar-soporte-documento-externo', ...)`

4. **`app/Views/documentacion/_components/tabla_documentos_sst.php`**:
   - `'documentos_externos'` agregado al array `$tiposConTabla`

### SP actualizado
- **`app/SQL/sp/sp_04_generar_carpetas_por_nivel.sql`** - Ya contenia 2.5.1.1 (lineas 154-156)
- SP en PRODUCCION actualizado via `actualizar_sp_produccion.php`

### BD nivelada
| Ambiente | Carpetas 2.5.1 | Sub-carpetas 2.5.1.1 creadas |
|----------|----------------|------------------------------|
| LOCAL | 9 | 9 |
| PRODUCCION | 4 | 4 |

### Que falta (posible siguiente paso)
- Probar en navegador: navegar a carpeta 2.5.1 de un cliente y verificar que aparece 2.5.1.1
- Probar adjuntar un documento externo (archivo + enlace)
- Commit y deploy a produccion

### Git flow
`git add .` → `git commit` → `git checkout main` → `git merge cycloid` → `git push origin main` → `git checkout cycloid`
