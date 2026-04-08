"""
Importar Compendio Legal MMB Excel → tabla matriz_legal (v2)
Ejecutar: python app/SQL/importar_excel_matriz_legal.py

Lee el Excel, parsea tipo_norma + id_norma_legal, normaliza estado, inserta en LOCAL y PRODUCCION.
"""

import openpyxl
import re
import mysql.connector
import sys
from datetime import datetime

# ====== CONFIGURACION ======
EXCEL_PATH = r"D:\DOCUMENTOS EDI\SST\Compendio Legal MMB_07042026.xlsx"

LOCAL_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'empresas_sst',
    'port': 3306,
    'charset': 'utf8mb4'
}

PROD_CONFIG = {
    'host': 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'user': 'cycloid_userdb',
    'password': 'AVNS_MR2SLvzRh3i_7o9fEHN',
    'database': 'empresas_sst',
    'port': 25060,
    'charset': 'utf8mb4',
    'ssl_disabled': False
}

# Mapeo hoja → categoria
HOJAS = {
    'Medicina Laboral': 'Medicina Laboral',
    'Sistema General de SSS': 'Sistema General de SSS',
    'Seguridad e Higiene Industrial': 'Seguridad e Higiene Industrial',
    'COVID 19': 'COVID 19',
    'Ambiente Nacional': 'Ambiente Nacional',
    'Ambiente Regional': 'Ambiente Regional',
    'Ambiente Bogota': 'Ambiente Bogotá',
}

# Mapeo de estructura por hoja (indice de columna, base 0)
# Algunas hojas tienen CLASIFICACION en col C, otras no
ESTRUCTURA = {
    'Medicina Laboral':              {'clasificacion': 2, 'tema': 3, 'subtema': 4, 'norma': 5, 'expedida': 6, 'fecha': 7, 'anio': 8, 'vigencia': 9, 'tematica': 10},
    'Sistema General de SSS':       {'clasificacion': 2, 'tema': 3, 'subtema': 4, 'norma': 5, 'expedida': 6, 'fecha': 7, 'anio': 8, 'vigencia': 9, 'tematica': 10},
    'Seguridad e Higiene Industrial': {'tema': 2, 'subtema': 3, 'norma': 4, 'expedida': 5, 'fecha': 6, 'anio': 7, 'vigencia': 8, 'tematica': 9},
    'COVID 19':                      {'tema': 2, 'subtema': 3, 'norma': 4, 'expedida': 5, 'fecha': 6, 'anio': 7, 'vigencia': 8, 'tematica': 9},
    'Ambiente Nacional':             {'clasificacion': 1, 'tema': 2, 'subtema': 3, 'norma': 4, 'expedida': 5, 'fecha': 6, 'anio': 7, 'vigencia': 8, 'tematica': 9},
    'Ambiente Regional':             {'clasificacion': 2, 'tema': 3, 'subtema': 4, 'norma': 5, 'expedida': 6, 'fecha': 7, 'anio': 8, 'vigencia': 9, 'tematica': 10},
    'Ambiente Bogota':               {'clasificacion': 1, 'tema': 2, 'subtema': 3, 'norma': 4, 'expedida': 5, 'fecha': 6, 'anio': 7, 'vigencia': 8, 'tematica': 9},
}


def limpiar(val):
    """Limpia un valor de celda"""
    if val is None:
        return ''
    s = str(val).strip()
    # Quitar saltos de linea extras
    s = re.sub(r'\s+', ' ', s)
    return s


