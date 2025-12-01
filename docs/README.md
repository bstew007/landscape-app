# 📚 Documentation Index

**Last Updated:** November 30, 2025

---

## 🚀 Quick Start (START HERE)

### For Tomorrow's Session
1. **[QUICK_START_PHASE_2.md](QUICK_START_PHASE_2.md)** ⭐ **START HERE**
   - Quick reference for starting Phase 2 (Timesheets)
   - Commands, patterns, testing strategy
   - Database schema preview
   - Success criteria

2. **[CURRENT_STATE_SUMMARY.md](CURRENT_STATE_SUMMARY.md)**
   - Complete overview of what's working
   - System architecture diagram
   - File structure
   - Key relationships

3. **[JOBS_PHASE_1_COMPLETION_STATUS.md](JOBS_PHASE_1_COMPLETION_STATUS.md)**
   - Detailed Phase 1 completion report
   - All files created
   - Issues encountered and resolved
   - Testing results

---

## 📋 Implementation Plans

### Jobs, Timesheets & Mobile
- **[JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md](JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md)**
  - 9-week implementation roadmap
  - Phase 1: Jobs ✅ COMPLETE
  - Phase 2: Timesheets ⏳ IN PROGRESS
  - Phase 3: Material Tracking 🔜
  - Phase 4: Mobile App 🔜
  - Phase 5: Reports & Analytics 🔜

---

## 💰 Budget & Pricing System

- **[BUDGET_SYSTEM_OVERVIEW.md](BUDGET_SYSTEM_OVERVIEW.md)**
  - Budget model architecture
  - Overhead rate calculations
  - Labor cost multipliers
  - Profit margin targets

- **[BUDGET_QUICK_REFERENCE.md](BUDGET_QUICK_REFERENCE.md)**
  - Quick lookup for budget formulas
  - Rate calculations
  - Common scenarios

- **[ESTIMATE_BUDGET_INTEGRATION_SUMMARY.md](ESTIMATE_BUDGET_INTEGRATION_SUMMARY.md)**
  - How estimates integrate with budgets
  - Cost calculation flow
  - Variance tracking

- **[DYNAMIC_LABOR_RATE_INTEGRATION.md](DYNAMIC_LABOR_RATE_INTEGRATION.md)**
  - Dynamic labor rates based on budget
  - Rate adjustment strategies

- **[CUSTOM_PRICING_FEATURE.md](CUSTOM_PRICING_FEATURE.md)**
  - Custom pricing per line item
  - Override default catalog pricing
  - Use cases

---

## 🧮 Calculator System

- **[CALCULATORS_MASTER_GUIDE.md](CALCULATORS_MASTER_GUIDE.md)** ⭐ **START HERE**
  - Complete calculator system overview
  - All calculator types explained
  - Implementation status (91% complete)
  - Production rates, catalog integration, import workflow
  - Development guide for new calculators

- **[CALCULATOR_OVERHAUL_STATUS.md](CALCULATOR_OVERHAUL_STATUS.md)**
  - Calculator system overhaul status
  - Recent improvements
  - Known issues

- **[CALCULATOR_IMPORT_PLAN.md](CALCULATOR_IMPORT_PLAN.md)**
  - Plan for importing calculator data
  - Migration strategy

- **[CALCULATOR_IMPORT_FIXES_SUMMARY.md](CALCULATOR_IMPORT_FIXES_SUMMARY.md)**
  - Fixes applied during calculator import
  - Issues resolved

---

## 📊 Estimates

- **[ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md](ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md)**
  - How estimate calculations work
  - Reactive updates in UI
  - Cost propagation flow

- **[Estimate_print_docs_section.md](Estimate_print_docs_section.md)**
  - Print layout documentation
  - Branded estimate templates
  - PDF generation

---

## 🛠️ Catalogs

- **[labor_catalog.md](labor_catalog.md)**
  - Labor catalog structure
  - Labor items and rates
  - Crew classifications

- **[labor_catolog.md](labor_catolog.md)** *(duplicate, can be deleted)*
  - Older version of labor catalog docs

---

## 📍 Site Visits

- **[site_visit_data.md](site_visit_data.md)**
  - Site visit data structure
  - Calculator inputs
  - Measurement tracking

---

## 🎨 Design & Theme

- **[theme.md](theme.md)**
  - CFL charcoal/brand theme specification
  - Color palette
  - Component patterns
  - Design system

---

## 🐛 Bug Fixes & Diagnostics

- **[RESET_BUTTON_DIAGNOSTICS.md](RESET_BUTTON_DIAGNOSTICS.md)**
  - Reset button diagnostic report
  - Issue investigation

- **[RESET_BUTTON_FIX_SUMMARY.md](RESET_BUTTON_FIX_SUMMARY.md)**
  - Reset button fixes applied
  - Resolution summary

---

## 🚀 Deployment

- **[DEPLOYMENT_INSTRUCTIONS.md](DEPLOYMENT_INSTRUCTIONS.md)**
  - Production deployment guide
  - Server requirements
  - Environment setup
  - Database migration
  - SSL configuration

---

## 📁 Documentation Organization

