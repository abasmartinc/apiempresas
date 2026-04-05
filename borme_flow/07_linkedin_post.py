# -*- coding: utf-8 -*-
import os
import sys
import datetime
import requests
import json
import argparse
import pymysql
import json
import argparse
from config import mysql_connect
from dotenv import load_dotenv

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

        # "RADAR B2B" Title
        draw.text((60, 50), "RADAR B2B", fill=self.colors["primary"], font=font_title)
        draw.text((340, 50), "| INFORME DIARIO", fill=self.colors["subtitle"], font=font_title)

        # 3. Main Metric
        total = stats["total_companies"]
        draw.text((60, 160), f"{total}", fill=self.colors["gold"], font=font_huge)
        draw.text((60, 260), "NUEVAS EMPRESAS CREADAS HOY", fill=self.colors["text"], font=font_body)

        # 4. Provinces & Sectors Column
        draw.line([60, 320, 600, 320], fill=self.colors["subtitle"], width=2)
        
        y_off = 360
        draw.text((60, y_off), "📍 Proximidad Económica", fill=self.colors["subtitle"], font=font_body)
        y_off += 40
        for p in stats["provinces"][:3]:
            draw.text((80, y_off), f"• {p['registro_mercantil']}: {p['count']} registros", fill=self.colors["text"], font=font_body)
            y_off += 35

        y_off += 40
        draw.text((60, y_off), "📈 Sectores Relevantes", fill=self.colors["subtitle"], font=font_body)
        y_off += 40
        for cat, count in list(stats["sectors"].items())[:3]:
            if count > 0:
                draw.text((80, y_off), f"• {cat} ({count})", fill=self.colors["text"], font=font_body)
                y_off += 35

        # 5. Footer / Logo
        logo_path = os.path.join(os.path.dirname(os.path.dirname(__file__)), "logo.png")
        if os.path.exists(logo_path):
            try:
                logo = Image.open(logo_path).convert("RGBA")
                # Resize logo to ~150px width
                w_ratio = 150 / float(logo.size[0])
                new_h = int(float(logo.size[1]) * w_ratio)
                logo = logo.resize((150, new_h), Image.Resampling.LANCZOS)
                img.paste(logo, (self.width - 200, self.height - 100), logo)
            except:
                pass
        
        draw.text((self.width - 280, self.height - 50), "apiempresas.es", fill=self.colors["subtitle"], font=font_body)

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
            }
        }

    conn = mysql_connect()
    stats = {
        "total_companies": 0,
        "provinces": [],
        "sectors": {}
    }
    
    try:
        with conn.cursor() as cur:
            # 1. Total companies today (optimized range scan)
            cur.execute("SELECT COUNT(*) as count FROM companies WHERE created_at >= CURDATE()")
            row = cur.fetchone()
            stats["total_companies"] = row['count'] if row else 0
            
            if stats["total_companies"] == 0:
                # Fallback: Get stats for the LAST DAY that contains any data
                cur.execute("SELECT DATE(MAX(created_at)) as last_day FROM companies")
                last_day_row = cur.fetchone()
                if last_day_row and last_day_row['last_day']:
                    last_day = last_day_row['last_day']
                    cur.execute("SELECT COUNT(*) as count FROM companies WHERE created_at >= %s AND created_at < %s + INTERVAL 1 DAY", (last_day, last_day))
                    row = cur.fetchone()
                    stats["total_companies"] = row['count'] if row else 0
                    query_date = last_day
                else:
                    query_date = dt.date.today()
            else:
                query_date = dt.date.today()

            # 2. Top Provinces
            cur.execute("""
                SELECT registro_mercantil, COUNT(*) as count 
                FROM companies 
                WHERE created_at >= %s AND created_at < %s + INTERVAL 1 DAY
                GROUP BY registro_mercantil 
                ORDER BY count DESC 
                LIMIT 3
            """, (query_date, query_date))
            stats["provinces"] = cur.fetchall()

            # 3. Sectors Categorization
            cur.execute("""
                SELECT company_name 
                FROM companies 
                WHERE created_at >= %s AND created_at < %s + INTERVAL 1 DAY
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

    except Exception as e:
        print(f"[!] Error gathering stats: {e}")
    finally:
        conn.close()
        
    return stats

def generate_post(stats):
    """Composes the LinkedIn post text."""
    today_str = datetime.date.today().strftime("%d/%m/%Y")
    total = stats["total_companies"]
    if total == 0: return None

    prov_list = [f"{p['registro_mercantil']} ({p['count']})" for p in stats["provinces"]]
    top_provinces = ", ".join(prov_list)

    top_sectors = []
    for cat, count in list(stats["sectors"].items())[:3]:
        if count > 0:
            percentage = round((count / total) * 100, 1)
            top_sectors.append(f"• {cat}: {percentage}%")
    
    sectors_str = "\n".join(top_sectors) if top_sectors else "• Diversos sectores industriales"

    post = f"""🚀 Resumen de Creación de Empresas en España - {today_str}

¡Día de gran actividad económica! Nuestro Radar B2B ha detectado la constitución de {total} nuevas sociedades en las últimas 24 horas.

📍 Liderazgo Geográfico:
Los registros mercantiles con mayor movimiento han sido {top_provinces}.

📈 Sectores en Crecimiento:
{sectors_str}

La analítica avanzada de APIEmpresas sigue monitorizando en tiempo real el pulso económico del país.

#Emprendimiento #Empresas #BORME #DataIntel #España #Economía #B2B #DigitalOps"""

    return post

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
    post_content = generate_post(stats)

    if not post_content:
        print("[!] Failed to generate post content.")
        return

    if args.dry_run:
        print("\n=== PREVISUALIZACIÓN DEL CONTENIDO ===")
        print(post_content)
        if img_path:
            print(f"\n📊 Infografía generada en: {os.path.abspath(img_path)}")
        else:
            print("\n⚠️ Nota: Pillow no está instalado, no se ha generado infografía.")
        print("=======================================\n")
    else:
        publish_to_linkedin(post_content, img_path)

if __name__ == "__main__":
    main()
