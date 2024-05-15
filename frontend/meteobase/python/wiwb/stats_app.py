# -*- coding: utf-8 -*-
"""
Python app for Meteo Base. Handling computation of duration curves
"""

__author__ = "Daniel Tollenaar"
__credits__ = ["Daniel Tollenaar", "Siebe Bosch"]
__maintainer__ = "Daniel Tollenaar"
__email__ = "daniel@d2hydro.nl"
__status__ = "Development"

from collections import defaultdict
from flask import (
    Blueprint, request
)
from json import dumps
from wiwb.stats_lib import GEVCDF, GEVINVERSE, GLOCDF, GLOINVERSE, AREA
from wiwb.series_lib import xy_series

    
from numpy import array, log, log10,  exp, concatenate, where, interp, abs, isnan, savetxt, ones
import os
import xlrd

import matplotlib.pyplot as plt
import numpy as np
import pandas as pd


# vaste variabelen (worden ingeladen bij het starten van de app)
duren = {'regenduurlijnen':[1/6,1/2,1,2,4,8,12,24,48,96,192],
            "oppervlaktereductie":[0.25,0.5,1,2,4,8,12,24,48,96,192,216]}
volumes = [10,20,30,40,50,75,100,150]
herhalingstijden = [0.5,1,2,5,10,20,25,50,100,250,500,1000]
max_iters = 1000
accuracy = 0.1

# set url
bp = Blueprint('stats', __name__, url_prefix='/api/stats')

# set working-directory
abspath = os.path.abspath(__file__)
dname = os.path.dirname(abspath)
os.chdir(dname)

#%% lees gepubliceerde parameters
wb = xlrd.open_workbook(os.path.abspath(r'resources\Regenduurlijnparameters.xlsm'))
parameters = {'STOWA2015':defaultdict(list),
          'STOWA2018':defaultdict(list),
          'STOWA2019':defaultdict(list)}

for key, value in parameters.items():
    sh = wb.sheet_by_name(key)
    for i in range(1,sh.nrows):
        climate = int(sh.cell(i,0).value)
        scenario = str(sh.cell(i,1).value)
        region = str(sh.cell(i,2).value)
        season = str(sh.cell(i,3).value)
        values = ()   
        parameters[key][climate,scenario,region,season].append([sh.cell(i,j).value for j in range(4,len(sh.row(0)))])

del wb

#%% functies om de kansverdelingsparameters af te leiden uit gepubliceerde parameters
def get_2015_GEV_params(climate,scenario,season,region,durations): 
    '''geeft de GEV parameters van STOWA2015, geldig voor duren > 12 uur'''    
    cp = parameters['STOWA2015'][int(climate),scenario,region,season][0]
    mu = (float(cp[0]) + float(cp[1]) * log(durations))**(1/float(cp[2]))
    disp = float(cp[3]) + float(cp[4]) * log(durations) + float(cp[5]) * log(durations)**2
    sigma = mu * disp
    Zeta = -(-0.090 + 0.017 * array(durations) / 24 )# alleen voor RD_X_ARRAY < 240 uur!
    
    return mu, sigma, Zeta

