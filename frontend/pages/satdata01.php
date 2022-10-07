<!--    <script>-->
<!--    $('#coordinaten').css('opacity','1');-->
<!--	-->
<!--	function check() {-->
<!--        if($('#veldnaam').val() == 'Veldnaam') {-->
<!--            $("#veldnaam").after("<span style='color:red;'> Graag uw veldnaam vermelden.</span>");-->
<!--        }else{-->
<!--		    $('#uploadbox').fadeOut('fast', function() {-->
<!--			    melding();-->
<!--                document.forms.rasterdata.submit();-->
<!--			});-->
<!--        }-->
<!--    }-->
<!--    function upload_pu(x) {-->
<!--        if (x == 'sobek' || x == 'csv') {-->
<!--            $('#uploadbox').fadeIn();-->
<!--            $('#downloadbutton').html('<a style="color:#000" href="javascript:void(0)" onclick="check()">Downloaden</a>');-->
<!--		}else{-->
<!--            $('#downloadbutton').html('<a style="color:#000" href="javascript:void(0)" onclick="document.forms.rasterdata.submit(); melding();">Downloaden</a>');-->
<!--            $('#uploadbox').fadeOut('fast');-->
<!--        }-->
<!--    }-->
<!--    </script>-->
<!---->
<!--    <form name="rasterdata" action="?tb=rasterdata&dp=rasterdata&dp_sub=introductie" enctype="multipart/form-data" method="post">-->
<!--        <div style="width:170px; float:left">-->
<!--            <input type="hidden" name="subrd" />-->
<!--            <input type="hidden" name="coordx" value="coordinaten x" />-->
<!--            <input type="hidden" name="coordy" value="coordinaten y" />-->
<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="neerslag" value="neerslag"--><?php //if(isset($neerslag)){ echo 'checked="checked"'; } ?><!-- /> Neerslag</p></div>-->
<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="makkink" value="makkink"--><?php //if(isset($makkink)){ echo 'checked="checked"'; } ?><!-- /> Verdamping (Makkink)</p></div>-->
<!--            <div style="margin-top:5px"><p style="padding-bottom:0px"><input type="checkbox" name="penman" value="penman"--><?php //if(isset($penman)){ echo 'checked="checked"'; } ?><!-- /> Verdamping (Penman)</p></div>-->
<!--            <br />-->
<!--            <select name="formaat" onchange="upload_pu(this.value)" >-->
<!--                <option value="ascii">ASCII</option>-->
<!--				<option value="hdf5">HDF5</option>-->
<!--                <option value="modflow">MODFLOW</option>-->
<!--				<option value="simgro">SIMGRO</option>-->
<!--                <option value="sobek">SOBEK</option>-->
<!--                <option value="csv">CSV</option>-->
<!--            </select>-->
<!--        </div>-->
<!---->
<!--        <div id="uploadbox" style="padding-left:38px;">-->
<!--            <span>&nbsp;Upload hier uw gezipte shapefile (*.shp, *.shx, *.dbf).</span>-->
<!--            <input type="file" name="zipfile" />-->
<!--            <input id="veldnaam" type="text" name="veldnaam" value="Veldnaam" size="12" maxlength="20" onclick="this.select()" />-->
<!--        </div>-->
<!---->
<!--        <div style="width:110px; margin-left:20px; float:left">-->
<!--            <p style="padding-bottom:10px">van datum:</p>-->
<!--            <p style="padding-bottom:10px">tot datum:</p>-->
<!--            <br />-->
<!--            <br />-->
<!--   	    </div>-->
<!---->
<!--        <div style="width:140px; margin-left:20px; float:left">-->
<!--            <div><p style="padding-bottom:0px"><input type="text" name="dv" size="10" value="--><?php //if(isset($d_check)){ echo $dv; }else{ echo '01/01/1990'; } ?><!--"></p></div>-->
<!--            <div><p style="padding-bottom:0px"><input type="text" name="dt" size="10" value="--><?php //if(isset($d_check)){ echo $dt; }else{ echo '31/12/2013'; } ?><!--"></p></div>-->
<!--        </div>-->
<!---->
<!--        <div id="downloadbutton" class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.rasterdata.submit(); melding();">Downloaden</a></div>-->
<!--    </form>-->

    <!--Starting RasterData Section-->
    <div class="row">
        <div class="content-row form-row-container">
            <div class="col-md-12">
                <div class="row">
                    <h2 class="section-title">
                        SAT Data 2.0
                    </h2>
                    <div class="form-container">
                        <form method="POST" enctype="multipart/form-data" action="<?php echo($_SERVER["PHP_SELF"]); ?>?tb=satdata&dp=satdata&dp_sub=introductie" class="sat-data" id="rasterDataForm">
                            <input type="hidden" name="subrd" />
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <label class="checkbox-label">Werkelijke verdamping</label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etact_2016_NL.7z">Werkelijke verdamping 2016</a>
                                        </label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etact_2017_NL.7z">Werkelijke verdamping 2017</a>
                                        </label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etact_2018_NL.7z">Werkelijke verdamping 2018</a>
                                        </label>
										<br>
                                        <label class="checkbox-label">Verdampingstekort (potentieel - werkelijk)</label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etdef_2016_NL.7z">Verdampingstekort 2016</a>
										</label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etdef_2017_NL.7z">Verdampingstekort 2017</a>
                                        </label>
                                        <label class="checkbox-label">
											<a href="./meteobase/downloads/fixed/etdef_2018_NL.7z">Verdampingstekort 2018</a>
                                        </label>
                                    </div>
                                </div>                        
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--Ending Rasterdata Section-->
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
                            SAT Data 2.0
                        </h3>
                        <p class="slider-text">
                            Deze sectie bevat de gegevens van het project SAT DATA 2.0. Klik op de links om de bijbehorende bronbestanden te downloaden. De rapporten staan vermeld in het hoofdstuk Documentatie, maar kunt u ook <a href="./downloads/fixed/literatuur/20171101_Methodiek SAT-DATA_eLEAF_v4.pdf">hier</a> (rapport methodiek) en <a href="./downloads/fixed/literatuur/20171101_Plausibiliteit_SAT-DATA_eLEAF_v4.pdf">hier</a> (rapport plausibiliteit) downloaden.
                        </p>
                    </div>
                    <div class="col-md-6 col-sm-12 col-xs-12">
                        <img src="images/SAT_DATA.png" alt="" class="img-responsive">
                    </div>
                </div>
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