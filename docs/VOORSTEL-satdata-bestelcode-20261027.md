# Voorstel: bestelcode verwijderen uit `pages\satdata.php`

| | |
|---|---|
| Datum | 27-10-2026 |
| Server | Meteobase-productieserver 8833EAF (Windows Server 2022, Apache 2.4 + PHP 8, PostgreSQL) |
| Status | **Voorstel, niet toegepast.** Ook deze versie is alleen door onderzoek tot stand gekomen; op de server is niets gewijzigd. |
| Gevraagd van de expert | (1) beoordeling van dit voorstel, (2) beoordeling van de wijzigingen die al op 26-10-2026 zijn doorgevoerd (hoofdstuk 9) |

In dit document staan geen wachtwoorden, tokens, IP-adressen, e-mailadressen of klantgegevens. Waarden van geheimen zijn niet opgenomen; waar een geheim door de code wordt gebruikt, staat alleen de naam van de constante.

---

## 1. Samenvatting

- Het tabblad **SAT Data 2.0** (`index.php?tb=satdata`) toont alleen nog **zes downloadlinks** naar vaste archieven (2016–2018). Er is geen bestelformulier meer: er staat nog wel een leeg `<form>`-element met één verborgen veld, maar er zijn geen invoervelden en geen verzendknop.
- `pages\satdata.php` bevat daarnaast nog ±250 regels **bestelcode**. Die wordt alleen uitgevoerd bij een POST met het veld `subrd`. Ze maakt een databaseverbinding, neemt een bestelnummer, schrijft een rij in `tbbasis` en start een rekentool. Deze code werkt niet meer goed: de tools krijgen 2 argumenten te weinig (zie 3.2). Bovendien ontbreken CSRF-controle en een rate limit.
- SATDATA (actuele verdamping, verdampingstekort) wordt al besteld via het tabblad **Rasterdata** (`pages\raster01.php` r. 101 en 104, `pages\rasterdata.php`). Er gaat dus geen functionaliteit verloren.
- In de toegangslogs (23-11-2023 t/m 27-10-2026) komt **geen enkele legitieme POST** naar `tb=satdata` voor. Er zijn alleen scannerverzoeken met aanvalspayloads en de 5 test-POSTs van 26-10-2026.
- **Voorstel:** `satdata.php` terugbrengen tot alleen het tonen van de pagina (variant A, 6 regels). Optioneel: het lege formulier en de oude, uitgecommentarieerde HTML uit `satdata01.php` halen (deel B). Een POST alsnog met 405 weigeren (variant C) kan ook en wordt als alternatief besproken.

## 2. Context

### 2.1 Architectuur van `index.php` en de tabbladen

De site is één groot PHP-bestand `C:\Apache24\htdocs\index.php` (4.008 regels: PHP, HTML en inline JavaScript) met tabbladen in `htdocs\pages\`.

- `index.php` r. 109–121: whitelist `$allowTabs` (o.a. `satdata`, `rasterdata`, `rasterDataUpload`, `onderhoud`).
- r. 124–135: `$tab = $_GET['tb']` als die op de whitelist staat. Anders volgt een `Refresh`-header naar `index.php`. Zonder `tb` wordt het tabblad `aanmelden`.
- r. 138–148: twee tabbladen worden **vóór** de HTML-uitvoer afgehandeld en sluiten af met `exit`: `onderhoud` (backoffice-login) en sinds 26-10-2026 `rasterDataUpload` (de upload-endpoint, zie hoofdstuk 9).
- r. 214–218: `$content = 'pages/' . $tab . '.php'` (alleen `feedback` wijkt af).
- r. 730: `<?php include($content); ?>` staat **midden in de HTML**, na menu en kaart. Een tabbladpagina kan daarom geen HTTP-status of headers meer zetten. Fouten en meldingen gaan via `$_SESSION['errorMsg']` / `$_SESSION['dataFeedbackMsg']`, die onderaan in `index.php` als JavaScript-alert worden getoond (r. 3979–4010).
- Bestellingen in alle tabbladen werken op dezelfde manier: POST naar `index.php?tb=<tab>`, de tabbladpagina valideert, neemt een bestelnummer uit sequence `public.tbbasis_bestellingnr_seq` (`next_id()` in `htdocs\sanitize.php`), schrijft een rij in `tbbasis` en start een .NET-tool in `C:\Program Files\Hydroconsult\` via `start_tool()` (zonder shell, argumenten gevalideerd). De tool maakt het resultaat en mailt de klant.
- De gebruiker is "aangemeld" via cookie `gebruiker[naam|mail|sessionid]` (geen wachtwoord). `valid_user_cookie()` in `sanitize.php` controleert die cookie.

### 2.2 Waarom deze wijziging

Op 26-10-2026 kreeg `satdata.php` dezelfde shapefile-controle als `rasterdata.php` (hoofdstuk 9). Bij het testen bleek:

1. Een geldige SAT Data-bestelling via een zelfgemaakte POST levert niets op. De site meldt "in goede orde ontvangen", maar de tool stopt met *incorrect number of arguments* en draait daarna als sessie 0 / bestelling 0. De mail aan de klant mislukt.
2. De pagina heeft geen formulier waarmee een klant deze code kan bereiken. `satdata.php` is alleen bereikbaar met een handgemaakte POST, zonder CSRF-token en zonder rate limit. Elke POST met `subrd` kost een bestelnummer en een databaserij en start een tool.

Er zijn twee opties: de ontbrekende argumenten aanvullen of de bestelcode verwijderen. Omdat SATDATA al via Rasterdata te bestellen is en niemand dit pad gebruikt, stelt dit document verwijderen voor.

### 2.3 Wat al is gedaan (26-10-2026, details in hoofdstuk 9)

- `htdocs\shapefile_check.php` (nieuw): controle van een gezipte shapefile (polygonen, RD, compleet, max. 5.000 objecten, bruikbare ID-velden, unieke en ingevulde waarden).
- `pages\rasterDataUpload.php` (herschreven): upload-endpoint met POST-only, CSRF, gebruikerscookie, rate limit 10/10 min, opslag onder willekeurige naam, en registratie van de upload in de sessie.
- `pages\rasterdata.php` en `pages\satdata.php`: bij bestellen moet de upload uit de eigen sessie komen, en het veld moet bestaan met unieke, ingevulde waarden. Bij `satdata.php` gebeurt die controle vóór het nemen van een bestelnummer.
- `index.php`: de upload-endpoint wordt vóór de HTML afgehandeld; Dropzone stuurt het CSRF-token mee en toont meldingen als tekst.
- `httpd.conf`: `meteobase\uploads\` is niet meer via HTTP op te vragen. `php.ini`: `extension=zip`, upload- en POST-limiet 20 MB.

## 3. Bevindingen

### 3.1 Routering van `?tb=satdata` (onderzoekspunt 1)

| Stap | Bestand en regel | Wat er gebeurt |
|---|---|---|
| 1 | `index.php` r. 5–12 | `local_config.php`, `session_start()`, CSRF-token in sessie |
| 2 | `index.php` r. 17–90 | feedbackformulier (alleen bij POST met `userFullNameFeedback`) |
| 3 | `index.php` r. 109–135 | `satdata` staat op de whitelist → `$tab = 'satdata'` |
| 4 | `index.php` r. 138–148 | geen vroege afhandeling voor `satdata` |
| 5 | `index.php` r. 217 | `$content = 'pages/satdata.php'` |
| 6 | `index.php` r. 222–291 | aanmeldformulier (alleen bij POST met `userFullName`) |
| 7 | `index.php` r. 730 | `include('pages/satdata.php')` midden in de HTML |
| 8 | `pages\satdata.php` r. 7–8 | `include('local_config.php')` (constanten zijn met `if (!defined(...))` beschermd, dus geen dubbele definitie) en `require_once 'sanitize.php'` |
| 9 | `pages\satdata.php` r. 13 / 267–268 | zonder `$_POST['subrd']`: alleen `include('pages/satdata01.php')` |
| 10 | `pages\satdata.php` r. 55–94 | met `subrd` en `fileType` csv/sobek: `require_once 'shapefile_check.php'` |
| 11 | `index.php` r. 2846–2888 | tabblad-specifiek JavaScript voor `satdata` (kaartlaag, zie 3.6) |

Varianten:

- `?tb=satdata` (met of zonder `dp`/`dp_sub`) volgt altijd hetzelfde pad. `dp` en `dp_sub` sturen alleen het documentatiepaneel (`index.php` r. 294–303).
- **Desktopmenu**: de link `?tb=satdata&dp=satdata&dp_sub=introductie` staat op `index.php` r. 641 (menu Rasterdata → "SAT Data 2.0"). Het **mobiele menu** (r. 551–560) heeft geen link naar SAT Data.
- **Direct `/pages/satdata.php`**: `htdocs\pages\` heeft geen `Require all denied` in `httpd.conf`. Een direct verzoek voert het bestand los uit. Sinds 23-10-2026 geeft dat HTTP 500, omdat `require_once 'sanitize.php'` relatief tot `pages\` faalt (`php8_errors.log`, 23-10-2026). In de logs staan 13 directe GETs op `/pages/satdata.php` en 12 op `/pages/satdata01.php` (scanners, 2024–2026).

### 3.2 Wat `satdata.php` doet, per blok (onderzoekspunt 2)

Huidige versie: 270 regels.

| Regels | Blok | Nodig voor de downloadpagina? |
|---|---|---|
| 1 | `<div id="gegevenspaneel_aanmelden">`: wrapper. Wordt nergens gesloten, net als in `basisgegevens.php`, `rasterdata.php`, `rasterview.php` e.a. Stijl in `style.css` r. 213 | **ja** (lay-out ongewijzigd laten) |
| 2–8 | commentaar, `include('local_config.php')`, `require_once 'sanitize.php'` | nee (`index.php` laadt `local_config.php` al; `sanitize.php` is alleen nodig voor de bestelcode) |
| 10–12 | `$bericht`, `$data`, `$waarde_check` initialiseren | nee (worden door `satdata01.php` en door `index.php` na r. 730 niet gebruikt) |
| 13 | `if(isset($_POST['subrd']))`: begin van de bestelafhandeling | nee |
| 14–29 | uitgecommentarieerde oude upload | nee |
| 32–53 | checkboxen neerslag/makkink/penman → `$NSL`, `$MAKKINK`, `$PENMAN` | nee |
| 54–94 | `$formaat = $_POST['fileType']`; bij csv/sobek de shapefilecontrole van 26-10-2026 (cookie `ZIPFILE` → `$_SESSION['shape_uploads']`, veld in .dbf, `shape_field_values_ok`) | nee |
| 95–103 | `$sommen` (ongebruikt), datums uit POST, `$_COOKIE['gebruiker']` | nee |
| 106–128 | **database**: verbinding met `MASTERUSER`/`MASTERPASSWORD`/`HOST`/`POORT`/`DATABASE` uit `local_config.php`; `pg_ping`, bij fout `echo` en `exit` midden in de pagina | nee |
| 130 | `next_id($dbHandle, SEQ_BESTELLINGNR)`: **neemt een bestelnummer** | nee |
| 136–158 | datums omzetten, `$XMIN..$YMAX` uit cookies `minX/minY/maxX/maxY` (zonder controle op bestaan; 26-10 gaf dat "Undefined array key"-waarschuwingen), `$ExportFileName`/`$ResultsZIPFile` (ongebruikt) | nee |
| 161–201 | **INSERT in `tbbasis`** (`sessienr, startdatum, stopdatum, bestellingnr, neerslag, penman, makkink`, waarden `"<bool> GRID"`), prepared statement `satDataInsert` | nee |
| 204–212 | "foutafhandeling" ruimtelijke selectie en tijdselectie: zet `$ruimtelijke_selectie`/`$tijdselectie`, die **nergens worden gebruikt** | nee |
| 214 | `$adres` = oud IP-adres van de vorige server (http) | nee |
| 217–228 | `valid_user_cookie()` en `valid_token()` op alle toolargumenten | nee |
| 229–244 | argumentlijsten per formaat: ascii/modflow/simgro → `WIWBRASTER2ASCII.exe`, hdf5 → `WIWBNATIVERASTER.exe`, sobek/csv → `WIWBRASTERBYPOLY.exe`. **EVTACT en EVTSHO ontbreken**: RASTERBYPOLY krijgt 12 i.p.v. 14 argumenten, RASTER2ASCII 15 i.p.v. 17, NATIVERASTER 14 i.p.v. 16 (vergelijk `rasterdata.php` r. 334–335) | nee |
| 245–254 | `start_tool()`: **toolaanroep**; de tool verstuurt de **mail** | nee |
| 258–263 | `$link`, `$bericht`, `$data` (ongebruikt; `$data` leest `$coord_0..3` uit `index.php`); `$_SESSION['dataFeedbackMsg']` → succesmelding "in goede orde ontvangen" (`index.php` r. 3983–3998) | nee |
| 265 / 268 | `include('pages/satdata01.php')` | **ja** |

**Cookies en sessie:** `satdata.php` zet zelf geen cookies. Het leest `gebruiker[...]`, `ZIPFILE`, `minX/minY/maxX/maxY`, en `$_SESSION['shape_uploads']`, `errorMsg` en `dataFeedbackMsg`. Ook `order_error()` gebruikt alleen de sessie.

**`satdata01.php`** (180 regels):
- r. 1–63: uitgecommentarieerd oud formulier. Het staat in HTML-commentaar (`<!-- -->`) en wordt dus wél naar de browser gestuurd; de PHP-fragmenten erin staan achter `//` en worden niet uitgevoerd.
- r. 74–103: `<form method="POST" … action="…?tb=satdata…" class="sat-data" id="rasterDataForm">` met alleen `<input type="hidden" name="subrd">` en zes `<a>`-links naar `meteobase\downloads\fixed\etact_201[678]_NL.7z` en `etdef_201[678]_NL.7z`. Er zijn geen invoervelden en er is geen knop.
- r. 110–180: handleidingslider.

