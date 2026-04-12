# -*- coding: utf-8 -*-
import os
import sys
import datetime as dt
import requests
import json
import argparse
import pymysql
import json
import argparse
from config import mysql_connect
from dotenv import load_dotenv
import smtplib
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.mime.image import MIMEImage

# Force UTF-8 for console output to handle emojis on Windows
if sys.platform == 'win32':
    import io
    sys.stdout = io.TextIOWrapper(sys.stdout.detach(), encoding='utf-8', errors='replace')
    sys.stderr = io.TextIOWrapper(sys.stderr.detach(), encoding='utf-8', errors='replace')

try:
    from PIL import Image, ImageDraw, ImageFont
    HAS_PILLOW = True
except ImportError:
    HAS_PILLOW = False

# Load .env
_env_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), ".env")
load_dotenv(dotenv_path=_env_path)

class InfographicGenerator:
    """Generates a professional image summary of the day's stats."""
    
    def __init__(self, width=1200, height=627):
        self.width = width
        self.height = height
        self.colors = {
            "bg_dark": (15, 23, 42),      # Slate 900
            "bg_light": (30, 41, 59),     # Slate 800
            "primary": (37, 99, 235),     # Blue 600
            "gold": (255, 184, 0),        # Custom Gold
            "text": (248, 250, 252),      # White/Gray
            "subtitle": (148, 163, 184)   # Slate 400
        }

    def generate(self, stats, output_path="today_summary.png"):
        if not HAS_PILLOW:
            print("[!] Pillow not installed. Skipping image generation.")
            return None

        # 1. Create Background (Gradient-like)
        img = Image.new('RGB', (self.width, self.height), self.colors["bg_dark"])
        draw = ImageDraw.Draw(img)
        
        # Subtle accent circles
        draw.ellipse([600, -200, 1400, 400], fill=(20, 30, 50))
        draw.ellipse([-200, 300, 500, 800], fill=(20, 32, 55))

        # 2. Add Branding Header
        try:
            print("[*] Loading fonts...")
            # For Windows:
            arial_path = "C:\\Windows\\Fonts\\arial.ttf"
            if os.path.exists(arial_path):
                font_title = ImageFont.truetype(arial_path, 48)
                font_huge = ImageFont.truetype(arial_path, 100)
                font_body = ImageFont.truetype(arial_path, 28)
            else:
                print("[!] arial.ttf not found at C:\\Windows\\Fonts\\, using default.")
                font_title = font_huge = font_body = ImageFont.load_default()
        except Exception as e:
            print(f"[!] Font load error: {e}")
            font_title = font_huge = font_body = ImageFont.load_default()

        # "RADAR B2B" Title (Moved down slightly)
        draw.text((60, 60), "RADAR B2B", fill=self.colors["primary"], font=font_title)
        draw.text((340, 60), "| INFORME DIARIO", fill=self.colors["subtitle"], font=font_title)

        # 3. Main Metric
        total = stats["total_companies"]
        draw.text((60, 160), f"{total}", fill=self.colors["gold"], font=font_huge)
        draw.text((60, 260), "NUEVAS EMPRESAS CREADAS HOY", fill=self.colors["text"], font=font_body)

        # 4. Provinces & Sectors Column
        draw.line([60, 310, 450, 310], fill=self.colors["subtitle"], width=2)
        
        y_off = 340
        
        # --- Provinces Section ---
        if stats.get("provinces"):
            # Manual icon (circle)
            draw.ellipse([60, y_off+8, 75, y_off+23], fill=self.colors["primary"])
            draw.text((90, y_off), "Proximidad Geográfica", fill=self.colors["subtitle"], font=font_body)
            y_off += 40
            for p in stats["provinces"][:3]:
                draw.text((90, y_off), f"• {p['registro_mercantil']}: {p['count']} registros", fill=self.colors["text"], font=font_body)
                y_off += 30
            y_off += 25
        else:
            print("[*] No province data. Skipping section in image.")

        # --- Sectors Section ---
        has_sectors = any(count > 0 for count in stats.get("sectors", {}).values())
        if has_sectors:
            # Manual icon (triangle)
            draw.polygon([(60, y_off+23), (75, y_off+8), (90, y_off+23)], fill=self.colors["gold"])
            draw.text((105, y_off), "Sectores en Crecimiento", fill=self.colors["subtitle"], font=font_body)
            y_off += 40
            # Sort sectors by count and take top 3
            sorted_sectors = sorted(stats["sectors"].items(), key=lambda x: x[1], reverse=True)
            for cat, count in sorted_sectors[:3]:
                if count > 0:
                    draw.text((105, y_off), f"• {cat} ({count})", fill=self.colors["text"], font=font_body)
                    y_off += 30
        else:
            print("[*] No sector data. Skipping section in image.")

        # 5. Footer / Logo
        logo_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "logo.png")
        if os.path.exists(logo_path):
            try:
                logo = Image.open(logo_path).convert("RGBA")
                # Resize logo to ~100px width (smaller to avoid overlap)
                w_ratio = 100 / float(logo.size[0])
                new_h = int(float(logo.size[1]) * w_ratio)
                logo = logo.resize((100, new_h), Image.Resampling.LANCZOS)
                # Paste logo in a cleaner position (bottom right)
                img.paste(logo, (self.width - 150, self.height - 180), logo)
            except:
                pass
        
        url_font = font_body # or maybe smaller if needed
        draw.text((self.width - 250, self.height - 70), "apiempresas.es", fill=self.colors["subtitle"], font=url_font)

        img.save(output_path)
        print(f"[*] Infographic generated: {output_path}")
        return output_path

