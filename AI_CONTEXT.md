# AI_CONTEXT.md

## 1) Project overview (ground truth)
- This repository is **not** a React/Vite project. It is a procedural PHP + MySQL web app with static HTML/CSS views and server-rendered pages.
- App purpose: hostel management with public landing pages plus student/rector flows.
- Current date context from environment: **2026-04-14 (UTC)**.
- Existing architectural constraints are documented in this same file previously; those constraints are preserved in spirit but the project implementation currently contains some inconsistencies (noted below).

## 2) Tech stack
- Backend: PHP (procedural, mysqli).
- Database: MySQL/MariaDB.
- Frontend: plain HTML + CSS (no JS framework).
- JS usage: minimal, jQuery loaded in some dashboard pages for content injection (`$("#content-area").load(...)`).
- Runtime assumptions: local XAMPP-style setup (`localhost`, `root`, empty password).

## 3) Folder/file structure map
- Root PHP pages:
  - Public: `index.php`, `about.php`, `fee_structure.php`, `login.php`, `register.php`.
  - Student area: `sdashboard.php`, `my_room_details.php`, `fee_status.php`, `notices.php`, `leave_request.php`, `request_status.php`.
  - Rector/Admin area: `rector_dashboard.php`, `admin_dashboard.php`, `approve_students.php`, `check_rooms.php`, `check_students.php`, `manage_students.php`, `send_notices.php`, `approve_leave.php`, `assign_room.php`, `update_fees.php`.
  - Utility: `db_connect.php`, `config.php`, `logout.php`, `test_connection.php`.
- Stylesheets:
  - Public pages: `Istyle.css`, `astyle.css`, `fee_structure.css`.
  - Auth pages: `styles.css`.
  - Dashboards: `adminstyle.css`, `dashstyle.css`.
- Assets/data:
  - `asset/` folder contains `I_BG.jpg`, `20944167.jpg`.
  - Database dump: `Databse/hostel_management (1).sql` (folder name is spelled `Databse`).

## 4) Routing/navigation map
- Public nav links are hardcoded in each public file:
  - Home -> `index.php`
  - About -> `about.php`
  - Fee Structure -> `fee_structure.php`
  - Login -> `login.php`
  - Register -> `register.php`
- Login redirect behavior:
  - `rector` -> `rector_dashboard.php`
  - `student` -> `sdashboard.php`
- Student dashboard uses JS-loaded subpages in `#content-area`:
  - Room details / fee status / notices / leave request / request status.
- Rector pages are primarily direct full-page navigations from sidebars.

## 5) Component map (PHP page = component unit)
Since this is not React, there are no JSX components. The practical component boundaries are page files:

### Public-facing pages
- `index.php`
  - Header/nav, hero, 3 info boxes, footer.
- `about.php`
  - Header/nav, hero, 4 about cards, footer.
- `fee_structure.php`
  - Header/nav, hero, fee table, footer.

### Authentication
- `login.php`
  - Form, error messaging, session bootstrap.
- `register.php`
  - Student registration form (forces user_type `student`, status `pending`).

### Student workspace
- `sdashboard.php`
  - Sidebar shell + AJAX content panel.
- `my_room_details.php`
  - Room number + roommate table.
- `fee_status.php`
  - Fee table for logged-in user.
- `notices.php`
  - Notice listing table.
- `leave_request.php`
  - Leave request form.
- `request_status.php`
  - Leave history + pending cancel action.

### Rector/Admin workspace
- `rector_dashboard.php`
  - Rector sidebar shell.
- `admin_dashboard.php`
  - Legacy admin dashboard page (checks for `admin` role).
- `approve_students.php`
  - Pending students table + approve action.
- `check_rooms.php`
  - Room-to-occupants table.
- `check_students.php`
  - Student directory table.
- `manage_students.php`
  - Remove student + room reassignment.
- `send_notices.php`
  - CRUD notices.
- `approve_leave.php`
  - Approve/reject leave requests.
- `assign_room.php`
  - Allocate room where capacity available.
- `update_fees.php`
  - Fetch and update fee record.

## 6) Section-by-section page breakdown + exact file locations

### `index.php` sections
1. Header/nav
2. Hero (“Welcome to Our Hostel”)
3. Info cards (“About Us”, “Fee Structure”, “Contact”)
4. Footer

### `about.php` sections
1. Header/nav
2. Hero (“About Our Hostel”)
3. About card grid (Facilities, Location, Rules, Contact)
4. Footer

