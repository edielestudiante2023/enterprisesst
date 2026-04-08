"""
Paso 1: Extraer datos del Excel y exportar a CSV limpio
Ejecutar: python app/SQL/paso1_excel_a_csv.py
"""
import openpyxl
import re
import csv
import sys
import os
from datetime import datetime

EXCEL_PATH = r"D:\DOCUMENTOS EDI\SST\Compendio Legal MMB_07042026.xlsx"
CSV_PATH = os.path.join(os.path.dirname(__file__), 'matriz_legal_import.csv')

HOJAS = {
    'Medicina Laboral': 'Medicina Laboral',
    'Sistema General de SSS': 'Sistema General de SSS',
    'Seguridad e Higiene Industrial': 'Seguridad e Higiene Industrial',
    'COVID 19': 'COVID 19',
    'Ambiente Nacional': 'Ambiente Nacional',
    'Ambiente Regional': 'Ambiente Regional',
    'Ambiente Bogota': 'Ambiente Bogota',
}

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
    if val is None:
        return ''
    s = str(val).strip()
    s = re.sub(r'\s+', ' ', s)
    return s


def parsear_norma(norma_raw):
    norma = limpiar(norma_raw)
    if not norma:
        return ('', '')
    m = re.match(
        r'^(LEY|DECRETO\s+LEY|DECRETO|RESOLUCI[OÓ]N|CIRCULAR\s*EXTERNA|CIRCULAR|ACUERDO|SENTENCIA|CONCEPTO|NORMA\s+T[EÉ]CNICA|C[OÓ]DIGO)\s+(.+)',
        norma, re.IGNORECASE)
    if m:
        tipo = m.group(1).strip().title()
        resto = m.group(2).strip()
        tipo = tipo.replace('Resolucion', 'Resolucion')
        num_match = re.match(r'^(\d+[\w.-]*)', resto)
        numero = num_match.group(1) if num_match else resto[:50]
        return (tipo, numero)
    if re.match(r'^Constituci', norma, re.IGNORECASE):
        return ('Constitucion', '')
    return (norma[:100], '')


def normalizar_estado(vigencia):
    v = limpiar(vigencia).lower()
    if 'derogad' in v:
        return 'derogada'
    elif 'modificad' in v:
        return 'modificada'
    return 'activa'


def parsear_fecha(fecha_val):
    if fecha_val is None:
        return ''
    if isinstance(fecha_val, datetime):
        return fecha_val.strftime('%Y-%m-%d')
    s = str(fecha_val).strip()
    for fmt in ['%Y-%m-%d %H:%M:%S', '%Y-%m-%d', '%d/%m/%Y']:
        try:
            return datetime.strptime(s, fmt).strftime('%Y-%m-%d')
        except ValueError:
            continue
    return ''


def parsear_anio(anio_val):
    if anio_val is None:
        return 0
    s = str(anio_val).strip()
    m = re.search(r'(\d{4})', s)
    return int(m.group(1)) if m else 0


def extraer_valor(row, col_map, campo):
    if campo not in col_map:
        return ''
    idx = col_map[campo]
    if idx < len(row):
        return row[idx]
    return ''


def main():
    print("=== EXTRACCION EXCEL -> CSV ===")
    print(f"Excel: {EXCEL_PATH}")
    print(f"CSV destino: {CSV_PATH}\n")

    wb = openpyxl.load_workbook(EXCEL_PATH, read_only=True, data_only=True)
    registros = []

    for hoja_nombre, categoria in HOJAS.items():
        ws = wb[hoja_nombre]
        col_map = ESTRUCTURA[hoja_nombre]
        count = 0

        for row_num, row in enumerate(ws.iter_rows(values_only=True), 1):
            if row_num <= 15:
                continue
            row_list = list(row)
            tema_val = extraer_valor(row_list, col_map, 'tema')
            norma_val = extraer_valor(row_list, col_map, 'norma')
            if not tema_val and not norma_val:
                continue

            clasificacion = limpiar(extraer_valor(row_list, col_map, 'clasificacion'))
            tema = limpiar(tema_val)
            subtema = limpiar(extraer_valor(row_list, col_map, 'subtema'))
            norma_raw = extraer_valor(row_list, col_map, 'norma')
            autoridad = limpiar(extraer_valor(row_list, col_map, 'expedida'))
            fecha_raw = extraer_valor(row_list, col_map, 'fecha')
            anio_raw = extraer_valor(row_list, col_map, 'anio')
            vigencia = extraer_valor(row_list, col_map, 'vigencia')
            tematica = limpiar(extraer_valor(row_list, col_map, 'tematica'))

            tipo_norma, id_norma = parsear_norma(norma_raw)
            estado = normalizar_estado(vigencia)
            fecha = parsear_fecha(fecha_raw)
            anio = parsear_anio(anio_raw)

            if not tema and clasificacion:
                tema = clasificacion
            if not tema and not tipo_norma:
                continue

            registros.append([
                categoria, clasificacion, tema[:255], subtema[:255],
                tipo_norma[:100], id_norma[:50], anio, fecha,
                tematica, autoridad[:255], estado
            ])
            count += 1

        print(f"  {hoja_nombre}: {count} registros")

    wb.close()

    # Escribir CSV
    with open(CSV_PATH, 'w', newline='', encoding='utf-8-sig') as f:
        writer = csv.writer(f, delimiter=';', quoting=csv.QUOTE_ALL)
        writer.writerow([
            'categoria', 'clasificacion', 'tema', 'subtema',
            'tipo_norma', 'id_norma_legal', 'anio', 'fecha_expedicion',
            'descripcion_norma', 'autoridad_emisora', 'estado'
        ])
        writer.writerows(registros)

    print(f"\n[OK] CSV generado: {CSV_PATH}")
    print(f"[OK] Total registros: {len(registros)}")


if __name__ == '__main__':
    main()
