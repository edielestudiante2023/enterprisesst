# Guia de Contribucion — Enterprise SST

## Flujo de ramas

```
main          <- Produccion. Solo codigo validado y estable.
develop       <- Integracion. Cambios se unen aqui antes de ir a main.
feature/xxx   <- Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    <- Correcciones urgentes. Se crean desde main.
```

### Crear una feature

```bash
git checkout develop
git pull origin develop
git checkout -b feature/nombre-descriptivo
# ... trabajar ...
git push origin feature/nombre-descriptivo
# Crear PR hacia develop en Gitea
```

### Crear un hotfix

```bash
git checkout main
git pull origin main
git checkout -b hotfix/descripcion-del-bug
# ... corregir ...
git push origin hotfix/descripcion-del-bug
# Crear PR hacia main Y hacia develop
```

## Convencion de commits

Usar prefijos para categorizar los cambios:

| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Correccion de bug |
| `docs:` | Documentacion |
| `refactor:` | Refactorizacion sin cambio funcional |
| `chore:` | Tareas de mantenimiento (deps, config) |
| `test:` | Tests |
| `style:` | Formato, espacios, punto y coma (sin cambio logico) |

Ejemplo: `feat: agregar generacion IA de matriz de comunicacion`

## Convencion de nombres de ramas

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Feature | `feature/modulo-descripcion` | `feature/documentos-matriz-legal` |
| Hotfix | `hotfix/bug-descripcion` | `hotfix/fix-sendgrid-timeout` |
| Release | `release/vX.Y.Z` | `release/v2.1.0` |

## Reglas

1. **No push directo a `main`** — siempre via PR
2. **No push directo a `develop`** — siempre via PR desde feature/
3. **No credenciales en el codigo** — usar `.env` y `getenv()`/`env()`
4. **No archivos temporales** — no commitear `tmp_*.php`, `*.stackdump`, CSVs de prueba
5. **No operaciones destructivas en produccion** — no DELETE sin WHERE, no DROP sin respaldo

## Proceso de revision

1. Crear PR con descripcion clara de los cambios
2. El pipeline CI/CD ejecuta automaticamente:
   - Validacion de sintaxis PHP (`php -l`)
   - Escaneo de vulnerabilidades (Trivy)
   - Analisis estatico de seguridad (Semgrep)
   - Busqueda de credenciales hardcodeadas
3. Si todos los checks pasan, solicitar revision
4. Merge a la rama destino

## Seguridad

- **NUNCA** commitear `.env` con credenciales reales
- **NUNCA** hardcodear API keys, passwords o tokens en el codigo
- Usar `.env.example` como referencia (sin valores reales)
- Si encuentras una credencial expuesta, reportar inmediatamente
