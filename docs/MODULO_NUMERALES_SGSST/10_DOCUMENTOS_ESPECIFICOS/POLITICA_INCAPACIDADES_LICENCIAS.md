# Política de Incapacidades y Licencias (POL-INC)

**Numeral:** 2.1.1 - Resolución 0312 de 2019
**Código:** POL-INC
**Tipo:** Tipo A (1 parte) — flujo `secciones_ia`
**Justificación legal:** Ley 2466 de 2025 (Reforma Laboral) + CST + normativa complementaria

---

## 1. Contexto Legal

Esta política integra los cambios de la **Ley 2466 de 2025** (Reforma Laboral para el Trabajo Decente y Digno) junto con la normativa preexistente sobre incapacidades y licencias.

### Cambios introducidos por la Ley 2466 de 2025

| Artículo | Cambio |
|----------|--------|
| Art. 15 | Nuevas licencias remuneradas obligatorias: citas médicas urgentes/especialistas (incluye endometriosis - Ley 2338/2023), obligaciones escolares como acudiente, 1 día/semestre por uso de bicicleta como transporte |
| Art. 17 | Prohibición expresa de despedir o presionar renuncia por enfermedad o afectaciones de salud mental |
| Art. 45 | Reconocimiento explícito de incapacidad/maternidad como causa válida de contrato de reemplazo temporal |

### Normativa preexistente integrada

| Norma | Tema |
|-------|------|
| CST Art. 236-238 | Licencia de maternidad (18 semanas) |
| Ley 1780/2016 | Licencia de paternidad (2 semanas) |
| Ley 1280/2009 | Licencia de luto (5 días hábiles) |
| CST Art. 227-228 | Pago de incapacidades por enfermedad general |
| Decreto 1295/1994 | Incapacidades de origen laboral (ARL) |
| Ley 361/1997 | Protección trabajadores en situación de discapacidad |
| Ley 100/1993 | Sistema General de Seguridad Social (EPS/ARL/Pensión) |
| Decreto 1072/2015 | Obligaciones del empleador en SST |
| Resolución 0312/2019 | Estándares Mínimos SG-SST, numeral 2.1.1 |

---

## 2. Configuración del Documento

```
tipo_documento : politica_incapacidades_licencias
nombre         : Política de Gestión de Incapacidades y Licencias
codigo         : POL-INC
estandar       : 2.1.1
flujo          : secciones_ia
categoria      : politicas
icono          : fas fa-file-medical
```

---

## 3. Secciones (10)

| # | Key | Nombre |
|---|-----|--------|
| 1 | `objetivo` | Objetivo |
| 2 | `alcance` | Alcance |
| 3 | `declaracion` | Declaración de la Política |
| 4 | `definiciones` | Definiciones |
| 5 | `tipos_licencias` | Tipos de Licencias y Permisos |
| 6 | `manejo_incapacidades` | Manejo de Incapacidades |
| 7 | `derechos_obligaciones` | Derechos y Obligaciones |
| 8 | `procedimiento` | Procedimiento de Reporte y Gestión |
| 9 | `marco_legal` | Marco Legal |
| 10 | `comunicacion` | Comunicación y Divulgación |

---

## 4. Firmantes

| Orden | Tipo | Rol Display | Columna Encabezado | Licencia |
|-------|------|-------------|-------------------|----------|
| 1 | `consultor_sst` | Consultor SST | Elaboró | Sí |
| 2 | `representante_legal` | Representante Legal | Aprobó | No |

---

## 5. Prompts IA por Sección

### `objetivo`
- Establecer el compromiso de la empresa con la gestión responsable de incapacidades y licencias
- Garantizar el cumplimiento de la Ley 2466 de 2025 y la normativa complementaria
- Proteger los derechos de los trabajadores durante períodos de incapacidad o licencia
- Promover el retorno seguro al trabajo
- Máx. 2-3 párrafos

### `alcance`
- Todos los trabajadores (contrato a término fijo, indefinido, temporal)
- Todos los tipos de incapacidad: enfermedad general (EPS), accidente/enfermedad laboral (ARL), maternidad/paternidad
- Todas las licencias: maternidad, paternidad, luto, citas médicas, obligaciones escolares (Ley 2466)
- Ajustar por número de estándares del cliente

### `declaracion`
- Compromiso formal de la alta dirección (primera persona plural)
- Respeto por los derechos del trabajador incapacitado o en licencia
- Prohibición de presionar renuncia o despedir por enfermedad (Art. 17 Ley 2466)
- Compromiso con el pago oportuno y la gestión transparente

### `definiciones`
- Incapacidad temporal: inhabilidad física o mental para trabajar por tiempo determinado
- Incapacidad de origen común: generada por enfermedad o accidente no laboral (EPS)
- Incapacidad de origen laboral: accidente de trabajo o enfermedad profesional (ARL)
- Licencia remunerada: permiso con pago del salario durante la ausencia autorizada
- Licencia no remunerada: permiso sin pago por acuerdo entre partes
- Licencia de maternidad: 18 semanas posparto (CST Art. 236)
- Licencia de paternidad: 2 semanas (Ley 1780/2016)
- Licencia de luto: 5 días hábiles (Ley 1280/2009)
- Reintegro laboral: proceso de retorno al trabajo tras incapacidad