### 3.3 Gebruik van `satdata.php` door andere code (onderzoekspunt 3)

Zoekopdracht `satdata|satData|SATDATA|SatData` in heel `htdocs` (PHP, JS, CSS, HTML), `backoffice\`, `C:\Apache24\conf\`:

| Plaats | Wat | Raakt het voorstel? |
|---|---|---|
| `index.php` r. 113 | whitelist | nee (blijft) |
| `index.php` r. 641 | menulink | nee (blijft) |
| `index.php` r. 1358, 1610 | links naar SATDATA-handleiding/rapport (documentatie) | nee |
| `index.php` r. 2846–2888 | JS-blok `if ($tab == 'satdata')`, kaart (zie 3.6) | nee |
| `pages\raster01.php` r. 101, 104 | checkboxen `evtact`/`evtsho` "(SATDATA 3.0)" op het tabblad Rasterdata | nee, dit is de huidige bestelroute |
| `pages\satdata.php`, `pages\satdata01.php` | zelf | ja |
| `backoffice\`, `css\`, `js\`, `style.css`, `conf\` | geen treffers | — |

- Functies en variabelen: `satdata.php` definieert geen functies. De variabelen die het zet (`$adres`, `$link`, `$bericht`, `$naam`, `$mail`, `$user`, `$dbHandle` e.a.) worden door `index.php` na r. 730 **niet** gebruikt (gecontroleerd met grep op regelnummer > 730). `$link` en `$adres` worden in `index.php` alleen vóór de include gebruikt (r. 305–354).
- Prepared statement `satDataInsert`: alleen in `satdata.php`.
- `$_SESSION['dataFeedbackMsg']` wordt ook gezet door `basisgegevens.php`, `rasterdata.php`, `stedelijk.php` en `toetsingsdata.php`. De weergave in `index.php` blijft dus nodig.

### 3.4 Toegangslogs (onderzoekspunt 4)

Bron: `C:\Apache24\logs\access.log` (formaat *common*, alle vhosts; HTTP en HTTPS). De log loopt van **23-11-2023 t/m 27-10-2026** en is niet geroteerd. `ssl_request.log` (alleen HTTPS, zonder statuscode) geeft hetzelfde beeld. Er is geteld op regels met "satdata" in de request-regel; IP-adressen zijn niet uitgelezen. "Normaal" betekent exact `/?tb=satdata`, `/index.php?tb=satdata`, eventueel met `&dp=satdata` en `&dp_sub=introductie`. "Afwijkend" is al het andere, vooral scannerpayloads in `dp`/`dp_sub` (SQL-injectie, log4j, command injection, path traversal).

**POST naar `tb=satdata`**

| | Aantal |
|---|---|
| Totaal in de log | 6.958 |
| waarvan "afwijkend" (scanner) | 6.953, verdeeld over **15 dagen**, steeds in één burst: 09-04-2025 (1.203), 15-06, 09-07, 13-08, 09-09, 16-10, 09-11 (1), 09-12-2025, 09-02, 09-05 (1.052), 09-06, 09-07, 09-08, 09-09, 09-10-2026 (elk ±370–430) |
| waarvan "normaal" | **5**, alle op 26-10-2026: de eigen tests (4 weigeringen en 1 geldige bestelling, zie 2.2) |
| Statuscode | alle 200 (de pagina geeft altijd 200, ook bij weigering) |
| Laatste legitieme klant-POST | **geen in de hele logperiode** (sinds 23-11-2023). Wanneer het formulier is verdwenen, blijkt niet uit de logs. |

Of de scanner-POSTs het veld `subrd` bevatten (en dus de bestelcode raakten), is niet uit de logs af te leiden: de POST-body wordt niet gelogd. `php8_errors.log` bestaat pas sinds 21-10-2026. De oudere PHP-foutlogs (`logs\htdocs_php_errors_verplaatst_20261022\`) bevatten geen regels over `satdata`.

**GET naar `tb=satdata`, laatste 12 maanden (27-10-2025 t/m 27-10-2026)**

| | Aantal | Status |
|---|---|---|
| Normaal | 2.342 | 2.340× 200, 2× 503 |
| Afwijkend (scanners en varianten) | 7.876 | 7.654× 200, 126× 206, 61× 304, 21× 403, 10× 404, 3× 503, 1× 500 |

Normale GETs per maand (HTTPS-log) liggen tussen ±40 en ±200, met een piek in sep–nov 2025 en sep–okt 2026. De pagina wordt dus gewoon bezocht (voor de downloadlinks), en dat moet zo blijven.

### 3.5 Bestellingen in `tbbasis` (onderzoekspunt 5)

Alleen gelezen: één verbinding met `default_transaction_read_only = on`, alleen tellingen, geen persoonsgegevens.

- Kolommen van `tbbasis`: `sessienr, startdatum, stopdatum, bestellingnr, tijdswaarde, meteostations[], neerslag, penman, makkink, stochast, tijdreeks, klimaatscenario, ZJ_HUIDIG, ZJ_2030, ZJ_2050, ZJ_2085`. Er is **geen besteldatum en geen kolom voor bron of tabblad**.
- Totaal 464.412 rijen. Rijen met `… GRID` in neerslag/penman/makkink (rasterbestellingen): 15.543. Rijen met `neerslag LIKE '% GRID'`: 12.151, bestelnummers 945 t/m 464.367.
- `satdata.php` schrijft **precies dezelfde kolommen en waarden** als `rasterdata.php` (`satdata.php` r. 161–193 tegen `rasterdata.php` r. 269–307). EVTACT/EVTSHO worden in geen van beide opgeslagen. **SAT Data-bestellingen zijn in `tbbasis` dus niet van Rasterdata-bestellingen te onderscheiden.** Ook een laatste datum is niet te bepalen.
- Wel bekend: de geldige testbestelling van 26-10-2026 (testgebruiker 19723) heeft een bestelnummer en een rij gekregen. Die rij staat er nog, net als de andere testrijen van 26-10.

### 3.6 JS/CSS voor het oude bestelformulier (onderzoekspunt 6)

Er bestaat **geen JS of CSS die alleen voor het SAT Data-bestelformulier is.** Wel hangt het formulier van SAT Data via `id="rasterDataForm"` aan code die voor Rasterdata bedoeld is:

| `index.php` | Code | Effect op de SAT Data-pagina |
|---|---|---|
| r. 3086–3125 | click-handler `#rasterDataForm .submit-btn` | geen: er is geen `.submit-btn` op SAT Data |
| r. 3642–3705 | `$('#rasterDataForm').validate({...})` (fromDate/toDate/fileType) | wordt gebonden, maar er zijn geen velden en geen submit |
| r. 3831–3856 | herstel van formulierdata uit cookie `rasterDataFormData` en `trigger('click')` op `.submit-btn` | draait op SAT Data mee (want `#rasterDataForm` bestaat), maar doet niets zonder velden en knop |
| r. 3983–3998 | succesmelding `dataFeedbackMsg` | gedeeld met andere tabbladen, blijft nodig |
| r. 2846–2888 | `if ($tab == 'satdata')`: OpenLayers-kaart met `$_SESSION['rasterViewImage']` (kopie van het Herhalingstijd-blok r. 2801 e.v.; die sessievariabele wordt alleen door `rasterview.php` r. 46 gezet) | hoort bij de kaart, niet bij het formulier. Waarschijnlijk overbodig, maar buiten de scope van dit voorstel (zie open vraag 5) |

CSS: `.sat-data` en `#rasterDataForm` komen in geen enkel stylesheet voor. `.form-row-container .form-container` (`css\style.css` r. 304) blijft gelden, ook als het `<form>`-element een `<div>` wordt.

**Dode code** is dus vooral: `satdata.php` r. 13–266 (de hele bestelafhandeling), `satdata01.php` r. 1–63 (uitgecommentarieerd oud formulier, wordt wél naar de browser gestuurd) en het lege `<form>`-element met `subrd` in `satdata01.php` r. 74–75/103.

## 4. Voorgestelde wijziging (niet toegepast)

### 4.1 Variant A (aanbevolen): `satdata.php` toont alleen de pagina

Alle bestelcode vervalt. Een POST (met of zonder `subrd`) toont gewoon de pagina met status 200, zonder databaseverbinding, bestelnummer, toolaanroep of melding. `php -l` op de voorgestelde versie: *No syntax errors detected*.

Nieuwe inhoud (6 regels):

```php
<div id="gegevenspaneel_aanmelden">
<?php
// SAT Data 2.0: only download links to the fixed SATDATA archives (pages/satdata01.php).
// SATDATA (actual evapotranspiration, evaporation deficit) is ordered on the tab
// Rasterdata (pages/rasterdata.php); this tab has no order form, so a POST just shows the page.
include('pages/satdata01.php');
?>
```

Diff ten opzichte van de huidige versie. De verwijderde regels bevatten alleen namen van constanten, geen waarden; het IP-adres van de oude server in r. 214 is vervangen door <IP-ADRES-OUDE-SERVER>:

