import csv, json, re

INPUT = 'materials.csv'
OUTPUT = 'materials.json'

def clean_money(s):
    if s is None: return 0.0
    n = re.sub(r'[^\d\.\-]', '', str(s))
    try: return float(n)
    except: return 0.0

def clean_tax_rate(s):
    if s is None: return 0.0
    t = str(s).strip()
    if '%' in t:
        n = re.sub(r'[^\d\.\-]', '', t)
        try: return float(n)/100.0
        except: return 0.0
    n = re.sub(r'[^\d\.\-]', '', t)
    try:
        val = float(n)
        return (val/100.0) if val > 1 else val
    except:
        return 0.0

def to_bool(v):
    return str(v).strip().lower() in ('1','true','yes','y')

rows = []
with open(INPUT, newline='', encoding='utf-8') as f:
    r = csv.DictReader(f)
    for row in r:
        rows.append({
            'name': (row.get('name') or '').strip(),
            'sku': (row.get('sku') or '').strip(),            # IMPORTANT: must exist in CSV
            'category': (row.get('category') or '').strip(),
            'unit': (row.get('unit') or '').strip(),
            'unit_cost': clean_money(row.get('unit_cost')),
            'tax_rate': clean_tax_rate(row.get('tax_rate')),
            'vendor_name': (row.get('vendor_name') or '').strip(),
            'vendor_sku': (row.get('vendor_sku') or '').strip(),
            'description': (row.get('description') or '').strip(),
            'is_taxable': to_bool(row.get('is_taxable')),
            'is_active': to_bool(row.get('is_active')),
        })

with open(OUTPUT, 'w', encoding='utf-8') as f:
    json.dump(rows, f, ensure_ascii=False, indent=2)

print(f"Wrote {OUTPUT} with {len(rows)} rows.")