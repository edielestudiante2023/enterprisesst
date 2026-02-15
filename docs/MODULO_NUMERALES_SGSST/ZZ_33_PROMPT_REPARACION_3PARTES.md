# PROMPT PARA REPARAR MÓDULOS IA DE 3 PARTES (TIPO B)

## Instrucción para Claude

Copia y pega este prompt en una nueva conversación con Claude para reparar un módulo de documento SST de 3 partes (Tipo B, flujo programa_con_pta) que está mal estructurado o incompleto.

---

## PROMPT DE REPARACIÓN TIPO B

```
Necesito reparar un módulo de documento SST de 3 PARTES (Tipo B, flujo programa_con_pta) en el sistema Enterprise SST (CodeIgniter 4 + PHP 8).

Este módulo tiene o debería tener 3 partes:
- Parte 1: Generador de Actividades del Plan de Trabajo Anual (PTA)
- Parte 2: Generador de Indicadores SST
- Parte 3: Documento formal que consume datos de Parte 1 y Parte 2

---

## PASO 1: LEER LA DOCUMENTACIÓN

ANTES DE HACER CUALQUIER CAMBIO, debes leer la documentación específica de módulos de 3 partes. Lee estos archivos EN ORDEN:

1. **PARTE 1 - Generador de Actividades PTA**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_80_PARTE1.md
   - Servicio de actividades (getContextoCliente, getLimiteActividades, getActividadesBase, previewActividades, generarActividades)
   - Tabla tbl_pta_cliente y campo tipo_servicio
   - Límites según estándares (7est=3act, 21est=5act, 60est=8act)
   - Componentes: Service, 6 métodos Controller, 6 rutas, vista con modal

2. **PARTE 2 - Generador de Indicadores SST**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_81_PARTE2.md
   - Servicio de indicadores (verificarActividadesPrevias, previewIndicadores, generarIndicadores)
   - Tabla tbl_indicadores_sst y campo categoria
   - Límites según estándares (7est=2ind, 21est=4ind, 60est=6ind) ← DISTINTOS de actividades
   - OBLIGATORIO: Registrar categoría en IndicadorSSTModel::CATEGORIAS
   - 5 campos Ficha Técnica obligatorios
   - Vinculación: tipo_servicio (Parte1) → categoria (Parte2) → getIndicadoresParaContexto (Parte3)

3. **SweetAlert - Verificación de Datos Pre-Generación**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_90_PARTESWEETALERT.md
   - Endpoint previsualizarDatos: muestra datos que alimentarán la IA
   - getFiltroServicioPTA() y getCategoriaIndicador(): mapeos OBLIGATORIOS para cada tipo
   - Fuentes de datos: PTA + Indicadores + Contexto (las 3 fuentes para Tipo B)
   - Estilos INLINE (Bootstrap NO funciona dentro del DOM de Swal)
   - Se muestra UNA SOLA VEZ por sesión

4. **Sistema de Mensajes Toast**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_91_MENSAJESTOAST.md
   - mostrarToast(tipo, titulo, mensaje, reintentarCallback?)
   - 8 tipos con duraciones específicas
   - modoBatch: suprime toasts individuales durante "Generar Todo" (excepto error)
   - Flujo típico: progress → ia → database

5. **Árboles de Decisión - Rutas de Generación**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_93_ARBOLES_DECISION.md
   - 3 variantes (A: soporte, B: IA, C: normativo)
   - 4 puntos de decisión en orden: determinarTipoCarpetaFases → vista _tipos → Factory::crear → requiereGeneracionIA
   - Carpetas híbridas (A + B)

ADICIONALMENTE, lee la documentación de arquitectura base:

6. **PARTE 3 - Documento Formal**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_90_PARTE3.md
   - Factory pattern, getContextoBase(), DocumentoVersionService

7. **PARTE 4 - Implementación Paso a Paso**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_96_PARTE4.md
   - Plantilla clase documento, script SQL, checklist

---

## PASO 2: ENTENDER LA ARQUITECTURA DE 3 PARTES

┌──────────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ PARTE 1          │     │ PARTE 2          │     │ PARTE 3          │
│ Actividades PTA  │────▶│ Indicadores SST  │────▶│ Documento Final  │
│                  │     │                  │     │                  │
│ tbl_pta_cliente  │     │ tbl_indicadores  │     │ tbl_documentos   │
│ campo:           │     │ _sst             │     │ _sst             │
│ tipo_servicio    │     │ campo: categoria │     │ campo:           │
│                  │     │                  │     │ tipo_documento    │
│ Service.php      │     │ IndicService.php │     │ Handler.php      │
│ 6 métodos ctrl   │     │ 6 métodos ctrl   │     │ Factory + clase  │
│ 6 rutas          │     │ 6 rutas          │     │ getContextoBase()│
│ vista modal      │     │ vista modal      │     │ consume P1 + P2  │
└──────────────────┘     └──────────────────┘     └──────────────────┘
        │                        │                        │
        └────────────────────────┼────────────────────────┘
                                 │
                    CONSTANTES QUE DEBEN COINCIDIR:
                    TIPO_SERVICIO (Parte1 = Parte3)
                    CATEGORIA (Parte2 = Parte3)

### Vinculación entre partes:
- Parte 1 → Parte 2: campo tipo_servicio en tbl_pta_cliente
- Parte 2 → Parte 3: campo categoria en tbl_indicadores_sst + método getIndicadoresParaContexto()
- Parte 3 consume ambas: getContextoBase() consulta PTA filtrado por TIPO_SERVICIO e indicadores filtrados por CATEGORIA

### Regla crítica de bloqueo:
- Parte 2 BLOQUEA acceso si Parte 1 no tiene actividades (verificarActividadesPrevias())
- Parte 3 DEBERÍA generar con datos incompletos pero con advertencia en SweetAlert

---

## PASO 3: DIAGNÓSTICO DE PROBLEMAS (3 PARTES)

### 3.1 Diagnóstico Parte 1 - Actividades PTA

Buscar: app/Services/[NombreDocumento]Service.php

PROBLEMAS COMUNES:
- [ ] No existe el servicio de actividades
- [ ] tipo_servicio no coincide con la constante TIPO_SERVICIO de la clase del documento (Parte 3)
- [ ] Límites incorrectos (usa 2/4/6 de indicadores en vez de 3/5/8 de actividades)
- [ ] getActividadesBase() tiene menos de 8 actividades base
- [ ] Faltan métodos en GeneradorIAController (debe tener 6: vista, preview, generar, eliminar, eliminarTodas, regenerar)
- [ ] Faltan rutas en Routes.php (6 rutas con prefijo generador-ia/ en kebab-case)
- [ ] La vista no tiene modal Bootstrap XL con checkbox + edición inline
- [ ] Cada actividad no tiene los 6 campos: actividad, descripción, meta SMART, responsable, phva, periodicidad

VERIFICAR:
```sql
SELECT tipo_servicio, COUNT(*) FROM tbl_pta_cliente
WHERE id_cliente = [ID] GROUP BY tipo_servicio;
```

### 3.2 Diagnóstico Parte 2 - Indicadores SST

Buscar: app/Services/[NombreDocumento]IndicadoresService.php

PROBLEMAS COMUNES:
- [ ] No existe el servicio de indicadores
- [ ] La categoría NO está registrada en IndicadorSSTModel::CATEGORIAS (indicadores caen en "Otros")
- [ ] La categoría está registrada DESPUÉS de 'otro' (debe estar ANTES)
- [ ] Límites confundidos con los de actividades (indicadores = 2/4/6, NO 3/5/8)
- [ ] Faltan los 5 campos de Ficha Técnica: definicion, interpretacion, origen_datos, cargo_responsable, cargos_conocer_resultado
- [ ] verificarActividadesPrevias() no existe o no bloquea correctamente
- [ ] El preview redirige a otra página en vez de usar modal en la misma vista
- [ ] UX diferente a Parte 1 (debe ser IDÉNTICO: modal XL, checkbox, edición inline, panel IA colapsable)

VERIFICAR:
```sql
SELECT categoria, COUNT(*) FROM tbl_indicadores_sst
WHERE id_cliente = [ID] GROUP BY categoria;
```
```php
// Verificar en IndicadorSSTModel::CATEGORIAS
grep -n "CATEGORIAS" app/Models/IndicadorSSTModel.php
```

### 3.3 Diagnóstico Parte 3 - Documento + SweetAlert + Toast

Buscar: app/Libraries/DocumentosSSTTypes/[NombreClase].php

PROBLEMAS DE LA CLASE:
- [ ] No existe la clase o no está en Factory
- [ ] La clase no sobrescribe getContextoBase() (OBLIGATORIO para Tipo B)
- [ ] getContextoBase() no consulta tbl_pta_cliente filtrado por TIPO_SERVICIO
- [ ] getContextoBase() no consulta tbl_indicadores_sst filtrado por CATEGORIA
- [ ] TIPO_SERVICIO o CATEGORIA no coinciden con los valores de Parte 1 y Parte 2

PROBLEMAS DEL SWEETALERT:
- [ ] Falta entrada en getFiltroServicioPTA() para este tipo_documento
- [ ] Falta entrada en getCategoriaIndicador() para este tipo_documento
- [ ] SweetAlert no muestra las 3 fuentes de datos (PTA + indicadores + contexto)
- [ ] Usa clases Bootstrap dentro del DOM de Swal (deben ser estilos INLINE)
- [ ] Discrepancias BD: usa nivel_riesgo en vez de nivel_riesgo_arl, numero_trabajadores en vez de total_trabajadores
- [ ] No tiene cache de datos (datosPreviewCache) ni control de una-sola-vez (verificacionConfirmada)

PROBLEMAS DE TOAST:
- [ ] Flujo incompleto: falta progress, ia o database toast
- [ ] modoBatch no suprime toasts individuales durante "Generar Todo"
- [ ] Toasts de error se suprimen en modoBatch (NUNCA deben suprimirse)
- [ ] Toast BD no muestra tablas consultadas correctamente

PROBLEMAS DE ÁRBOL DE DECISIÓN:
- [ ] determinarTipoCarpetaFases() no mapea la carpeta al tipo correcto
- [ ] Vista _tipos no existe o no tiene botón "Crear con IA"
- [ ] Factory no tiene el tipo registrado
- [ ] requiereGeneracionIA() retorna false (debe retornar true para Tipo B)

---

## PASO 4: INFORMACIÓN DEL MÓDULO A REPARAR

[COMPLETAR CON LA INFORMACIÓN DEL MÓDULO]

- URL de la carpeta:
- Nombre del documento:
- Estándar de la Resolución 0312/2019:
- tipo_documento actual (snake_case):
- TIPO_SERVICIO esperado:
- CATEGORIA esperada:
- ¿Qué partes tiene implementadas?:
  - [ ] Parte 1 (Actividades PTA) - Service + Controller + Rutas + Vista
  - [ ] Parte 2 (Indicadores SST) - Service + Controller + Rutas + Vista + CATEGORIAS
  - [ ] Parte 3 (Documento) - Clase + Factory + getContextoBase() + SweetAlert + Toast

PROBLEMAS IDENTIFICADOS:
[Lista los problemas específicos que detectaste]

---

## PASO 5: PLAN DE REPARACIÓN

Reparar EN ESTE ORDEN (las partes dependen de la anterior):

### Fase 1: Reparar/Crear Parte 1 (Actividades PTA)

1. Crear o corregir app/Services/[Nombre]Service.php
   - TIPO_SERVICIO = '[valor_correcto]'
   - getContextoCliente(), getLimiteActividades(), getActividadesBase() (mínimo 8)
   - previewActividades(), personalizarConIA(), generarActividades()
   - getActividadesExistentes(), eliminarActividad(), eliminarTodasActividades(), regenerarActividad()

2. Verificar/agregar 6 métodos en GeneradorIAController.php:
   - vista[Nombre], preview[Nombre], generar[Nombre], eliminar[Nombre], eliminarTodas[Nombre], regenerar[Nombre]

3. Verificar/agregar 6 rutas en Routes.php:
   - GET generador-ia/[nombre-kebab]/{id}
   - POST generador-ia/[nombre-kebab]/preview/{id}
   - POST generador-ia/[nombre-kebab]/generar/{id}
   - DELETE generador-ia/[nombre-kebab]/eliminar/{id}/{idActividad}
   - DELETE generador-ia/[nombre-kebab]/eliminar-todas/{id}
   - POST generador-ia/[nombre-kebab]/regenerar/{id}/{idActividad}

4. Crear/corregir vista app/Views/generador_ia/[nombre].php
   - Modal Bootstrap XL con checkbox por actividad
   - Edición inline de cada campo
   - Panel IA colapsable para personalización
   - Botones: "Seleccionar Todo", "Guardar Seleccionadas", "Regenerar Individual"

### Fase 2: Reparar/Crear Parte 2 (Indicadores SST)

5. PRIMERO: Registrar categoría en IndicadorSSTModel::CATEGORIAS ANTES de 'otro'

6. Crear o corregir app/Services/[Nombre]IndicadoresService.php
   - CATEGORIA = '[valor_correcto]' (debe coincidir con Parte 3)
   - verificarActividadesPrevias() BLOQUEA si Parte 1 no existe
   - Límites: 7est=2ind, 21est=4ind, 60est=6ind (NO confundir con actividades)
   - INDICADORES_BASE con los 5 campos de Ficha Técnica (con fallback ?? null)

7. Verificar/agregar 6 métodos + 6 rutas (igual que Parte 1 pero para indicadores)

8. Crear/corregir vista app/Views/generador_ia/indicadores_[nombre].php
   - UX IDÉNTICO a Parte 1 (modal XL, checkbox, edición inline, panel IA)
   - NUNCA redirigir a otra vista para preview

### Fase 3: Reparar Parte 3 (Documento + SweetAlert + Toast)

9. Crear o corregir clase en app/Libraries/DocumentosSSTTypes/[Nombre].php
   - Extender AbstractDocumentoSST + implementar DocumentoSSTInterface
   - TIPO_SERVICIO = mismo valor que Parte 1
   - CATEGORIA = mismo valor que Parte 2
   - getContextoBase() SOBRESCRITO: consulta PTA + Indicadores + Contexto

10. Registrar en DocumentoSSTFactory.php

11. Agregar mapeos en DocumentosSSTController::previsualizarDatos():
    - Entrada en getFiltroServicioPTA() para este tipo_documento
    - Entrada en getCategoriaIndicador() para este tipo_documento

12. Verificar SweetAlert:
    - Muestra las 3 fuentes: actividades PTA + indicadores + contexto cliente
    - Estilos INLINE (no Bootstrap classes)
    - Cache datosPreviewCache + control verificacionConfirmada
    - Nombres BD correctos: nivel_riesgo_arl, total_trabajadores

13. Verificar Toast:
    - Flujo: progress(aparece) → [espera IA] → progress(cierra) → ia(aparece) → 500ms → database(aparece)
    - modoBatch = true suprime progress/ia/database (NUNCA error)
    - Toast BD muestra tablas consultadas con conteo

14. Verificar árbol de decisión:
    - determinarTipoCarpetaFases() mapea la carpeta correctamente
    - Vista _tipos existe con botón "Crear con IA"
    - requiereGeneracionIA() retorna true

### Fase 4: Vista Web + Acciones + SQL

15. Ruta Vista Web en Routes.php:
    /documentos-sst/(:num)/[nombre-kebab]/(:num) → DocumentosSSTController::[nombreMetodo]

16. Método en DocumentosSSTController.php (copiar de otro existente)

17. Entradas en acciones_documento.php:
    - $mapaRutas: '[tipo_documento]' => '[nombre-kebab]/' . $docSST['anio']
    - elseif para $urlEditar

18. Vista _tipos con botón SIEMPRE visible:
    - Sin documento: "Crear con IA [año]" (btn-success)
    - Con documento: "Nueva versión [año]" (btn-outline-success)

19. Script SQL en app/SQL/ y EJECUTARLO

---

## REGLAS DE REPARACIÓN TIPO B

1. **Orden de constantes**: TIPO_SERVICIO y CATEGORIA deben ser IDÉNTICOS en las 3 partes
2. **NO romper datos existentes**: Si ya hay actividades/indicadores en BD, mantener compatibilidad
3. **Límites distintos**: Actividades = 3/5/8, Indicadores = 2/4/6 (NUNCA confundir)
4. **Categoría antes de 'otro'**: En IndicadorSSTModel::CATEGORIAS, la nueva categoría va ANTES del catch-all
5. **Bloqueo secuencial**: Parte 2 bloquea sin Parte 1, pero Parte 3 advierte sin Parte 2
6. **UX consistente entre partes**: Modal XL + checkbox + edición inline + panel IA en AMBAS partes (1 y 2)
7. **SweetAlert INLINE**: Bootstrap classes NO funcionan dentro del DOM de Swal
8. **Toast error NUNCA se suprime**: Ni en modoBatch ni en ningún contexto
9. **URLs**: Generación snake_case, vista previa kebab-case, NUNCA mezclar

---

## CHECKLIST FINAL DE REPARACIÓN TIPO B

### Parte 1 - Actividades PTA
- [ ] Service existe con TIPO_SERVICIO correcto
- [ ] getActividadesBase() tiene mínimo 8 actividades
- [ ] Límites: 3/5/8 según estándares
- [ ] 6 métodos en GeneradorIAController
- [ ] 6 rutas en Routes.php (kebab-case, prefijo generador-ia/)
- [ ] Vista con modal XL + checkbox + edición inline + panel IA
- [ ] Cada actividad tiene 6 campos: actividad, descripción, meta SMART, responsable, phva, periodicidad

### Parte 2 - Indicadores SST
- [ ] Categoría registrada en IndicadorSSTModel::CATEGORIAS ANTES de 'otro'
- [ ] Service existe con CATEGORIA correcta
- [ ] verificarActividadesPrevias() bloquea si Parte 1 vacía
- [ ] Límites: 2/4/6 según estándares (NO 3/5/8)
- [ ] 5 campos Ficha Técnica en INDICADORES_BASE con fallback ?? null
- [ ] 6 métodos en GeneradorIAController
- [ ] 6 rutas en Routes.php
- [ ] Vista con modal XL IDÉNTICO a Parte 1 (NUNCA redirigir a otra página)

### Parte 3 - Documento + SweetAlert + Toast
- [ ] Clase existe y extiende AbstractDocumentoSST
- [ ] Registrada en DocumentoSSTFactory
- [ ] TIPO_SERVICIO = mismo valor que Parte 1
- [ ] CATEGORIA = mismo valor que Parte 2
- [ ] getContextoBase() SOBRESCRITO: consulta PTA + Indicadores
- [ ] Entrada en getFiltroServicioPTA() para este tipo
- [ ] Entrada en getCategoriaIndicador() para este tipo
- [ ] SweetAlert muestra 3 fuentes con estilos INLINE
- [ ] Toast flujo completo: progress → ia → database
- [ ] modoBatch suprime todo EXCEPTO error
- [ ] determinarTipoCarpetaFases() mapea correctamente
- [ ] requiereGeneracionIA() retorna true

### Vista Web + Acciones
- [ ] Ruta en Routes.php (kebab-case)
- [ ] Método en DocumentosSSTController
- [ ] Entrada en $mapaRutas de acciones_documento.php
- [ ] Entrada en elseif de $urlEditar
- [ ] Botón siempre visible en vista _tipos
- [ ] Script SQL ejecutado
- [ ] Contenido inicial usa DocumentoConfigService (NO hardcodeo)

---

## ERRORES RECURRENTES Y FIXES RÁPIDOS

| Error | Causa | Fix |
|-------|-------|-----|
| Indicadores caen en "Otros" | Categoría no registrada en CATEGORIAS | Agregar en IndicadorSSTModel ANTES de 'otro' |
| Parte 2 vacía o bloqueada | No hay actividades de Parte 1 | Completar Parte 1 primero |
| SweetAlert muestra datos vacíos | Falta entrada en getFiltroServicioPTA() o getCategoriaIndicador() | Agregar mapeo para el nuevo tipo |
| SweetAlert muestra PTA de otros módulos | getFiltroServicioPTA() retorna [] (sin filtro) | Agregar filtro específico para este tipo_servicio |
| Campos BD no encontrados | Discrepancia nombres | nivel_riesgo→nivel_riesgo_arl, numero_trabajadores→total_trabajadores |
| Límites confundidos | Usar límites de actividades para indicadores | Actividades=3/5/8, Indicadores=2/4/6 |
| Toast no aparece en batch | modoBatch=true suprime toasts | Solo error NO se suprime; progress/ia/database sí |
| Bootstrap classes en SweetAlert | DOM separado de Swal | Usar estilos INLINE |
| Preview redirige a otra página | Anti-patrón Parte 2 | SIEMPRE modal en misma vista (igual que Parte 1) |
| "[Sección no definida]" | Falta clase PHP o no está en Factory | Crear clase + registrar en Factory |
| getContextoBase() no trae PTA | No sobrescribe método base | OBLIGATORIO sobrescribir en Tipo B |

---

## DIAGRAMA DE DECISIÓN: ¿DÓNDE ESTÁ EL PROBLEMA?

```
El módulo de 3 partes no funciona?
│
├── Parte 1 (Actividades) falla?
│   ├── No se ve la vista → Falta ruta o método en Controller
│   ├── Preview vacío → getActividadesBase() incompleto o límite = 0
│   ├── Error al guardar → Falta tipo_servicio o numeral_plandetrabajo
│   └── IA no personaliza → personalizarConIA() no recibe contexto
│
├── Parte 2 (Indicadores) falla?
│   ├── Bloqueado "Complete Parte 1" → verificarActividadesPrevias() correcto, completar Parte 1
│   ├── Indicadores en "Otros" → Categoría no en IndicadorSSTModel::CATEGORIAS
│   ├── Campos Ficha Técnica vacíos → Faltan en INDICADORES_BASE
│   └── Límite incorrecto → Confusión 3/5/8 (act) vs 2/4/6 (ind)
│
├── Parte 3 (Documento) falla?
│   ├── "[Sección no definida]" → Clase no existe o no está en Factory
│   ├── No trae datos de PTA → getContextoBase() no sobrescrito
│   ├── No trae indicadores → CATEGORIA no coincide con Parte 2
│   └── Contenido vacío → Script SQL no ejecutado
│
├── SweetAlert falla?
│   ├── Datos vacíos → Falta en getFiltroServicioPTA() o getCategoriaIndicador()
│   ├── Datos de OTROS módulos → Filtro retorna [] (sin WHERE)
│   ├── Se queda colgado → Clase PHP falta o error en endpoint
│   └── Estilos rotos → Usa Bootstrap classes (cambiar a INLINE)
│
└── Toast falla?
    ├── No aparece → mostrarToast() no llamado o contenedor falta
    ├── No aparece en batch → modoBatch suprime (correcto, excepto error)
    └── Toast BD vacío → Tablas no consultadas o conteo = 0