def get_stats(is_test=False):
    """Extracts stats from the database, or returns mock data if is_test=True."""
    if is_test:
        print("[*]   Using MOCK data for testing purposes.")
        return {
            "total_companies": 42,
            "provinces": [
                {"registro_mercantil": "MADRID", "count": 15},
                {"registro_mercantil": "BARCELONA", "count": 12},
                {"registro_mercantil": "VALENCIA", "count": 8}
            ],
            "sectors": {
                "Tecnología/IT": 10,
                "Construcción": 8,
                "Servicios B2B": 7,
                "Hostelería": 5,
                "Energía/Sosten.": 4
            },
            "featured_company": {
                "company_name": "TECHNO SOLUTIONS SL",
                "registro_mercantil": "MADRID",
                "score_total": 92,
                "priority_level": "muy_alta",
                "cnae_label": "Servicios informáticos"
            }
        }

    conn = mysql_connect()
    stats = {
        "total_companies": 0,
        "provinces": [],
        "sectors": {},
        "featured_company": None
    }
    
    try:
        with conn.cursor() as cur:
            # 1. Total nuevas Constituciones hoy (procesadas en esta ejecución)
            cur.execute("""
                SELECT COUNT(DISTINCT company_id) as count 
                FROM borme_posts 
                WHERE created_at >= CURDATE() 
                  AND act_types LIKE '%%Constitu%%'
            """)
            row = cur.fetchone()
            stats["total_companies"] = row['count'] if row else 0
            
            if stats["total_companies"] == 0:
                # Fallback: Estadísticas del último día procesado que tenga Constituciones
                cur.execute("""
                    SELECT DATE(MAX(created_at)) as last_day 
                    FROM borme_posts 
                    WHERE act_types LIKE '%%Constitu%%'
                """)
                last_day_row = cur.fetchone()
                if last_day_row and last_day_row['last_day']:
                    last_day = last_day_row['last_day']
                    cur.execute("""
                        SELECT COUNT(DISTINCT company_id) as count 
                        FROM borme_posts 
                        WHERE created_at >= %s AND created_at < %s + INTERVAL 1 DAY
                          AND act_types LIKE '%%Constitu%%'
                    """, (last_day, last_day))
                    row = cur.fetchone()
                    stats["total_companies"] = row['count'] if row else 0
                    query_date = last_day
                else:
                    query_date = dt.date.today()
            else:
                query_date = dt.date.today()

            print(f"[*] Extracting details for Constitutions on: {query_date}")

            # 2. Top Provincias (de las nuevas Constituciones)
            cur.execute("""
                SELECT c.registro_mercantil, COUNT(DISTINCT p.company_id) as count 
                FROM borme_posts p
                JOIN companies c ON c.id = p.company_id
                WHERE p.created_at >= %s AND p.created_at < %s + INTERVAL 1 DAY
                  AND p.act_types LIKE '%%Constitu%%'
                  AND c.registro_mercantil IS NOT NULL 
                  AND c.registro_mercantil != ''
                GROUP BY c.registro_mercantil 
                ORDER BY count DESC 
                LIMIT 3
            """, (query_date, query_date))
            stats["provinces"] = cur.fetchall()

            # 3. Sectores (de las nuevas Constituciones)
            cur.execute("""
                SELECT DISTINCT c.company_name 
                FROM borme_posts p
                JOIN companies c ON c.id = p.company_id
                WHERE p.created_at >= %s AND p.created_at < %s + INTERVAL 1 DAY
                  AND p.act_types LIKE '%%Constitu%%'
                LIMIT 1000
            """, (query_date, query_date))
            names = cur.fetchall()
            
            categories = {
                "Tecnología/IT": ["tech", "software", "digital", "it", "sistemas", "tecnolog"],
                "Construcción": ["construccion", "reformas", "obras", "viviendas", "edificaci"],
                "Servicios B2B": ["consulting", "asesoria", "gestion", "marketing", "publicidad", "services"],
                "Hostelería": ["restaurante", "hosteleria", "comercio", "tienda", "alimentacion", "hotel"],
                "Energía/Sosten.": ["energia", "solar", "renovables", "electrica", "eol"]
            }
            
            sector_counts = {cat: 0 for cat in categories}
            for n in names:
                name = n['company_name'].lower()
                for cat, keywords in categories.items():
                    if any(kw in name for kw in keywords):
                        sector_counts[cat] += 1
                        break
            
            stats["sectors"] = dict(sorted(sector_counts.items(), key=lambda item: item[1], reverse=True))
            
            # 4. Empresa Destacada (B2B, High Score, Vendible)
            cur.execute("""
                SELECT c.company_name, c.registro_mercantil, crs.score_total, crs.priority_level, c.cnae_label
                FROM borme_posts p
                JOIN companies c ON c.id = p.company_id
                JOIN company_radar_scores crs ON crs.company_id = c.id
                WHERE p.created_at >= %s AND p.created_at < %s + INTERVAL 1 DAY
                  AND p.act_types LIKE '%%Constitu%%'
                  AND (c.cnae_label LIKE '%%Tecnolog%%' OR c.cnae_label LIKE '%%Consult%%' OR c.cnae_label LIKE '%%Inform%%' OR c.cnae_label LIKE '%%Marketing%%' OR c.cnae_label LIKE '%%Publi%%' OR c.cnae_label LIKE '%%Constru%%')
                ORDER BY crs.score_total DESC, c.capital_social_raw DESC
                LIMIT 1
            """, (query_date, query_date))
            featured = cur.fetchone()
            
            if not featured:
                cur.execute("""
                    SELECT c.company_name, c.registro_mercantil, crs.score_total, crs.priority_level, c.cnae_label
                    FROM borme_posts p
                    JOIN companies c ON c.id = p.company_id
                    JOIN company_radar_scores crs ON crs.company_id = c.id
                    WHERE p.created_at >= %s AND p.created_at < %s + INTERVAL 1 DAY
                      AND p.act_types LIKE '%%Constitu%%'
                    ORDER BY crs.score_total DESC
                    LIMIT 1
                """, (query_date, query_date))
                featured = cur.fetchone()
                
            stats["featured_company"] = featured

    except Exception as e:
        print(f"[!] Error gathering stats: {e}")
    finally:
        conn.close()
        