def parsear_norma(norma_raw):
    """
    Separa 'RESOLUCIÓN 2400' → ('Resolución', '2400')
    Separa 'Ley 100 de 1993' → ('Ley', '100')
    Separa 'Decreto 1072 de 2015' → ('Decreto', '1072')
    """
    norma = limpiar(norma_raw)
    if not norma:
        return ('', '')

    # Patrones comunes
    patrones = [
        r'^(LEY|DECRETO LEY|DECRETO|RESOLUCI[OÓ]N|CIRCULAR\s*EXTERNA|CIRCULAR|ACUERDO|SENTENCIA|CONCEPTO|NORMA\s+T[EÉ]CNICA|C[OÓ]DIGO)\s+(.+)',
    ]

    for patron in patrones:
        m = re.match(patron, norma, re.IGNORECASE)
        if m:
            tipo = m.group(1).strip().title()
            resto = m.group(2).strip()

            # Normalizar tipos
            tipo = tipo.replace('Resolucion', 'Resolución')
            tipo = tipo.replace('Codigo', 'Código')
            tipo = tipo.replace('Tecnica', 'Técnica')

            # Extraer numero del resto (ej: "2400 de 1979" → "2400")
            num_match = re.match(r'^(\d+[\w.-]*)', resto)
            if num_match:
                numero = num_match.group(1)
            else:
                numero = resto[:50]  # Si no hay numero, tomar lo que haya

            return (tipo, numero)

    # Si empieza con "Constitución..."
    if re.match(r'^Constituci', norma, re.IGNORECASE):
        return ('Constitución', '')

    # Fallback: todo como tipo_norma
    return (norma[:100], '')


def normalizar_estado(vigencia):
    """Normaliza el estado: vigente/derogada/modificada"""
    v = limpiar(vigencia).lower()
    if 'derogad' in v:
        return 'derogada'
    elif 'modificad' in v:
        return 'modificada'
    else:
        return 'activa'


def parsear_fecha(fecha_val):
    """Convierte fecha a formato YYYY-MM-DD"""
    if fecha_val is None:
        return None
    if isinstance(fecha_val, datetime):
        return fecha_val.strftime('%Y-%m-%d')
    s = str(fecha_val).strip()
    # Intentar parsear formatos comunes
    for fmt in ['%Y-%m-%d %H:%M:%S', '%Y-%m-%d', '%d/%m/%Y']:
        try:
            return datetime.strptime(s, fmt).strftime('%Y-%m-%d')
        except ValueError:
            continue
    return None


def parsear_anio(anio_val):
    """Extrae año como entero"""
    if anio_val is None:
        return 0
    s = str(anio_val).strip()
    m = re.search(r'(\d{4})', s)
    if m:
        return int(m.group(1))
    return 0


def extraer_valor(row, col_map, campo):
    """Extrae valor de una fila según el mapa de columnas"""
    if campo not in col_map:
        return ''
    idx = col_map[campo]
    if idx < len(row):
        return row[idx]
    return ''


def leer_excel():
    """Lee todas las hojas del Excel y retorna lista de registros"""
    print(f"[INFO] Leyendo Excel: {EXCEL_PATH}")
    wb = openpyxl.load_workbook(EXCEL_PATH, read_only=True, data_only=True)

    registros = []

    for hoja_nombre, categoria in HOJAS.items():
        ws = wb[hoja_nombre]
        col_map = ESTRUCTURA[hoja_nombre]
        count = 0

        for row_num, row in enumerate(ws.iter_rows(values_only=True), 1):
            # Saltar encabezados (filas <= 15)
            if row_num <= 15:
                continue

            # Saltar filas vacías
            row_list = list(row)
            tema_val = extraer_valor(row_list, col_map, 'tema')
            norma_val = extraer_valor(row_list, col_map, 'norma')
            if not tema_val and not norma_val:
                continue

            # Extraer campos
            clasificacion = limpiar(extraer_valor(row_list, col_map, 'clasificacion'))
            tema = limpiar(tema_val)
            subtema = limpiar(extraer_valor(row_list, col_map, 'subtema'))
            norma_raw = extraer_valor(row_list, col_map, 'norma')
            autoridad = limpiar(extraer_valor(row_list, col_map, 'expedida'))
            fecha_raw = extraer_valor(row_list, col_map, 'fecha')
            anio_raw = extraer_valor(row_list, col_map, 'anio')
            vigencia = extraer_valor(row_list, col_map, 'vigencia')
            tematica = limpiar(extraer_valor(row_list, col_map, 'tematica'))

            # Parsear
            tipo_norma, id_norma = parsear_norma(norma_raw)
            estado = normalizar_estado(vigencia)
            fecha = parsear_fecha(fecha_raw)
            anio = parsear_anio(anio_raw)

            # Si no hay tema, usar clasificacion
            if not tema and clasificacion:
                tema = clasificacion

            # Validar mínimos
            if not tema and not tipo_norma:
                continue

            registros.append({
                'categoria': categoria,
                'clasificacion': clasificacion,
                'tema': tema[:255],
                'subtema': subtema[:255],
                'tipo_norma': tipo_norma[:100],
                'id_norma_legal': id_norma[:50],
                'anio': anio,
                'fecha_expedicion': fecha,
                'descripcion_norma': tematica,
                'autoridad_emisora': autoridad[:255],
                'estado': estado,
            })
            count += 1

        print(f"  {hoja_nombre}: {count} registros")

    wb.close()
    print(f"[INFO] Total registros extraidos: {len(registros)}")
    return registros