def get_2019_params(durations,season,return_period=1):
    '''geeft de parameters voor STOWA2019 huidig klimaat'''
    durations = array(durations)
    durations_mins = durations * 60
    
    if season == 'jaarrond':
        mu = concatenate([1.02 * (7.339 + 0.848 * log10(durations_mins[durations_mins <= 720]) + 2.844 * log10(durations_mins[durations_mins <= 720])**2),
                          1.02 * (0.239 - 0.0250 * log(durations[durations_mins > 720]))**(-1/0.512)])
        

        disp = concatenate([0.04704 + 0.1978 * log10(durations_mins[durations_mins <= 104]) - 0.05729 * log10(durations_mins[durations_mins <= 104])**2,
                                0.2801 - 0.0333 * log10(durations_mins[(durations_mins <=720) & (104 < durations_mins)]),
                                0.478 - 0.0681 * log10(durations_mins[durations_mins > 720])])
        
        if return_period <= 120:
            Zeta = concatenate([-0.0336 - 0.264 * log10(durations_mins[durations_mins <= 720]) + 0.0636 * log10(durations_mins[durations_mins <= 720])**2,
                                 0.118 - 0.266 * log10(durations_mins[durations_mins > 720]) + 0.0586 * log10(durations_mins[durations_mins > 720])**2])
        else:
            Zeta = concatenate([-0.0336 - 0.264 * log10(durations_mins[durations_mins <= 90]) + 0.0636 * log10(durations_mins[durations_mins <= 90])**2,
                                -0.310 - 0.0544 * log10(durations_mins[(durations_mins <=720) & (90 < durations_mins)]) + 0.0288 * log10(durations_mins[(durations_mins <=720) & (90 < durations_mins)])**2,
                                 0.118 - 0.266 * log10(durations_mins[durations_mins > 720]) + 0.0586 * log10(durations_mins[durations_mins > 720])**2])

                    
    if season == 'winter':
        
        mu = concatenate([1.07 * 1.02 * (4.883 - 5.587 * log10(durations_mins[durations_mins <= 720]) + 3.526 * log10(durations_mins[durations_mins <= 720])**2),
                          (0.670 - 0.0426 * log(durations[durations_mins > 720]))**(-1/0.193)])
        
        disp = concatenate([0.41692 - 0.07583 * log10(durations_mins[durations_mins < 91]),
                            durations_mins[(durations_mins <=720) & (91 <= durations_mins)] * 0 + 0.2684,
                            durations_mins[durations_mins > 720] * 0 + 0.234])
        
        Zeta = concatenate([-0.294 + 0.1474 * log10(durations_mins[durations_mins <= 720]) - 0.0192 * log10(durations_mins[durations_mins <= 720])**2,
                            -0.090 + 0.017 * durations[durations_mins > 720] / 24])
                            
    sigma = mu * disp
        
    return mu, sigma, Zeta                                  

#%% functies voor het berekenen van herhalingstijd, volume en klimaat-factoren
def vols_2019_huidig(pars_default,pars_high,rp,prob,durations,season):   
    if season == 'jaarrond':
        vols = concatenate([concatenate([GLOINVERSE(pars_default[0][array(durations) <= 12],
                                  pars_default[1][array(durations) <= 12],
                                  pars_default[2][array(durations) <= 12],
                                  prob[array(rp) <= 120]),
                           GEVINVERSE(pars_default[0][array(durations) > 12],
                                  pars_default[1][array(durations) > 12],
                                  -pars_default[2][array(durations) > 12],
                                  prob[array(rp) <= 120])],axis=1),
                            concatenate([GLOINVERSE(pars_high[0][array(durations) <= 12],
                                  pars_high[1][array(durations) <= 12],
                                  pars_high[2][array(durations) <= 12],
                                  prob[array(rp) > 120]),
                           GEVINVERSE(pars_high[0][array(durations) > 12],
                                  pars_high[1][array(durations) > 12],
                                  -pars_high[2][array(durations) > 12],
                                  prob[array(rp) > 120])],axis=1)])
    elif season == 'winter':
        vols = GEVINVERSE(pars_default[0], pars_default[1], -pars_default[2], prob)
        
    return vols


def rp_2019_huidig(pars_default,pars_high,vols,durations,season):   
    if season == 'jaarrond':
        res = {'low_rp':concatenate([GLOCDF(pars_default[0][array(durations) <= 12],
                                  pars_default[1][array(durations) <= 12],
                                  pars_default[2][array(durations) <= 12],
                                  vols),
                            GEVCDF(pars_default[0][array(durations) > 12],
                                  pars_default[1][array(durations) > 12],
                                  -pars_default[2][array(durations) > 12],
                                  vols)],axis=1),
               'high_rp':concatenate([GLOCDF(pars_high[0][array(durations) <= 12],
                                  pars_high[1][array(durations) <= 12],
                                  pars_high[2][array(durations) <= 12],
                                  vols),
                            GEVCDF(pars_high[0][array(durations) > 12],
                                  pars_high[1][array(durations) > 12],
                                  -pars_high[2][array(durations) > 12],
                                  vols)],axis=1)
               }
        
        res = where(res['low_rp'] <= 120, res['low_rp'], res['high_rp'])

    elif season == 'winter':
        res =  GEVCDF(pars_default[0], pars_default[1], -pars_default[2], vols)

    return 1/-log(res)
    