```
docs/
├── README.md (this file) ⭐ INDEX
│
├── 🚀 QUICK START
│   ├── QUICK_START_PHASE_2.md ⭐ START HERE TOMORROW
│   ├── CURRENT_STATE_SUMMARY.md
│   └── JOBS_PHASE_1_COMPLETION_STATUS.md
│
├── 📋 PLANNING & ROADMAPS
│   └── JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md
│
├── 💰 BUDGET & PRICING
│   ├── BUDGET_SYSTEM_OVERVIEW.md
│   ├── BUDGET_QUICK_REFERENCE.md
│   ├── ESTIMATE_BUDGET_INTEGRATION_SUMMARY.md
│   ├── DYNAMIC_LABOR_RATE_INTEGRATION.md
│   └── CUSTOM_PRICING_FEATURE.md
│
├── 🧮 CALCULATORS
│   ├── CALCULATORS_MASTER_GUIDE.md
│   ├── CALCULATOR_OVERHAUL_STATUS.md
│   ├── CALCULATOR_IMPORT_PLAN.md
│   └── CALCULATOR_IMPORT_FIXES_SUMMARY.md
│
├── 📊 ESTIMATES
│   ├── ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md
│   └── Estimate_print_docs_section.md
│
├── 🛠️ CATALOGS
│   ├── labor_catalog.md
│   └── labor_catolog.md (duplicate)
│
├── 📍 SITE VISITS
│   └── site_visit_data.md
│
├── 🎨 DESIGN
│   └── theme.md
│
├── 🐛 BUG FIXES
│   ├── RESET_BUTTON_DIAGNOSTICS.md
│   └── RESET_BUTTON_FIX_SUMMARY.md
│
└── 🚀 DEPLOYMENT
    └── DEPLOYMENT_INSTRUCTIONS.md
```

---

## 🎯 Recommended Reading Order for New Developers

### Day 1: Understanding the System
1. [CURRENT_STATE_SUMMARY.md](CURRENT_STATE_SUMMARY.md) - System overview
2. [theme.md](theme.md) - Design system
3. [BUDGET_SYSTEM_OVERVIEW.md](BUDGET_SYSTEM_OVERVIEW.md) - Budget concepts

### Day 2: Working with Estimates
4. [CALCULATOR_INTEGRATION_IMPLEMENTATION_GUIDE.md](CALCULATOR_INTEGRATION_IMPLEMENTATION_GUIDE.md) - Calculator flow
5. [ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md](ESTIMATE_LINE_ITEM_REACTIVE_CALCULATIONS.md) - How estimates calculate
6. [CUSTOM_PRICING_FEATURE.md](CUSTOM_PRICING_FEATURE.md) - Pricing flexibility

### Day 3: Jobs & Future Development
7. [JOBS_PHASE_1_COMPLETION_STATUS.md](JOBS_PHASE_1_COMPLETION_STATUS.md) - Jobs system
8. [JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md](JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md) - Roadmap
9. [QUICK_START_PHASE_2.md](QUICK_START_PHASE_2.md) - Next steps

---

## 📝 Document Naming Convention

- **UPPERCASE_WITH_UNDERSCORES.md** - System documentation, guides, summaries
- **lowercase_with_underscores.md** - Feature-specific documentation
- **README.md** - Index/overview files

---

## 🔄 Document Maintenance

### When to Update
- **After completing a phase** - Update completion status docs
- **After major changes** - Update relevant feature docs
- **Before deploying** - Update deployment instructions
- **When bugs are found** - Document in bug fix summaries

### Document Owners
- **System docs** - Lead developer
- **Feature docs** - Feature developer
- **Bug fixes** - Developer who fixed the issue
- **Deployment** - DevOps/lead developer

---

## ❓ FAQ

### Where do I start tomorrow?
**[QUICK_START_PHASE_2.md](QUICK_START_PHASE_2.md)** - Everything you need to hit the ground running

### What's the current state of the app?
**[CURRENT_STATE_SUMMARY.md](CURRENT_STATE_SUMMARY.md)** - Complete system overview

### How do I deploy to production?
**[DEPLOYMENT_INSTRUCTIONS.md](DEPLOYMENT_INSTRUCTIONS.md)** - Step-by-step deployment guide

### What's the roadmap?
**[JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md](JOBS_TIMESHEETS_MOBILE_IMPLEMENTATION_PLAN.md)** - 9-week plan

### How do budgets work?
**[BUDGET_SYSTEM_OVERVIEW.md](BUDGET_SYSTEM_OVERVIEW.md)** - Budget system explained

### What colors should I use?
**[theme.md](theme.md)** - CFL charcoal/brand theme specification

---

## 🎉 Recent Updates

**November 30, 2025:**
- ✅ Completed Phase 1: Jobs System
- ✅ Created JOBS_PHASE_1_COMPLETION_STATUS.md
- ✅ Created QUICK_START_PHASE_2.md
- ✅ Created CURRENT_STATE_SUMMARY.md
- ✅ Created this README index
- ✅ All docs organized in /docs folder

---

## 📞 Need Help?

If you're stuck:
1. Check the relevant doc in this index
2. Review [CURRENT_STATE_SUMMARY.md](CURRENT_STATE_SUMMARY.md) for context
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Use Tinker to test: `php artisan tinker`

---

**Happy coding! 🚀**
