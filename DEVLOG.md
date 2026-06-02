# DEVLOG

---

## 2026-06-02

## Milestone 1 — Projektopsætning

## Implementerede funktioner
- Mappestruktur oprettet (`public/`, `src/`, `config/`, `database/`, `data/`, `uploads/`)
- `composer.json` konfigureret med smalot/pdfparser
- Database schema oprettet (alle 10 tabeller)
- Upload side (`public/index.php`) implementeret
- Begrebsdatabase (`data/concepts.json`) oprettet med alle kategorier fra CLAUDE.md
- `.gitignore` konfigureret

## Oprettede filer
- `.gitignore`
- `composer.json`
- `config/database.php`
- `data/concepts.json`
- `database/schema.sql`
- `public/css/style.css`
- `public/index.php`
- `public/js/app.js`
- `src/Analysis/.gitkeep`
- `src/BossBattle/.gitkeep`
- `src/Cloze/.gitkeep`
- `src/Quiz/.gitkeep`
- `uploads/.gitkeep`
- `DEVLOG.md`

## Ændrede filer
- Ingen

## Databaseændringer
- Schema oprettet: `database/schema.sql`
  - Tabeller: reports, report_sections, concepts, quiz_sets, quiz_questions, cloze_sets, cloze_questions, boss_battles, progress, badges

## Kendte problemer
- Ingen

## Næste milestone
Milestone 2 — PDF Upload (afventer godkendelse)