```diff
--- a/htdocs/pages/satdata.php (huidig)
+++ b/htdocs/pages/satdata.php (voorstel)
@@ -1,270 +1,7 @@
 <div id="gegevenspaneel_aanmelden">
 <?php
-// ** Renamed to v6.7 by Arend Ketelaar on 2012-04-24
-// Version 6-7
-// Meteobase
-
-include('local_config.php');
-require_once 'sanitize.php';
-
-$bericht = '';
-$data = '';
-$waarde_check = '';
-if(isset($_POST['subrd'])){
-    // Upload verwerken (Rasterdata, SOBEK)
-    $locatie_zip = '';
-    $valid_file = true;
-//    if($_FILES['zipfile']['name']) {
-//        if(!$_FILES['zipfile']['error']) {
-//            $file_tmp = $_FILES['zipfile']['tmp_name'];
-//            $file_name = strtolower($_FILES['zipfile']['name']);
-//            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/meteobase2/uploads/";
-//            if($_FILES['zipfile']['size'] > (6000000)) { $valid_file = false; }
-//            $file_ext = end(explode('.',$file_name));
-//            if($file_ext !== 'zip') { $valid_file = false; }
-//            if($valid_file) { move_uploaded_file($file_tmp,"$upload_dir".strtolower($_FILES['zipfile']['name'])); }
-//            $locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\' . $file_name;
-//            $veldnaam = addslashes($_POST['veldnaam']);
-//        }
-//    }
-    
-    // Verwerken van het formulier
-    $gegevenstype = '';
-    if(isset($_POST['neerslag'])){
-        $neerslag = $_POST['neerslag'];
-        $gegevenstype.= $neerslag . ' ';
-        $NSL = "true";
-    } else {
-        $NSL = "false";
-        }
-    if(isset($_POST['makkink'])){
-        $makkink = $_POST['makkink'];
-        $gegevenstype.= $makkink . ' ';
-        $MAKKINK = "true";
-    } else {
-        $MAKKINK = "false";
-    }
-    if(isset($_POST['penman'])){
-        $penman = $_POST['penman'];
-        $gegevenstype.= $penman . ' ';
-        $PENMAN = "true";
-    } else {
-        $PENMAN = "false";
-    }
-    $formaat = $_POST['fileType'];
-    $zipNaam = null;
-    if ($formaat == 'csv' || $formaat == 'sobek') {
-        // same check as pages/rasterdata.php, before an order number is taken: the ZIPFILE cookie holds the
-        // stored name of an upload of this session and this user (pages/rasterDataUpload.php, shapefile_check.php)
-        require_once 'shapefile_check.php';
-        $shapeUser = valid_user_cookie();
-        if ($shapeUser === null) {
-            order_error(MSG_INVALID_USER, 'satdata: gebruiker-cookie');
-            include('pages/satdata01.php');
-            return;
-        }
-        $zipNaam = valid_filename(basename(str_replace('\\', '/', (string)($_COOKIE['ZIPFILE'] ?? ''))), 'zip');
-        $upload = $zipNaam !== null ? ($_SESSION['shape_uploads'][$zipNaam] ?? null) : null;
-        if ($upload === null || strpos($zipNaam, $shapeUser['sessionid'] . '_') !== 0 || !is_file(SHAPE_UPLOAD_DIR . $zipNaam)) {
-            order_error('Uw bestelling is niet verwerkt: er is geen geldige shapefile geüpload in deze sessie. Upload uw shapefile (zip) opnieuw.',
-                'satdata: shapefile niet van deze sessie');
-            include('pages/satdata01.php');
-            return;
-        }
-        // the field must exist in the .dbf; the tool gets the name as it is written there
-        $opgegeven = valid_text($_POST['veldnaam'] ?? null, 64);
-        $veldnaam = null;
-        foreach ($upload['velden'] as $veld) {
-            if ($opgegeven !== null && strcasecmp($veld, $opgegeven) === 0) {
-                $veldnaam = $veld;
-            }
-        }
-        if ($veldnaam === null) {
-            order_error('Uw bestelling is niet verwerkt: kies het veld met het gebieds-ID. Beschikbare velden: '
-                . implode(', ', $upload['velden']) . '.', 'satdata: veldnaam niet in .dbf');
-            include('pages/satdata01.php');
-            return;
-        }
-        if (!shape_field_values_ok(SHAPE_UPLOAD_DIR . $zipNaam, $veldnaam)) {
-            order_error('Het veld ' . $veldnaam . ' bevat lege of dubbele waarden. Ieder polygoon moet in dit veld een eigen, ingevulde waarde hebben.',
-                'satdata: lege/dubbele waarden in ' . $veldnaam);
-            include('pages/satdata01.php');
-            return;
-        }
-    }
-    $sommen = $_POST['sommen'];
-    
-    //$waarde = $_POST['waarde'];
-    $datum_van = $_POST['fromDate'];
-    $datum_tot = $_POST['toDate'];
-    $gebruiker = $_COOKIE['gebruiker'];
-    $mail = $gebruiker['mail'];
-    $naam = $gebruiker['naam'];
-    $sessionid = $gebruiker['sessionid'];
-//    $tel = $gebruiker['tel'];
-
-//Gekopieerd van backoffice_basisgegevens
-// ****** Login-data *********
-    $sConnectionString="UNSET";
-    $dbHandle=0;
-    $sMasterUser = MASTERUSER;
-    $sMasterPassword = MASTERPASSWORD;
-    $sHost = HOST;
-    $sPoort =POORT;
-    $sDataBase = DATABASE;
-    $sTableName = "tbbasis";
-
-// ** Compile the connection-string to the DB:
-    $sConnectionString= "host=" .$sHost. " port=" .$sPoort. " user=" .$sMasterUser . " password=" .$sMasterPassword . " dbname=" .$sDataBase ;
-// ** and connect to the database Server:
-    $dbHandle= pg_connect($sConnectionString);
-// ** Test for access to the database :
-    IF (!pg_ping($dbHandle) )
-    {
-        echo "Geen PostgreSQL server verbinding" . "<BR>";
-        // (PHP 8: geen pg_free_result op een verbinding)
-        pg_close($dbHandle);
-        exit;
-    }
-// New order number from the sequence (see next_id in sanitize.php)
-    $NewOrder = next_id($dbHandle, SEQ_BESTELLINGNR);
-    //echo("SessionID : ".$sessionid . "<br>");
-    //echo("OrderID : ".$NewOrder . "<br>");
-    //Gekopieerd van backoffice_basisgegevens
-
-    // variabelen voor de EXE
-    $datum_array = array();
-    $FDATE = explode("/", $datum_van);
-    $datum_array[] = $FDATE;
-    $FDATE = $FDATE[2].$FDATE[1].$FDATE[0];
-    $TDATE = explode("/", $datum_tot);
-    $datum_array[] = $TDATE;
-    $TDATE = $TDATE[2].$TDATE[1].$TDATE[0];
-    $RasterDataDir = "d:\METEOBASE\RasterData";
-    $XMIN = round($_COOKIE['minX'], 0);
-    $YMIN = round($_COOKIE['minY'], 0);
-    $XMAX = round($_COOKIE['maxX'], 0);
-    $YMAX = round($_COOKIE['maxY'], 0);
-    
-//		echo('penmanbool:' . $PENMAN . '<br>');
-//		echo('makkinkbool:' . $MAKKINK . '<br>');
-//		echo('neerslagbool:' . $NSL . '<br>');
-    
-    $TempDir = '"c:\Result"';
-    $GDALTOOLSDIR = '"D:\GDAL64"';
-
-    $ExportFileName = '\Bestelling_' . $sessionid . '_' . $NewOrder . '_PENMAN.zip' ;
-    //echo('Export File Name:' . $ExportFileName . '<br>');
-    $ResultsZIPFile = chr(34) . 'c:\Apache24\htdocs\meteobase\downloads' . $ExportFileName . chr(34);
-    //echo('ResultsZIPFile:' . $ResultsZIPFile . '<br>');
-    
-    // Compile the insertion-string, start with the Keys :
-    $sInsertQuery = "INSERT INTO $sTableName (" ;
-    $sInsertQuery .= "sessienr" . ",";
-    $sInsertQuery .= " startdatum" . ",";
-    $sInsertQuery .= " stopdatum" . ",";
-    $sInsertQuery .= " bestellingnr" . ",";
-    $sInsertQuery .= " neerslag" . ",";
-    $sInsertQuery .= " penman" . ",";
-    $sInsertQuery .= " makkink";
-
-    // sla de bestelling op in de database
-    $sInsertQuery .= ") VALUES (";
-    $sInsertQuery .= "$1". ",";
-    $sInsertQuery .= "$2". ",";
-    $sInsertQuery .= "$3". ",";
-    $sInsertQuery .= "$4". ",";
-    $sInsertQuery .= "$5" . ",";
-    $sInsertQuery .= "$6" . ",";
-    $sInsertQuery .= "$7";
-    $sInsertQuery .= ")";
-
-    //echo($sInsertQuery . "<BR>");
-    //echo("----------------------------<BR>");
-    pg_prepare($dbHandle, "satDataInsert", $sInsertQuery);
-    $bResult =  pg_execute($dbHandle, "satDataInsert", array(
-        $sessionid,
-        $FDATE,
-        $TDATE,
-        $NewOrder,
-        "$NSL GRID",
-        "$PENMAN GRID",
-        "$MAKKINK GRID"
-    ));
-    if (!$bResult)
-        {
-        echo "<BR>"."Bestelling kon niet in de database worden opgeslagen";
-        }
-    else
-        {
-        //echo("Resultaat : ". $bResult . "<BR>");
-        }
-    
-    // FOUTAFHANDELING //
-    $ruimtelijke_selectie = true;
-    $tijdselectie = true;
-    // Ruimtelijke selectie //
-    if(round(($XMIN)/1000)*1000 >= round(($XMAX)/1000)*1000 or round(($YMIN)/1000)*1000 >= round(($YMAX)/1000)*1000) {	$ruimtelijke_selectie = false; }
-    if(round(($XMAX - $XMIN) / 1000) * round(($YMAX - $YMIN) / 1000) > 10000) { $ruimtelijke_selectie = false; }
-    // Tijdselectie //
-    if(intval(abs(strtotime($TDATE)-strtotime($FDATE))/86400) > 366*5) {
-        $tijdselectie = false;
-    }
-    
-    $adres = 'http://<IP-ADRES-OUDE-SERVER>/meteobase/downloads/';
-    
-    // Arguments for the tool; started without a shell (see sanitize.php)
-    $user = valid_user_cookie();
-    if ($user === null) {
-        order_error(MSG_INVALID_USER, 'satdata: gebruiker-cookie');
-        include('pages/satdata01.php');
-        return;
-    }
-    $tokens = [$FDATE, $TDATE, $XMIN, $YMIN, $XMAX, $YMAX, $NSL, $MAKKINK, $PENMAN, $user['sessionid'], $NewOrder];
-    if (in_array(null, array_map('valid_token', $tokens), true)) {
-        order_error(MSG_INVALID_INPUT, 'satdata: argumenten');
-        include('pages/satdata01.php');
-        return;
-    }
-    $grid = [$FDATE, $TDATE, $XMIN, $YMIN, $XMAX, $YMAX, $NSL, $MAKKINK, $PENMAN];
-    $poly = [$FDATE, $TDATE, $NSL, $MAKKINK, $PENMAN];
-    $order = [$user['sessionid'], $NewOrder, $user['naam'], $user['mail']];
-    if ($zipNaam !== null) {
-        // checked above, before the order number was taken
-        $order[] = 'c:\Apache24\htdocs\meteobase\uploads\\' . $zipNaam;
-        $order[] = $veldnaam;
-    }
-    $tools = array(
-    'ascii' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['ASCII', 'false'], $order)],
-    'hdf5' => ['NATIVERASTER/WIWBNATIVERASTER.exe', array_merge($grid, ['false'], $order)],
-    'modflow' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['MODFLOW', 'false'], $order)],
-    'simgro' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['SIMGRO', 'false'], $order)],
-    'sobek' => ['RASTERBYPOLY/WIWBRASTERBYPOLY.exe', array_merge($poly, ['SOBEK'], $order)],
-    'csv' => ['RASTERBYPOLY/WIWBRASTERBYPOLY.exe', array_merge($poly, ['CSV'], $order)],
-    );
-    if (!isset($tools[$formaat])) {
-        order_error(MSG_INVALID_INPUT, 'satdata: formaat');
-        include('pages/satdata01.php');
-        return;
-    }
-    if (!start_tool($tools[$formaat][0], $tools[$formaat][1])) {
-        order_error(MSG_TOOL_FAILED, 'satdata: ' . $tools[$formaat][0] . ' niet gestart');
-        include('pages/satdata01.php');
-        return;
-    }
-
-    //include('backoffice/backoffice_rasterdata.php');
-
-    $link = '<a href="'.$adres.'" target="_blank">downloadpagina</a>';
-    //$bericht = $bericht . '<br><br>' . 'Geachte ' . $naam . ',</p><p>Als de bevraging succesvol was, kunnen de opgevraagde gegevens met sessie-ID ' . $sessionid . ' worden gedownload van de ' . $link . '.<br>';
-    $bericht = 'Geachte ' . $naam . ',</p><p>Uw bestelling wordt verwerkt. U ontvangt nu een e-mail met een downloadlink.<br />';
-    $data = 'Gegevenstype: ' . $gegevenstype . '<br>Datum: ' . $datum_van . ' tot ' . $datum_tot . '<br>RD-Co&ouml;rdinaten (zuidwest): ' . round($coord_0, 0) . ', ' . round($coord_1, 0) . '<br />RD-Co&ouml;rdinaten (noordoost): ' . round($coord_2, 0) . ', ' . round($coord_3, 0) . '</p>';
-    
-    $_SESSION['dataFeedbackMsg'] = "send message";
-    
-    include('pages/satdata01.php');
-    
-}else{
-    include('pages/satdata01.php');
-}
+// SAT Data 2.0: only download links to the fixed SATDATA archives (pages/satdata01.php).
+// SATDATA (actual evapotranspiration, evaporation deficit) is ordered on the tab
+// Rasterdata (pages/rasterdata.php); this tab has no order form, so a POST just shows the page.
+include('pages/satdata01.php');
 ?>
```

