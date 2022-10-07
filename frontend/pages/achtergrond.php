<?php
exec('"C:/Program Files (x86)/Hydroconsult/RASTER2NETCDF/MBRASTER2NETCDF.EXE" ' . $FDATE . ' ' . $TDATE . ' ' . $XMIN . ' ' . $YMIN . ' ' . $XMAX . ' ' . $YMAX . ' ' . $NSL . ' ' . $MAKKINK . ' ' . $PENMAN . ' false ' .$sessionid . ' ' . $NewOrder . ' "' . $naam . '" "' . $mail . '"');
?>