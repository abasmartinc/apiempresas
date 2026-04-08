# -*- coding: utf-8 -*-
import os
import smtplib
from email.mime.text import MIMEText
import pymysql
from dotenv import load_dotenv

# Load .env
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

def mysql_connect():
    return pymysql.connect(
        host="217.61.210.127",
        user="apiempresas_user",
        password="WONwyjpsmx3h3$@2",
        database="reseller3537_apiempresas",
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )

def send_summary_email():
    """ Envia un email automatizado por SMTP con una tabla detallada del enriquecimiento del día. """
    host = os.getenv("SMTP_HOST") or "serv327.controldeservidor.com"
    user = os.getenv("SMTP_USER") or "soporte@apiempresas.es"
    password = os.getenv("SMTP_PASS") or "O6wozTrxljcf2"
    port = 465
    dest = "papelo.amh@gmail.com"
    
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

            # 2. Detail Data for Table
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
        print(f"[EMAIL ERROR] {e}")
    finally:
        conn.close()

    subject = f"Prueba de Informe: {new_companies_count} nuevas y {len(enriched_rows)} enriquecidas"
    
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
                <td>{web}</td>
                <td>{email}</td>
                <td>{phone}</td>
            </tr>
            """
    table_html += "</table>"

    body_html = f"""
    <html>
    <body style="font-family: Arial, sans-serif; color: #333;">
        <h2>Pipeline BORME - Informe Manual de Prueba</h2>
        <p>Este es un resumen de los datos capturados durante la ejecución manual de esta tarde.</p>
        <ul>
            <li><strong>Nuevas empresas detectadas:</strong> {new_companies_count}</li>
            <li><strong>Anuncios procesados:</strong> {new_posts_count}</li>
            <li><strong>Empresas enriquecidas (CIF/Web/Email):</strong> {len(enriched_rows)}</li>
        </ul>
        <h3>Detalle de Resultados</h3>
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
        print("Enviando correo...")
        server = smtplib.SMTP_SSL(host, port)
        server.login(user, password)
        server.sendmail(user, [dest], msg.as_string())
        server.quit()
        print("Correo enviado con éxito.")
    except Exception as e:
        print(f"Error enviando correo: {e}")

if __name__ == "__main__":
    send_summary_email()