### `fee_structure.php` sections
1. Header/nav
2. Hero (“Hostel Fee Structure”)
3. Fee table (6 static room-type rows)
4. Footer

### `sdashboard.php` sections
1. Sidebar menu
2. Dynamic content area (`#content-area`) loaded by jQuery

### Rector operational pages (shared layout style)
- Each uses `.dashboard-container` -> `.sidebar` + `.content` with operation-specific forms/tables.

## 7) Which file renders which section (quick lookup)
- Landing hero + cards: `index.php`
- About cards: `about.php`
- Fee table: `fee_structure.php`
- Student shell: `sdashboard.php`
- Rector shell: `rector_dashboard.php`
- Notices table: `notices.php`
- Footer areas: `index.php`, `about.php`, `fee_structure.php`

## 8) Placeholder inventory (requested items + reality check)
User-requested placeholders were inspected literally; findings:

### A) Rocket placeholder graphic boxes
- **Status:** Not found in this codebase.
- Searched terms include `rocket`, `🚀`, and related section names; no matching content/components exist.
- Closest analogous UI is the 3 info boxes in `index.php` (`<div class="box">`) but they contain text only, no rocket graphics.

### B) “Tomorrow’s Aerospace Engineers” section
- **Status:** Not found.

### C) “Our Rocket Series” section
- **Status:** Not found.

### D) “How It All Started” section
- **Status:** Not found.

### E) Footer social links with emoji placeholders
- **Status:** Not found.
- Current footers only contain copyright text; no social link list/icons.

### F) Timeline section with first two images cut off
- **Status:** Not found.
- No timeline markup/classes found; no image gallery sections in public pages.

## 9) Image inventory
- Actual files in repo:
  - `asset/I_BG.jpg`
  - `asset/20944167.jpg`
- Actively referenced image URLs in CSS:
  - `Istyle.css`: `background: url('assets/I_BG.jpg')` (note: points to `assets/` plural, folder does not exist).
  - `astyle.css`: same path issue.
  - `fee_structure.css`: same path issue.
- No `<img>` tags in public landing/about/fee pages.
- No timeline/rocket images in source.

## 10) Footer inventory
- `index.php`: simple text footer only.
- `about.php`: simple text footer only.
- `fee_structure.php`: simple text footer only.
- No social links, no emoji icons, no SVG icon system.

## 11) Timeline/image layout notes (specific request)
- There is no timeline section in current files.
- Because no timeline markup or images are present, there is no active `object-fit`, container-height, overflow, aspect-ratio, or grid/flex clipping bug to isolate for that feature.
- If user is referring to another branch/version, this repo state does not contain it.

## 12) Logo notes (specific request)
- No dedicated logo asset reference found (`logo`, `<img ...logo...>`, favicon-like branding elements absent).
- Current public header shows only text nav links; no logo rendered.
- Therefore:
  - “How current logo is displayed” -> not displayed (no logo markup).
  - “Whether background baked into image” -> cannot evaluate; no logo file reference exists.
  - “File format used” -> unknown/not applicable in current branch.
  - “What needed for clean logo display later” -> add a transparent asset (prefer SVG/PNG with alpha), place in asset folder, render via `<img>` in header, constrain with explicit dimensions and `object-fit: contain`.

## 13) Footer social icon notes (specific request)
- No social section exists in footer.
- No emoji placeholders exist for social icons in current source.
- Future replacement strategy (when feature is added): keep social links in a single structured list (array/config/PHP include), use official SVG icons (e.g., simple-icons or self-hosted SVG), and render accessible anchor labels.

## 14) Current design system notes
- Typography: Arial default throughout.
- Public pages:
  - Dark translucent overlays on top of full-page background image.
  - Card-based blocks (`.box`, `.about-box`) with rounded corners and shadows.
- Auth pages (`styles.css`): centered card form on flat dark background.
- Dashboard pages:
  - Full-height split layout with left sidebar + right main content.
  - Colorful gradients (`dashstyle.css` blue-purple, `adminstyle.css` red-orange).
- Repetition and inconsistency:
  - CSS tokens repeated across multiple files.
  - Duplicate/overlapping table/button selectors in dashboard CSS.
  - `dashstyle.css` contains malformed nesting (`form` styles appear inside `.content-box` block due to brace placement), which can cause parsing anomalies.