def factors_2019(durations,prob,season,climate,scenario):  
    if season == 'jaarrond':
        durs = {'STOWA2015':array(durations)[array(durations) >= 24],
                    'kort':array(durations)[array(durations) <= 2],
                    'midden': array([duration for duration in durations 
                                     if duration > 2 and duration < 24])}
        
        xp = [durs['kort'][-1],durs['STOWA2015'][0]]
        
        pars = {'huidig':get_2015_GEV_params('2014','-',season,'-',durs['STOWA2015']),
                    'scenario':get_2015_GEV_params(climate,scenario,season,region,durs['STOWA2015'])}
        
        factors = {'STOWA2015':
                   GEVINVERSE(pars['scenario'][0], pars['scenario'][1], pars['scenario'][2], prob)/
                   GEVINVERSE(pars['huidig'][0], pars['huidig'][1], pars['huidig'][2], prob),
                   'kort': parameters['STOWA2019'][int(climate),scenario,region,season][0][0]}
        
        factors['midden'] = array([interp(durs['midden'],xp,fp = [factors['kort'],factor]) for factor in factors['STOWA2015'][:,0]])
            
        return concatenate([array([[factors['kort']] * len(durs['kort'])] * len(prob)),
                            factors['midden'],
                            factors['STOWA2015']],
                           axis=1)
    
    if season == 'winter':
        durs = {'STOWA2015':array(durations)[array(durations) >= 2],
            'kort':array(durations)[array(durations) < 2]}
        
        pars = {'huidig':get_2015_GEV_params('2014','-',season,'-',durs['STOWA2015']),
                'scenario':get_2015_GEV_params(climate,scenario,season,region,durs['STOWA2015'])}
        
        factors = GEVINVERSE(pars['scenario'][0], pars['scenario'][1], pars['scenario'][2], prob)/GEVINVERSE(pars['huidig'][0], pars['huidig'][1], pars['huidig'][2], prob)

        return concatenate([array([list(factors[:,0])] * len(durs['kort'])).T,
                               factors],
                              axis=1)


def poly(D):
    return 5.952e-06 * D**2 - 1.515e-03 * D + 1.277

def logpoly(D):
    return 0.009143 * np.log(D)**2 - 0.1508 * np.log(D) + 1.621

def calculate_verandergetal(D, Ts, v_values):
    # Apply the appropriate v-value based on duration D using NumPy's piecewise logic
    conditions = [D <= 24, (D > 24) & (D < 120), (D >= 120) & (D <= 240)]
    v = np.select(conditions, v_values, default=np.nan)
    return 1 + (v - 1) * Ts / 4

def verandergetalfunctie_jaarrond(Ts, D):
    v_values = [1.234, logpoly(D), 1.109]
    return calculate_verandergetal(D, Ts, v_values)

def verandergetalfunctie_winter(Ts, D):
    v_values = [1.244, poly(D), 1.181]
    return calculate_verandergetal(D, Ts, v_values)

def get_verander_getal(climate, scenario, season, duur_uren):
    temperature_increases = {
        ('2033', 'L'): 0.6,
        ('2050', 'L'): 0.8,
        ('2050', 'M'): 1.1,
        ('2050', 'H'): 1.5,
        ('2100', 'L'): 0.8,
        ('2100', 'M'): 1.9,
        ('2100', 'H'): 4.0,
        ('2150', 'L'): 0.8,
        ('2150', 'M'): 2.1,
        ('2150', 'H'): 5.5
    }
    
    if climate == '2024':
        return np.ones_like(duur_uren)  # Return array of 1's if the year is 2024

    key = (climate, scenario)
    if key in temperature_increases:
        temp_increase = temperature_increases[key]
        if season == 'winter':
            return [verandergetalfunctie_winter(temp_increase, duur_uren[id]) for id in range(len(duur_uren))]
        else:
            return [verandergetalfunctie_jaarrond(temp_increase, duur_uren[id]) for id in range(len(duur_uren))]
    else:
        return np.zeros_like(duur_uren)  # Return array of 0's for non-existing scenarios


