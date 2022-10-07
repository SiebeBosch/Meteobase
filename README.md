# Meteobase

Meteobase is een webapplicatie en -service die wordt gefinancierd en onderhouden door Het Waterschapshuis (https://www.hetwaterschapshuis.nl/).

De applicatie en service zijn in het leven geroepen om medewerkers van waterschappen en adviesbureaus in de watersector te ondersteunen bij het uitvoeren van modelstudies waarvoor meteorologische gegevens nodig zijn. De applicatie ontsluit Nederlandse historische neerslag- en verdampingsgegevens en presenteert die in een kant-en-klaar bestandsformaat ten behoeve van veelgebruikte modellen (SOBEK, SIMGRO, MODFLOW). Daarnaast ontsluit Meteobase statistische data omtrent neerslag in Nederland en heeft het een uitgebreide literatuursectie.

Bouw van de applicatie en beheer & onderhoud is vanaf het begin in handen van:

* Siebe Bosch (Hydroconsult, https://github.com/SiebeBosch)

met bijdragen van:

* Arend Ketelaar (GeoPro): front-end
* Maxim Bureac: front-end
* Daniël Tollenaar (D2Hydro, https://github.com/d2hydro): python-script regenduurlijnen
* Jacques Doeleman (Iconica, https://github.com/JacquesDIconica): front-end

De architectuur bestaat uit:
* een front-end, geschreven in PHP
* een backend, bestaande uit:
  * Een negental executables, geschreven in VB.NET en bijgehouden in Visual Studio 2022 van Microsoft:
     * WIWBBASIS: levert meetreeksen van de neerslagstations van het KNMI: uursommen, etmaalsommen, neerslag en verdamping
     * WIWBRASTER2ASCI: levert geijkte radarneerslagsommen en ruimtelijk verdeelde verdamping in ASCII-rasterformaat
     * WIWBRASTERBYPOLY: levert geijke radarneerslag en ruimtelijk verdeelde verdamping, geaggregeerd naar polygonen
     * WIWBNATIVERASTER: levert de geijkte radarneerslag en ruimtelijk verdeelde verdamping in het oorspronkelijke formaat (HDF5)
     * WIWBSTEDELIJK: levert de stedelijke neerslaggebeurtenissen
     * WIWBSTOCHASTEN: levert kansen bij neerslagvolumes, voor huidig klimaat en diverse klimaatprojecties
     * WIWBTOETSING: levert langjarige klimaatgecorrigeerde neerslagreeksen voor diverse klimaatregio's in Nederland
     * WIWBHERHALINGSTIJD: genereert een kaart met de terugkeertijd in jaren voor de neerslag op gegeven dag en met gegeven duur
     * WIWBFEEDBACK: verstuurt de input van het feedbackformulier op de site naar inf-at-meteobase.nl. Momenteel niet in gebruik
  * Een python-script voor de in-app regenduurlijnen-applicatie
  * Een PostgreSQL-database t.b.v. opslag gebruiksstatistieken
  * Een Apache webserver
* installatiescripts voor de executables, geschreven in Inno Script Studio

Historische neerslag- en verdampingsgegevens worden door Meteobase betrokken van de WIWB-server via de WIWB-API (https://portal.hydronet.com/data/files/Technische%20Instructies%20WIWB%20API.pdf). We merken op dat deze API niet publiekelijk toegankelijk is. Een eigen implementatie van de applicatie of executables zal daarom alleen functioneren vanaf een IP-adres dat door WIWB is gewhitelist.

De verantwoordelijkheid voor het beheer en onderhoud van Meteobase ligt tot en met Q3 van 2023 bij Hydroconsult.





