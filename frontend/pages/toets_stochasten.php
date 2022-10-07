    	<form name="toetsingsdata02" action="?tb=toetsingsdata&dp=toetsingsdata" method="post">
    <div style="width:250px; float:left">
    <input type="hidden" name="subtds">
    <input type="hidden" name="subtd">
    <input type="hidden" name="select01" value="Stochasten">
    	<div><select name="select02" style="width:150px" disabled="disabled"><option>Stochasten</option></select></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="VOL_HUIDIG" value="VOL_HUIDIG"<?php if(isset($VOL_HUIDIG)){ echo 'checked="checked"'; } ?> /> Huidig, volumeklassen</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="OVF_HUIDIG" value="OVF_HUIDIG"<?php if(isset($OVF_HUIDIG)){ echo 'checked="checked"'; } ?> /> Huidig, herhalingstijden</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="VOL_2030" value="VOL_2030"<?php if(isset($VOL_2030)){ echo 'checked="checked"'; } ?> /> 2030, volumeklassen</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="OVF_2030" value="OVF_2030"<?php if(isset($OVF_2030)){ echo 'checked="checked"'; } ?> /> 2030, herhalingstijden</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="VOL_2050" value="VOL_2050"<?php if(isset($VOL_2050)){ echo 'checked="checked"'; } ?> /> 2050, volumeklassen</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="OVF_2050" value="OVF_2050"<?php if(isset($OVF_2050)){ echo 'checked="checked"'; } ?> /> 2050, herhalingstijden</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="VOL_2085" value="VOL_2085"<?php if(isset($VOL_2085)){ echo 'checked="checked"'; } ?> /> 2085, volumeklassen</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="OVF_2085" value="OVF_2085"<?php if(isset($OVF_2085)){ echo 'checked="checked"'; } ?> /> 2085, herhalingstijden</p></div>
		<br />
         <div style="margin-top:5px"><p style="padding-bottom:0px">&nbsp;</p></div>
<!--        <div style="margin-top:5px"><p style="padding-bottom:0px">Areal Reduction Factor &nbsp;<input type="text" name="arf" size="3" value="<?php if(isset($arf_check)){ echo $arf_check; }else{ echo '0.97'; } ?>" ></p></div> -->
    </div>
	<br>
	<?php 
		$bericht = "Klik op de knop 'Downloaden' om uw bestelling op te halen.";	
	?>
<!--    <div class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.toetsingsdata02.submit()">Downloaden</a></div> -->
<!--    <div class="gegevenspaneel_btn"><a style="color:#000" href="http://62.148.170.210/meteobase/downloads/fixed/Stochastendata.xls">Downloaden</a></div> -->
    <div id="downloadbutton" class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.toetsingsdata02.submit(); melding();">Downloaden</a></div>
    </form>
	
