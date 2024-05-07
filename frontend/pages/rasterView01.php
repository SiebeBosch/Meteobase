<div class="row">
    <div class="content-row form-row-container">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="row">

                <h2 class="section-title">
                    Herhalingstijd
                </h2>
                <div class="form-container">
                    <form method="POST" name="rasterview" action="index.php?tb=rasterview&dp=rasterview"
                        id="rasterViewForm" class="raster-view-form">
                        <input type="hidden" name="subbg">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="rasterViewDateFrom">Datum</label>
                                <div class='input-group date date-picker' id="date-picker-from">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input id="rasterViewDateFrom" class="form-control" name="rasterViewDateFrom" />
                                </div>
                            </div>

                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <input type='hidden' name="interval-start" id="interval-start" value="-15" />
                                <input type="hidden" name="interval-end" id="interval-end" value="15" />
                                <input type="hidden" name="intervalStartDate" />
                                <input type='hidden' name="intervalEndDate" />
                                <input type="hidden" name="fileName" />
                                <label for="interval">Interval: <span id="interval">0 tot 1 dagen na gekozen datum (24
                                        uur)</span></label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div id="slider-interval"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-primary pull-right submit-btn" type="submit">Bekijken</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Ending Basic Info Section-->
<!--Starting Features Slider-->
<div class="features-slider content-row">
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <h2 class="section-title">
                Handleiding
            </h2>
            <div id="features-slider" class="col-md-12 col-xs-12 col-sm-12">
                <div class="slide">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h3 class="section-subtitle">
                            Basisgegevens
                        </h3>
                        <p class="slider-text">
                            Dit tabblad bevat de basisgegevens zoals geproduceerd door het KNMI.
                            Maak eerst de keuze voor uurstations of dagstations, en selecteer daarna op
                            de kaart de stations van welke u gegevens wilt downloaden.
                            Merk op dat verdampingscijfers alleen beschikbaar zijn op de uurstations.
                            <br>
                            Gebruik "SHIFT" om meerdere stations te selecteren. Datums kunt u ook intypen in het formaat
                            dd/mm/jjjj.
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <img src="images/basicInfo.jpg" alt="" class="img-responsive">
                    </div>
                </div>
                <div class="slide">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h3 class="section-subtitle">
                            Rasterdata
                        </h3>
                        <p class="slider-text">
                            Deze sectie bevat neerslag- en verdampingsgegevens in rasterformaat.
                            Selecteer een rechthoek op de kaart door twee hoekpunten aan te klikken.
                            Geef een start- en einddatum op in "dd/mm/jjjj" of klik op de kalendericoontjes om te
                            selecteren.
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <img src="images/rasterdata.jpg" alt="" class="img-responsive">
                    </div>
                </div>
                <div class="slide">
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <h3 class="section-subtitle">
                            Toetsingsdata
                        </h3>
                        <p class="slider-text">
                            In deze sectie kunt u meteorologische gegevens downloaden ten behoeve van statistische
                            analyses zoals hoogwaterstudies.
                            De gegevens zijn beschikbaar in volumes als functie van herhalingstijd en in herhalingstijd
                            als functie van volume
                        </p>
                        <p class="slider-text">
                            Omdat KNMI-station De Bilt beschikt over de langste homogene dataset van Nederland
                            (1906-heden), zijn alle gegevens in deze sectie ontleend aan meetwaarden van dit station.

                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <img src="images/toetsingsdata.jpg" alt="" class="img-responsive">
                    </div>
                </div>
            </div>
            <div class="arrows">
                <div class="prev arrow">
                    <i class="fa fa-chevron-left"></i>
                </div>
                <div class="next arrow">
                    <i class="fa fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<!--Ending Features Slider-->