# -*- coding: utf-8 -*-
import os
import subprocess
import sys
import datetime as dt
import smtplib
from email.mime.text import MIMEText
from config import mysql_connect
from dotenv import load_dotenv
import requests

try:
    import sentry_sdk
except ImportError:
    sentry_sdk = None

# Load .env from same directory
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

# Configurar Sentry si la variable existe
sentry_dsn = os.getenv("SENTRY_DSN")
if sentry_dsn and sentry_sdk:
    sentry_sdk.init(
        dsn=sentry_dsn,
        traces_sample_rate=1.0,
    )


# Lista de scripts a ejecutar en orden
PIPELINE = [
    "01_extract_sumario.py",
    "02_parse_details.py",
    "03_find_new_candidates.py",
    "04_insert_new_companies.py",
    "05_associate_companies.py",
    "06_extract_from_borme_text.py",  # Extrae administradores y fecha de constitución
    "10_ai_cnae_classifier.py",       # Clasifica sector por IA si no viene en el BORME
    "fill_radar_scores.py --all",
    "08_enrich_contacts.py",           # Descubre CIF, Web y Email para los mejores leads
    "07_linkedin_post.py",           # Publica resumen diario en LinkedIn
]

LOG_FILE = os.path.join(os.path.dirname(os.path.abspath(__file__)), "pipeline_execution.log")

def log(message):
    timestamp = dt.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    entry = f"[{timestamp}] {message}"
    print(entry)
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(entry + "\n")

def run_script(script_cmd):
    log(f"--- Ejecutando: {script_cmd} ---")
    
    parts = script_cmd.split()
    script_name = parts[0]
    args = parts[1:]
    
    script_path = os.path.join(os.path.dirname(__file__), script_name)
    
    # Usamos el mismo intérprete de python que está ejecutando este script
    # Esto es crucial en hostings con Virtualenv
    try:
        result = subprocess.run(
            [sys.executable, script_path] + args,
            capture_output=True,
            text=True,
            encoding="utf-8",
            errors="replace",
            check=True
        )
        log(f"Finalizado con éxito: {script_cmd}")
        return True
    except subprocess.CalledProcessError as e:
        err_msg = f"ERROR en {script_cmd}\nSalida de Error: {e.stderr}"
        log(err_msg)
        if sentry_sdk and sentry_dsn:
            sentry_sdk.capture_message(err_msg, level="error")
        return False
    except Exception as e:
        err_msg = f"Excepción inesperada en {script_cmd}: {str(e)}"
        log(err_msg)
        if sentry_sdk and sentry_dsn:
            sentry_sdk.capture_exception(e)
        return False

def clear_radar_cache():
    """
    Llama al endpoint de la web para invalidar el cache de Radar
    tras importar nuevas empresas del BORME.
    """
    base_url   = os.getenv("APP_BASE_URL", "").rstrip("/")
    cache_token = os.getenv("RADAR_CACHE_TOKEN", "")

    if not base_url or not cache_token:
        log("[CACHE] APP_BASE_URL o RADAR_CACHE_TOKEN no configurados en .env — cache NO limpiado.")
        return

    url = f"{base_url}/cron/radar-cache-clear/{cache_token}"
    try:
        resp = requests.get(url, timeout=15)
        try:
            data = resp.json()
            if isinstance(data, dict):
                msg = data.get('message', resp.text)
            else:
                msg = str(data)
        except Exception:
            msg = resp.text
        log(f"[CACHE] Respuesta del servidor: {msg}")
    except Exception as e:
        log(f"[CACHE] Error al limpiar cache: {e}")

