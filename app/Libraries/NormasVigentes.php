<?php

namespace App\Libraries;

use App\Models\NormaDerogadaModel;

/**
 * Fuente única de verdad para vigencia normativa SST Colombia.
 * Usado por IADocumentacionService y MarcoNormativoService al construir prompts de Marco Legal.
 *
 * Las normas derogadas se leen de tbl_normas_derogadas (alimentada por consultores desde la UI).
 */
class NormasVigentes
{
    /** Cache estático para evitar queries repetidas en la misma request */
    private static ?array $normasCache = null;

    /**
     * Carga normas derogadas desde BD (con cache por request)
     */
    private static function cargarNormas(): array
    {
        if (self::$normasCache === null) {
            $model = new NormaDerogadaModel();
            self::$normasCache = $model->getActivas();
        }
        return self::$normasCache;
    }

    /**
     * Limpia el cache (llamar después de insertar una nueva norma)
     */
    public static function limpiarCache(): void
    {
        self::$normasCache = null;
    }

    /**
     * Instrucción de vigencia para inyectar en cualquier prompt de Marco Legal.
     * Construida dinámicamente desde tbl_normas_derogadas.
     */
    public static function instruccionVigencia(): string
    {
        $normas = self::cargarNormas();

        $listaNegra = '';
        foreach ($normas as $n) {
            $listaNegra .= "- {$n['norma_derogada']}: derogada/reemplazada por {$n['norma_reemplazo']}.\n";
        }

        return
            "\n\n=== INSTRUCCIÓN CRÍTICA DE VIGENCIA NORMATIVA ===\n" .
            "Año de referencia: " . date('Y') . ".\n" .
            "SOLO incluye normas VIGENTES en Colombia a la fecha actual.\n" .
            "NUNCA cites como vigentes normas derogadas o compiladas.\n" .
            "Normas que NO debes citar como vigentes independientes:\n" .
            $listaNegra .
            "SOLO incluye normas directamente relacionadas con el tema del documento. NO incluyas normas de temas tangenciales.\n";
    }

    /**
     * Lista de normas SST vigentes verificadas, usada como fallback cuando el web search no está disponible.
     */
    public static function listaFallback(): string
    {
        return
            "\n\nNormas SST colombianas VIGENTES de referencia (verificadas a " . date('Y') . "):\n" .
            "- Ley 9 de 1979 (Código Sanitario Nacional)\n" .
            "- Ley 1010 de 2006 modificada por Ley 2209 de 2022 (Acoso Laboral)\n" .
            "- Ley 1257 de 2008 (Violencia y discriminación contra la mujer)\n" .
            "- Ley 1482 de 2011 modificada por Ley 1752 de 2015 (Antidiscriminación)\n" .
            "- Ley 1562 de 2012 (Sistema General de Riesgos Laborales)\n" .
            "- Decreto 1072 de 2015 (Decreto Único Reglamentario Sector Trabajo — incluye SG-SST)\n" .
            "- Decreto 1477 de 2014 (Tabla de Enfermedades Laborales)\n" .
            "- Resolución 2400 de 1979 (Estatuto de Seguridad Industrial)\n" .
            "- Resolución 2646 de 2008 (Factores de Riesgo Psicosocial)\n" .
            "- Resolución 0312 de 2019 (Estándares Mínimos SG-SST — derogó Res. 1111 de 2017)\n" .
            "- Resolución 3461 de 2025 (Comité de Convivencia Laboral — derogó Res. 652 y 1356 de 2012)\n" .
            "Cita SOLO las que apliquen al tema específico del documento.\n";
    }
}