### 4.2 Deel B (optioneel, aanbevolen samen met A): `satdata01.php` opschonen

- r. 1–64 (uitgecommentarieerd oud Rasterdata-formulier) verwijderen.
- `<form … id="rasterDataForm">` + `<input type="hidden" name="subrd">` vervangen door `<div class="sat-data">`, en `</form>` door `</div>`.

Gevolg: de Rasterdata-JavaScript van r. 3642 en r. 3831 bindt niet meer aan deze pagina, de pagina stuurt ±5 kB minder HTML, en er bestaat geen formulier meer dat naar `tb=satdata` post. De zichtbare lay-out verandert niet, want er is geen CSS op `form` binnen `.form-container`. Dit moet wel in de browser gecontroleerd worden (testplan T3). `php -l`: *No syntax errors detected*.

```diff
--- a/htdocs/pages/satdata01.php (huidig)
+++ b/htdocs/pages/satdata01.php (voorstel, optioneel)
@@ -1,67 +1,3 @@
-<!--    <script>-->
-<!--    $('#coordinaten').css('opacity','1');-->
-<!--	-->
-<!--	function check() {-->
-<!--        if($('#veldnaam').val() == 'Veldnaam') {-->
-<!--            $("#veldnaam").after("<span style='color:red;'> Graag uw veldnaam vermelden.</span>");-->
-<!--        }else{-->
-<!--		    $('#uploadbox').fadeOut('fast', function() {-->
-<!--			    melding();-->
-<!--                document.forms.rasterdata.submit();-->
-<!--			});-->
-<!--        }-->
-<!--    }-->
-<!--    function upload_pu(x) {-->
-<!--        if (x == 'sobek' || x == 'csv') {-->
-<!--            $('#uploadbox').fadeIn();-->
-<!--            $('#downloadbutton').html('<a style="color:#000" href="javascript:void(0)" onclick="check()">Downloaden</a>');-->
-<!--		}else{-->
-<!--            $('#downloadbutton').html('<a style="color:#000" href="javascript:void(0)" onclick="document.forms.rasterdata.submit(); melding();">Downloaden</a>');-->
-<!--            $('#uploadbox').fadeOut('fast');-->
-<!--        }-->
-<!--    }-->
-<!--    </script>-->
-<!---->
-<!--    <form name="rasterdata" action="?tb=rasterdata&dp=rasterdata&dp_sub=introductie" enctype="multipart/form-data" method="post">-->
-<!--        <div style="width:170px; float:left">-->
-<!--            <input type="hidden" name="subrd" />-->
-<!--            <input type="hidden" name="coordx" value="coordinaten x" />-->
-<!--            <input type="hidden" name="coordy" value="coordinaten y" />-->
-<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="neerslag" value="neerslag"--><?php //if(isset($neerslag)){ echo 'checked="checked"'; } ?><!-- /> Neerslag</p></div>-->
-<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="makkink" value="makkink"--><?php //if(isset($makkink)){ echo 'checked="checked"'; } ?><!-- /> Verdamping (Makkink)</p></div>-->
-<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="penman" value="penman"--><?php //if(isset($penman)){ echo 'checked="checked"'; } ?><!-- /> Verdamping (Penman)</p></div>-->
-<!--            <br />-->
-<!--            <select name="formaat" onchange="upload_pu(this.value)" >-->
-<!--                <option value="ascii">ASCII</option>-->
-<!--				<option value="hdf5">HDF5</option>-->
-<!--                <option value="modflow">MODFLOW</option>-->
-<!--				<option value="simgro">SIMGRO</option>-->
-<!--                <option value="sobek">SOBEK</option>-->
-<!--                <option value="csv">CSV</option>-->
-<!--            </select>-->
-<!--        </div>-->
-<!---->
-<!--        <div id="uploadbox" style="padding-left:38px;">-->
-<!--            <span>&nbsp;Upload hier uw gezipte shapefile (*.shp, *.shx, *.dbf).</span>-->
-<!--            <input type="file" name="zipfile" />-->
-<!--            <input id="veldnaam" type="text" name="veldnaam" value="Veldnaam" size="12" maxlength="20" onclick="this.select()" />-->
-<!--        </div>-->
-<!---->
-<!--        <div style="width:110px; margin-left:20px; float:left">-->
-<!--            <p style="padding-bottom:10px">van datum:</p>-->
-<!--            <p style="padding-bottom:10px">tot datum:</p>-->
-<!--            <br />-->
-<!--            <br />-->
-<!--   	    </div>-->
-<!---->
-<!--        <div style="width:140px; margin-left:20px; float:left">-->
-<!--            <div><p style="padding-bottom:0px"><input type="text" name="dv" size="10" value="--><?php //if(isset($d_check)){ echo $dv; }else{ echo '01/01/1990'; } ?><!--"></p></div>-->
-<!--            <div><p style="padding-bottom:0px"><input type="text" name="dt" size="10" value="--><?php //if(isset($d_check)){ echo $dt; }else{ echo '31/12/2013'; } ?><!--"></p></div>-->
-<!--        </div>-->
-<!---->
-<!--        <div id="downloadbutton" class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.rasterdata.submit(); melding();">Downloaden</a></div>-->
-<!--    </form>-->
-
     <!--Starting RasterData Section-->
     <div class="row">
         <div class="content-row form-row-container">
@@ -71,8 +7,7 @@
                         SAT Data 2.0
                     </h2>
                     <div class="form-container">
-                        <form method="POST" enctype="multipart/form-data" action="<?php echo($_SERVER["PHP_SELF"]); ?>?tb=satdata&dp=satdata&dp_sub=introductie" class="sat-data" id="rasterDataForm">
-                            <input type="hidden" name="subrd" />
+                        <div class="sat-data">
                             <div class="col-md-12 col-sm-12 col-xs-12">
                                 <div class="row">
                                     <div class="col-md-12 col-sm-12 col-xs-12">
@@ -100,7 +35,7 @@
                                     </div>
                                 </div>                        
                             </div>
-                        </form>
+                        </div>
                     </div>
                 </div>
             </div>
```

### 4.3 Alternatieven

| Variant | Wat | Voordelen | Nadelen |
|---|---|---|---|
| **A: stil de pagina tonen** (aanbevolen) | alleen `satdata.php` inkorten | kleinste wijziging, één bestand, geen wijziging aan `index.php`; een POST heeft geen enkel neveneffect meer | scanners krijgen nog 200 (dezelfde pagina als bij GET); geen expliciet signaal dat POST niet bestaat |
| **C: POST weigeren met 405** | extra blok in `index.php` vóór de HTML-uitvoer (diff hieronder), plus variant A | correcte HTTP-semantiek; geen volledige pagina-opbouw voor ±370 scanner-POSTs per maand; duidelijk in de logs | wijziging in het grote `index.php`; elke toekomstige legitieme POST naar `tb=satdata` (bijv. een formulier in een modal met `action=""`) krijgt 405; het enige zo'n formulier, `#polygonCoordinates` (r. 1659), wordt alleen geopend vanaf Rasterdata (`raster01.php` r. 183), maar dat moet zo blijven |
| **D: 303-redirect naar GET** | idem als C, maar `header('Location: ?tb=satdata…', true, 303)` | gebruiker ziet de pagina, geen "dubbele POST" bij verversen | extra request; scanners volgen het niet; weinig winst t.o.v. A |
| **E: argumenten aanvullen** (niet voorgesteld) | EVTACT/EVTSHO aan `satdata.php` toevoegen | SAT Data-bestellen werkt weer via handgemaakte POST | er is geen formulier, dus niemand kan het gebruiken; dubbel met Rasterdata; CSRF en rate limit zouden er nog bij moeten |

Diff voor variant C (alleen ter illustratie, `php -l` op een kopie: geen fouten):

```diff
--- a/htdocs/index.php (huidig)
+++ b/htdocs/index.php (variant 405)
@@ -147,6 +147,16 @@
 	exit;
 }
 
+// SAT Data has no order form (pages/satdata01.php only has download links): a POST is refused
+// here, before any HTML output, so that the status code reaches the client
+if($tab == 'satdata' && $_SERVER['REQUEST_METHOD'] === 'POST'){
+	http_response_code(405);
+	header('Allow: GET, HEAD');
+	header('Content-Type: text/plain; charset=utf-8');
+	echo 'SAT Data heeft geen bestelformulier. SATDATA bestelt u via Rasterdata > Downloaden.';
+	exit;
+}
+
 function rd2wgs ($x, $y){
     // Calculate WGS84 coordinates
     $dX = ($x - 155000) * pow(10, - 5);
```

`http_response_code(405)` werkt hier, omdat dit blok, net als de upload-endpoint op r. 144–148, vóór elke HTML-uitvoer staat. `session_start()` (r. 7) heeft dan wel al een sessiecookie gezet; dat is onschadelijk.

## 5. Wat bewust níet verandert

