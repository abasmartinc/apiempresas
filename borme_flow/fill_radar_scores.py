# -*- coding: utf-8 -*-
import os
import re
import sys
import logging
import argparse
import datetime
from typing import List, Dict, Any, Optional, Tuple

import pymysql
from config import mysql_connect

# Configure Logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s',
    handlers=[
        logging.StreamHandler(sys.stdout),
        logging.FileHandler('radar_scoring.log', encoding='utf-8')
    ]
)
logger = logging.getLogger(__name__)

class RadarScorer:
    def __init__(self, dry_run: bool = False):
        self.dry_run = dry_run
        self.version = "1.1.0" # Updated version for the new algorithm

    def parse_capital_amount(self, capital_raw: Optional[str]) -> float:
        """Parses capital strings like '3.000,00 Euros' or '3000 EUR' to float."""
        if not capital_raw:
            return 0.0
        try:
            # Remove currency symbols and non-numeric chars except , and .
            clean_str = re.sub(r'[^0-9,\.]', '', capital_raw).strip()
            if not clean_str:
                return 0.0
            
            # Handle European format: 1.234,56 -> 1234.56
            if ',' in clean_str and '.' in clean_str:
                if clean_str.find('.') < clean_str.find(','):
                    # 3.000,00
                    clean_str = clean_str.replace('.', '').replace(',', '.')
                else:
                    # 3,000.00
                    clean_str = clean_str.replace(',', '')
            elif ',' in clean_str:
                # 3000,00
                clean_str = clean_str.replace(',', '.')
            
            return float(clean_str)
        except Exception as e:
            logger.debug(f"Error parsing capital '{capital_raw}': {e}")
            return 0.0

    def detect_main_act_type(self, act_types: str, description: str) -> Tuple[str, int, int]:
        """Detects the main act type and its associated score and penalty."""
        text = (str(act_types) + " " + str(description)).lower()
        
        # Priority mapping (Act, Score, Penalty)
        patterns = [
            (r'extinci.n', "Extinción", -35, 35),
            (r'concurso|liquidaci.n|concursal', "Situación Concursal/Liquidación", -30, 30),
            (r'disoluci.n', "Disolución", -25, 25),
            (r'constituci.n', "Constitución", 30, 0),
            (r'ampliaci.n de capital', "Ampliación de Capital", 12, 0),
            (r'cambio de objeto social', "Cambio de Objeto Social", 10, 0),
            (r'cambio de domicilio', "Cambio de Domicilio", 8, 0),
            (r'unipersonalidad', "Declaración de Unipersonalidad", 6, 0),
            (r'nombramiento', "Nombramientos", 5, 0),
            (r'apoderad|apoderamiento', "Apoderamientos", 3, 0),
            (r'cese|dimisi.n', "Ceses/Dimisiones", -3, 0),
        ]

        for pattern, label, score, penalty in patterns:
            if re.search(pattern, text):
                return label, score, penalty
        
        return "Otros", 0, 0

    def get_recency_score(self, borme_date: Optional[datetime.date]) -> int:
        """
        BONO DE VELOCIDAD: Crucial para Radar.
        Puntúa muy alto si el lead es de las últimas 48h-7d.
        """
        if not borme_date:
            return 0
        
        days_diff = (datetime.date.today() - borme_date).days
        if days_diff < 0: days_diff = 0
        
        if days_diff <= 2: return 40 # Lead de hoy o ayer: ¡ORO!
        if days_diff <= 7: return 20 # Esta semana
        if days_diff <= 15: return 10
        if days_diff <= 30: return 5
        return 0

    def get_capital_score(self, amount: float) -> int:
        """Score based on social capital amount."""
        if amount <= 0: return 0
        if amount <= 3000: return 2
        if amount <= 6000: return 4
        if amount <= 15000: return 7
        if amount <= 30000: return 10
        if amount <= 100000: return 13
        return 15

    def get_legal_form_score(self, name: str) -> int:
        """Detects legal form and returns score."""
        name = name.upper()
        if any(x in name for x in [" S.L.", " SL", "SOCIEDAD LIMITADA"]):
            if any(x in name for x in ["SLU", "S.L.U.", "UNIPERSONAL"]):
                return 7
            return 8
        if any(x in name for x in [" S.A.", " SA", "SOCIEDAD ANONIMA"]):
            return 8
        return 3 # Other identifiable forms

    def get_activity_score(self, cnae_code: str, cnae_label: str, object_social: str) -> int:
        """Score based on activity (CNAE or Social Object keywords)."""
        text = (str(cnae_label) + " " + str(object_social)).lower()
        
        tech_keywords = ["software", "programación", "tecnología", "consultoría it", "tecnologico", "digital", "informática", "saas", "ciberseguridad", "ia", "inteligencia artificial"]
        b2b_keywords = ["marketing", "publicidad", "consultoría", "formación", "servicios b2b", "asesoría", "gestión", "comercialización", "b2b"]
        industrial_keywords = ["inmobiliario", "construcción", "instalaciones", "reformas", "obras", "energía", "solar", "fotovoltaica", "eléctrica"]
        logistics_keywords = ["transporte", "logística", "almacén", "distribución", "mensajería", "e-commerce", "comercio electrónico"]

        # CLUSTERS DE VALOR (Bonos directos)
        if any(kw in text for kw in tech_keywords) or (cnae_code and cnae_code.startswith(('62', '63'))):
            return 25 # Cluster Premium
        if any(kw in text for kw in b2b_keywords) or (cnae_code and cnae_code.startswith(('70', '73', '85'))):
            return 18
        if any(kw in text for kw in industrial_keywords) or (cnae_code and cnae_code.startswith(('41', '42', '43'))):
            return 15
        if any(kw in text for kw in logistics_keywords) or (cnae_code and cnae_code.startswith(('49', '52'))):
            return 12
        
        return 5 # Por defecto

    def get_object_score(self, object_social: str, borme_desc: str) -> int:
        """Deep analysis of social object."""
        text = (str(object_social) + " " + str(borme_desc)).lower()
        keywords = [
            "consultoría", "servicios", "gestión", "tecnología", "software", "digital",
            "instalaciones", "construcción", "inmobiliaria", "comercio", "importación",
            "exportación", "marketing", "logística", "formación", "gimnasio", "clínica",
            "nutrición", "fisioterapia"
        ]
        matches = sum(1 for kw in keywords if kw in text)
        if matches >= 3: return 8
        if matches >= 1: return 5
        return 1

    def get_location_score(self, registry: str, municipality: str) -> int:
        """Score based on economic relevance of the city/registry."""
        text = (str(registry) + " " + str(municipality)).upper()
        if "MADRID" in text or "BARCELONA" in text:
            return 4
        if any(x in text for x in ["VALENCIA", "ALICANTE", "MALAGA", "SEVILLA", "BIZKAIA"]):
            return 3
        important_provinces = ["MURCIA", "ZARAGOZA", "PONTEVEDRA", "ASTURIAS", "CORUÑA", "BALEARS", "PALMAS", "TENERIFE"]
        if any(x in text for x in important_provinces):
            return 2
        return 1

    def get_name_score(self, name: str) -> int:
        """Score based on naming appeal/sector focus."""
        name = name.lower()
        keywords = [
            "consulting", "digital", "tech", "solutions", "group", "gestión",
            "inmobiliaria", "reformas", "instalaciones", "logística", "capital",
            "advisory", "studio"
        ]
        if any(kw in name for kw in keywords):
            return 3
        return 1

    def get_priority_level(self, total: int) -> str:
        if total >= 90: return 'muy_alta'
        if total >= 75: return 'alta'
        if total >= 55: return 'media'
        if total >= 35: return 'baja'
        return 'muy_baja'

    def build_score_reasons(self, components: Dict[str, Any], main_act: str) -> str:
        reasons = []
        
        # Act and Key Info
        reasons.append(f"{main_act}")
        
        # Priority Messages
        if components['score_recency'] >= 40: reasons.append("Oportunidad Crítica (48h)")
        if components['score_activity'] >= 25: reasons.append("Cluster Tech/Digital")
        
        # Capital
        if components['score_capital'] >= 15: reasons.append("Inyección Financiera Sólida")
        elif components['score_capital'] >= 5: reasons.append("Capital Inicial")
        
        # Business triggers
        if "Ampliación" in main_act: reasons.append("Señal de Crecimiento")

        return " · ".join(reasons[:4]) # Limit to top 4 reasons

    def select_best_post(self, posts: List[Dict[str, Any]]) -> Optional[Dict[str, Any]]:
        """Selects the best post: Constitution vs Most Recent."""
        if not posts: return None
        
        # Try to find a Constitution post in the recent list
        for post in posts[:5]: # Look at last 5
            act_type = str(post.get('act_types', '')).lower()
            if 'constituci' in act_type:
                return post
        
        # Otherwise, the most recent one (assuming they are ordered)
        return posts[0]

    def calculate_admin_score(self, admin_signals: List[Dict[str, Any]]) -> int:
        """Analyzes administrator actions."""
        if not admin_signals: return 0
        
        recent_actions = 0
        for signal in admin_signals:
            action = str(signal.get('action', '')).lower()
            if 'nombramiento' in action or 'relección' in action:
                recent_actions += 1
            elif 'cese' in action or 'dimisi' in action:
                recent_actions -= 0.5
        
        if recent_actions >= 3: return 5
        if recent_actions >= 1: return 3
        return max(0, int(recent_actions))

    def process_company(self, company: Dict[str, Any], posts: List[Dict[str, Any]], admins: List[Dict[str, Any]]) -> Dict[str, Any]:
        best_post = self.select_best_post(posts)
        
        # 1. Act Type Signal
        main_act = "Sin Actos Recientes"
        act_score = 0
        penalty = 0
        last_post_id = None
        last_borme_date = None
        borme_desc = ""
        
        if best_post:
            main_act, act_score, penalty = self.detect_main_act_type(best_post.get('act_types', ''), best_post.get('description', ''))
            last_post_id = best_post.get('id')
            last_borme_date = best_post.get('borme_date')
            borme_desc = best_post.get('description', '')

        # 2. Recency
        recency_score = self.get_recency_score(last_borme_date)
        
        # 3. Capital
        cap_val = self.parse_capital_amount(company.get('capital_social_raw'))
        capital_score = self.get_capital_score(cap_val)
        
        # 4. Legal Form
        legal_score = self.get_legal_form_score(company.get('company_name', ''))
        
        # 5. Activity
        activity_score = self.get_activity_score(company.get('cnae_code', ''), company.get('cnae_label', ''), company.get('objeto_social', ''))
        
        # 6. Object
        object_score = self.get_object_score(company.get('objeto_social', ''), borme_desc)
        
        # 7. Location
        location_score = self.get_location_score(company.get('registro_mercantil', ''), company.get('municipality', ''))
        
        # 8. Name
        name_score = self.get_name_score(company.get('company_name', ''))
        
        # 9. Admin signal
        admin_score = self.calculate_admin_score(admins)

        # TOTAL SCORE CALCULATION (Weighted)
        base_score = act_score + recency_score + activity_score + object_score + location_score + name_score + legal_score + admin_score
        
        # APLICAR MULTIPLICADOR DE CAPITAL
        multiplier = 1.0
        if cap_val >= 100000: multiplier = 1.6
        elif cap_val >= 30000: multiplier = 1.4
        elif cap_val >= 10000: multiplier = 1.2
        
        # Bonus por Ampliación de Capital (si es reciente y significativa)
        ampliacion_bonus = 0
        if "Ampliación" in main_act and cap_val > 5000:
            ampliacion_bonus = 15

        total = (base_score * multiplier) + capital_score + ampliacion_bonus - penalty
        total = max(0, min(100, total))
        
        comp_scores = {
            "score_act_type": act_score,
            "score_recency": recency_score,
            "score_capital": capital_score,
            "score_legal_form": legal_score,
            "score_activity": activity_score,
            "score_object": object_score,
            "score_location": location_score,
            "score_name": name_score,
            "score_admin_signal": admin_score,
            "penalty_score": penalty
        }
        
        reasons = self.build_score_reasons(comp_scores, main_act)
        priority = self.get_priority_level(total)

        return {
            "company_id": company['id'],
            "score_total": total,
            "priority_level": priority,
            "score_reasons": reasons,
            "main_act_type": main_act,
            "last_borme_date": last_borme_date,
            "last_post_id": last_post_id,
            "calculation_version": self.version,
            **comp_scores
        }

