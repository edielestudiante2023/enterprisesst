<?php
/**
 * Script para agregar la Política de Prevención del Acoso Sexual (nueva, separada)
 * Numeral 2.1.1 - Ley 2365 de 2024 - Art. 210A Código Penal
 *
 * Ejecutar LOCAL: php app/SQL/agregar_politica_acoso_sexual.php
 * Ejecutar PROD:  php app/SQL/agregar_politica_acoso_sexual.php --prod
 *
 * IMPORTANTE: Ejecutar LOCAL primero, solo si OK pasar a PROD
 */

$isProd = in_array('--prod', $argv ?? []);

if ($isProd) {
    $config = [
        'host'     => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port'     => 25060,
        'database' => 'empresas_sst',
        'username' => 'cycloid_userdb',
        'password' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl'      => true
    ];
    echo "*** MODO PRODUCCION ***\n";
} else {
    $config = [
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'empresas_sst',
        'username' => 'root',
        'password' => '',
        'ssl'      => false
    ];
    echo "*** MODO LOCAL ***\n";
}

$db = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $config['port']);
if ($db->connect_error) {
    echo "ERROR conexion: " . $db->connect_error . "\n";
    exit(1);
}
$db->set_charset('utf8mb4');

// ─── 1) Verificar que no exista ya ─────────────────────────────────────────
$check = $db->query("SELECT id_tipo_config FROM tbl_doc_tipo_configuracion WHERE tipo_documento='politica_acoso_sexual'");
if ($check->num_rows > 0) {
    echo "Ya existe politica_acoso_sexual en tbl_doc_tipo_configuracion. Nada que hacer.\n";
    $row = $check->fetch_assoc();
    $idTipo = $row['id_tipo_config'];
} else {
    // ─── 2) Insertar tipo de documento ─────────────────────────────────────
    $db->query("INSERT INTO tbl_doc_tipo_configuracion
        (tipo_documento, nombre, descripcion, estandar, flujo, categoria, icono, orden, activo)
        VALUES (
            'politica_acoso_sexual',
            'Politica de Prevencion del Acoso Sexual',
            'Politica que establece el compromiso de la empresa con la prevencion y sancion del acoso sexual conforme a la Ley 2365 de 2024 y el Art. 210A del Codigo Penal',
            '2.1.1',
            'secciones_ia',
            'politicas',
            'bi-shield-exclamation',
            14,
            1
        )");

    if ($db->error) {
        echo "ERROR insertando tipo: " . $db->error . "\n";
        exit(1);
    }
    $idTipo = $db->insert_id;
    echo "Insertado en tbl_doc_tipo_configuracion. id_tipo_config=$idTipo\n";
}

// ─── 3) Insertar secciones con prompts ─────────────────────────────────────
$secciones = [
    [
        'numero' => 1,
        'nombre' => 'Objetivo',
        'seccion_key' => 'objetivo',
        'prompt_ia' => "Genera el objetivo de la Politica de Prevencion del Acoso Sexual de la empresa. Menciona la Ley 2365 de 2024 y el Art. 210A del Codigo Penal colombiano. Objetivo: establecer compromiso de tolerancia cero, garantizar ambiente laboral seguro y libre de acoso sexual, proteger la dignidad e integridad de todos los trabajadores.",
        'tipo_contenido' => 'texto',
        'orden' => 10,
    ],
    [
        'numero' => 2,
        'nombre' => 'Alcance',
        'seccion_key' => 'alcance',
        'prompt_ia' => "Define el alcance de la Politica de Prevencion del Acoso Sexual. Debe aplicar a todos los trabajadores sin distincion de genero o nivel jerarquico, incluyendo directivos, contratistas, practicantes y visitantes. Cubrir todas las situaciones laborales: instalaciones, actividades externas, comunicaciones virtuales.",
        'tipo_contenido' => 'texto',
        'orden' => 20,
    ],
    [
        'numero' => 3,
        'nombre' => 'Declaracion de la Politica',
        'seccion_key' => 'declaracion',
        'prompt_ia' => "Genera la declaracion formal de la Politica de Prevencion del Acoso Sexual. Debe incluir: TOLERANCIA CERO, compromisos de la empresa (respeto a la dignidad, prevencion activa, canales confidenciales, proteccion a victimas, investigacion diligente, colaboracion con autoridades). En la frase final de sanciones indicar UNICAMENTE que seran sancionados de acuerdo al Reglamento Interno de Trabajo.",
        'tipo_contenido' => 'texto',
        'orden' => 30,
    ],
    [
        'numero' => 4,
        'nombre' => 'Definicion de Acoso Sexual',
        'seccion_key' => 'definiciones',
        'prompt_ia' => "Define el acoso sexual usando la definicion EXACTA de la Ley 2365 de 2024: 'todo acto de persecucion, hostigamiento o asedio, de caracter o connotacion sexual, lasciva o libidinosa, que se manifieste por relaciones de poder de orden vertical u horizontal, mediadas por la edad, el sexo, el genero, orientacion e identidad sexual, la posicion laboral, social, o economica, que se de una o varias veces en contra de otra persona en el contexto laboral.' Luego explicar las modalidades: Quid Pro Quo y Acoso Ambiental. Incluir nota sobre consentimiento. Aclarar diferencia con acoso laboral (Ley 1010).",
        'tipo_contenido' => 'texto',
        'orden' => 40,
    ],
    [
        'numero' => 5,
        'nombre' => 'Conductas Constitutivas de Acoso Sexual',
        'seccion_key' => 'conductas',
        'prompt_ia' => "Lista las conductas constitutivas de acoso sexual prohibidas en la empresa. Incluir: insinuaciones sexuales no deseadas, comentarios sobre el cuerpo o apariencia, gestos obscenos, contacto fisico no consentido, envio de material sexual por cualquier medio, chantaje sexual (condicionar beneficios laborales), amenazas por rechazar propuestas, difusion de rumores sobre vida sexual. Aclarar que aplica independientemente del nivel jerarquico.",
        'tipo_contenido' => 'texto',
        'orden' => 50,
    ],
    [
        'numero' => 6,
        'nombre' => 'Mecanismos de Prevencion',
        'seccion_key' => 'mecanismos_prevencion',
        'prompt_ia' => "Describe los mecanismos de prevencion del acoso sexual. Incluir: 1) Capacitacion obligatoria sobre Ley 2365/2024 y Art. 210A. 2) Canales internos de atencion (Talento Humano, correo confidencial). IMPORTANTE: Indicar que el Comite de Convivencia Laboral NO tiene competencia sobre acoso sexual al ser un delito penal, las denuncias van a la Fiscalia. 3) Protocolo de atencion inmediata sin revictimizacion. 4) Coordinacion con Fiscalia y Comisaria de Familia.",
        'tipo_contenido' => 'texto',
        'orden' => 60,
    ],
    [
        'numero' => 7,
        'nombre' => 'Procedimiento de Denuncia y Atencion',
        'seccion_key' => 'procedimiento_denuncia',
        'prompt_ia' => "Describe el procedimiento de denuncia y atencion del acoso sexual. CRITICO: Iniciar con aviso de que el CCL NO tiene competencia porque es delito penal (Art. 210A, Ley 2365/2024). Canales: internos para medidas de proteccion inmediata (RRHH, correo), externos obligatorios para investigacion penal (Fiscalia, Comisaria de Familia, Inspector del Trabajo). Incluir atencion inmediata sin revictimizacion, medidas de proteccion, colaboracion con autoridades. Sanciones internas: UNICAMENTE de acuerdo al Reglamento Interno de Trabajo, sin perjuicio de sanciones penales.",
        'tipo_contenido' => 'texto',
        'orden' => 70,
    ],
    [
        'numero' => 8,
        'nombre' => 'Marco Legal',
        'seccion_key' => 'marco_legal',
        'prompt_ia' => "Lista el marco normativo de la Politica de Acoso Sexual. Incluir obligatoriamente: Constitucion Politica Arts. 1/13/43, Codigo Penal Art. 210A, Ley 2365 de 2024 (modificacion Art. 210A), Ley 1257 de 2008, Ley 1719 de 2014, Resolucion 652/2012, Decreto 1072/2015, Resolucion 0312/2019.",
        'tipo_contenido' => 'texto',
        'orden' => 80,
    ],
    [
        'numero' => 9,
        'nombre' => 'Comunicacion y Divulgacion',
        'seccion_key' => 'comunicacion',
        'prompt_ia' => "Define como se comunicara la Politica de Prevencion del Acoso Sexual. Incluir: comunicacion a todo el personal, publicacion visible con canales de denuncia, induccion/reinduccion, informacion a contratistas, lineas de ayuda externas (Linea 155, Fiscalia Linea 122, Comisaria de Familia). Revision anual.",
        'tipo_contenido' => 'texto',
        'orden' => 90,
    ],
];

$insertados = 0;
$existentes = 0;
foreach ($secciones as $sec) {
    $chk = $db->query("SELECT id_seccion_config FROM tbl_doc_secciones_config WHERE id_tipo_config=$idTipo AND seccion_key='{$sec['seccion_key']}'");
    if ($chk->num_rows > 0) {
        $existentes++;
        continue;
    }
    $stmt = $db->prepare("INSERT INTO tbl_doc_secciones_config
        (id_tipo_config, numero, nombre, seccion_key, prompt_ia, tipo_contenido, es_obligatoria, orden, activo)
        VALUES (?, ?, ?, ?, ?, ?, 1, ?, 1)");
    $stmt->bind_param('iissssi',
        $idTipo, $sec['numero'], $sec['nombre'], $sec['seccion_key'],
        $sec['prompt_ia'], $sec['tipo_contenido'], $sec['orden']
    );
    $stmt->execute();
    if ($stmt->error) {
        echo "ERROR sección {$sec['seccion_key']}: " . $stmt->error . "\n";
        exit(1);
    }
    $insertados++;
}

echo "Secciones insertadas: $insertados | ya existian: $existentes\n";
echo "OK - politica_acoso_sexual lista en BD\n";