def generate_post(stats):
    """Composes the LinkedIn post text (Local Fallback)."""
    total = stats["total_companies"]
    if total <= 0: return None

    featured = stats.get("featured_company")
    example_block = ""
    if featured:
        example_block = f"""
Ejemplo real detectado hoy:

Empresa: {featured['company_name']}
📍 {featured['registro_mercantil']}
💰 Ticket estimado: 5.000€ – 12.000€
🎯 Probabilidad: Alta

👉 Momento ideal para contactar: ahora"""

    post = f"""Hoy se han creado +{total} nuevas empresas en España 👇

Contexto rápido de hoy:
- Total: {total} empresas nuevas.
- Provincias top: {", ".join([p['registro_mercantil'] for p in stats["provinces"]])}.
- Sectores: {", ".join(list(stats["sectors"].keys())[:2])}.

Pero esto es lo importante 👇

👉 Muchas están en sus primeros días
👉 Sin proveedores definidos
👉 Con necesidad inmediata de servicios
{example_block}

Estamos detectando este tipo de oportunidades automáticamente y mostrando a quién contactar, por qué y qué decirle.

Si trabajas con empresas (agencia, SaaS, asesoría...) esto te interesa.

Puedes ver ejemplos reales aquí:
https://apiempresas.es/radar-demo
"""
    return post