- Het tabblad zelf: whitelist (`index.php` r. 113), menulink (r. 641), de zes downloadlinks, de handleidingslider en de wrapper-`<div>` (ook niet gesloten, net als in de andere tabbladen).
- `index.php` bij variant A en B. Het JS-blok r. 2846–2888 (kaart) en de gedeelde Rasterdata-JS blijven staan.
- `pages\rasterdata.php`, `raster01.php`, `rasterDataUpload.php`, `shapefile_check.php`, `sanitize.php`: de SATDATA-bestelroute via Rasterdata blijft precies gelijk.
- De bestanden in `meteobase\downloads\fixed\` en het nachtelijke opruimscript.
- Database: geen schemawijziging; de bestaande rijen in `tbbasis` (ook testrijen van 26-10) blijven staan.
- De tools in `C:\Program Files\Hydroconsult\`, waaronder het bekende probleem dat `clsWIWBRasterData.Write()` ook na een fout True teruggeeft.
- Directe toegang tot `htdocs\pages\*.php` (zie open vraag 4).
- Apache- en PHP-configuratie: geen herstart nodig (opcache staat uit in `php.ini`, dus PHP-bestanden worden per request gelezen).

## 6. Risico's en afhankelijkheden

| Risico | Kans | Gevolg | Maatregel |
|---|---|---|---|
| Iemand gebruikt toch een eigen script dat naar `tb=satdata` post | zeer klein (geen enkele normale POST in 3 jaar log) | bestelling komt niet meer binnen; bij variant A zonder foutmelding | de bestelling werkte toch al niet (argumentfout). Bij variant C krijgt zo iemand een duidelijke 405 met uitleg |
| `include('pages/satdata01.php')` faalt na de wijziging | zeer klein | lege pagina | de include is identiek aan de huidige; testplan T1 |
| Deel B verandert de lay-out (`<form>` → `<div>`) | klein | afwijkende marges | geen CSS op `form` gevonden; visuele controle T3, eventueel deel B weglaten |
| JS-fout doordat `#rasterDataForm` op SAT Data ontbreekt (deel B) | zeer klein | — | alle code checkt `$('#rasterDataForm').length` of werkt op een lege selectie; console-check T3 |
| `local_config.php` / `sanitize.php` niet meer geladen in `satdata.php` | geen | — | `index.php` laadt `local_config.php` al op r. 5; niets in `satdata01.php` gebruikt `sanitize.php` |
| Direct `/pages/satdata.php` (scanners) | bestaand | nu HTTP 500; na A waarschijnlijk 200 met een PHP-waarschuwing (de relatieve include faalt), zonder inhoud of gevoelige data | open vraag 4: `<Directory "${SRVROOT}/htdocs/pages"> Require all denied` |
| Afhankelijkheid: niemand werkt tegelijk aan `satdata.php` | — | — | vóór toepassen hash vergelijken met de backup van 26-10 + de huidige versie (zie 7.1) |

## 7. Testplan en rollback

### 7.1 Voorbereiding

1. Backup in `C:\backup\satdata-<yyyyMMdd>\` van `pages\satdata.php` en (bij deel B) `pages\satdata01.php`, (bij C) `index.php`. Vergelijk de SHA-256 van de backup met het origineel.
2. Noteer vooraf: `select last_value from public.tbbasis_bestellingnr_seq` (read-only), het aantal regels in `logs\php8_errors.log`, en de inhoud van `meteobase\downloads\` (nieuwste bestanden).
3. Wijziging toepassen, `C:\php8\php.exe -l` op elk gewijzigd bestand.

### 7.2 Tests

Testgebruiker 19723 ("Testgebruiker firewalltest"); eventuele mail naar info@meteobase.nl.

| # | Stap | Verwachte uitkomst |
|---|---|---|
| T1 | GET `https://www.meteobase.nl/index.php?tb=satdata&dp=satdata&dp_sub=introductie` | 200; kop "SAT Data 2.0", zes downloadlinks, handleidingslider, menu en footer zoals vóór de wijziging (vergelijk de HTML vóór/na met `curl`, behalve het CSRF-token) |
| T2 | HEAD op de zes links `meteobase/downloads/fixed/et{act,def}_201{6,7,8}_NL.7z` | 200 met de bekende bestandsgroottes |
| T3 | De pagina in de browser (desktop en mobiel), console openen | geen lay-outverschil, geen JS-fouten; menu Rasterdata → SAT Data werkt |
| T4 | Handgemaakte POST naar `?tb=satdata…` met `subrd`, `fileType=ascii`, datums, en een geldige `gebruiker`-cookie van 19723 | **A:** 200 + gewone pagina, geen succes- of foutmelding. **C:** 405 met header `Allow: GET, HEAD` en de Nederlandse tekst. In beide gevallen: sequence `tbbasis_bestellingnr_seq` ongewijzigd, geen nieuwe rij voor 19723, geen nieuw proces of log in `downloads\`/`C:\Result\`, geen nieuwe `satdata`-regel in `php8_errors.log` |
| T5 | Idem met `fileType=csv` en een geüploade shapefile (cookie `ZIPFILE`) | als T4 |
| T6 | POST zonder `subrd` en zonder cookies (scannergedrag) | A: 200 pagina; C: 405 |
| T7 | Regressie: tabblad Rasterdata, SATDATA "Actuele evapotranspiratie" + CSV met shapefile-upload, geldige bestelling | upload 200 met veldenlijst, bestelling "in goede orde ontvangen", tool draait, mail met downloadlink, resultaat correct |
| T8 | Regressie: tabblad Rasterdata, ASCII-bestelling zonder shapefile (alleen kaartselectie) | als vóór de wijziging |
| T9 | Regressie: registreren vanuit de SAT Data-pagina (modal, `action` = `index.php`) en het feedbackformulier | werkt zoals vóór de wijziging (deze POSTs gaan niet naar `tb=satdata`) |
| T10 | Direct GET `/pages/satdata.php` | geen 200 met gevoelige inhoud (verwacht: 200 met een waarschuwing in het PHP-log of 500; met de maatregel uit open vraag 4: 403) |
| T11 | Opruimen | testuploads `19723_*` in `meteobase\uploads\`, eventuele logs en zips van de testbestelling in `downloads\` |

### 7.3 Rollback

1. Kopieer de bestanden uit `C:\backup\satdata-<yyyyMMdd>\` terug naar `htdocs\pages\` (en `htdocs\index.php` bij C).
2. Voer `C:\php8\php.exe -l` uit op de teruggezette bestanden en doe T1.
3. Een Apache-herstart is niet nodig.

## 8. Open vragen voor de expert

1. **Variant A of C** (of D)? Is een 405 in `index.php` de extra wijziging waard, gegeven ±370 scanner-POSTs per maand die nu een volledige pagina krijgen?
2. **Deel B**: akkoord met het vervangen van het lege `<form id="rasterDataForm">` door een `<div>` en het verwijderen van het uitgecommentarieerde oude formulier?
3. Is er een reden om de bestelcode te **bewaren** (bijvoorbeeld als basis voor een toekomstig SAT Data-formulier), of is versiebeheer via de backup voldoende?
4. Moet `htdocs\pages\` (en eventueel `htdocs\*.php` behalve `index.php`) via `httpd.conf` worden afgeschermd (`Require all denied`)? Volgens `php8_errors.log` en de toegangslogs proberen scanners deze bestanden direct op te vragen.
5. Het JS-blok `if ($tab == 'satdata')` in `index.php` r. 2846–2888 (kaartlaag met `rasterViewImage`) lijkt een kopie van Herhalingstijd zonder functie op SAT Data. Meenemen in een latere opruimronde?
6. `tbbasis` heeft geen besteldatum en geen bron/tabblad. Is het zinvol om dat toe te voegen voor toekomstige analyses? (Buiten deze scope.)
7. Beoordeel ook de wijzigingen van 26-10-2026 in hoofdstuk 9, met name:
   - de sessiekoppeling van uploads (`$_SESSION['shape_uploads']` + prefix sessie-ID);
   - de waardevergelijking in `shape_field_values_ok()`. Die is hoofdletterongevoelig en getrimd voor tekst, en via `(float)` voor getallen. Klopt dat met hoe de tool `WIWBRASTERBYPOLY` de ID's koppelt?
   - de grenzen: 20 MB, 5.000 polygonen, RD-bbox x 0–300.000 en y 289.000–629.000;
   - de rate limit per sessie. Een client zonder sessiecookie krijgt telkens een nieuwe sessie, maar wordt dan al door de CSRF-controle tegengehouden;
   - het feit dat `index.php` het CSRF-token met `json_encode` in inline JS zet.

---

## 9. Reeds doorgevoerd op 26-10-2026 (ter beoordeling)

> **Expert: graag ook deze wijzigingen beoordelen.** Ze zijn in productie en niet teruggedraaid.

Aanleiding: bij de functie "rasterdata per polygoon" (RASTERBYPOLY) controleerde de upload alleen de extensie `.zip`. Daardoor kwam een ongeldige shapefile pas bij de tool aan, en die stuurde dan een misleidende "afgehandeld"-mail. Verder waren uploads publiek downloadbaar, en kon een bestelling via de cookie `ZIPFILE` naar de upload van een ander verwijzen.

Backups van de oude versies: `C:\backup\upload-20261026\` (httpd.conf, php.ini, index.php, rasterDataUpload.php, rasterdata.php, satdata.php). De diffs hieronder zijn gemaakt van die backup naar de huidige versie. Ze bevatten geen geheimen; de enige treffers op "token" zijn verwijzingen naar de sessievariabele `csrf_token`, geen waarden.

### 9.1 `C:\Apache24\conf\httpd.conf`

De map met klantuploads is niet meer via HTTP op te vragen (403). PHP en de tools lezen de bestanden via het bestandssysteem. Uitgevoerd: `httpd -t` (OK), daarna `httpd -k restart -n Apache2.4`.

```diff
--- a/conf/httpd.conf (backup 26-10)
+++ b/conf/httpd.conf (huidig)
@@ -123,6 +123,12 @@
     Require all denied
 </Directory>
 
+# meteobase/uploads/ receives the customers' shapefiles (pages/rasterDataUpload.php); the tools and PHP read
+# them from the file system, so they must not be served over HTTP (2026-10-26)
+<Directory "${SRVROOT}/htdocs/meteobase/uploads">
+    Require all denied
+</Directory>
+
 ErrorLog "logs/error.log"
 
 LogLevel warn
```

### 9.2 `C:\php8\php.ini`

- `extension=zip` (voor `ZipArchive` in `shapefile_check.php`; na de herstart gecontroleerd dat de extensie onder Apache geladen is).
- `post_max_size` en `upload_max_filesize` zijn 20M, gelijk aan `maxFilesize: 20` in Dropzone. Eerder werd alles boven 2 MB stil afgewezen.

```diff
--- a/php8/php.ini (backup 26-10)
+++ b/php8/php.ini (huidig)
@@ -699,7 +699,7 @@
 ; Its value may be 0 to disable the limit. It is ignored if POST data reading
 ; is disabled through enable_post_data_reading.
 ; https://php.net/post-max-size
-post_max_size = 8M
+post_max_size = 20M
 
 ; Automatically add files before PHP document.
 ; https://php.net/auto-prepend-file
@@ -852,7 +852,7 @@
 
 ; Maximum allowed size for uploaded files.
 ; https://php.net/upload-max-filesize
-upload_max_filesize = 2M
+upload_max_filesize = 20M
 
 ; Maximum number of files that can be uploaded via a single request
 max_file_uploads = 20
@@ -966,7 +966,7 @@
 ;extension=sqlite3
 ;extension=tidy
 ;extension=xsl
-;extension=zip
+extension=zip
 
 ;zend_extension=opcache
 
