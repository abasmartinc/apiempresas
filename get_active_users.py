import re

def get_active_paid_users(filepath):
    active_users = []
    with open(filepath, 'r', encoding='utf-16') as f:
        for line in f:
            parts = [p.strip() for p in line.split('|')]
            if len(parts) >= 6 and parts[1].isdigit():
                plan_id = parts[3]
                status = parts[5].lower()
                user_id = parts[2]
                if plan_id in ['2', '3', '7'] and status == 'active':
                    active_users.append({'user_id': user_id, 'plan_id': plan_id})
    return active_users

if __name__ == "__main__":
    users = get_active_paid_users('subscriptions_dump.txt')
    print("Active Paid Users:")
    for u in users:
        print(f"User {u['user_id']} (Plan {u['plan_id']})")