def vols_2024(rp,durations,season,climate,scenario):
    prob = exp(-1/array(rp))
    
    # in de publicatie van 2024 is de statistiek van het huidige klimaat niet veranderd tov de publicatie van 2019
    # de statistiek van de zichtjaren echter wel, en dit komt tot uiting via het zgn. verandergetal, een multiplier op het overschrijdingsvolume
    vols = vols_2019_huidig(get_2019_params(durations,season),
                             get_2019_params(durations,season,return_period=121),
                             rp,
                             prob,
                             durations,
                             season)
    
    # als het zichtjaar niet 2024 is, dan klimaatverandering toepassen
    if not climate == '2024':
        vols = vols * get_verander_getal(climate,scenario,season,durations)  
        
    return vols

def rp_2024(vols,durations,season,climate,scenario,verandergetalIdx=0):
    verandergetal = get_verander_getal(climate, scenario, season, durations)
    
    #schaal eerst het volume terug naar zijn equivalent onder huidig klimaat door te delen door het verandergetal
    for id in range(len(vols)):
        vols[id] = vols[id] / verandergetal[verandergetalIdx]   

    #nu we het volume hebben teruggeschaald naar de equivalent voor scenario 'Huidig'
    #kunnen we eenvoudigweg de functie rp_2019_huidig aanroepen
    rp = rp_2019_huidig(get_2019_params(durations,season),
                         get_2019_params(durations,season,return_period=121),
                         vols,
                         durations,
                         season)
                         
    return rp


def vols_2019(rp,durations,season,climate,scenario):
    prob = exp(-1/array(rp))
    
    vols = vols_2019_huidig(get_2019_params(durations,season),
                             get_2019_params(durations,season,return_period=121),
                             rp,
                             prob,
                             durations,
                             season)
    
    # als het zichtjaar niet 2024 is, dan klimaatverandering toepassen
    if not climate == '2024':
        vols = vols * factors_2019(durations,prob,season,climate,scenario)
        
    return vols

def rp_2019(vols,durations,season,climate,scenario,debug=False):
    
    # rp = rp_2019_huidig(get_2019_params(durations,season),
    #                     get_2019_params(durations,season,return_period=121),
    #                     vols,
    #                     durations,
    #                     season)

    # rp[isnan(rp)] = 0.001
    
    rp = ones((len(vols),len(durations)))

    #als het niet de huidige situatie is, iteratief zoeken naar de juiste herhalingstijd
    #if not climate == '2024':
    iters = 0
    optimize = True
    vols = array([vols]  * len(rp[0])).T
    
    while optimize:
        v_estimate = array([(vols_2019_huidig(get_2019_params(durations,season),
                             get_2019_params(durations,season,return_period=121),
                             rp[:,idx],
                             exp(-1/array(rp[:,idx])),
                             durations,
                             season) * factors_2019(durations,
                                                    exp(-1/array(rp[:,idx])),
                                                    season,
                                                    climate,
                                                    scenario))[:,idx] for idx in list(range(0,len(durations)))]).T
        
        v_estimate[isnan(v_estimate)] = vols[isnan(v_estimate)]
        rp[isnan(rp)] = accuracy
        
        rp = rp * vols / v_estimate
        iters += 1
        if abs(v_estimate - vols).max() < 0.001 or iters == max_iters:
            optimize = False
    
    if debug:
        print('finished in {} iterations'.format(iters))
        print(abs(v_estimate - vols))
                
    return rp