```

### 9.3 `C:\Apache24\htdocs\index.php`

- Het tabblad `rasterDataUpload` wordt nu vóór de HTML-uitvoer afgehandeld, met `exit`. Daardoor kan de endpoint een HTTP-status en JSON teruggeven. Vroeger werd het bestand midden in de HTML ge-include.
- Dropzone stuurt `csrf_token` mee, heeft Nederlandse meldingen en toont de foutmelding van de server.
- Server- en bestandstekst worden via `mbEscape()` als tekst getoond, omdat `mbAlert()` HTML invoegt.
- Na de upload wordt het tekstveld `veldnaam` een keuzelijst met de velden uit de .dbf (`setVeldnamen()`).
- De client zet de cookie `ZIPFILE` niet meer zelf; dat doet nu de server.

```diff
--- a/htdocs/index.php (backup 26-10)
+++ b/htdocs/index.php (huidig)
@@ -141,6 +141,12 @@
 	exit;
 }
 
+// Shapefile upload (Dropzone, tab Rasterdata): its own response with HTTP status, before any HTML output
+if($tab == 'rasterDataUpload'){
+	include('pages/rasterDataUpload.php');
+	exit;
+}
+
 function rd2wgs ($x, $y){
     // Calculate WGS84 coordinates
     $dX = ($x - 155000) * pow(10, - 5);
@@ -2726,19 +2732,48 @@
                 $('.fileUpload').fadeOut();
             }
         })