def upsert_scores(conn, scores: List[Dict[str, Any]]):
    if not scores: return
    
    sql = """
    INSERT INTO company_radar_scores (
        company_id, score_total, score_act_type, score_recency, score_capital,
        score_legal_form, score_activity, score_object, score_location,
        score_name, score_admin_signal, penalty_score, priority_level,
        score_reasons, main_act_type, last_borme_date, last_post_id, calculation_version,
        updated_at
    ) VALUES (
        %(company_id)s, %(score_total)s, %(score_act_type)s, %(score_recency)s, %(score_capital)s,
        %(score_legal_form)s, %(score_activity)s, %(score_object)s, %(score_location)s,
        %(score_name)s, %(score_admin_signal)s, %(penalty_score)s, %(priority_level)s,
        %(score_reasons)s, %(main_act_type)s, %(last_borme_date)s, %(last_post_id)s, %(calculation_version)s,
        NOW()
    ) ON DUPLICATE KEY UPDATE
        score_total = VALUES(score_total),
        score_act_type = VALUES(score_act_type),
        score_recency = VALUES(score_recency),
        score_capital = VALUES(score_capital),
        score_legal_form = VALUES(score_legal_form),
        score_activity = VALUES(score_activity),
        score_object = VALUES(score_object),
        score_location = VALUES(score_location),
        score_name = VALUES(score_name),
        score_admin_signal = VALUES(score_admin_signal),
        penalty_score = VALUES(penalty_score),
        priority_level = VALUES(priority_level),
        score_reasons = VALUES(score_reasons),
        main_act_type = VALUES(main_act_type),
        last_borme_date = VALUES(last_borme_date),
        last_post_id = VALUES(last_post_id),
        calculation_version = VALUES(calculation_version),
        updated_at = NOW();
    """
    with conn.cursor() as cur:
        cur.executemany(sql, scores)
    conn.commit()