```

---

## EJEMPLO DE USO

```
Necesito reparar un módulo de documento SST de 3 PARTES...

[... resto del prompt anterior ...]

## PASO 4: INFORMACIÓN DEL MÓDULO A REPARAR

- URL de la carpeta: /documentacion/carpeta/XXX
- Nombre del documento: Programa de Capacitación en SST
- Estándar de la Resolución 0312/2019: 3.1.7
- tipo_documento actual: programa_capacitacion
- TIPO_SERVICIO esperado: capacitacion_sst
- CATEGORIA esperada: capacitacion
- Partes implementadas:
  - [X] Parte 1 (Actividades PTA) - Service existe pero límites incorrectos
  - [ ] Parte 2 (Indicadores SST) - No existe
  - [X] Parte 3 (Documento) - Existe pero getContextoBase() no consume Parte 1

PROBLEMAS IDENTIFICADOS:
1. Parte 2 nunca fue implementada (no hay servicio ni categoría en IndicadorSSTModel)
2. getContextoBase() en la clase no sobrescribe el método base
3. SweetAlert muestra PTA de todos los módulos (falta filtro en getFiltroServicioPTA)
4. Límites de actividades usan 2/4/6 en vez de 3/5/8
```

---

## DIFERENCIAS CON PROMPT DE REPARACIÓN TIPO A

| Aspecto | Tipo A (ZZ_22) | Tipo B (este prompt) |
|---------|----------------|----------------------|
| Partes | Solo Parte 3 | Parte 1 + Parte 2 + Parte 3 |
| Fuentes de datos | Solo contexto cliente | PTA + Indicadores + Contexto |
| getContextoBase() | NO se sobrescribe | OBLIGATORIO sobrescribir |
| TIPO_SERVICIO | null | Valor que vincula las 3 partes |
| CATEGORIA | null | Valor que vincula Parte 2 y 3 |
| SweetAlert | Solo contexto | 3 fuentes (PTA + ind + contexto) |
| getFiltroServicioPTA | No necesita entrada | OBLIGATORIO agregar entrada |
| getCategoriaIndicador | No necesita entrada | OBLIGATORIO agregar entrada |
| Services | No tiene | 2 services (actividades + indicadores) |
| Métodos Controller | 0 adicionales | 12 adicionales (6+6) |
| Rutas | 0 adicionales | 12 adicionales (6+6) |
| Vistas generador_ia | 0 | 2 (actividades + indicadores) |

---

## CONTACTO

Documentación creada para Enterprise SST
Última actualización: Febrero 2026
