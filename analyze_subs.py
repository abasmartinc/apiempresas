import re
from collections import Counter

def analyze_subscriptions(filepath):
    plan_counter = Counter()
    status_counter = Counter()
    
    with open(filepath, 'r', encoding='utf-16') as f:
        for line in f:
            parts = [p.strip() for p in line.split('|')]
            if len(parts) >= 6 and parts[1].isdigit():
                plan_id = parts[3]
                status = parts[5].lower()
                plan_counter[plan_id] += 1
                if status == 'active':
                    status_counter[f"{plan_id}_active"] += 1
                elif status == 'trialing':
                    status_counter[f"{plan_id}_trialing"] += 1
                elif status == 'canceled' or parts[9]: # if canceled_at is not empty
                    status_counter[f"{plan_id}_canceled"] += 1
                else:
                    status_counter[f"{plan_id}_other"] += 1

    print("--- Plan Distribution ---")
    for plan, count in sorted(plan_counter.items()):
        print(f"Plan {plan}: {count} total")
    
    print("\n--- Active Status Distribution ---")
    for key, count in sorted(status_counter.items()):
        print(f"{key}: {count}")

if __name__ == "__main__":
    analyze_subscriptions('subscriptions_dump.txt')
