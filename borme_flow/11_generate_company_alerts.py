# -*- coding: utf-8 -*-
"""
11_generate_company_alerts.py
─────────────────────────────
Analiza las publicaciones del BORME para detectar eventos clave y generar alertas
específicas para las empresas que están en la borme_watchlist.
"""

import re
import datetime
from config import mysql_connect

# Configuración de categorías y patrones (Más estrictos para evitar falsos positivos)
# Usamos \b para límites de palabra y anclajes donde sea posible
ALERT_CATEGORIES = {
    'Nueva constitución': r'^Constituci.n\b',
    'Ampliación de capital': r'\bAmpliaci.n de capital\b|\bResultante Suscrito\b',
    'Inversión recibida': r'\bPrima de emisi.n\b', # Más específico que solo ampliación
    'Nuevo administrador': r'^Nombramientos\b',
    'Cambio de objeto social': r'\bCambio de objeto social\b|\bModificaci.n del objeto\b',
    'Fusiones y absorciones': r'\bFusi.n por absorci.n\b|\bProyecto de fusi.n\b|\bEscisi.n\b',
    'Cambio de domicilio social': r'\bCambio de domicilio social\b|\bTraslado de domicilio\b',
    'Reducción de capital': r'\bReducci.n de capital\b',
    'Declaración de insolvencia': r'\bSituaci.n concursal\b|\bInsolvencia\b|\bConcurso de acreedores\b|\bDecreto de insolvencia\b',
    'Proceso de disolución': r'\bDisoluci.n\b|\bLiquidaci.n\b|\bExtinci.n\b',
    'Incidencias registrales': r'\bAnotaci.n preventiva\b|\bEmbargo\b'
}

# Patrones extra para la descripción (Búsqueda en el cuerpo del texto)
EXTRA_PATTERNS = {
    'Centros de trabajo': r'\bApertura de sucursal\b|\bCentro de trabajo\b|\bEstablecimiento\b'
}

def detect_alerts(act_types, description):
    alerts = []
    
    # Normalizamos para búsqueda
    act_types_str = (act_types or "").strip()
    description_str = (description or "").strip()
    
    # 1. Análisis de act_types (Suele ser lo más preciso)
    # Separamos los actos por coma para analizar cada uno individualmente
    acts = [a.strip() for a in act_types_str.split(',')]
    
    for category, pattern in ALERT_CATEGORIES.items():
        # Primero buscamos en los tipos de acto individuales (anclados al inicio si es necesario)
        for act in acts:
            if re.search(pattern, act, re.IGNORECASE):
                alerts.append(category)
                break
            
    # 2. Análisis de descripción (Para categorías que no tienen act_type específico)
    for category, pattern in EXTRA_PATTERNS.items():
        if re.search(pattern, description_str, re.IGNORECASE):
            alerts.append(category)

    # 3. Lógica de "Inversión recibida" vs "Ampliación"
    # Si detectamos Inversión (Prima de emisión), mantenemos ambas o priorizamos.
    # El usuario pidió ambas, así que las dejamos.

    return list(set(alerts))

def run():
    conn = mysql_connect()
    total_alerts = 0
    total_posts = 0

    try:
        with conn.cursor() as cur:
            # Seleccionamos posts que no han sido procesados para alertas
            # y que están vinculados a una empresa.
            # Opcional: Podríamos procesar TODOS los posts para detectar "Nuevas constituciones"
            # aunque no estén en la watchlist aún, pero el usuario pidió monitorizar la watchlist.
            
            cur.execute("""
                SELECT 
                    p.id AS post_id, 
                    p.company_id, 
                    p.act_types, 
                    p.description, 
                    p.borme_date,
                    c.cif,
                    c.company_name
                FROM borme_posts p
                JOIN companies c ON p.company_id = c.id
                WHERE p.alerts_processed = 0
                  AND p.company_id IS NOT NULL
                ORDER BY p.id ASC
                LIMIT 5000
            """)
            posts = cur.fetchall()
            
            if not posts:
                print("[*] No hay nuevos posts para procesar alertas.")
                return

            print(f"[*] Analizando {len(posts)} posts para alertas...")

            # Cargamos la watchlist en memoria para rapidez (o hacemos join en la query)
            # Como la watchlist puede ser grande, mejor un join o un set de IDs.
            cur.execute("SELECT company_id, company_cif FROM borme_watchlist WHERE company_id IS NOT NULL OR company_cif IS NOT NULL")
            watchlist_data = cur.fetchall()
            watched_ids = {row['company_id'] for row in watchlist_data if row['company_id']}
            watched_cifs = {row['company_cif'] for row in watchlist_data if row['company_cif']}

            for post in posts:
                total_posts += 1
                post_id = post['post_id']
                company_id = post['company_id']
                cif = post['cif']
                
                # ¿Está la empresa en la watchlist?
                is_watched = (company_id in watched_ids) or (cif and cif in watched_cifs)
                
                # Caso especial: Si es una constitución, tal vez queremos alertar siempre?
                # Por ahora nos ceñimos a lo que dijo el usuario: borme_watchlist.
                
                if is_watched:
                    detected = detect_alerts(post['act_types'], post['description'])
                    
                    for alert_type in detected:
                        # Evitar duplicados
                        cur.execute("""
                            SELECT id FROM company_alerts 
                            WHERE company_id = %s AND post_id = %s AND alert_type = %s
                            LIMIT 1
                        """, (company_id, post_id, alert_type))
                        
                        if not cur.fetchone():
                            cur.execute("""
                                INSERT INTO company_alerts (company_id, post_id, alert_type, description, borme_date)
                                VALUES (%s, %s, %s, %s, %s)
                            """, (
                                company_id, 
                                post_id, 
                                alert_type, 
                                post['act_types'], # Usamos act_types como descripción corta
                                post['borme_date']
                            ))
                            total_alerts += 1
                
                # Marcar como procesado independientemente de si hubo alerta o no
                cur.execute("UPDATE borme_posts SET alerts_processed = 1 WHERE id = %s", (post_id,))
                
                if total_posts % 500 == 0:
                    conn.commit()
                    print(f"    Procesados {total_posts} registros...")

            conn.commit()
            print(f"\n[OK] Generación de alertas finalizada.")
            print(f"     Posts analizados: {total_posts}")
            print(f"     Alertas creadas: {total_alerts}")

    except Exception as e:
        print(f"[!] Error: {e}")
        conn.rollback()
        raise
    finally:
        conn.close()

if __name__ == "__main__":
    run()
