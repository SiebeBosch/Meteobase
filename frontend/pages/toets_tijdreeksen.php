    	<form name="toetsingsdata03" action="?tb=toetsingsdata&dp=toetsingsdata" method="post">
    <div style="width:170px; float:left">
    <input type="hidden" name="subtdt">
    <input type="hidden" name="subtd">
    <input type="hidden" name="select01" value="Tijdreeksen">
    	<div><select name="select03" style="width:150px" disabled="disabled"><option>Tijdreeksen</option></select></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="HUIDIG" value="HUIDIG"<?php if(isset($HUIDIG)){ echo 'checked="checked"'; } ?> /> Huidig</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="ALL_2030" value="ALL_2030"<?php if(isset($ALL_2030)){ echo 'checked="checked"'; } ?> /> 2030</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="GL_2050" value="GL_2050"<?php if(isset($GL_2050)){ echo 'checked="checked"'; } ?> /> 2050 GL</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="GH_2050" value="GH_2050"<?php if(isset($GH_2050)){ echo 'checked="checked"'; } ?> /> 2050 GH</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="WL_2050" value="WL_2050"<?php if(isset($WL_2050)){ echo 'checked="checked"'; } ?> /> 2050 WL</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="WH_2050" value="WH_2050"<?php if(isset($WH_2050)){ echo 'checked="checked"'; } ?> /> 2050 WH</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="GL_2085" value="GL_2085"<?php if(isset($GL_2085)){ echo 'checked="checked"'; } ?> /> 2085 GL</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="GH_2085" value="GH_2085"<?php if(isset($GH_2085)){ echo 'checked="checked"'; } ?> /> 2085 GH</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="WL_2085" value="WL_2085"<?php if(isset($WL_2085)){ echo 'checked="checked"'; } ?> /> 2085 WL</p></div>
    	<div style="margin-top:10px"><p style="padding-bottom:0px"><input type="checkbox" name="WH_2085" value="WH_2085"<?php if(isset($WH_2085)){ echo 'checked="checked"'; } ?> /> 2085 WH</p></div>
		<br />
<!--		<div style="margin-top:5px"><p style="padding-bottom:0px">Oppervlakte studiegebied [km&sup2;]</p></div> -->
<!--		<div style="margin-top:5px"><p style="padding-bottom:0px"><input id="area" type="text" name="area" value="<?php if(isset($area)){ echo $area;}else{ echo '6'; } ?>" size="5" /> (optioneel)</p></div> -->
<!--		<div style="margin-top:5px; margin-left:23px"><p style="padding-bottom:0px">Areal Reduction Factor</p></div> -->
    </div>
    
    <div style="width:100px; margin-left:20px; float:left">
<!--    	<p style="padding-bottom:10px">van datum:</p> -->
<!--        <p style="padding-bottom:10px">tot datum:</p> -->
        <br /><br /><br /><br /><br /><br />
<!--        <div style="margin-top:-45px; margin-left:23px"><p style="padding-bottom:0px"><input type="text" name="arf" size="3" value="<?php if(isset($arf_check)){ echo $arf_check; }else{ echo '0.97'; } ?>" ></p></div> -->
    </div>
    
    <div style="width:150px; margin-left:20px; float:left">
<!--        <div><p style="padding-bottom:0px"><input type="text" name="dv" size="10" value="<?php if(isset($d_check)){ echo $dv; }else{ echo '01/01/1906'; } ?>"></p></div> -->
<!--        <div><p style="padding-bottom:0px"><input type="text" name="dt" size="10" value="<?php if(isset($d_check)){ echo $dt; }else{ echo '31/12/2010'; } ?>"></p></div> -->
    </div>
    
    <div id="downloadbutton" class="gegevenspaneel_btn"><a style="color:#000" href="javascript: document.forms.toetsingsdata03.submit(); melding();">Downloaden</a></div>
    </form>