def insertar_registros(config, nombre, registros):
    """Inserta registros en la BD"""
    print(f"\n========== INSERTANDO EN {nombre} ==========")

    try:
        conn = mysql.connector.connect(**config)
        cursor = conn.cursor()
        print(f"[OK] Conexion exitosa a {nombre}")

        sql = """INSERT INTO matriz_legal
            (categoria, clasificacion, tema, subtema, tipo_norma, id_norma_legal,
             anio, fecha_expedicion, descripcion_norma, autoridad_emisora, estado)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"""

        batch_size = 100
        total = len(registros)
        insertados = 0

        for i in range(0, total, batch_size):
            batch = registros[i:i + batch_size]
            values = []
            for r in batch:
                values.append((
                    r['categoria'],
                    r['clasificacion'] or None,
                    r['tema'],
                    r['subtema'] or None,
                    r['tipo_norma'],
                    r['id_norma_legal'],
                    r['anio'],
                    r['fecha_expedicion'],
                    r['descripcion_norma'] or None,
                    r['autoridad_emisora'] or None,
                    r['estado'],
                ))
            cursor.executemany(sql, values)
            conn.commit()
            insertados += len(batch)
            print(f"  Progreso: {insertados}/{total}")

        # Verificar
        cursor.execute("SELECT COUNT(*) FROM matriz_legal")
        count = cursor.fetchone()[0]
        print(f"[OK] {nombre}: {count} registros en tabla matriz_legal")

        # Stats por categoria
        cursor.execute("SELECT categoria, COUNT(*) as n FROM matriz_legal GROUP BY categoria ORDER BY n DESC")
        print(f"[INFO] Desglose por categoria:")
        for row in cursor.fetchall():
            print(f"  - {row[0]}: {row[1]}")

        cursor.close()
        conn.close()
        return True

    except Exception as e:
        print(f"[ERROR] {nombre}: {e}")
        return False


# ====== MAIN ======
if __name__ == '__main__':
    print("=== IMPORTACION COMPENDIO LEGAL -> MATRIZ LEGAL v2 ===")
    print(f"Fecha: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")

    # Paso 1: Leer Excel
    registros = leer_excel()

    if not registros:
        print("[ERROR] No se extrajeron registros del Excel")
        sys.exit(1)

    # Paso 2: Insertar en LOCAL
    local_ok = insertar_registros(LOCAL_CONFIG, 'LOCAL', registros)

    if not local_ok:
        print("\n[ABORT] LOCAL fallo. NO se ejecuta en PRODUCCION.")
        sys.exit(1)

    # Paso 3: Insertar en PRODUCCION
    print("\n[INFO] LOCAL OK -> Insertando en PRODUCCION...")
    prod_ok = insertar_registros(PROD_CONFIG, 'PRODUCCION', registros)

    # Resumen
    print(f"\n========== RESUMEN ==========")
    print(f"Registros extraidos del Excel: {len(registros)}")
    print(f"LOCAL:      {'OK' if local_ok else 'ERROR'}")
    print(f"PRODUCCION: {'OK' if prod_ok else 'ERROR'}")

    if local_ok and prod_ok:
        print("\n[LISTO] Importacion completada en ambos entornos.")
