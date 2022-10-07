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
					Stedelijke neerslaggebeurtenissen
				</h2>
				<div class="form-container">
					<form method="POST" action="<?php echo($_SERVER["PHP_SELF"]); ?>?tb=stedelijk&dp=toetsingsdata" class="testing-modal" id="testingInfoForm">
						<div class="col-md-12">
							<div class="form-group">
								<label>Scenario</label>
								<div class="row">
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stedelijk_2014"> Klimaat 2014
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stedelijk_2030"> Klimaat 2030 
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stedelijk_2050"> Klimaat 2050
										</label>
									</div>
									<div class="col-md-9">
										<label>
											<input type="radio" name="dataType" value="stedelijk_2085"> Klimaat 2085
										</label>
									</div>
								</div>
							</div>
						</div>						
						<div class="col-md-12">
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