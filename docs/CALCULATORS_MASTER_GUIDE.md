# 🧮 Calculator System - Master Guide

**Last Updated:** December 1, 2025  
**Status:** 10 of 11 calculators fully modernized (91% complete)

---

## 📚 Table of Contents

1. [Quick Start](#quick-start)
2. [System Overview](#system-overview)
3. [Calculator Types](#calculator-types)
4. [Implementation Status](#implementation-status)
5. [Integration with Estimates](#integration-with-estimates)
6. [Material Catalog Integration](#material-catalog-integration)
7. [Production Rates](#production-rates)
8. [Import Workflow](#import-workflow)
9. [Development Guide](#development-guide)
10. [Related Documentation](#related-documentation)

---

## 🚀 Quick Start

### For Users
1. Navigate to site visit
2. Click calculator type (Mulching, Planting, etc.)
3. Fill in measurements and crew details
4. Click "Calculate"
5. Review results
6. Choose "Import to Estimate" (granular) or "Save Only"

### For Developers
- **Backend Logic:** `app/Http/Controllers/*CalculatorController.php`
- **Views:** `resources/views/calculators/*/form.blade.php` and `result.blade.php`
- **Services:** `app/Services/LaborCostCalculatorService.php`
- **Production Rates:** `production_rates` database table
- **Import Logic:** `app/Services/CalculationImportService.php`

---

## 🎯 System Overview

### Architecture

