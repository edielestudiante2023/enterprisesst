# Programa de Inspecciones a Instalaciones, Maquinaria o Equipos (4.2.4)

## Identificadores

| Concepto | Valor |
|----------|-------|
| `tipo_documento` | `programa_inspecciones` |
| `tipo_servicio` | `Programa de Inspecciones` |
| `categoria` | `inspecciones` |
| Clase PHP | `ProgramaInspecciones` |
| `tipoCarpetaFases` | `programa_inspecciones` |
| Slug URL (kebab) | `programa-inspecciones` |
| Estandar | `4.2.4` |
| Flujo | `programa_con_pta` |

## Normatividad Aplicable

- **Resolucion 0312/2019** - Estandar 4.2.4: Realizacion de inspecciones a instalaciones, maquinaria o equipos con participacion del COPASST
- **Decreto 1072/2015** - Art. 2.2.4.6.12 numeral 9: Inspecciones sistematicas
- **Resolucion 2400/1979** - Condiciones de instalaciones locativas
- **NTC 4114** - Inspecciones planeadas de seguridad

## Firmantes

| Tipo | Rol Display | Orden | Licencia |
|------|-------------|-------|----------|
| `responsable_sst` | Elaboro | 1 | Si |
| `representante_legal` | Aprobo | 2 | No |

---

## PARTE 1: Actividades para el Plan de Trabajo Anual

12 actividades distribuidas por mes, ciclo PHVA, con participacion obligatoria del COPASST.

| # | Mes | Actividad | Responsable | PHVA | Numeral |
|---|-----|-----------|-------------|------|---------|
| 1 | Ene | Elaboracion del plan anual de inspecciones (tipos, frecuencia, areas, responsables, formatos) | Responsable SST | PLANEAR | 4.2.4 |
| 2 | Feb | Capacitacion al COPASST/Vigia SST en metodologia de inspeccion y uso de listas de verificacion | Responsable SST / ARL | PLANEAR | 4.2.4 |
| 3 | Mar | Inspeccion de instalaciones generales (locativas, electricas, sanitarias, vias de circulacion) con COPASST | Responsable SST / COPASST | HACER | 4.2.4 |
| 4 | Abr | Inspeccion de maquinaria y equipos (guardas de seguridad, dispositivos de parada, mantenimiento preventivo) | Responsable SST / COPASST | HACER | 4.2.4 |
| 5 | May | Inspeccion de equipos de emergencia (extintores, camillas, botiquines, senalizacion, alarmas, rutas de evacuacion) | Responsable SST / COPASST / Brigada | HACER | 4.2.4 |
| 6 | Jun | Inspeccion de elementos de proteccion personal (inventario, estado, registros de entrega, uso correcto) | Responsable SST / COPASST | HACER | 4.2.4 |
| 7 | Jul | Inspeccion de areas de almacenamiento, orden y aseo (sustancias quimicas, materiales, residuos) | Responsable SST / COPASST | HACER | 4.2.4 |
| 8 | Ago | Seguimiento a acciones correctivas y preventivas derivadas de inspecciones del primer semestre | Responsable SST | VERIFICAR | 4.2.4 |
| 9 | Sep | Segundo ciclo: Inspeccion de instalaciones generales y maquinaria con COPASST (comparar con hallazgos previos) | Responsable SST / COPASST | HACER | 4.2.4 |
| 10 | Oct | Segundo ciclo: Inspeccion de equipos de emergencia y EPP (verificar reposiciones y mantenimientos) | Responsable SST / COPASST / Brigada | HACER | 4.2.4 |
| 11 | Nov | Evaluacion de indicadores del programa de inspecciones y eficacia de acciones correctivas | Responsable SST | VERIFICAR | 4.2.4 |
| 12 | Dic | Informe anual del programa de inspecciones y planificacion del siguiente ano | Responsable SST | ACTUAR | 4.2.4 |

### Distribucion PHVA

| Fase | Cantidad | Meses |
|------|----------|-------|
| PLANEAR | 2 | Ene, Feb |
| HACER | 6 | Mar, Abr, May, Jun, Jul, Sep, Oct |
| VERIFICAR | 2 | Ago, Nov |
| ACTUAR | 1 | Dic |

---

## PARTE 2: Indicadores de Medicion

7 indicadores que miden el cumplimiento y eficacia del programa de inspecciones.

