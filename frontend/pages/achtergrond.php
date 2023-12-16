<?php
exec('"c:/Program Files/Hydroconsult/NATIVERASTER/WIWBNATIVERASTER.exe" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" "' . $mail . '"');
?>