def mark_processed_tasks(conn, company_ids: List[int]):
    """Optionally track in companies_processed_log as requested."""
    if not company_ids: return
    try:
        sql = "INSERT IGNORE INTO companies_processed_log (company_id, process_name, processed_at) VALUES (%s, 'radar_scoring', NOW())"
        with conn.cursor() as cur:
            cur.executemany(sql, [(cid,) for cid in company_ids])
        conn.commit()
    except Exception as e:
        logger.warning(f"Could not update companies_processed_log: {e}")

def main():
    parser = argparse.ArgumentParser(description="Radar Lead Scorer for BORME companies")
    parser.add_argument("--all", action="store_true", help="Process all companies")
    parser.add_argument("--start-id", type=int, help="Start company ID")
    parser.add_argument("--end-id", type=int, help="End company ID")
    parser.add_argument("--days-since-const", type=int, default=90, help="Process only companies created in the last X days (default 90)")
    parser.add_argument("--limit", type=int, default=1000, help="Batch limit")
    parser.add_argument("--offset", type=int, default=0, help="Batch offset")
    parser.add_argument("--dry-run", action="store_true", help="Simulate without writing to DB")
    
    args = parser.parse_args()
    
    scorer = RadarScorer(dry_run=args.dry_run)
    conn = mysql_connect()
    
    try:
        # Base query to fetch companies
        query = "SELECT * FROM companies WHERE 1=1"
        params = []
        
        # Filter by constitution date (recent only for radar)
        if args.days_since_const:
            query += " AND fecha_constitucion >= (CURDATE() - INTERVAL %s DAY)"
            params.append(args.days_since_const)

        if args.start_id:
            query += " AND id >= %s"
            params.append(args.start_id)
        if args.end_id:
            query += " AND id <= %s"
            params.append(args.end_id)
            
        if not args.all and not args.start_id:
            query += " LIMIT %s OFFSET %s"
            params.extend([args.limit, args.offset])
        elif args.all:
             # For --all we might still want to iterate in chunks
             pass

        logger.info(f"Fetching companies... {'(Dry Run)' if args.dry_run else ''}")
        
        offset = args.offset
        total_processed_session = 0
        
        while True:
            # Base query
            query = "SELECT * FROM companies WHERE 1=1"
            params = []
            
            # Filter by constitution date
            if args.days_since_const:
                query += " AND fecha_constitucion >= (CURDATE() - INTERVAL %s DAY)"
                params.append(args.days_since_const)

            if args.start_id:
                query += " AND id >= %s"
                params.append(args.start_id)
            if args.end_id:
                query += " AND id <= %s"
                params.append(args.end_id)
                
            # Pagination
            query += " ORDER BY id ASC LIMIT %s OFFSET %s"
            params.extend([args.limit, offset])

            with conn.cursor() as cur:
                cur.execute(query, params)
                companies = cur.fetchall()
            
            if not companies:
                if total_processed_session == 0:
                    logger.info("No companies found to process.")
                break

            logger.info(f"Processing chunk of {len(companies)} companies (Current Offset: {offset})...")
            
            batch_scores = []
            count_chunk = 0
            
            for comp in companies:
                cid = comp['id']
                
                with conn.cursor() as cur:
                    cur.execute("SELECT id, borme_date, act_types, description FROM borme_posts WHERE company_id = %s ORDER BY borme_date DESC LIMIT 10", (cid,))
                    posts = cur.fetchall()
                    
                    cur.execute("SELECT action, position, name FROM company_administrators WHERE company_id = %s ORDER BY created_at DESC LIMIT 20", (cid,))
                    admins = cur.fetchall()
                
                score_data = scorer.process_company(comp, posts, admins)
                batch_scores.append(score_data)
                
                count_chunk += 1
                if count_chunk % 100 == 0:
                    if not args.dry_run:
                        upsert_scores(conn, batch_scores)
                        mark_processed_tasks(conn, [s['company_id'] for s in batch_scores])
                        batch_scores = []

            # Final batch in current chunk
            if batch_scores and not args.dry_run:
                upsert_scores(conn, batch_scores)
                mark_processed_tasks(conn, [s['company_id'] for s in batch_scores])
            elif args.dry_run and batch_scores:
                for s in batch_scores[:3]:
                    logger.info(f"DRY-RUN Result CID {s['company_id']}: Score {s['score_total']}, Priority {s['priority_level']}")

            total_processed_session += len(companies)
            
            # If we are not in --all mode, we stop after one chunk
            if not args.all:
                break
                
            # If the chunk was smaller than the limit, we reached the end
            if len(companies) < args.limit:
                break
                
            offset += args.limit

        logger.info(f"Process finished. Total companies processed: {total_processed_session}")

    except Exception as e:
        logger.error(f"Execution failed: {e}", exc_info=True)
    finally:
        conn.close()

if __name__ == "__main__":
    main()
