# -*- coding: utf-8 -*-
"""
Created on Wed Mar 18 14:14:38 2020

@author: danie
"""
import math
 
def get_kansverdelingsparameters_jaarrond_2019(duur_minuten, herhalingstijd=10):
   
    # deze functie geeft de kansverdelingsparameters voor jaarrond-neerslag, conform de statistieken van 2019
    # merk op dat er in de statistieken van 2019 géén kansverdelingsfunctie beschikbaar is voor de klimaatscenario's
    # in plaats daarvan wordt de relatieve verandering tussen oude_statistiek_2014 en nieuwe_statistiek_2014 ook toegepast op de oude klimaatscenario's
    if (10 <= duur_minuten <= 720):
    
        #find the location parameter for the GLO probability distribution:
        glo_loc = 1.02 * (7.339 + 0.848 * math.log(duur_minuten,10) + 2.844 * math.log(duur_minuten,10)**2)
    
        #find the dispersion coefficient (this is NOT the scalepar; the scalepar = dispcoef * locationpar):
        if (10 <= duur_minuten <= 104):
            glo_disp = 0.04704 + 0.1978 * math.log(duur_minuten,10) - 0.05729 * math.log(duur_minuten, 10)**2
        elif (104 < duur_minuten <= 720):
            glo_disp = 0.2801 - 0.0333 * math.log(duur_minuten,10)
    
        #find the shapeparameter for the GLO probability distribution
        if (10 <= duur_minuten <= 90) or (herhalingstijd <= 120):
            glo_shape = -0.0336 - 0.264 * math.log(duur_minuten, 10) + 0.0636 * math.log(duur_minuten, 10)**2
        else:
            glo_shape = -0.310 - 0.0544 * math.log(duur_minuten,10) + 0.0288 * math.log(duur_minuten, 10)**2      
        
        glo_scale = glo_loc * glo_disp
        return glo_loc, glo_scale, glo_shape
                
    elif (duur_minuten/60 > 12):
    
        #find the location parameter for the GEV probability distribution:
        gev_loc = 1.02 * (0.239 - 0.0250 * math.log(duur_minuten/60))**(-1/0.512)
    
        #find the dispersion coefficient for the GEV probability distribution:
        gev_disp = 0.478 - 0.0681 * math.log(duur_minuten,10)
    
        #find the location parameter for the GEV probability distribution:
        gev_shape = 0.118 - 0.266 * math.log(duur_minuten,10) + 0.0586 * math.log(duur_minuten,10)**2
        
        #finally compute the scale parameter
        gev_scale = gev_loc * gev_disp
        return gev_loc, gev_scale, gev_shape
    
    elif (duur_minuten < 10):
        return "ongeldige duur gekozen"

def get_kansverdelingsparameters_winter_2019(duur_minuten):
    #met winter worden de maanden November, December, Januari en Februari bedoeld. Voor de zomerstatistiek kan gebruik worden gemaakt van de jaarrondstatistiek.
    if (10 <=  duur_minuten <= 720):
        gev_loc = 1.07 * 1.02 * (4.883 - 5.587 * math.log(duur_minuten,10) + 3.526 * math.log(duur_minuten,10)**2)
        if (10 <= duur_minuten < 91):
            gev_disp = 0.41692 - 0.07583 * math.log(duur_minuten,10)
        else:
            gev_disp = 0.2684
        gev_shape = -0.294 + 0.1474 * math.log(duur_minuten,10) - 0.0192 * math.log(duur_minuten,10)**2
        gev_scale = gev_loc * gev_disp
        return gev_loc, gev_scale, gev_shape
    else:
        gev_loc = (0.670 - 0.0426 * math.log(duur_minuten/60))**(-1/0.193)
        gev_disp = 0.234
        gev_shape = -0.090 + 0.017 * (duur_minuten/60)/24
        gev_scale = gev_loc * gev_disp
        return gev_loc, gev_scale, gev_shape