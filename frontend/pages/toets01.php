<!--	<form name="toetsingsdata01" action="?tb=toetsingsdata&dp=toetsingsdata" method="post">-->
<!--		<div style="width:170px; float:left">-->
<!--        	<input type="hidden" name="subtd">-->
<!--    		<div style="margin-top:5px"><select name="select01" style="width:150px"><option>Stochasten</option><option>Tijdreeksen</option></select></div>-->
<!--        </div>-->
<!--		<div class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.toetsingsdata01.submit()">Verder</a></div>-->
<!--    </form>-->
<!--Starting About Meteobase Section-->
<div class="row">
	<div class="content-row form-row-container">
		<div class="col-md-12">
			<div class="row">
				<h2 class="section-title">
					Toetsingsdata
				</h2>
				<div class="form-container">
					<form method="POST" action="index.php?tb=toetsingsdata&dp=toetsingsdata" class="testing-modal" id="testingInfoForm">
						<div class="col-md-12">
							<div class="form-group">
								<label>Gegevenstype</label>
								<div class="row">
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stochasten_2019"> Statistieken STOWA 2019
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="tijdreeksen_2019"> Tijdreeksen STOWA 2019
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stochasten_2015"> Oude statistieken
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="tijdreeksen_2015"> Oude tijdreeksen
										</label>
									</div>
								</div>
							</div>
						</div>
						
						<!--<div class="col-md-12 checkbox-list hidden" id="stochasten-checkboxes">
							<div class="row">
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="VOL_HUIDIG" value="VOL_HUIDIG"> Huidig, volumeklassen
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="OVF_HUIDIG" value="OVF_HUIDIG"> Huidig, herhalingstijden
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="VOL_2030" value="VOL_2030"> 2030, volumeklassen
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="OVF_2030" value="OVF_2030"> 2030, herhalingstijden
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="VOL_2050" value="VOL_2050"> 2050, volumeklassen
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="OVF_2050" value="OVF_2050"> 2050, herhalingstijden
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="VOL_2085" value="VOL_2085"> 2085, volumeklassen
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="OVF_2085" value="OVF_2085"> 2085, herhalingstijden
									</label>
								</div>
							</div>
						</div>-->
						<div class="col-md-12 checkbox-list hidden" id="tijdreeksen_2019-checkboxes">
							<div class="row">
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="HUIDIG" value="HUIDIG"> Klimaat 2014
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="ALL_2030" value="ALL_2030"> Klimaat 2030
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GL_2050" value="GL_2050"> Klimaat 2050 GL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GH_2050" value="GH_2050"> Klimaat 2050 GH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WL_2050" value="WL_2050"> Klimaat 2050 WL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WH_2050" value="WH_2050"> Klimaat 2050 WH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GL_2085" value="GL_2085"> Klimaat 2085 GL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GH_2085" value="GH_2085"> Klimaat 2085 GH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WL_2085" value="WL_2085"> Klimaat 2085 WL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WH_2085" value="WH_2085"> Klimaat 2085 WH
									</label>
								</div>
							</div>
						</div>
						<div class="col-md-12 checkbox-list hidden" id="tijdreeksen_2015-checkboxes">
							<div class="row">
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="HUIDIG" value="HUIDIG"> Klimaat 2014
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="ALL_2030" value="ALL_2030"> Klimaat 2030
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GL_2050" value="GL_2050"> Klimaat 2050 GL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GH_2050" value="GH_2050"> Klimaat 2050 GH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WL_2050" value="WL_2050"> Klimaat 2050 WL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WH_2050" value="WH_2050"> Klimaat 2050 WH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GL_2085" value="GL_2085"> Klimaat 2085 GL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="GH_2085" value="GH_2085"> Klimaat 2085 GH
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WL_2085" value="WL_2085"> Klimaat 2085 WL
									</label>
								</div>
								<div class="col-md-6">
									<label>
										<input type="checkbox" name="WH_2085" value="WH_2085"> Klimaat 2085 WH
									</label>
								</div>
							</div>
						</div>
						
						<div class="col-md-6">
							<button class="btn btn-primary submit-btn pull-right" type="submit">Downloaden</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<!--Ending About Meteobase Section-->
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