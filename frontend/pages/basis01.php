<!--                <form name="basisgegevens" action="index.php?tb=basisgegevens&dp=basisgegevens" method="post">-->
<!--                    <div style="width:170px; float:left;">-->
<!--                        <input type="hidden" name="subbg">-->
<!--                        <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="neerslag" value="neerslag"--><?php //if(isset($neerslag)){ echo 'checked="checked"'; } ?><!-- /> Neerslag</p></div>-->
<!--                        <div style="margin-top:5px"><p style="padding-bottom:0px"><input id="makkink" type="checkbox" name="makkink" value="makkink"--><?php //if(isset($makkink)){ echo 'checked="checked"'; } if($waarde_check == 'dag') { echo 'disabled="disabled"'; } ?><!-- /> Verdamping (Makkink)</p></div>-->
<!---->
<!--                        <br />-->
<!--                    </div>-->
<!---->
<!--        <div style="width:110px; margin-left:20px; float:left">-->
<!--    		<p style="padding-bottom:0px"><input id="waarde_uur" type="radio" onclick="selectStation(this); test('enable');" name="waarde" value="uur" --><?php //if($waarde_check == 'uur'){ echo 'checked="checked"'; } ?><!-- /> Uurstations</p>-->
<!--	        <p style="padding-bottom:0px"><input id="waarde_dag" type="radio" onclick="selectStation(this); test('disable');" name="waarde" value="dag" --><?php //if($waarde_check == 'dag'){ echo 'checked="checked"'; } ?><!-- /> Dagstations</p>-->
<!--			<br />-->
<!--            <br />-->
<!--            <br />-->
<!--   		</div>-->
<!--    -->
<!--        <div style="width:140px; margin-left:20px; float:left">-->
<!--            <div><p style="padding-bottom:0px"><input type="text" name="dv" size="10" value="--><?php //if(isset($d_check)){ echo $dv; }else{ echo '01/01/1906'; } ?><!--"></p></div>-->
<!--            <div><p style="padding-bottom:0px"><input type="text" name="dt" size="10" value="--><?php //if(isset($d_check)){ echo $dt; }else{ echo '31/12/2013'; } ?><!--"></p></div>-->
<!--        </div>-->
<!--        -->
<!--        <div id="downloadbutton" class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.basisgegevens.submit(); melding();">Downloaden</a></div>-->
<!--        </form>-->
<!--Starting Basic Info Section-->
<div class="row">
    <div class="content-row form-row-container">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="row">
                <h2 class="section-title">
                    Basisgegevens
                </h2>
                <div class="form-container">
                    <form method="POST" name="basisgegevens" action="index.php?tb=basisgegevens&dp=basisgegevens" id="basicInfoForm" class="basic-info-form">
                        <input type="hidden" name="subbg">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Gegevenstype</label>
                                <div class="input-group">
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="neerslag" name="neerslag" value="neerslag"> Neerslag
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" id="verdamping" name="makkink" value="makkink"> Verdamping (Makkink)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label>Stationstype</label>
                                <div class="input-group">
                                    <label class="radio-inline">
                                        <input type="radio" id="uurstations" name="stationsType" value="Uurstations"> Uurgegevens
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" id="dagstations" name="stationsType" value="Dagstations"> Etmaalgegevens
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="dateFrom">Van Datum</label>
                                <div class='input-group date date-picker' id="date-picker-from">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input id="dateFrom" class="form-control" name="fromDate" />
                                </div>
                            </div>
                        
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="dateTo">Tot Datum</label>
                                <div class='input-group date date-picker' id="date-picker-to">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                    <input type='text' id="dateTo" class="form-control" name="toDate"/>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <button class="btn btn-primary pull-right submit-btn" type="submit">Downloaden</button>
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
                            Gebruik "SHIFT" om meerdere stations te selecteren. Datums kunt u ook intypen in het formaat dd/mm/jjjj.
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
                            Geef een start- en einddatum op in "dd/mm/jjjj" of klik op de kalendericoontjes om te selecteren.
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
                            De gegevens zijn beschikbaar in volumes als functie van herhalingstijd en in herhalingstijd als functie van volume
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