def generate_post_with_gpt(stats):
    """Generates a high-impact LinkedIn post using OpenAI ChatGPT."""
    api_key = os.getenv("OPENAI_API_KEY")
    if not api_key or "sk-" not in api_key:
        print("[!] OpenAI API Key missing or invalid. Falling back to local template.")
        return None

    today_str = dt.date.today().strftime("%d/%m/%Y")
    total = stats["total_companies"]
    prov_list = [f"{p['registro_mercantil']} ({p['count']})" for p in stats["provinces"]]
    top_provinces = ", ".join(prov_list)
    
    sector_summary = ""
    for cat, count in list(stats["sectors"].items())[:3]:
        if count > 0:
            percentage = round((count / total) * 100, 1)
            sector_summary += f"- {cat}: {percentage}%\n"

    featured = stats.get("featured_company")
    example_data = ""
    if featured:
        example_data = f"""
        Empresa: {featured['company_name']}
        Provincia: {featured['registro_mercantil']}
        Sector: {featured['cnae_label']}
        Score Radar: {featured['score_total']} (Sobre 100)
        """

    prompt = f"""Eres un experto en Social Selling B2B y Growth Marketing. Crea un post de LinkedIn irresistible basado en estos datos del BORME ({today_str}).

DATOS CLAVE:
- Total empresas creadas hoy: {total}
- Provincias top: {top_provinces}
- Sectores activos: {sector_summary}
- Ejemplo de oportunidad destacada: {example_data}

REGLAS DE ORO:
- OBJETIVO: Generar leads y tráfico a la demo. Que el lector piense "aquí hay dinero".
- TONO: Humano, directo, cero corporativo. Nada de "ecosistema" o "innovación". Habla de clientes y dinero.
- LONGITUD: 10-14 líneas. Frases cortas. 2-4 emojis (💰 🎯 📈 ⚡).
- ESTRUCTURA:
  1. HOOK: "Hoy se han creado +{total} empresas en España 👇" o similar.
  2. CONTEXTO: Datos de provincias y sectores (formato lista corta).
  3. CAMBIO A OPORTUNIDAD: "Pero esto es lo importante 👇" + 3 bullets sobre por qué es una oportunidad ahora.
  4. EJEMPLO REAL: Formato exacto:
     Empresa: {featured['company_name'] if featured else 'Nombre'}
     📍 {featured['registro_mercantil'] if featured else 'Provincia'}
     💰 Ticket estimado: 5.000€ – 12.000€
     🎯 Probabilidad: Alta
     👉 Momento ideal para contactar: ahora
  5. VALOR: Breve (detectamos automáticamente quién, por qué y qué decir).
  6. TARGET: "Si trabajas con empresas (agencia, SaaS, asesoría...)"
  7. CTA: "Puedes ver ejemplos reales aquí: https://apiempresas.es/radar-demo"

QUIERO 2 VARIANTES:
1. VERSIÓN ESTÁNDAR: Equilibrada y profesional.
2. VERSIÓN AGRESIVA: Más directa a negocio y dinero, enfocada en la oportunidad perdida si no actúan.

Escribe ambas variantes separadas por una línea de guiones. Escribe directamente en español."""

    try:
        url = "https://api.openai.com/v1/chat/completions"
        headers = {
            "Authorization": f"Bearer {api_key}",
            "Content-Type": "application/json"
        }
        payload = {
            "model": "gpt-4o",
            "messages": [
                {"role": "system", "content": "Eres un asistente experto en copy para redes sociales profesionales."},
                {"role": "user", "content": prompt}
            ],
            "temperature": 0.7
        }
        
        print("[*] Calling OpenAI for AI post generation...")
        resp = requests.post(url, headers=headers, json=payload, timeout=30)
        if resp.status_code == 200:
            data = resp.json()
            ai_content = data['choices'][0]['message']['content'].strip()
            print("[OK] AI post content generated successfully!")
            return ai_content
        else:
            print(f"[!] OpenAI Error: {resp.status_code} - {resp.text}")
            return None
    except Exception as e:
        print(f"[!] Exception calling OpenAI: {e}")
        return None