### `tipos_licencias`
Tabla con todos los tipos:
1. **Licencia de maternidad** → 18 semanas, paga EPS (si cotizó mín. 1 semana antes del parto)
2. **Licencia de paternidad** → 2 semanas, paga EPS
3. **Licencia de luto** → 5 días hábiles por fallecimiento de cónyuge/compañero/familiar hasta 2° consanguinidad
4. **Calamidad doméstica** → Hasta 5 días, situaciones graves familiares hasta 3° consanguinidad (Ley 2466)
5. **Cita médica** (nueva Ley 2466) → Tiempo necesario con certificado, urgencias o especialistas
6. **Obligaciones escolares** (nueva Ley 2466) → Cuando el centro educativo cite al acudiente, con certificado
7. **Comisiones sindicales** → Según convenio/CST
8. **Citaciones judiciales/administrativas** → Tiempo necesario con comprobante
9. **Día de bicicleta** (nueva Ley 2466) → 1 día remunerado por semestre certificando uso de bicicleta como transporte

### `manejo_incapacidades`
Tres escenarios:
- **Incapacidad EPS (enfermedad común):** Días 1-3 el empleador paga el 66.67% del salario; días 4-180 la EPS paga el 66.67%; días 181-540 el Fondo de Pensión (Colpensiones/AFP) puede continuar
- **Incapacidad ARL (accidente/enfermedad laboral):** Desde día 1, la ARL paga el 100%. Obligación del empleador: reportar dentro de los 2 días hábiles siguientes
- **Proceso de reconocimiento:** Entrega del certificado médico, radicación en nómina, gestión ante EPS/ARL, control en planilla de novedades
- Prohibición de descontar días de incapacidad del tiempo de vacaciones

### `derechos_obligaciones`
**Derechos del trabajador:**
1. Recibir pago oportuno de incapacidades
2. No ser despedido ni presionado a renunciar por enfermedad o salud mental (Art. 17 Ley 2466)
3. Recibir las licencias legales a que tiene derecho
4. Reintegrarse a su cargo tras la incapacidad (o a uno igual/equivalente)
5. Confidencialidad de su diagnóstico
6. Ajuste razonable de puesto para retorno progresivo
7. Protección reforzada para trabajadores con discapacidad (Ley 361/1997)

**Obligaciones del empleador:**
1. Pagar los primeros 3 días de incapacidad por enfermedad general
2. Reportar accidentes de trabajo a la ARL dentro de los 2 días hábiles
3. Conceder las licencias remuneradas de Ley (maternidad, paternidad, luto, citas, escolares)
4. Gestionar los trámites ante EPS/ARL para reconocimiento de incapacidades
5. No contratar reemplazos en condiciones inferiores al trabajador incapacitado
6. Mantener el contrato durante la incapacidad temporal

**Obligaciones del trabajador:**
1. Informar la incapacidad tan pronto como sea posible
2. Presentar el certificado médico dentro de los 2 días hábiles
3. No realizar actividades laborales durante la incapacidad
4. Seguir el tratamiento médico prescrito
5. Presentar los certificados que acrediten las licencias remuneradas

### `procedimiento`
Flujo paso a paso:
1. Trabajador informa al jefe inmediato (mismo día o al día siguiente)
2. Trabajador entrega certificado médico/constancia a RR.HH. (máx. 2 días hábiles)
3. RR.HH. radica la novedad en el sistema de nómina
4. Según origen:
   - EPS: Gestionar pago de incapacidades desde día 4
   - ARL: Reportar accidente/enfermedad laboral dentro de 2 días hábiles
5. Comunicar al área correspondiente la ausencia y duración estimada
6. Al finalizar incapacidad: trabajador presenta certificado de alta médica
7. RR.HH. coordina el reintegro laboral (con seguimiento si aplica)

### `marco_legal`
- Constitución Política de Colombia, Art. 49: Derecho a la seguridad social y a la salud
- CST Arts. 227-228: Incapacidades por enfermedad no profesional
- CST Arts. 236-238: Licencia de maternidad y protección a la maternidad
- Ley 100/1993: Sistema General de Seguridad Social en Salud
- Decreto 1295/1994: Sistema General de Riesgos Laborales (incapacidades ARL)
- Ley 361/1997: Mecanismos de integración social de personas con discapacidad
- Ley 1280/2009: Licencia de luto (5 días hábiles)
- Ley 1780/2016: Licencia de paternidad (2 semanas)
- Ley 2338/2023: Endometriosis, inclusión como condición que genera licencia médica
- Decreto 1072/2015: Decreto Único Reglamentario del Sector Trabajo
- Resolución 0312/2019: Estándares Mínimos del SG-SST (numeral 2.1.1)
- **Ley 2466/2025 (Reforma Laboral):** Art. 15 (nuevas licencias), Art. 17 (protección salud mental), Art. 45 (contratos de reemplazo)

### `comunicacion`
- Comunicada al COPASST/Vigía SST (según número de estándares)
- Publicada en lugares visibles, carteleras, intranet
- Incluida en inducción y reinducción de nuevos trabajadores
- Socializada con jefes inmediatos y nómina para correcta gestión
- Revisión anual o cuando cambie la normativa
- Canal de consultas: área de talento humano o RR.HH.

---

## 6. Contenido Estático (getContenidoEstatico)

El documento usa contenido estático (la IA puede complementar con detalles del cliente).

Ver implementación en:
`app/Libraries/DocumentosSSTTypes/PoliticaIncapacidadesLicencias.php`

---

## 7. URLs

| Acción | URL |
|--------|-----|
| Generación | `/documentos/generar/politica_incapacidades_licencias/18` |
| Vista previa | `/documentos-sst/18/politica-incapacidades-licencias/2026` |
| Previsualizar datos | `/documentos/previsualizar-datos/politica_incapacidades_licencias/18` |

---

## 8. Historial de Cambios

| Versión | Fecha | Cambio |
|---------|-------|--------|
| 1.0 | 2026-02-18 | Creación inicial — Ley 2466 de 2025 |