def send_summary_email():
    """ Envia un email automatizado por SMTP con una tabla detallada del enriquecimiento del día. """
    host = os.getenv("SMTP_HOST") or os.getenv("email.SMTPHost")
    user = os.getenv("SMTP_USER") or os.getenv("email.SMTPUser")
    password = os.getenv("SMTP_PASS") or os.getenv("email.SMTPPass")
    port_str = os.getenv("SMTP_PORT") or os.getenv("email.SMTPPort") or "465"
    port = int(port_str)
    dest = os.getenv("SUMMARY_EMAIL_TO", "papelo.amh@gmail.com")
    
    if not host or not user or not password:
        log("[EMAIL] Credenciales SMTP faltantes. No se envía el resumen.")
        return
        
    enriched_rows = []
    new_companies_count = 0
    new_posts_count = 0
    
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            # 1. Stats
            cur.execute("""
                SELECT COUNT(DISTINCT company_id) as c 
                FROM borme_posts 
                WHERE DATE(created_at) = CURDATE() 
                  AND act_types LIKE '%Constitu%'
            """)
            r = cur.fetchone()
            if r: new_companies_count = r.get('c', 0)
            
            cur.execute("SELECT COUNT(*) as c FROM borme_posts WHERE DATE(created_at) = CURDATE()")
            r = cur.fetchone()
            if r: new_posts_count = r.get('c', 0)

            # 2. Detail Data for Tablet
            # We look for leads enriched today that were also created/updated today
            sql = """
            SELECT c.company_name, c.cif, ce.website_official, ce.email, ce.phone_enriched
            FROM company_enrichment ce
            JOIN companies c ON ce.company_id = c.id
            WHERE DATE(ce.updated_at) = CURDATE()
            ORDER BY ce.updated_at DESC
            LIMIT 100
            """
            cur.execute(sql)
            enriched_rows = cur.fetchall()
            
    except Exception as e:
        log(f"[EMAIL] Error obteniendo datos para el resumen: {e}")
    finally:
        conn.close()

    subject = f"Resumen BORME: {new_companies_count} nuevas empresas y {len(enriched_rows)} enriquecidas"
    
    # HTML Table Construction
    table_html = """
    <table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;">
        <tr style="background-color: #f2f2f2;">
            <th>Empresa</th>
            <th>CIF</th>
            <th>Web Oficial</th>
            <th>Email</th>
            <th>Teléfono</th>
        </tr>
    """
    
    if not enriched_rows:
        table_html += "<tr><td colspan='5' style='text-align: center;'>No se han enriquecido empresas hoy todavía.</td></tr>"
    else:
        for row in enriched_rows:
            web = row.get('website_official') or '-'
            email = row.get('email') or '-'
            phone = row.get('phone_enriched') or '-'
            table_html += f"""
            <tr>
                <td>{row.get('company_name')}</td>
                <td>{row.get('cif') or '-'}</td>
                <td><a href='{web}'>{web}</a></td>
                <td>{email}</td>
                <td>{phone}</td>
            </tr>
            """
    table_html += "</table>"

    body_html = f"""
    <html>
    <body style="font-family: Arial, sans-serif; color: #333;">
        <h2>Pipeline BORME - Informe Diario</h2>
        <p>El proceso automatizado de extracción y enriquecimiento ha finalizado hoy.</p>
        <ul>
            <li><strong>Nuevas empresas detectadas:</strong> {new_companies_count}</li>
            <li><strong>Anuncios procesados:</strong> {new_posts_count}</li>
            <li><strong>Empresas enriquecidas (CIF/Web/Email):</strong> {len(enriched_rows)}</li>
        </ul>
        <h3>Detalle de Enriquecimiento (Últimos 100)</h3>
        {table_html}
        <br>
        <p>Saludos,<br><strong>Radar B2B Bot</strong></p>
    </body>
    </html>
    """
    
    msg = MIMEText(body_html, "html", "utf-8")
    msg["Subject"] = subject
    msg["From"] = user
    msg["To"] = dest
    
    try:
        log("[EMAIL] Enviando correo de resumen HTML...")
        if port == 465:
            server = smtplib.SMTP_SSL(host, port)
        else:
            server = smtplib.SMTP(host, port)
            server.starttls()
            
        server.login(user, password)
        server.sendmail(user, [dest], msg.as_string())
        server.quit()
        log("[EMAIL] Correo enviado con éxito.")
    except Exception as e:
        log(f"[EMAIL] Error enviando correo: {e}")
        if sentry_sdk and sentry_dsn:
            sentry_sdk.capture_exception(e)

def main():
    log("==================================================")
    log("INICIANDO PIPELINE DIARIO DE BORME")
    log("==================================================")
    
    success_count = 0
    for script in PIPELINE:
        if run_script(script):
            success_count += 1
        else:
            log(f"Pipeline detenido debido a un fallo en {script}")
            break

    log("==================================================")
    log(f"PIPELINE FINALIZADO. Procesados {success_count}/{len(PIPELINE)} scripts.")
    log("==================================================")

    # Si el pipeline completó todos los pasos
    if success_count == len(PIPELINE):
        log("[CACHE] Pipeline exitoso — limpiando cache de Radar...")
        clear_radar_cache()
        send_summary_email()
    else:
        log("[CACHE] Pipeline incompleto — cache NO limpiado para preservar datos anteriores.")
        # Opcional: podríamos enviar un email de fallo aquí si quisiéramos.

if __name__ == "__main__":
    main()