+        // text from the server or the file is shown as text, never as HTML
+        function mbEscape(text) {
+            return $('<div>').text(String(text)).html();
+        }
+
+        // after a successful upload the field "veldnaam" becomes a list of the fields in the .dbf
+        function setVeldnamen(velden) {
+            var current = $('#veldnaam').val();
+            var select = $('<select>', {id: 'veldnaam', name: 'veldnaam', 'class': 'form-control'});
+            select.append($('<option>', {value: ''}).text('Kies het veld met het gebieds-ID'));
+            $.each(velden, function(i, veld) {
+                select.append($('<option>', {value: veld}).text(veld));
+            });
+            if ($.inArray(current, velden) >= 0) {
+                select.val(current);
+            } else if (velden.length == 1) {
+                select.val(velden[0]);
+            }
+            $('#veldnaam').replaceWith(select);
+        }
+
         $(".upload-files-wrapper").dropzone({
             url: "<?php echo($_SERVER["PHP_SELF"]); ?>?tb=rasterDataUpload",
             maxFilesize: 20,
+            params: {csrf_token: <?php echo json_encode($_SESSION['csrf_token']); ?>},
             dictDefaultMessage: "Drop your files here",
-            success: function($status, $data) {
-                var text = $status.name;
-                createCookie('ZIPFILE', text);
-                $('#fileUploaded').val(text);
-                mbAlert('Uw bestand is geüpload', 'alert-success');
+            dictFileTooBig: "Het bestand is te groot (maximaal {{maxFilesize}} MB).",
+            dictInvalidFileType: "Upload een zip-bestand met daarin de shapefile (.shp, .shx en .dbf).",
+            dictResponseError: "Het uploaden is mislukt (foutcode {{statusCode}}). Probeer het opnieuw.",
+            success: function(file, response) {
+                // the server keeps the stored file name in the cookie ZIPFILE itself
+                var info = (typeof response === 'string') ? JSON.parse(response) : response;
+                $('#fileUploaded').val(info.bestand);
+                setVeldnamen(info.velden);
+                mbAlert('Uw bestand ' + mbEscape(info.bestand) + ' is geüpload (' + info.aantal +
+                    ' polygonen). Kies hieronder het veld met het gebieds-ID.', 'alert-success');
             },
             acceptedFiles: '.zip',
-            error: function() {
-                mbAlert('Upload een geldige zip-file.', 'alert-danger');
+            error: function(file, message) {
+                $('#fileUploaded').val('');
+                var text = (typeof message === 'string' && message !== '') ? message : 'Upload een geldige zip-file.';
+                mbAlert(mbEscape(text), 'alert-danger');
             },
             init: function() {
                 this.on('sending', function() {
```

### 9.4 `C:\Apache24\htdocs\pages\rasterDataUpload.php`

De oude versie was 13 regels:
- alleen een extensiecontrole;
- `$_FILES['sile']` (typfout, dus de groottecontrole werkte niet);
- opslag onder de oorspronkelijke bestandsnaam (overschrijven en raden mogelijk);
- het volledige pad in de cookie.

Nieuw:
- **Toegang en invoer:** alleen POST (anders 405), te grote request (400), CSRF via `hash_equals` (400), geldige `gebruiker`-cookie verplicht (400), rate limit 10 uploads per 10 minuten per sessie (429), PHP-uploadfouten afgehandeld, `is_uploaded_file`, extensie `.zip`.
- **Controle en opslag:** `shape_inspect_zip()` (400 met een Nederlandse melding), daarna opslag als `<sessionid>_<16 hex>.zip`.
- **Sessie en cookie:** registratie in `$_SESSION['shape_uploads']` (laatste 10). De cookie `ZIPFILE` bevat alleen de opgeslagen naam (HttpOnly, SameSite=Lax, Secure op HTTPS).
- **Antwoord:** JSON `{bestand, aantal, velden}`.

```diff
--- a/htdocs/pages/rasterDataUpload.php (backup 26-10)
+++ b/htdocs/pages/rasterDataUpload.php (huidig)
@@ -1,13 +1,112 @@
 <?php
-	$valid_file = true;
-    $file_tmp = $_FILES['file']['tmp_name'];
-    $file_name = strtolower($_FILES['file']['name']);
-    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/meteobase/uploads/";
-    if($_FILES['sile']['size'] > (20971520)) { $valid_file = false; }
-    $file_ext = end(explode('.',$file_name));
-    if($file_ext !== 'zip') { $valid_file = false; }
-    if($valid_file) { move_uploaded_file($file_tmp,"$upload_dir".$file_name); }
-    $locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\\' . $file_name;
-    setcookie('ZIPFILE', $locatie_zip, time() + (86400 * 30), "/");
-	echo $file_name;
-?>
\ No newline at end of file
+// Upload of a zipped shapefile for rasterdata per polygon (Dropzone on the tab Rasterdata).
+// index.php includes this file before any HTML output and exits afterwards, so the
+// HTTP status and headers below reach the browser.
+// Error: HTTP 400 (429 for too many uploads) with a short Dutch message as plain text.
+// Success: HTTP 200 with JSON {bestand, aantal, velden} (field names from the .dbf).
+// The zip is stored under a random name; that name, the original name and the field
+// names are kept in $_SESSION['shape_uploads'], and the cookie ZIPFILE holds only the
+// random name. pages/rasterdata.php accepts only an upload from this session.
+
+require_once __DIR__ . '/../sanitize.php';
+require_once __DIR__ . '/../shapefile_check.php';
+
+const UPLOAD_MAX_PER_WINDOW = 10;
+const UPLOAD_WINDOW = 600; // seconds
+const UPLOAD_MSG_TOO_BIG = 'Het bestand is te groot (maximaal 20 MB).';
+const UPLOAD_MSG_FAILED = 'Het uploaden is mislukt. Probeer het opnieuw.';
+
+function upload_reply(int $status, string $body, string $type = 'text/plain'): void {
+    http_response_code($status);
+    header('Content-Type: ' . $type . '; charset=utf-8');
+    header('Cache-Control: no-store');
+    header('X-Content-Type-Options: nosniff');
+    echo $body;
+    exit;
+}
+
+if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
+    header('Allow: POST');
+    upload_reply(405, 'Een shapefile kan alleen via het formulier worden geüpload.');
+}
+
+// a request larger than post_max_size arrives without $_POST and $_FILES
+if (!$_POST && !$_FILES && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
+    upload_reply(400, UPLOAD_MSG_TOO_BIG);
+}
+
+if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !is_string($_POST['csrf_token'])
+    || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
+    upload_reply(400, 'Beveiligingsvalidatie mislukt. Laad de pagina opnieuw en probeer het nog eens.');
+}
+
+$user = valid_user_cookie();
+if ($user === null) {
+    upload_reply(400, 'U dient zich eerst te registreren.');
+}
+
+// Rate limiting - max 10 uploads per 10 minutes per session
+$_SESSION['upload_attempts'] = array_values(array_filter(
+    (array)($_SESSION['upload_attempts'] ?? []),
+    function($timestamp) {
+        return is_int($timestamp) && (time() - $timestamp) < UPLOAD_WINDOW;
+    }
+));
+if (count($_SESSION['upload_attempts']) >= UPLOAD_MAX_PER_WINDOW) {
+    upload_reply(429, 'Te veel uploads. Probeer het over 10 minuten opnieuw.');
+}
+$_SESSION['upload_attempts'][] = time();
+
+$file = $_FILES['file'] ?? null;
+if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
+    upload_reply(400, 'Er is geen bestand ontvangen.');
+}
+switch ($file['error']) {
+    case UPLOAD_ERR_OK:
+        break;
+    case UPLOAD_ERR_INI_SIZE:
+    case UPLOAD_ERR_FORM_SIZE:
+        upload_reply(400, UPLOAD_MSG_TOO_BIG);
+    case UPLOAD_ERR_NO_FILE:
+        upload_reply(400, 'Er is geen bestand ontvangen.');
+    default:
+        error_log('Meteobase upload: PHP-uploadfout ' . (int)$file['error']);
+        upload_reply(400, UPLOAD_MSG_FAILED);
+}
+if (!is_uploaded_file($file['tmp_name'])) {
+    upload_reply(400, UPLOAD_MSG_FAILED);
+}
+
+$origName = basename(str_replace('\\', '/', (string)$file['name']));
+if (strtolower(pathinfo($origName, PATHINFO_EXTENSION)) !== 'zip') {
+    upload_reply(400, 'Upload een zip-bestand met daarin de shapefile (.shp, .shx en .dbf).');
+}
+
+try {
+    $info = shape_inspect_zip($file['tmp_name']);
+} catch (ShapeCheckError $e) {
+    upload_reply(400, $e->getMessage());
+}
+
+$stored = $user['sessionid'] . '_' . bin2hex(random_bytes(8)) . '.zip';
+if (!move_uploaded_file($file['tmp_name'], SHAPE_UPLOAD_DIR . $stored)) {
+    error_log('Meteobase upload: opslaan mislukt voor ' . $stored);
+    upload_reply(500, 'Het bestand kon niet worden opgeslagen. Probeer het later opnieuw of neem contact op via info@meteobase.nl.');
+}
+
+// keep the last 10 uploads of this session
+$uploads = (array)($_SESSION['shape_uploads'] ?? []);
+$uploads[$stored] = ['naam' => $origName, 'velden' => $info['fields'], 'aantal' => $info['count'], 'tijd' => time()];
+$_SESSION['shape_uploads'] = array_slice($uploads, -10, null, true);
+
+setcookie('ZIPFILE', $stored, [
+    'path' => '/',
+    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
+    'httponly' => true,
+    'samesite' => 'Lax',
+]);
+
+upload_reply(200, json_encode(
+    ['bestand' => $origName, 'aantal' => $info['count'], 'velden' => $info['fields']],
+    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
+), 'application/json');
```

### 9.5 `C:\Apache24\htdocs\pages\rasterdata.php`

Bij een csv/sobek-bestelling geldt nu:
- de upload moet in `$_SESSION['shape_uploads']` staan, met het sessie-ID van de gebruiker als prefix, en op schijf bestaan;
- het opgegeven veld moet in de .dbf staan (de tool krijgt de schrijfwijze uit de .dbf);
- de waarden in dat veld moeten ingevuld en uniek zijn.

Bij een weigering volgt een specifieke Nederlandse melding via `order_error()`. De controle staat vóór de databaseverbinding (r. 213) en `next_id` (r. 223), dus een geweigerde bestelling kost geen bestelnummer.

```diff
--- a/htdocs/pages/rasterdata.php (backup 26-10)
+++ b/htdocs/pages/rasterdata.php (huidig)
@@ -153,11 +153,34 @@
     }
     $zipNaam = null;
     if ($formaat == 'csv' || $formaat == 'sobek') {
-        // the ZIPFILE cookie holds the full path of the upload (pages/rasterDataUpload.php)
+        // the ZIPFILE cookie holds the stored name of the upload; it must be an upload of this
+        // session and this user (pages/rasterDataUpload.php, checks in shapefile_check.php)
+        require_once 'shapefile_check.php';
         $zipNaam = valid_filename(basename(str_replace('\\', '/', (string)($_COOKIE['ZIPFILE'] ?? ''))), 'zip');
-        $veldnaam = valid_text($_POST['veldnaam'] ?? null, 64);
-        if ($zipNaam === null || $veldnaam === null || !is_file('C:/Apache24/htdocs/meteobase/uploads/' . $zipNaam)) {
-            order_error(MSG_INVALID_INPUT, 'rasterdata: shapefile/veldnaam');
+        $upload = $zipNaam !== null ? ($_SESSION['shape_uploads'][$zipNaam] ?? null) : null;
+        if ($upload === null || strpos($zipNaam, $user['sessionid'] . '_') !== 0 || !is_file(SHAPE_UPLOAD_DIR . $zipNaam)) {
+            order_error('Uw bestelling is niet verwerkt: er is geen geldige shapefile geüpload in deze sessie. Upload uw shapefile (zip) opnieuw.',
+                'rasterdata: shapefile niet van deze sessie');
+            include('pages/raster01.php');
+            return;
+        }
+        // the field must exist in the .dbf; the tool gets the name as it is written there
+        $opgegeven = valid_text($_POST['veldnaam'] ?? null, 64);
+        $veldnaam = null;
+        foreach ($upload['velden'] as $veld) {
+            if ($opgegeven !== null && strcasecmp($veld, $opgegeven) === 0) {
+                $veldnaam = $veld;
+            }
+        }
+        if ($veldnaam === null) {
+            order_error('Uw bestelling is niet verwerkt: kies het veld met het gebieds-ID. Beschikbare velden: '
+                . implode(', ', $upload['velden']) . '.', 'rasterdata: veldnaam niet in .dbf');
+            include('pages/raster01.php');
+            return;
+        }
+        if (!shape_field_values_ok(SHAPE_UPLOAD_DIR . $zipNaam, $veldnaam)) {
+            order_error('Het veld ' . $veldnaam . ' bevat lege of dubbele waarden. Ieder polygoon moet in dit veld een eigen, ingevulde waarde hebben.',
+                'rasterdata: lege/dubbele waarden in ' . $veldnaam);
             include('pages/raster01.php');
             return;
         }
```

### 9.6 `C:\Apache24\htdocs\pages\satdata.php`

Dezelfde controle als in 9.5, maar vóór de databaseverbinding, `next_id` en de INSERT. Een geweigerde bestelling kost dus geen bestelnummer. De latere, kortere controle is vervangen door het doorgeven van de al gecontroleerde naam en het veld. **Dit voorstel (hoofdstuk 4) verwijdert deze code weer**, samen met de rest van de bestelafhandeling.

```diff
--- a/htdocs/pages/satdata.php (backup 26-10)
+++ b/htdocs/pages/satdata.php (huidig)
@@ -52,9 +52,45 @@
         $PENMAN = "false";
     }
     $formaat = $_POST['fileType'];
+    $zipNaam = null;
     if ($formaat == 'csv' || $formaat == 'sobek') {
-		$locatie_zip = 'c:\Apache24\htdocs\meteobase\uploads\\' . $_COOKIE['ZIPFILE'];
-        $veldnaam = addslashes($_POST['veldnaam']);
+        // same check as pages/rasterdata.php, before an order number is taken: the ZIPFILE cookie holds the
+        // stored name of an upload of this session and this user (pages/rasterDataUpload.php, shapefile_check.php)
+        require_once 'shapefile_check.php';
+        $shapeUser = valid_user_cookie();
+        if ($shapeUser === null) {
+            order_error(MSG_INVALID_USER, 'satdata: gebruiker-cookie');
+            include('pages/satdata01.php');
+            return;
+        }
+        $zipNaam = valid_filename(basename(str_replace('\\', '/', (string)($_COOKIE['ZIPFILE'] ?? ''))), 'zip');
+        $upload = $zipNaam !== null ? ($_SESSION['shape_uploads'][$zipNaam] ?? null) : null;
+        if ($upload === null || strpos($zipNaam, $shapeUser['sessionid'] . '_') !== 0 || !is_file(SHAPE_UPLOAD_DIR . $zipNaam)) {
+            order_error('Uw bestelling is niet verwerkt: er is geen geldige shapefile geüpload in deze sessie. Upload uw shapefile (zip) opnieuw.',
+                'satdata: shapefile niet van deze sessie');
+            include('pages/satdata01.php');
+            return;
+        }
+        // the field must exist in the .dbf; the tool gets the name as it is written there
+        $opgegeven = valid_text($_POST['veldnaam'] ?? null, 64);
+        $veldnaam = null;
+        foreach ($upload['velden'] as $veld) {
+            if ($opgegeven !== null && strcasecmp($veld, $opgegeven) === 0) {
+                $veldnaam = $veld;
+            }
+        }
+        if ($veldnaam === null) {
+            order_error('Uw bestelling is niet verwerkt: kies het veld met het gebieds-ID. Beschikbare velden: '
+                . implode(', ', $upload['velden']) . '.', 'satdata: veldnaam niet in .dbf');
+            include('pages/satdata01.php');
+            return;
+        }
+        if (!shape_field_values_ok(SHAPE_UPLOAD_DIR . $zipNaam, $veldnaam)) {
+            order_error('Het veld ' . $veldnaam . ' bevat lege of dubbele waarden. Ieder polygoon moet in dit veld een eigen, ingevulde waarde hebben.',
+                'satdata: lege/dubbele waarden in ' . $veldnaam);
+            include('pages/satdata01.php');
+            return;
+        }
     }
     $sommen = $_POST['sommen'];
     
@@ -193,17 +229,10 @@
     $grid = [$FDATE, $TDATE, $XMIN, $YMIN, $XMAX, $YMAX, $NSL, $MAKKINK, $PENMAN];
     $poly = [$FDATE, $TDATE, $NSL, $MAKKINK, $PENMAN];
     $order = [$user['sessionid'], $NewOrder, $user['naam'], $user['mail']];
-    if ($formaat == 'csv' || $formaat == 'sobek') {
-        // the ZIPFILE cookie holds the full path of the upload (pages/rasterDataUpload.php)
-        $zipNaam = valid_filename(basename(str_replace('\\', '/', (string)($_COOKIE['ZIPFILE'] ?? ''))), 'zip');
-        $veldnaamOk = valid_text($_POST['veldnaam'] ?? null, 64);
-        if ($zipNaam === null || $veldnaamOk === null) {
-            order_error(MSG_INVALID_INPUT, 'satdata: shapefile/veldnaam');
-            include('pages/satdata01.php');
-            return;
-        }
+    if ($zipNaam !== null) {
+        // checked above, before the order number was taken
         $order[] = 'c:\Apache24\htdocs\meteobase\uploads\\' . $zipNaam;
-        $order[] = $veldnaamOk;
+        $order[] = $veldnaam;
     }
     $tools = array(
     'ascii' => ['RASTER2ASCII/WIWBRASTER2ASCII.exe', array_merge($grid, ['ASCII', 'false'], $order)],
```

### 9.7 `C:\Apache24\htdocs\shapefile_check.php` (nieuw bestand, volledig)

Leest alleen headers en de .dbf uit de zip; er wordt niets naar schijf uitgepakt. Controles:
- precies één .shp met .shx en .dbf van dezelfde naam (mappen en `__MACOSX` toegestaan; absolute paden en `..` geweigerd);
- .shp-filecode 9994 en type polygoon (5/15/25);
- bounding box binnen de RD-grenzen van Nederland; een eventuele .prj moet RD zijn;
- aantal objecten volgens .shx = .dbf, tussen 1 en 5.000;
- minstens één C/N/F-veld met een geldige naam.

`shape_field_values_ok()` controleert per polygoon of de waarde ingevuld en uniek is. Verwijderde .dbf-records worden overgeslagen.

```php
<?php
// Checks for an uploaded, zipped shapefile (rasterdata per polygon, tool RASTERBYPOLY).
// Used when the zip is uploaded (pages/rasterDataUpload.php) and again when it is
// ordered (pages/rasterdata.php). Only the headers of .shp/.shx/.prj and the .dbf are
// read from the zip; nothing is extracted to disk. Messages are short Dutch texts for
// the user and contain no user input except field names from the .dbf (see
// SHAPE_FIELD_NAME), so they are safe in HTML and in a JS string.

const SHAPE_UPLOAD_DIR = 'C:/Apache24/htdocs/meteobase/uploads/';
const SHAPE_MAX_POLYGONS = 5000;
// RD New (EPSG:28992) extent of the Netherlands
const SHAPE_RD_XMIN = 0;
const SHAPE_RD_XMAX = 300000;
const SHAPE_RD_YMIN = 289000;
const SHAPE_RD_YMAX = 629000;
// field types in the .dbf that can hold an area ID: text, numeric, float
const SHAPE_ID_FIELD_TYPES = ['C', 'N', 'F'];
// field names that are offered and accepted (a .dbf name is at most 11 characters)
const SHAPE_FIELD_NAME = '/^[A-Za-z0-9_]{1,11}$/';

const SHAPE_MSG_RD = 'De shapefile ligt niet in RD (EPSG:28992) of valt buiten Nederland.';

class ShapeCheckError extends Exception {}

// Checks the zip and returns ['count' => number of polygons, 'fields' => usable field names].
// Throws ShapeCheckError with a message for the user.
function shape_inspect_zip(string $zipPath): array {
    $zip = shape_open_zip($zipPath);
    try {
        $parts = shape_find_parts($zip);

        // .shp header: file code 9994 (big-endian), shape type and bounding box (little-endian)
        $h = shape_read($zip, $parts['shp'], 100);
        if (strlen($h) < 100 || unpack('N', substr($h, 0, 4))[1] !== 9994) {
            throw new ShapeCheckError('Het .shp-bestand is geen geldige shapefile.');
        }
        $type = unpack('V', substr($h, 32, 4))[1];
        if (!in_array($type, [5, 15, 25], true)) {
            throw new ShapeCheckError('Alleen polygonen worden ondersteund.');
        }
        $box = unpack('exmin/eymin/exmax/eymax', substr($h, 36, 32));
        foreach ($box as $v) {
            if (!is_finite($v)) {
                throw new ShapeCheckError(SHAPE_MSG_RD);
            }
        }
        if ($box['xmin'] < SHAPE_RD_XMIN || $box['xmax'] > SHAPE_RD_XMAX
            || $box['ymin'] < SHAPE_RD_YMIN || $box['ymax'] > SHAPE_RD_YMAX) {
            throw new ShapeCheckError(SHAPE_MSG_RD);
        }

        // a .prj is optional (the tool assumes RD), but if there is one it must be RD
        if ($parts['prj'] !== null) {
            $prj = shape_read($zip, $parts['prj'], 8192);
            if (!preg_match('/Amersfoort|RD[_ ]?New|28992/i', $prj)) {
                throw new ShapeCheckError(SHAPE_MSG_RD);
            }
        }

        // number of shapes from the size of the .shx (100-byte header + 8 bytes per shape)
        $shx = $zip->statName($parts['shx']);
        if ($shx === false || $shx['size'] < 100 || ($shx['size'] - 100) % 8 !== 0) {
            throw new ShapeCheckError('Het .shx-bestand is beschadigd.');
        }
        $count = intdiv($shx['size'] - 100, 8);

        $dbf = shape_dbf_header($zip, $parts['dbf']);
        if ($dbf['records'] !== $count) {
            throw new ShapeCheckError('De .shp, .shx en .dbf horen niet bij elkaar (verschillend aantal objecten).');
        }
        if ($count === 0) {
            throw new ShapeCheckError('De shapefile bevat geen polygonen.');
        }
        if ($count > SHAPE_MAX_POLYGONS) {
            throw new ShapeCheckError('De shapefile bevat ' . $count . ' polygonen; maximaal ' . SHAPE_MAX_POLYGONS . ' zijn toegestaan.');
        }
        $fields = [];
        foreach ($dbf['fields'] as $f) {
            if (in_array($f['type'], SHAPE_ID_FIELD_TYPES, true) && preg_match(SHAPE_FIELD_NAME, $f['name'])) {
                $fields[] = $f['name'];
            }
        }
        if (!$fields) {
            throw new ShapeCheckError('De .dbf bevat geen tekst- of getalveld dat als gebieds-ID kan dienen.');
        }
        return ['count' => $count, 'fields' => $fields];
    } finally {
        $zip->close();
    }
}

// True if every polygon has a value in $field and no value occurs twice. Values are
// compared like the tool does (trimmed, case-insensitive). Deleted records are skipped.
function shape_field_values_ok(string $zipPath, string $field): bool {
    $zip = shape_open_zip($zipPath);
    try {
        $parts = shape_find_parts($zip);
        $dbf = shape_dbf_header($zip, $parts['dbf']);
        $col = null;
        foreach ($dbf['fields'] as $f) {
            if (strcasecmp($f['name'], $field) === 0) {
                $col = $f;
            }
        }
        if ($col === null) {
            return false;
        }
        $stream = $zip->getStream($parts['dbf']);
        if ($stream === false) {
            return false;
        }
        try {
            if (shape_fread($stream, $dbf['header']) === null) {
                return false;
            }
            $seen = [];
            for ($i = 0; $i < $dbf['records']; $i++) {
                $rec = shape_fread($stream, $dbf['record']);
                if ($rec === null) {
                    return false;
                }
                if ($rec[0] === '*') {
                    continue;
                }
                $value = trim(substr($rec, $col['offset'], $col['length']), " \0");
                if ($value === '') {
                    return false;
                }
                $key = $col['type'] === 'C' ? strtoupper($value) : (string)(float)$value;
                if (isset($seen[$key])) {
                    return false;
                }
                $seen[$key] = true;
            }
            return true;
        } finally {
            fclose($stream);
        }
    } catch (ShapeCheckError $e) {
        return false;
    } finally {
        $zip->close();
    }
}

// ---- helpers ------------------------------------------------------------------

function shape_open_zip(string $zipPath): ZipArchive {
    $zip = new ZipArchive();
    if (!is_file($zipPath) || $zip->open($zipPath, ZipArchive::RDONLY) !== true) {
        throw new ShapeCheckError('Het bestand is geen geldig zip-bestand.');
    }
    return $zip;
}

// Finds the one shapefile in the zip (folders allowed) and returns the entry names of
// its .shp, .shx, .dbf and optional .prj (same base name, case-insensitive).
function shape_find_parts(ZipArchive $zip): array {
    $entries = [];
    $shp = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === false) {
            throw new ShapeCheckError('Het bestand is geen geldig zip-bestand.');
        }
        $path = str_replace('\\', '/', $name);
        if ($path === '' || $path[0] === '/' || preg_match('#^[A-Za-z]:#', $path)
            || in_array('..', explode('/', $path), true)) {
            throw new ShapeCheckError('Het zip-bestand bevat ongeldige bestandsnamen (absolute paden of ..).');
        }
        // folders and macOS metadata (__MACOSX/, ._name.shp) are ignored
        if (substr($path, -1) === '/' || strpos($path, '__MACOSX/') === 0 || strpos(basename($path), '._') === 0) {
            continue;
        }
        $entries[strtolower($path)] = $name;
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'shp') {
            $shp[] = strtolower($path);
        }
    }
    if (count($shp) === 0) {
        throw new ShapeCheckError('Het zip-bestand bevat geen shapefile (.shp).');
    }
    if (count($shp) > 1) {
        throw new ShapeCheckError('Het zip-bestand bevat meer dan één shapefile (.shp); upload er één tegelijk.');
    }
    $stem = substr($shp[0], 0, -4);
    $missing = [];
    foreach (['shx', 'dbf'] as $ext) {
        if (!isset($entries["$stem.$ext"])) {
            $missing[] = '.' . $ext;
        }
    }
    if ($missing) {
        throw new ShapeCheckError('De shapefile is niet compleet: ' . implode(' en ', $missing)
            . ' ontbreekt. Een shapefile bestaat uit .shp, .shx en .dbf met dezelfde naam.');
    }
    return [
        'shp' => $entries["$stem.shp"],
        'shx' => $entries["$stem.shx"],
        'dbf' => $entries["$stem.dbf"],
        'prj' => $entries["$stem.prj"] ?? null,
    ];
}

// The first $length bytes of an entry (less if the entry is shorter).
function shape_read(ZipArchive $zip, string $entry, int $length): string {
    $stream = $zip->getStream($entry);
    if ($stream === false) {
        throw new ShapeCheckError('Het zip-bestand kon niet worden gelezen.');
    }
    $data = '';
    while (strlen($data) < $length && !feof($stream)) {
        $chunk = fread($stream, $length - strlen($data));
        if ($chunk === false || $chunk === '') {
            break;
        }
        $data .= $chunk;
    }
    fclose($stream);
    return $data;
}

// Exactly $length bytes from a stream, or null at the end of the data.
function shape_fread($stream, int $length): ?string {
    $data = '';
    while (strlen($data) < $length) {
        $chunk = fread($stream, $length - strlen($data));
        if ($chunk === false || $chunk === '') {
            return null;
        }
        $data .= $chunk;
    }
    return $data;
}

// Record count, header and record length, and the fields (name, type, offset, length) of a .dbf.
function shape_dbf_header(ZipArchive $zip, string $entry): array {
    $bad = 'Het .dbf-bestand is geen geldige dBase-tabel.';
    $h = shape_read($zip, $entry, 32);
    if (strlen($h) < 32) {
        throw new ShapeCheckError($bad);
    }
    $records = unpack('V', substr($h, 4, 4))[1];
    $headerLength = unpack('v', substr($h, 8, 2))[1];
    $recordLength = unpack('v', substr($h, 10, 2))[1];
    if ($headerLength < 33 || $recordLength < 1) {
        throw new ShapeCheckError($bad);
    }
    $h = shape_read($zip, $entry, $headerLength);
    if (strlen($h) < $headerLength) {
        throw new ShapeCheckError($bad);
    }
    $fields = [];
    $offset = 1; // byte 0 of a record is the deletion flag
    for ($pos = 32; $pos + 32 <= $headerLength && $h[$pos] !== "\x0D"; $pos += 32) {
        $d = substr($h, $pos, 32);
        $length = ord($d[16]);
        $fields[] = [
            'name' => explode("\0", substr($d, 0, 11))[0],
            'type' => strtoupper($d[11]),
            'offset' => $offset,
            'length' => $length,
        ];
        $offset += $length;
    }
    if ($offset > $recordLength) {
        throw new ShapeCheckError($bad);
    }
    return ['records' => $records, 'header' => $headerLength, 'record' => $recordLength, 'fields' => $fields];
}
```

### 9.8 Uitgevoerde tests (26-10-2026)

Testgebruiker 19723; mail naar info@meteobase.nl. Alle tests geslaagd.

- **Upload (`rasterDataUpload`)**, met testbestanden:
  - **a** geldig: 200 met veldenlijst;
  - **b** zonder .dbf, **c** zonder .prj (toegestaan), **d** in WGS84, **e1/e2** punten/lijnen, **f1–f3** naamveld ontbreekt / lege namen / dubbele namen (lege en dubbele waarden worden bij het bestellen geweigerd), **h** nep-zip, **i1** veel polygonen (2.500, toegestaan), **i2** buiten NL, **i3** groter dan 20 MB, **j** shapefile in een map in de zip, **k** zip met een pad.
  - Elk gaf de verwachte 200 of 400 met een passende melding.
  - Testbestanden zelf zijn niet bewaard; de exacte meldingen per geval staan niet in dit document.
- **Bestellen (`rasterdata.php`):**
  - bestelling met een upload uit een andere sessie geweigerd;
  - onbekend veld geweigerd (met de lijst van beschikbare velden);
  - lege of dubbele waarden geweigerd;
  - geldige bestellingen a, c en i1 met inhoudelijk gecontroleerde resultaten (o.a. waarden 0,41/0,14, 24 uurwaarden, 2.500 kolommen).
- **Beveiliging:**
  - GET op een bestand in `meteobase/uploads/`: 403;
  - 11e upload binnen 10 minuten: 429;
  - upload zonder registratie: 400;
  - GET op de endpoint: 405. Een directe GET op `/shapefile_check.php` geeft 200 met een lege body: het bestand definieert alleen functies (zie open vraag 4).
  - Niet aantoonbaar apart getest: upload zonder of met fout CSRF-token. Aanbevolen om dit alsnog te testen (verwacht: 400 "Beveiligingsvalidatie mislukt").
- **`satdata.php`:** andere sessie, lege/dubbele waarden en onbekend veld geweigerd. De geldige bestelling liep vast op de bestaande argumentfout (2.2); dat is de aanleiding voor dit voorstel.
- **Opruimen:** testbestanden zijn verwijderd. De testrijen in `tbbasis` blijven staan. Het nachtelijke script `C:\Scripts\Meteobase-Opruimen.ps1` ruimt alle `*.zip` in `meteobase\uploads\` op.

### 9.9 Terugdraaien van 26-10-2026

1. Kopieer de bestanden uit `C:\backup\upload-20261026\` terug naar:
   - `C:\Apache24\conf\httpd.conf`
   - `C:\php8\php.ini`
   - `C:\Apache24\htdocs\index.php`
   - `C:\Apache24\htdocs\pages\rasterDataUpload.php`, `rasterdata.php` en `satdata.php`
2. Verwijder `C:\Apache24\htdocs\shapefile_check.php`.
3. Voer `C:\Apache24\bin\httpd.exe -t` uit, daarna `httpd -k restart -n Apache2.4` (nodig vanwege `httpd.conf` en `php.ini`).
4. Zet de PHP-bestanden altijd **als set** terug: de nieuwe `rasterdata.php` en `satdata.php` hebben `shapefile_check.php` en de sessieregistratie van de nieuwe `rasterDataUpload.php` nodig.

Bij terugdraaien komen de oude problemen terug: publieke uploads, alleen een extensiecontrole, de typfout `sile`, en uploads die tussen sessies te verwisselen zijn. Uploads die al onder de nieuwe naamgeving zijn opgeslagen, werken dan niet meer in lopende sessies.
