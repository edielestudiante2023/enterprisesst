<?php

namespace App\Libraries\DocumentosSST;

/**
 * Clase base abstracta para definir estructura de documentos SST
 *
 * Cada tipo de documento hereda de esta clase y define:
 * - Metadatos del documento (codigo, nombre, estandar relacionado)
 * - Estructura de secciones
 * - Prompts especificos para cada seccion
 * - Variables de contexto requeridas
 */
abstract class DocumentoBase
{
    /** @var string Codigo del documento (ej: PRG-CAP) */
    public string $codigo;

    /** @var string Nombre completo del documento */
    public string $nombre;

    /** @var string Descripcion breve */
    public string $descripcion;

    /** @var string Estandar de la Res. 0312/2019 relacionado (ej: 1.2.1) */
    public string $estandar;

    /** @var string Codigo de carpeta PHVA donde se ubica (ej: 1.2) */
    public string $carpetaPhva;

    /** @var string Ciclo PHVA: PLANEAR, HACER, VERIFICAR, ACTUAR */
    public string $cicloPhva;

    /** @var int ID del tipo de documento en tbl_doc_tipos */
    public int $idTipo;

    /** @var bool Aplica para empresas con 7 estandares */
    public bool $aplica7 = true;

    /** @var bool Aplica para empresas con 21 estandares */
    public bool $aplica21 = true;

    /** @var bool Aplica para empresas con 60 estandares */
    public bool $aplica60 = true;

    /**
     * Retorna el array de secciones del documento
     * Cada seccion tiene: key, titulo, orden, tipo, prompt, variables
     *
     * @return array
     */
    abstract public function getSecciones(): array;

    /**
     * Retorna las variables de contexto requeridas para generar el documento
     *
     * @return array
     */
    abstract public function getVariablesRequeridas(): array;

    /**
     * Retorna la estructura en formato JSON para la BD
     *
     * @return string
     */
    public function getEstructuraJson(): string
    {
        $secciones = $this->getSecciones();
        $titulos = array_column($secciones, 'titulo');
        return json_encode($titulos, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Retorna los prompts en formato JSON para la BD
     *
     * @return string
     */
    public function getPromptsJson(): string
    {
        $secciones = $this->getSecciones();
        $prompts = [];
        foreach ($secciones as $seccion) {
            $prompts[$seccion['orden']] = $seccion['prompt'] ?? '';
        }
        return json_encode($prompts, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Retorna las variables en formato string para la BD
     *
     * @return string
     */
    public function getVariablesString(): string
    {
        return implode(',', $this->getVariablesRequeridas());
    }

    /**
     * Genera el SQL INSERT para la plantilla
     *
     * @return string
     */
    public function generarInsertSQL(): string
    {
        $estructura = addslashes($this->getEstructuraJson());
        $prompts = addslashes($this->getPromptsJson());
        $variables = $this->getVariablesString();

        $aplica7 = $this->aplica7 ? 1 : 0;
        $aplica21 = $this->aplica21 ? 1 : 0;
        $aplica60 = $this->aplica60 ? 1 : 0;

        return "INSERT INTO `tbl_doc_plantillas`
(`id_tipo`, `nombre`, `descripcion`, `codigo_sugerido`, `estructura_json`, `prompts_json`, `variables_contexto`, `activo`, `orden`, `aplica_7`, `aplica_21`, `aplica_60`)
VALUES
({$this->idTipo}, '{$this->nombre}', '{$this->descripcion}', '{$this->codigo}', '{$estructura}', '{$prompts}', '{$variables}', 1, 1, {$aplica7}, {$aplica21}, {$aplica60});";
    }
}
