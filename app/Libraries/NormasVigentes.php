<?php

namespace App\Libraries;

/**
 * Fuente única de verdad para vigencia normativa SST Colombia.
 * Usado por IADocumentacionService y MarcoNormativoService al construir prompts de Marco Legal.
 *
 * Cuando una nueva norma derogue a otra, actualizar SOLO este archivo.
 */
class NormasVigentes
{
    /**
     * Instrucción de vigencia para inyectar en cualquier prompt de Marco Legal.
     * Incluye lista negra de normas derogadas/compiladas.
     */
    public static function instruccionVigencia(): string
    {
        return
            "\n\n=== INSTRUCCIÓN CRÍTICA DE VIGENCIA NORMATIVA ===\n" .
            "Año de referencia: " . date('Y') . ".\n" .
            "SOLO incluye normas VIGENTES en Colombia a la fecha actual.\n" .
            "NUNCA cites como vigentes normas derogadas o compiladas.\n" .
            "Normas que NO debes citar como vigentes independientes:\n" .
            "- Decreto 1443 de 2014: fue compilado en el Decreto 1072 de 2015. Cita únicamente el Decreto 1072 de 2015.\n" .
            "- Resolución 1111 de 2017: derogada por Resolución 0312 de 2019.\n" .
            "- Resolución 652 de 2012: derogada por Resolución 3461 de 2025.\n" .
            "- Resolución 1356 de 2012: derogada por Resolución 3461 de 2025.\n" .
            "Nota importante: La Ley 1010 de 2006 sigue vigente pero fue modificada por la Ley 2209 de 2022; menciona ambas cuando sea relevante.\n";
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
