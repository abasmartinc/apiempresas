# -*- coding: utf-8 -*-
import os
import sys
import json
import requests
import pymysql
from dotenv import load_dotenv

# No redireccionamos stdout para ver errores directos
print("DEBUG: Script inicializado")

# Load .env
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")

def mysql_connect():
    """Conexión manual usando las variables del .env de CodeIgniter."""
    return pymysql.connect(
        host=os.getenv("database.default.hostname", "217.61.210.127"),
        user=os.getenv("database.default.username", "apiempresas_user"),
        password=os.getenv("database.default.password", "WONwyjpsmx3h3$@2"),
        database=os.getenv("database.default.database", "reseller3537_apiempresas"),
        port=int(os.getenv("database.default.port", "3306")),
        charset="utf8mb4",
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=True
    )

def get_cnae_from_ai(objeto_social):
    if not OPENAI_API_KEY or "sk-" not in OPENAI_API_KEY:
        print("    [ERROR] API Key de OpenAI no válida.")
        return None, None

    prompt = f"""Analiza el siguiente 'Objeto Social' de una empresa española y clasifícalo en el código CNAE-2009 de 4 dígitos más preciso.
Objeto Social: "{objeto_social}"
Responde EXCLUSIVAMENTE en formato JSON con esta estructura:
{{ "code": "####", "label": "Descripción oficial del sector" }}
"""
    try:
        url = "https://api.openai.com/v1/chat/completions"
        headers = { "Authorization": f"Bearer {OPENAI_API_KEY}", "Content-Type": "application/json" }
        payload = {
            "model": "gpt-4o-mini",
            "messages": [
                {"role": "system", "content": "Eres un experto en clasificación CNAE de España."},
                {"role": "user", "content": prompt}
            ],
            "response_format": { "type": "json_object" },
            "temperature": 0.1
        }
        print(f"    [AI] Llamando a OpenAI (gpt-4o-mini)...")
        resp = requests.post(url, headers=headers, json=payload, timeout=20)
        print(f"    [AI] Respuesta recibida: {resp.status_code}")
        if resp.status_code == 200:
            data = resp.json()
            result = json.loads(data['choices'][0]['message']['content'].strip())
            return result.get("code"), result.get("label")
        else:
            print(f"    [AI-ERROR] Status {resp.status_code}: {resp.text}")
    except Exception as e:
        print(f"    [AI-EXCEP] {e}")
    return None, None

def run():
    print("[*] Buscando empresas para clasificar por IA...")
    try:
        conn = mysql_connect()
        processed = 0
        with conn.cursor() as cur:
            cur.execute("""
                SELECT id, company_name, objeto_social 
                FROM companies 
                WHERE (cnae_label IS NULL OR cnae_label = '') 
                  AND objeto_social IS NOT NULL 
                  AND TRIM(objeto_social) != ''
                  AND created_at >= DATE_SUB(CURDATE(), INTERVAL 5 DAY)
                LIMIT 500
            """)
            companies = cur.fetchall()
            print(f"[*] Total a procesar en esta batida (máx 500): {len(companies)}")

            for co in companies:
                processed += 1
                code, label = get_cnae_from_ai(co['objeto_social'])
                if code and label:
                    cur.execute("UPDATE companies SET cnae_code = %s, cnae_label = %s WHERE id = %s", (code, label, co['id']))
                    print(f"  [{processed}] {co['company_name'][:30]} -> {code}")
                else:
                    print(f"  [{processed}] {co['company_name'][:30]} -> FALLO")
        conn.close()
        print("[FIN] Proceso terminado.")
    except Exception as e:
        print(f"[ERROR CRITICO] {e}")

if __name__ == "__main__":
    run()
