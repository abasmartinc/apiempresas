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
    "fill_radar_scores.py --all",
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
    """ Envia un email automatizado por SMTP con el recuento de inserciones del día. """
    host = os.getenv("SMTP_HOST") or os.getenv("email.SMTPHost")
    user = os.getenv("SMTP_USER") or os.getenv("email.SMTPUser")
    password = os.getenv("SMTP_PASS") or os.getenv("email.SMTPPass")
    port_str = os.getenv("SMTP_PORT") or os.getenv("email.SMTPPort") or "465"
    port = int(port_str)
    dest = os.getenv("SUMMARY_EMAIL_TO", "papelo.amh@gmail.com")
    
    if not host or not user or not password:
        log("[EMAIL] Credenciales SMTP faltantes. No se envía el resumen.")
        return
        
    # Obtener totales de base de datos de hoy
    new_companies, new_posts = 0, 0
    conn = mysql_connect()
    try:
        with conn.cursor() as cur:
            cur.execute("SELECT COUNT(*) as c FROM companies WHERE DATE(created_at) = CURDATE()")
            row = cur.fetchone()
            if row: new_companies = row.get('c', 0)
            
            cur.execute("SELECT COUNT(*) as c FROM borme_posts WHERE DATE(created_at) = CURDATE()")
            row = cur.fetchone()
            if row: new_posts = row.get('c', 0)
    except Exception as e:
        log(f"[EMAIL] Error contando estadísticas: {e}")
    finally:
        conn.close()

    subject = f"Resumen diario BORME: {new_companies} nuevas empresas"
    body = f"El pipeline del BORME ha finalizado correctamente hoy.\n\nNuevas empresas insertadas: {new_companies}\nNuevos anuncios BORME procesados: {new_posts}\n\nSaludos,\nEl Bot de APIEmpresas"
    
    msg = MIMEText(body, "plain", "utf-8")
    msg["Subject"] = subject
    msg["From"] = user
    msg["To"] = dest
    
    try:
        log("[EMAIL] Enviando correo de resumen...")
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

