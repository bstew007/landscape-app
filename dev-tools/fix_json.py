import json, re, os, sys

INPUT = 'materials.json'          # your current JSON that was skipped
OUTPUT = 'materials_fixed.json'   # new file to import

UNIT_MAP = {
    'tns': 'tn',
    'bags': 'bag',
    'bg': 'bag',
}

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

def sku_from_name(name):
    if not name:
        return ''
    s = name.upper()
    s = re.sub(r'[^A-Z0-9]+', '-', s).strip('-')
    return s[:24]

def fix_row(r):
    # Pull existing
    name         = (r.get('name') or '').strip()
    desc         = (r.get('description') or '').strip()
    category     = (r.get('category') or '').strip()
    unit_raw     = (r.get('unit') or '').strip()
    unit         = UNIT_MAP.get(unit_raw.lower(), unit_raw)
    unit_cost    = clean_money(r.get('unit_cost'))
    tax_rate     = clean_tax_rate(r.get('tax_rate'))
    vendor_name  = (r.get('vendor_name') or '').strip()
    vendor_sku   = (r.get('vendor_sku') or '').strip()
    sku          = (r.get('sku') or '').strip()
    is_taxable   = to_bool(r.get('is_taxable'))
    is_active    = to_bool(r.get('is_active'))

    # Fill name if missing
    if not name and desc:
        name = desc

    # Ensure sku is present
    if not sku:
        sku = vendor_sku or sku_from_name(name)

    return {
        'name': name,
        'sku': sku,
        'category': category,
        'unit': unit,
        'unit_cost': unit_cost,
        'tax_rate': tax_rate,
        'vendor_name': vendor_name,
        'vendor_sku': vendor_sku,
        'description': desc,
        'is_taxable': is_taxable,
        'is_active': is_active,
    }

def main():
    if not os.path.exists(INPUT):
        print(f"File not found: {INPUT}")
        sys.exit(1)

    with open(INPUT, 'r', encoding='utf-8') as f:
        data = json.load(f)

    if not isinstance(data, list):
        data = [data]

    fixed = [fix_row(r) for r in data if isinstance(r, dict)]

    # Optional: report empty names after fix (should be near zero)
    empty_names = [x for x in fixed if not x['name']]
    if empty_names:
        print(f"Warning: {len(empty_names)} rows still have empty name (will be skipped).")

    with open(OUTPUT, 'w', encoding='utf-8') as f:
        json.dump(fixed, f, ensure_ascii=False, indent=2)

    print(f"Wrote {OUTPUT} with {len(fixed)} rows.")

if __name__ == '__main__':
    main()