def publish_to_linkedin(content, image_path=None):
    """Sends the post to LinkedIn API with optional image."""
    token = os.getenv("LINKEDIN_ACCESS_TOKEN")
    author_urn = os.getenv("LINKEDIN_URN")
    enabled = os.getenv("LINKEDIN_POST_ENABLED", "false").lower() == "true"

    if not enabled:
        print("[!] LinkedIn post is disabled (LINKEDIN_POST_ENABLED=false)")
        return False

    if not token or not author_urn or "TU_TOKEN_AQUI" in token:
        print("[!] LinkedIn credentials missing. Set LINKEDIN_ACCESS_TOKEN and LINKEDIN_URN in .env")
        return False

    headers = {
        "Authorization": f"Bearer {token}",
        "Content-Type": "application/json",
        "X-Restli-Protocol-Version": "2.0.0"
    }

    # --- Step 1: Media Registration (if image exists) ---
    asset_urn = None
    if image_path and os.path.exists(image_path):
        print(f"[*] Registering image upload: {image_path}")
        register_url = "https://api.linkedin.com/v2/assets?action=registerUpload"
        register_payload = {
            "registerUploadRequest": {
                "recipes": ["urn:li:digitalmediaRecipe:feedshare-image"],
                "owner": author_urn,
                "serviceRelationships": [{"relationshipType": "OWNER", "identifier": "urn:li:userGeneratedContent"}]
            }
        }
        
        reg_resp = requests.post(register_url, headers=headers, json=register_payload)
        if reg_resp.status_code == 200:
            reg_data = reg_resp.json()
            upload_url = reg_data["value"]["uploadMechanism"]["com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest"]["uploadUrl"]
            asset_urn = reg_data["value"]["asset"]
            
            # Step 2: Push Binary Data
            print("[*] Uploading image binary...")
            with open(image_path, 'rb') as f:
                put_resp = requests.put(upload_url, data=f.read())
            
            if put_resp.status_code != 201:
                print(f"[!] Error uploading binary: {put_resp.status_code}")
                asset_urn = None
        else:
            print(f"[!] Error registering upload: {reg_resp.text}")

    # --- Step 3: Create Post ---
    post_url = "https://api.linkedin.com/v2/ugcPosts"
    
    share_content = {
        "shareCommentary": {"text": content},
        "shareMediaCategory": "IMAGE" if asset_urn else "NONE"
    }
    
    if asset_urn:
        share_content["media"] = [{"status": "READY", "media": asset_urn}]

    payload = {
        "author": author_urn,
        "lifecycleState": "PUBLISHED",
        "specificContent": {
            "com.linkedin.ugc.ShareContent": share_content
        },
        "visibility": {
            "com.linkedin.ugc.MemberNetworkVisibility": "PUBLIC"
        }
    }

    try:
        response = requests.post(post_url, headers=headers, json=payload)
        if response.status_code == 201:
            print("[OK] Post shared successfully on LinkedIn!")
            return True
        else:
            print(f"[!] Error sharing post: {response.status_code} - {response.text}")
            return False
    except Exception as e:
        print(f"[!] Exception during LinkedIn post: {e}")
        return False

