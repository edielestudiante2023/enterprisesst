# Enterprise SST

**Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) para empresas**

Plataforma web que permite a empresas cumplir la normatividad colombiana (Decreto 1072, Resolucion 0312) mediante gestion documental, evaluaciones, planes de trabajo, indicadores y generacion automatica de documentos con IA.

**Empresa:** Cycloid Talent
**Repositorio:** github.com/edielestudiante2023/enterprisesst

---

## Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.2 + CodeIgniter 4.6 |
| Base de datos | MySQL 8 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx (Ubuntu 24.04) — aaPanel (66.29.154.174) |
| Email | SendGrid API v3 |
| PDF | TCPDF + DOMPDF 3.1 |
| Excel | PhpSpreadsheet 3.9 |
| IA | OpenAI GPT-4o / GPT-4o-mini (generacion de documentos, chat Otto) |
| QR | chillerlan/php-qrcode 5.0 |
| Markdown | Parsedown 1.7 |

---

## Modulos principales (18)

| Modulo | Descripcion |
|--------|-------------|
| Documentos SGSST | 34+ documentos normativos generados con IA (politicas, programas, formatos) |
| Plan de Trabajo Anual (PTA) | Actividades PHVA por cliente, edicion inline, exportacion Excel |
| Evaluacion Estandares Minimos | Decreto 1072 / Res. 0312, evaluacion por ciclo, historial de puntajes |
| Indicadores SST | 17 indicadores (frecuencia, severidad, ausentismo, cobertura, etc.) |
| Actas de Visita | Registro con fotos, firma, PDF, notificaciones |
| Actas de Reunion | Comites, asistentes, compromisos, votaciones, firma digital |
| Contratos | Ciclo completo: creacion, firma digital, PDF |
| Capacitaciones | Cronograma, asistencia, induccion por etapas |
| Matriz Legal | Marco normativo con generacion IA |
| Matriz de Comunicacion | Planificacion de comunicaciones SST |
| Firmas Digitales | Firma electronica via token por email |
| Comites Electorales | Procesos electorales COPASST/CCL con votacion electronica |
| Inspecciones | Locativa, extintores, botiquin, senalizacion |
| KPIs | 17 KPIs con definiciones, variables, periodos de medicion |
| Pendientes | Compromisos con conteo de dias |
| Presupuesto SST | Categorias, items, detalle de ejecucion |
| Mantenimientos | Control de mantenimientos y vencimientos |
| Chat Otto (IA) | Asistente IA con consultas SQL readonly |

---

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultant | Gestion de clientes asignados + generacion de documentos + chat IA |
| client | Portal readonly + chat Otto (solo SELECT) |

---

## Estructura del proyecto

```
enterprisesst/
├── app/
│   ├── Commands/          # 2 comandos CLI (cron jobs)
│   ├── Config/            # Routes.php, Database.php, Filters.php, Security.php
│   ├── Controllers/       # ~201 controladores
│   ├── Database/          # Migraciones y seeders
│   ├── Filters/           # AuthFilter, ApiKeyFilter, AuthOrApiKeyFilter
│   ├── Helpers/           # Funciones auxiliares
│   ├── Libraries/         # 13 librerias de logica de negocio
│   ├── Models/            # ~98 modelos
│   ├── Services/          # 37+ servicios (IA, indicadores, documentos)
│   ├── SQL/               # Scripts de migracion
│   ├── Traits/            # Traits reutilizables
│   └── Views/             # Vistas PHP
├── docs/                  # Documentacion tecnica (24+ archivos)
├── public/                # Punto de entrada web (index.php)
├── scripts/               # Scripts utilitarios
├── sql/                   # Scripts SQL adicionales
├── tests/                 # Tests PHPUnit
├── translations/          # Archivos de traduccion
├── writable/              # Logs, cache, sesiones, uploads
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── CONTRIBUTING.md        # Guia de contribucion
├── README.md              # Este archivo
├── composer.json          # Dependencias PHP
└── spark                  # CLI de CodeIgniter
```

---

## Requisitos previos

- PHP 8.2+ con extensiones: intl, mbstring, mysqlnd, curl, gd, openssl
- MySQL 8.0+
- Composer 2.x
- Servidor web (Apache/Nginx)

---

## Instalacion local

```bash
# 1. Clonar el repositorio
git clone https://github.com/edielestudiante2023/enterprisesst.git
cd enterprisesst

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus credenciales locales

# 4. Configurar base de datos
# Crear BD 'empresas_sst' en MySQL y ejecutar migraciones

# 5. Iniciar servidor de desarrollo
php spark serve
# Acceder en http://localhost:8080
```

---

## Variables de entorno

| Variable | Descripcion |
|----------|-------------|
| `CI_ENVIRONMENT` | Entorno (development / production) |
| `app.baseURL` | URL base de la aplicacion |
| `database.default.*` | Conexion BD principal (hostname, database, username, password, port) |
| `readonly.*` | Conexion BD readonly para Chat Otto |
| `SENDGRID_API_KEY` | API Key de SendGrid para email transaccional |
| `SENDGRID_FROM_EMAIL` | Email remitente |
| `SENDGRID_FROM_NAME` | Nombre remitente |
| `OPENAI_API_KEY` | API Key de OpenAI |
| `OPENAI_MODEL` | Modelo principal (gpt-4o) |
| `OTTO_MODEL` | Modelo para Chat Otto (gpt-4o-mini) |
| `APP_API_KEY` | Token de acceso programatico a la API |

---

## Cron jobs (2 tareas programadas)

| Comando | Frecuencia | Descripcion |
|---------|-----------|-------------|
| `php spark notificaciones:procesar-actas` | Diario | Procesa notificaciones de actas pendientes |
| `php spark pendientes:resumen` | Periodico | Genera resumen de pendientes y envia por email |

---

## Deploy

El deploy se realiza via SSH al servidor de produccion (Hetzner LXC).

```bash
# Deploy manual
ssh root@<servidor> "cd /www/wwwroot/<proyecto> && git pull origin main && composer install --no-dev"
```

Pipeline CI/CD disponible en `.gitea/workflows/` para automatizar validacion y deploy.

---

## Documentacion adicional

- [docs/](docs/) — Documentacion tecnica del proyecto
- [CONTRIBUTING.md](CONTRIBUTING.md) — Guia de contribucion
