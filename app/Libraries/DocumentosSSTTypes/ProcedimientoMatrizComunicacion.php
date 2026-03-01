<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoMatrizComunicacion
 *
 * Implementa el Procedimiento de Matriz de Comunicacion del SG-SST
 * para el estandar 2.8.1 de la Resolucion 0312/2019.
 *
 * Tipo A (secciones_ia): lee configuracion desde BD via DocumentoConfigService.
 */
class ProcedimientoMatrizComunicacion extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'procedimiento_matriz_comunicacion';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento de Matriz de Comunicacion SST';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para identificar, documentar y mantener los protocolos de comunicacion interna y externa del SG-SST';
    }

    public function getEstandar(): ?string
    {
        return '2.8.1';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (empty($seccionesBD)) {
            return $this->getSeccionesFallback();
        }

        $secciones = [];
        foreach ($seccionesBD as $s) {
            $secciones[] = [
                'numero' => (int)($s['numero'] ?? 0),
                'nombre' => $s['nombre'] ?? '',
                'key' => $s['key'] ?? $s['seccion_key'] ?? ''
            ];
        }

        return $secciones;
    }

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Estructura de la Matriz', 'key' => 'estructura_matriz'],
            ['numero' => 6, 'nombre' => 'Procedimiento de Actualizacion', 'key' => 'actualizacion'],
            ['numero' => 7, 'nombre' => 'Canales y Mecanismos', 'key' => 'canales_mecanismos'],
            ['numero' => 8, 'nombre' => 'Indicadores y Seguimiento', 'key' => 'indicadores'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Matriz de Comunicacion SST. Debe establecer el proposito de identificar y documentar todos los protocolos de comunicacion interna y externa del SG-SST. Referencia Decreto 1072/2015 art. 2.2.4.6.14 y Resolucion 0312/2019 estandar 2.8.1.",

            'alcance' => "Define el alcance: aplica a todas las comunicaciones internas y externas relacionadas con SST en todas las sedes. Incluye comunicaciones con trabajadores, {$comite}, comite de convivencia, ARL, EPS, autoridades competentes y contratistas.",

            'definiciones' => "Define terminos clave: Matriz de Comunicacion, Protocolo de Comunicacion, Comunicacion Interna, Comunicacion Externa, Canal de Comunicacion, Auto Reporte, Trazabilidad, Mecanismo de Comunicacion, Registro.",

            'responsabilidades' => "Define responsables: Alta Direccion (garantizar canales), Responsable SG-SST (mantener matriz), {$comite} (comunicar recomendaciones), Comite de Convivencia (gestionar quejas), Trabajadores (auto-reportar condiciones).",

            'estructura_matriz' => "Describe la estructura de la matriz: columnas (categoria, situacion, que comunicar, quien, a quien, canal, plazo, registro, norma), categorias obligatorias (accidentes, incidentes, emergencias, convivencia laboral, peligros, auditorias).",

            'actualizacion' => "Describe cuando y como se actualiza la matriz: nuevas normas, cambios organizacionales, resultados de auditorias, eventos no contemplados. Revision minima anual.",

            'canales_mecanismos' => "Describe los canales disponibles: correo, carteleras, reuniones, intranet, alarma, buzon de denuncias. Para cada canal: tipo, registro, responsable, frecuencia.",

            'indicadores' => "Define indicadores: % protocolos ejecutados, tiempo de comunicacion vs plazo, auto-reportes recibidos, registros completos, cumplimiento reuniones {$comite}."
        ];

        return $prompts[$seccionKey] ?? "Genera contenido para la seccion '{$seccionKey}' del Procedimiento de Matriz de Comunicacion SST.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para identificar, documentar y mantener actualizados los protocolos de comunicacion interna y externa del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) de {$nombreEmpresa}.\n\nEste procedimiento garantiza el cumplimiento del articulo 2.2.4.6.14 del Decreto 1072 de 2015 y el estandar 2.8.1 de la Resolucion 0312 de 2019, asegurando que todos los actores del SG-SST conozcan los canales, responsables y plazos para comunicar situaciones relacionadas con la seguridad y salud en el trabajo.",

            'alcance' => "Este procedimiento aplica a todas las comunicaciones internas y externas relacionadas con Seguridad y Salud en el Trabajo en {$nombreEmpresa}, incluyendo:\n\n- Comunicaciones entre trabajadores y niveles jerarquicos\n- Comunicaciones con el {$comite} y Comite de Convivencia Laboral\n- Reportes a la ARL, EPS y autoridades competentes\n- Comunicaciones con contratistas y visitantes\n- Auto-reportes de condiciones de trabajo y salud",

            'alcance' => "Este procedimiento aplica a todas las comunicaciones internas y externas relacionadas con SST en {$nombreEmpresa}.",

            'definiciones' => "**Matriz de Comunicacion:** Herramienta que consolida todos los protocolos de comunicacion del SG-SST, indicando que comunicar, quien, a quien, por que canal, en que plazo y con que evidencia.\n\n**Protocolo de Comunicacion:** Conjunto de reglas que define como se transmite informacion especifica ante una situacion de SST.\n\n**Comunicacion Interna:** Flujo de informacion entre los trabajadores y niveles organizacionales.\n\n**Comunicacion Externa:** Flujo de informacion hacia entidades externas como ARL, EPS, MinTrabajo, Bomberos.\n\n**Auto Reporte:** Proceso mediante el cual el trabajador comunica voluntariamente sus condiciones de trabajo y salud.\n\n**Canal de Comunicacion:** Medio utilizado para transmitir la informacion (correo, cartelera, reunion, telefono).\n\n**Registro de Comunicacion:** Evidencia de que la comunicacion se realizo (acta, correo, formato firmado).",

            'responsabilidades' => "**Alta Direccion:**\n- Garantizar los canales y recursos para la comunicacion en SST\n- Aprobar la matriz de comunicacion\n\n**Responsable del SG-SST:**\n- Elaborar y mantener actualizada la matriz de comunicacion\n- Verificar el cumplimiento de los protocolos\n- Capacitar en el uso de los canales de comunicacion\n\n**{$comite}:**\n- Comunicar recomendaciones a la alta direccion\n- Participar en la difusion de politicas y procedimientos de SST\n\n**Comite de Convivencia Laboral:**\n- Gestionar las quejas y denuncias de acoso laboral y sexual\n- Comunicar resultados a la alta direccion\n\n**Trabajadores:**\n- Reportar condiciones inseguras y actos inseguros\n- Auto-reportar condiciones de salud\n- Utilizar los canales establecidos",

            'estructura_matriz' => "La Matriz de Comunicacion se estructura con las siguientes columnas:\n\n1. **Categoria:** Agrupacion del tipo de comunicacion\n2. **Situacion/Evento:** Descripcion del hecho que activa la comunicacion\n3. **Que Comunicar:** Informacion que debe transmitirse\n4. **Quien Comunica:** Rol responsable de emitir la comunicacion\n5. **A Quien Comunicar:** Destinatario(s)\n6. **Mecanismo/Canal:** Medio de comunicacion utilizado\n7. **Frecuencia/Plazo:** Tiempo maximo o periodicidad\n8. **Registro/Evidencia:** Documento que evidencia la comunicacion\n9. **Norma Aplicable:** Fundamento legal\n10. **Tipo:** Interna, Externa o Ambas",

            'actualizacion' => "La matriz se actualiza cuando:\n- Se expidan nuevas normas aplicables\n- Haya cambios organizacionales significativos\n- Se identifiquen nuevos peligros\n- Los resultados de auditorias lo requieran\n- Ocurran eventos no contemplados\n\n**Frecuencia minima:** Revision anual\n**Responsable:** Responsable del SG-SST",

            'canales_mecanismos' => "**Canales disponibles:**\n- Correo electronico institucional (formal, con registro)\n- Carteleras informativas (visual, sedes)\n- Reuniones periodicas (actas como registro)\n- Buzon de sugerencias/denuncias (anonimo/confidencial)\n- Sistema de alarma (emergencias)\n- Telefono/WhatsApp empresarial (comunicacion rapida)",

            'indicadores' => "**Indicadores de gestion:**\n\n1. % Protocolos ejecutados = (Protocolos ejecutados / Total protocolos) x 100\n   Meta: >= 90% | Frecuencia: Semestral\n\n2. Tiempo promedio de comunicacion = Suma tiempos / Total comunicaciones\n   Meta: <= Plazo establecido | Frecuencia: Trimestral\n\n3. Auto-reportes recibidos = Total auto-reportes del periodo\n   Meta: >= 1 por trabajador/ano | Frecuencia: Anual"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