def send_via_email(content, image_path=None):
    """Sends the post content and image via SMTP email fallback."""
    host = os.getenv("SMTP_HOST")
    user = os.getenv("SMTP_USER")
    password = os.getenv("SMTP_PASS")
    port = int(os.getenv("SMTP_PORT", "465"))
    dest = os.getenv("SUMMARY_EMAIL_TO", "papelo.amh@gmail.com")
    
    if not host or not user or not password:
        print("[!] SMTP credentials missing in .env. Cannot send email.")
        return False

    print(f"[*] Preparing email for: {dest}")
    msg = MIMEMultipart()
    msg["Subject"] = f"🚀 Publicación LinkedIn Lista - {dt.date.today().strftime('%d/%m/%Y')}"
    msg["From"] = user
    msg["To"] = dest

    # Add text body
    body = f"Aquí tienes el contenido para tu publicación de LinkedIn:\n\n{'-'*40}\n{content}\n{'-'*40}\n\nAdjunto encontrarás la infografía generada."
    msg.attach(MIMEText(body, "plain", "utf-8"))

    # Attach image if exists
    if image_path and os.path.exists(image_path):
        try:
            with open(image_path, 'rb') as f:
                img_data = f.read()
                image = MIMEImage(img_data, name=os.path.basename(image_path))
                msg.attach(image)
            print(f"[*] Image attached: {image_path}")
        except Exception as e:
            print(f"[!] Error attaching image to email: {e}")

    try:
        if port == 465:
            server = smtplib.SMTP_SSL(host, port)
        else:
            server = smtplib.SMTP(host, port)
            server.starttls()
            
        server.login(user, password)
        server.sendmail(user, [dest], msg.as_string())
        server.quit()
        print("[OK] Email sent successfully with the LinkedIn content!")
        return True
    except Exception as e:
        print(f"[!] Error sending email: {e}")
        return False

def main():
    parser = argparse.ArgumentParser(description="LinkedIn Dynamic Summary Bot")
    parser.add_argument("--dry-run", action="store_true", help="Preview post and image locally")
    parser.add_argument("--test", action="store_true", help="Use test data (mock stats)")
    args = parser.parse_args()

    print("[*] Gathering stats...")
    stats = get_stats(is_test=args.test)
    print(f"[*] Stats ready: {stats['total_companies']} companies summary.")
    
    if stats["total_companies"] == 0:
        print("[*] No data found for the last 24h. Try running after a BORME flow.")
        return

    # Image Generation
    img_path = None
    if HAS_PILLOW:
        print("[*] Generating infographic...")
        gen = InfographicGenerator()
        try:
            img_path = gen.generate(stats)
            print(f"[*] Image generated at: {img_path}")
        except Exception as e:
            print(f"[!] Error generating infographic: {e}")
    
    print("[*] Generating post content...")
    # Try AI generation first
    post_content = generate_post_with_gpt(stats)
    
    if not post_content:
        print("[!] Failed to generate post content.")
        return

    # If it is multiple variants, we might want to split them for publishing if publishing is enabled
    # but for now we keep the full content for preview/email.
    # publish_to_linkedin currently takes 'content' as a whole. 
    # If the user wants to publish TWO posts, that's different, but for now they likely want to choose one.
    
    if args.dry_run:
        print("\n=== PREVISUALIZACIÓN DE POSTS ===")
        print(post_content)
        if img_path:
            print(f"\n📊 Infografía generada en: {os.path.abspath(img_path)}")
        else:
            print("\n⚠️ Nota: Pillow no está instalado, no se ha generado infografía.")
        print("=======================================\n")
    else:
        # Check if we should use email fallback
        send_email = os.getenv("LINKEDIN_SEND_EMAIL", "false").lower() == "true"
        
        if send_email:
            send_via_email(post_content, img_path)
        else:
            publish_to_linkedin(post_content, img_path)

if __name__ == "__main__":
    main()