| # | Nombre | Tipo | Formula | Meta | Unidad | Periodicidad |
|---|--------|------|---------|------|--------|-------------|
| 1 | Cumplimiento del cronograma de inspecciones | Proceso | (Inspecciones ejecutadas / Inspecciones programadas) x 100 | 100 | % | Trimestral |
| 2 | Participacion del COPASST en inspecciones | Proceso | (Inspecciones con participacion COPASST / Inspecciones totales) x 100 | 90 | % | Trimestral |
| 3 | Cobertura de areas inspeccionadas | Proceso | (Areas inspeccionadas / Areas totales de la empresa) x 100 | 100 | % | Semestral |
| 4 | Cierre oportuno de hallazgos | Proceso | (Hallazgos cerrados en plazo / Hallazgos totales reportados) x 100 | 80 | % | Trimestral |
| 5 | Eficacia de acciones correctivas | Resultado | (Hallazgos NO reincidentes / Hallazgos cerrados) x 100 | 85 | % | Semestral |
| 6 | Reduccion de condiciones inseguras | Resultado | ((Hallazgos periodo anterior - Hallazgos periodo actual) / Hallazgos periodo anterior) x 100 | 10 | % | Semestral |
| 7 | Indice de condiciones inseguras | Resultado | (Condiciones inseguras encontradas / Areas inspeccionadas) x 100 | 15 | % | Trimestral |

### Distribucion por tipo

| Tipo | Cantidad | Indicadores |
|------|----------|-------------|
| Proceso | 4 | #1, #2, #3, #4 |
| Resultado | 3 | #5, #6, #7 |

---

## PARTE 3: Secciones del Documento Formal

| # | seccion_key | Nombre | Descripcion del prompt |
|---|-------------|--------|------------------------|
| 1 | objetivo | Objetivo | Objetivo del programa de inspecciones segun Res. 0312/2019 estandar 4.2.4 y Decreto 1072/2015 |
| 2 | alcance | Alcance | A quienes aplica, areas, instalaciones, maquinaria y equipos cubiertos |
| 3 | marco_normativo | Marco Normativo | Res. 0312/2019, Decreto 1072/2015, Res. 2400/1979, NTC 4114 |
| 4 | definiciones | Definiciones | Inspeccion planeada, no planeada, hallazgo, condicion insegura, acto inseguro, accion correctiva, accion preventiva |
| 5 | responsabilidades | Responsabilidades | Representante legal, responsable SST, COPASST/Vigia, trabajadores, supervisores |
| 6 | tipos_inspecciones | Tipos de Inspecciones | Planeadas, no planeadas, generales, especificas, pre-operacionales, de EPP, de emergencia |
| 7 | metodologia | Metodologia de Inspeccion | Preparacion, ejecucion, registro, clasificacion de hallazgos, prioridad |
| 8 | cronograma_inspecciones | Cronograma de Inspecciones | Frecuencia por tipo, responsables, areas, herramientas (debe referenciar actividades del PTA) |
| 9 | hallazgos_acciones | Hallazgos y Acciones Correctivas | Registro, clasificacion (critico/mayor/menor), seguimiento, cierre, verificacion eficacia |
| 10 | indicadores_gestion | Indicadores de Gestion | Medir cumplimiento y eficacia del programa (debe referenciar indicadores de Parte 2) |

> **NOTA:** La seccion `control_cambios` NO va como seccion del documento. El control de cambios
> lo maneja automaticamente el sistema de versionamiento (`tbl_doc_versiones_sst`).

---

## Decisiones de Diseno

1. **Participacion COPASST es obligatoria:** El estandar 4.2.4 exige "con participacion del COPASST". Todas las actividades de inspeccion incluyen al COPASST como responsable conjunto.

2. **Dos ciclos de inspeccion al ano:** Se programan inspecciones en el primer semestre (Mar-Jul) y un segundo ciclo (Sep-Oct) para poder comparar hallazgos y medir mejora.

3. **7 indicadores en vez de 5:** El programa de inspecciones tiene mas dimensiones medibles que un PVE (cumplimiento, participacion, cobertura, cierre, eficacia, reduccion, indice). Se incluyen 4 de proceso y 3 de resultado.

4. **Secciones 8 y 10 referencian Parte 1 y 2:** La IA debe usar los datos reales del PTA e indicadores al generar el cronograma y la seccion de indicadores del documento.

5. **COPASST vs Vigia:** Para 7 estandares se usa Vigia SST, para 21+ se usa COPASST. La IA ajusta segun `estandares_aplicables` del cliente.

---

## Patron de Referencia

Todo el codigo se basa en la implementacion existente de **PVE Riesgo Biomecanico** (`pve_riesgo_biomecanico`):

| Componente | Archivo de referencia |
|-----------|----------------------|
| Service Part 1 | `app/Services/ActividadesPveBiomecanicoService.php` |
| Service Part 2 | `app/Services/IndicadoresPveBiomecanicoService.php` |
| Document Class | `app/Libraries/DocumentosSSTTypes/PveRiesgoBiomecanico.php` |
| Vista Part 1 | `app/Views/generador_ia/pve_riesgo_biomecanico.php` |
| Vista Part 2 | `app/Views/generador_ia/indicadores_pve_biomecanico.php` |
| SQL Script | `app/SQL/agregar_pve_riesgo_biomecanico.php` |

---

*Documento creado: 2026-03-01*
*Siguiente paso: Crear Script BD (Paso 4 de GUIA_PASO_A_PASO_PROGRAMA_TIPO_B.md)*
