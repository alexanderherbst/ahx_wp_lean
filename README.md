# AHX WP Lean

Kleines, responsives WordPress-Theme mit Fokus auf Einfachheit, Zugänglichkeit und leichter Anpassbarkeit.

## Features
- Responsive Layout mit zweistufiger Navigation
- Accessibility-Verbesserungen (Skip-Link, ARIA für Burger-Button)
- `wp_nav_menu()`-Support mit Fallback auf Seitenstruktur (depth=2)
- Enqueue-Optimierungen (Cache-Busting via `filemtime`, Skript im Footer)
- Automatisch angelegtes Default-Menu beim Theme-Aktivieren

## Installation
1. Ordner `ahx_wp_lean` in `wp-content/themes/` kopieren.
2. Theme im WP-Admin unter Design → Themes aktivieren.
3. Im Admin unter Design → Menüs ein Menü anlegen oder das automatisch erstellte Menü anpassen.

## Anpassung / Entwicklung
- Hauptdateien:
  - [functions.php](functions.php) — Theme-Supports, Enqueue, Aktivierungs-Routinen
  - [header.php](header.php) — Seitenkopf, Skip-Link, Navigation
  - [template-parts/navigation.php](template-parts/navigation.php) — `wp_nav_menu()`-Einbindung
  - [style.css](style.css) — Basis-Styles
  - [script.js](script.js) — Menü-Toggle / Accessibility

- Übersetzungen: `load_theme_textdomain()` ist vorhanden; PO/POT-Dateien legen Sie in `languages/` ab.
- Für Produktionsbetrieb: CSS/JS minifizieren und Bilder optimieren (WebP, srcset).

## To‑Do / Empfehlungen
- Ergänzen von Templates: `single.php`, `page.php`, `404.php`, `archive.php`
- Erweiterte Accessibility: Keyboard-Navigation für Untermenüs
- Bildoptimierung und responsive `srcset`
- Optional: `theme.json` für Block-Editor-Support

## License
Dieses Theme steht unter der GNU General Public License v2 (oder später).

---
Wenn du möchtest, erstelle ich eine POT-Datei, ergänze fehlende Templates oder richte eine kleine Build-Pipeline ein.
