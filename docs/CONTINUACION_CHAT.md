# TAREA: Importar miembros de Comités Elecciones al módulo de Actas

**Fecha:** 2026-04-01
**Estado:** Diseño aprobado, pendiente implementación

## Contexto del problema

El módulo de **Comités Elecciones** (`/comites-elecciones/{id_cliente}`) ya tiene registrados los miembros elegidos y designados para COPASST/COCOLAB en la tabla `tbl_candidatos_comite`. Pero el módulo de **Actas** (`/actas/{id_cliente}`) requiere esos mismos miembros en `tbl_comite_miembros` + cuentas de usuario en `tbl_usuarios`. Actualmente el consultor tiene que volver a digitar todo manualmente.

## Qué se debe implementar

### Funcionalidad
1. Un botón **"Importar miembros desde Comités"** en la vista del comité dentro del módulo de Actas
2. Al hacer click, SweetAlert con **doble confirmación aritmética** (patrón existente: "¿Cuánto es 7+3?")
3. Al confirmar:
   - Leer candidatos con estado `elegido` o `designado` de `tbl_candidatos_comite` del proceso electoral del cliente
   - Insertar en `tbl_comite_miembros` (si no existen por `numero_documento`)
   - Crear usuario en `tbl_usuarios` con `tipo_usuario='miembro'` (si no existe por `email`)
   - Generar contraseña aleatoria segura
   - Enviar credenciales por email via SendGrid
4. Respuesta JSON con resumen: X miembros importados, Y usuarios creados, Z ya existían

### Mapeo de campos (tbl_candidatos_comite → tbl_comite_miembros)
```
candidato.nombres + ' ' + candidato.apellidos  → miembro.nombre_completo
candidato.documento_identidad                   → miembro.numero_documento
candidato.tipo_documento                        → miembro.tipo_documento
candidato.cargo                                 → miembro.cargo
candidato.area                                  → miembro.area_dependencia
candidato.email                                 → miembro.email
candidato.telefono                              → miembro.telefono
candidato.representacion                        → miembro.representacion (empleador/trabajador)
candidato.tipo_plaza                            → miembro.tipo_miembro (principal/suplente)
'miembro'                                       → miembro.rol_comite (default)
'activo'                                        → miembro.estado
NOW()                                           → miembro.fecha_ingreso
```

### Mapeo usuarios (tbl_candidatos_comite → tbl_usuarios)
```
candidato.email                                 → usuario.email (UNIQUE - es el login)
bcrypt(random_password)                         → usuario.password
candidato.nombres + ' ' + candidato.apellidos   → usuario.nombre_completo
'miembro'                                       → usuario.tipo_usuario
id_cliente                                      → usuario.id_entidad
'activo'                                        → usuario.estado
```

## Tablas involucradas

### Origen
- **`tbl_candidatos_comite`** — candidatos del proceso electoral
  - Filtrar por: `estado IN ('elegido', 'designado')`
  - Campos clave: `id_candidato`, `id_proceso`, `id_cliente`, `nombres`, `apellidos`, `documento_identidad`, `tipo_documento`, `cargo`, `area`, `email`, `telefono`, `representacion` (empleador/trabajador), `tipo_plaza` (principal/suplente)

- **`tbl_procesos_electorales`** — procesos de elección
  - Enlaza `id_proceso` → `id_comite` (cuando se completa el proceso)
  - Campo `tipo_comite` (COPASST, COCOLAB, BRIGADA, VIGIA)

### Destino
- **`tbl_comite_miembros`** — miembros del comité para actas
  - PK: `id_miembro`
  - FK: `id_comite`, `id_cliente`
  - Unique por: `id_comite` + `numero_documento`

- **`tbl_usuarios`** — cuentas de login
  - PK: `id_usuario`
  - Unique: `email`
  - `tipo_usuario`: 'miembro'
  - `id_entidad`: id_cliente

## Archivos a modificar

### 1. `app/Controllers/ActasController.php`
Agregar método `importarMiembrosComite()`:
- Recibe POST con `id_comite` e `id_cliente`
- Busca procesos electorales completados del cliente que tengan ese `id_comite`
- Lee candidatos elegidos/designados
- Para cada candidato:
  - Verificar si ya existe en `tbl_comite_miembros` por `numero_documento` + `id_comite`
  - Si no existe: INSERT
  - Verificar si ya existe usuario por `email` en `tbl_usuarios`
  - Si no existe: CREATE usuario + enviar credenciales
- Retornar JSON con resumen

**REFERENCIA:** El método `guardarMiembro()` en el mismo controller ya tiene el patrón de creación de usuario + envío de email. Reusar esa lógica.

### 2. `app/Views/actas/comite.php` (o la vista del comité)
Agregar:
- Botón "Importar miembros desde Comités" (visible solo si hay procesos completados)
- SweetAlert con doble confirmación aritmética
- Fetch AJAX al endpoint
- Toast de resultado

### 3. `app/Config/Routes.php`
Agregar ruta:
```php
$routes->post('/actas/importar-miembros-comite', 'ActasController::importarMiembrosComite');
```

## Patrón de doble confirmación aritmética (referencia)

Ya existe en el proyecto. Buscar en `app/Views/consultant/list_pta_cliente_nueva.php` el patrón de SweetAlert con operación aritmética:
```javascript
Swal.fire({
    title: 'Confirmar importación',
    html: `Se importarán X miembros. ¿Cuánto es ${a} + ${b}?`,
    input: 'number',
    preConfirm: (value) => {
        if (parseInt(value) !== a + b) {
            Swal.showValidationMessage('Respuesta incorrecta');
        }
    }
});
```

## Patrón de envío de credenciales por email (referencia)

En `ActasController::guardarMiembro()` ya existe la lógica:
1. Genera password aleatorio
2. Inserta en `tbl_usuarios`
3. Envía via SendGrid con plantilla HTML

## Patrón de envío de email SendGrid (referencia)

```php
$apiKey = getenv('SENDGRID_API_KEY');
$email = new \SendGrid\Mail\Mail();
$email->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'EnterpriseSST - Cycloid Talent');
$email->addTo($destinatario, $nombre);
$email->setSubject($asunto);
$email->addContent('text/html', $htmlBody);
$sendgrid = new \SendGrid($apiKey);
$response = $sendgrid->send($email);
```

## Validaciones importantes

1. **No duplicar miembros:** Verificar `numero_documento` + `id_comite` antes de insertar
2. **No duplicar usuarios:** Verificar `email` antes de crear en `tbl_usuarios`
3. **Email obligatorio:** Si un candidato no tiene email, importarlo como miembro pero NO crear usuario (reportar en el resumen)
4. **Proceso completado:** Solo importar de procesos con `estado = 'completado'`
5. **Un comité por tipo:** Un cliente puede tener COPASST y COCOLAB — el botón debe saber de cuál importar

## Deploy

Flujo estándar:
```bash
git add . && git commit -m "feat: importar miembros comites a actas"
git checkout main && git merge cycloid --squash && git add . && git commit
git push origin main && git checkout cycloid
ssh root@66.29.154.174 "cd /www/wwwroot/dashboard/enterprisesst && git pull origin main"
```