## 15) Current spacing/layout behavior
- Most screens force `height: 100vh`, creating rigid viewport layouts.
- Dashboard content is centrally aligned (`align-items:center`) which may constrain large forms/tables vertically.
- Public sections use fixed width percentages and fixed padding values.
- Table UIs are full-width in container but rely on static cell padding.

## 16) Current assets situation
- Asset folder naming mismatch:
  - Files stored under `asset/`.
  - CSS points to `assets/`.
- Result: background image likely fails to load unless duplicated path exists externally.
- `asset/20944167.jpg` is currently unreferenced.

## 17) Known issues + suspected causes
1. **Project type mismatch vs request**
   - Requested React/Vite structure not present.
   - Cause: repository content is PHP app.
2. **Requested aerospace/rocket/timeline/footer-social sections missing**
   - Cause: those sections do not exist in this branch.
3. **Background image path bug**
   - Cause: `assets/I_BG.jpg` typo vs actual `asset/I_BG.jpg` directory.
4. **Role model inconsistency across files**
   - Some pages still guard on `admin` (`admin_dashboard.php`, `check_rooms.php`, `check_students.php`) while login restricts to `student|rector`.
5. **Potential CSS parsing issue in `dashstyle.css`**
   - Cause: mismatched brace placement around `.content-box` block and nested form styles.
6. **Redundant DB connection files**
   - `db_connect.php` and `config.php` duplicate connection logic.

## 18) Safe edit points (low-risk change zones)
- Static public content text blocks in `index.php`, `about.php`, `fee_structure.php`.
- Footer text in the 3 public files.
- Dashboard sidebar labels/ordering (while keeping target file names intact).
- CSS token cleanup in dedicated stylesheet files (with regression checks).
- `AI_CONTEXT.md` as canonical change log for structure mapping.

## 19) Must-not-change accidentally
- Session auth checks and redirects.
- SQL behavior for approval/assignment/fee updates without transactional integrity.
- DB table expectations in `Databse/hostel_management (1).sql`.
- Rector/student role logic unless explicitly requested (currently mixed state; changes here impact login/authorization).
- Core navigation file names used by links and AJAX loaders.

## 20) Likely edit path by issue (ordered)
1. **Confirm baseline project type and intended target branch**
   - If React/Vite is intended, verify correct repo/branch before any UI work.
2. **Fix broken asset pathing (`asset` vs `assets`)**
   - Update CSS paths or folder naming consistently.
3. **Normalize role guards (`admin` vs `rector`)**
   - Align `check_rooms.php`, `check_students.php`, `admin_dashboard.php` with login policy.
4. **Repair CSS structural issues in `dashstyle.css`**
   - Correct brace structure and de-duplicate shared selectors.
5. **If aerospace sections are truly desired, add them explicitly**
   - Create new sections/files intentionally (not as “fixes” to missing existing blocks).
6. **Introduce footer social icons only after structure decision**
   - Implement via centralized data and proper SVG icons.

## 21) Placeholder/section records in requested format
Because requested sections are absent, records below include nearest analogs and replacement guidance:

### Record: `index.php` info boxes (closest “placeholder-like” blocks)
- File: `index.php`
- Component/page unit: Home landing page
- Current content: three text cards (“About Us”, “Fee Structure”, “Contact”)
- Probably replace later with: richer card content, real imagery/icons, links to deeper pages
- Layout risk: `.info` uses horizontal flex + fixed 30% card width; adding large content or images may overflow on smaller screens without responsive rules

### Record: Footer blocks
- Files: `index.php`, `about.php`, `fee_structure.php`
- Component/page unit: Public page footer
- Current content: copyright text only
- Probably replace later with: social links, contact details, legal links
- Layout risk: minimal now; risk increases if multiple columns/icons added without responsive footer layout

### Record: Missing rocket/aerospace/timeline/logo/social elements
- Files/components: none found
- Current content: not present
- Probably replace later with: newly created sections/components
- Layout risk: high if inserted into existing fixed-height/flex-centered pages without breakpoint handling

## 22) Verification commands used for this context pass
- `rg --files`
- `sed -n ...` across all PHP/CSS/SQL files for direct inspection
- `rg -n "rocket|Tomorrow|Aerospace|Our Rocket Series|How It All Started|timeline|emoji|🚀|footer|logo|social|icon" *.php *.css`
- `find . -maxdepth 2 -type d | sort`

---
This file is now the canonical context baseline for this repository state. Read/update it before structural edits.