def test(durations=[1/6, 1/2, 1, 2, 4, 8, 12, 24, 48, 96, 192, 240],
         rp=[0.5, 1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000],
         volumes=[10,20,30,40,50,75,100,150],
         climate='2024',
         season='jaarrond',
         scenario='-'
         ):
    import numpy
    numpy.set_printoptions(suppress=True)
    
    print('testen volume-tabel:')
    vols = vols_2024(rp,durations,season,climate,scenario)
    savetxt('vols_{}_{}_{}.csv.'.format(climate,season,scenario), concatenate([array([rp]).T,vols], axis = 1), delimiter=',', fmt='%.1f', header="herhalingstijden," + ",".join([str(dur) for dur in durations]))
    print(vols.round(1))
    
    print('terug-rekenen herhalingstijden:')
    return_periods = array([rp_2024(vols[:,idx],durations,season,climate,scenario, verandergetalIdx=idx)[:,idx] for idx in range(vols.shape[1])])
    print(return_periods.round(1))
    savetxt('rp_reverse_{}_{}_{}.csv.'.format(climate,season,scenario), concatenate([array([rp]).T,return_periods.T], axis = 1), delimiter=',', fmt='%.1f', header="herhalingstijden," + ",".join([str(dur) for dur in durations]))

    print('testen herhalingstijden-tabel:')
    return_periods = ones((len(volumes),len(durations)))
    for idx in range(len(durations)):
        vs = volumes.copy()
        return_periods[:,idx] = rp_2024(vs,durations,season,climate,scenario,verandergetalIdx=idx)[:,idx]
    print(return_periods.round(1))
    savetxt('rp_{}_{}_{}.csv.'.format(climate,season,scenario), 
            concatenate([array([durations]).T,return_periods.T], axis = 1), 
            delimiter=',', fmt='%.1f', header="volumes," + ",".join([str(vol) for vol in volumes]))

#%% app routes, functies die worden aangestuurd vanuit de front-end
@bp.route('/volume/STOWA2019', methods=('POST', ))
def volume():
    """ return the Google Data Table in JSON from a html form set of parameters """
    # get params from html form
    climate = request.form['climate']
    try: scenario = request.form['scenario']
    except: scenario = '-'
    season = request.form['season']
    #%%process volumes array
    try: vol = request.form['value']
    except: vol = ''

    vols = volumes.copy()
    if not vol == '': vols = vols + [float(vol)]
    vols.sort()
        
    y_labels = []
    for volume in vols:
        y_labels.append('{} mm'.format(int(volume)))
 
    #bereken de herhalingstijd alsof het de huidige situatie is 
    durations = duren['regenduurlijnen'].copy()

    rp = ones((len(vols),len(durations)))
    for idx in range(len(durations)):
        vs = vols.copy()
        rp[:,idx] = rp_2024(vs,durations,season,climate,scenario,verandergetalIdx=idx)[:,idx]
                                    
    result = xy_series(array(durations).round(decimals=2), rp, x_label="duur (uren)",
                       y_labels=y_labels,decimals=1).toGDT(min_val=herhalingstijden[0],max_val=herhalingstijden[-1])
#%%      
    return dumps(result), 200

@bp.route('/returnperiod/STOWA2019', methods=('POST', ))
def returnperiod():
    """ return the Google Data Table in JSON from a html form set of parameters """
    
    # haal de parameters op uit het html formulier
    climate = request.form['climate']
    try: scenario = request.form['scenario'] 
    except: scenario = '-'
    season = request.form['season']
    try: return_period = request.form['value']
    except: return_period = ''

    #het processen van de duren naar kansen (prob)
    rp = herhalingstijden.copy()
    if not return_period == '': rp = rp + [float(return_period)]
    rp.sort()
    
    y_labels = ['{} jr'.format(int(return_period)) for return_period in rp]
    y_labels[0] = '0.5 jr'
        
    # berekenen van volumes
    durations = duren['regenduurlijnen'].copy()

    vols = vols_2024(rp,durations,season,climate,scenario)
            
    result = xy_series(array(durations).round(decimals=2), vols, x_label="duur (uren)",
                           y_labels=y_labels,decimals=0).toGDT()  
        
    return dumps(result), 200

@bp.route('/area', methods=('POST', ))
def area():
    """ return the Google Data Table in JSON from a html form set of parameters """
    area = request.form['area']
    try:
        int(area)
        result = AREA(area)
        print("Name:", area)        
        reductions = array([list(res.values())[1:] for res in result])
        y_labels = ['{} jr'.format(res['T_series_Langbein']) for res in result]
        
        result = xy_series(array(duren['oppervlaktereductie']),reductions, x_label="duur (uren)", y_labels=y_labels,decimals=3).toGDT()
        
        return dumps(result), 200
    
    except ValueError:
        return "Invalid input for parameter 'area'", 422
