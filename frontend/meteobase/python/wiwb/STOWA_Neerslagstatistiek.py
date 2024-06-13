import math
from .Statistische_Functies import GEVINVERSE, GEVCDF, GLOCDF, GLOINVERSE, Interpolate

def STOWA2015_2018_WINTER_T(DuurMinuten, Zichtjaar, Scenario, Corner, Volume):
    """
    Deze functie berekent het winterneerslagvolume conform STOWA 2015/2018 met gegeven duur in minuten en volume in mm.
    We berekenen hem iteratief door gebruik te maken van de functie STOWA2015_2018_NDJF_V.
    """
    # Initialiseer de herhalingstijd simpelweg op T=1. Dit lijkt goed uit te pakken voor alle volumes
    T_estimate = 1.0
    Done = False
    iIter = 0

    while not Done:
        # Nu gaan we de geschatte herhalingstijd invoeren in de functie met actuele statistiek
        V_estimate = STOWA2015_2018_NDJF_V(DuurMinuten, T_estimate, Zichtjaar, Scenario, Corner)
        iIter += 1
        if iIter == 1000 or abs(V_estimate - Volume) < 0.001:
            Done = True
        else:
            T_estimate = T_estimate * Volume / V_estimate  # Pas de geschatte herhalingstijd aan naar rato van de afwijking tussen geschat en opgegeven volume
    
    return T_estimate

def STOWA2015_2018_NDJF_V(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de herhalingstijd voor winter-neerslagstatistiek conform STOWA, 2015/2018 met gegeven Herhalingstijd en duur in minuten
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input
    """
    P = math.exp(-1 / T)

    if DuurMinuten > 720:
        # Voor lange duren is de statistiek van alle klimaatscenario's beschikbaar
        dispcoef = GEVDispCoefBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner)
        locpar = GEVLocParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario)
        return GEVINVERSE(locpar, scalepar, shapepar, P)
    else:
        # Voor korte duren is alleen de statistiek van huidig klimaat gepubliceerd.
        # Volgens auteur Rudolf Versteeg mag de verhouding klimaat/huidig voor het winterseizoen uit de publicatie van 2015 worden toegepast om toch het klimaateffect te berekenen
        dispcoef = GEVDispCoefBasisstatistiek2018WinterKort(DuurMinuten)
        locpar = GEVLocParBasisstatistiek2018WinterKort(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisStatistiek2018WinterKort(DuurMinuten)
        
        # Pas nu de klimaatverandering toe, indien vereist
        if Zichtjaar == 2014:
            Multiplier = 1
        else:
            Multiplier = STOWA2015_WINTER_V(DuurMinuten, T, Zichtjaar, Scenario, Corner) / STOWA2015_WINTER_V(DuurMinuten, T, 2014, "", "")
        
        return GEVINVERSE(locpar, scalepar, shapepar, P) * 1.02 * Multiplier


def STOWA2015_WINTER_V(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de herhalingstijd voor winter-neerslagstatistiek conform STOWA, 2015/2018 met gegeven Herhalingstijd en duur in minuten
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input
    """
    P = math.exp(-1 / T)
    
    if DuurMinuten < 120:
        DuurMinuten = 120  # zoals overeengekomen met Rudolf Versteeg (auteur rapport STOWA) ten behoeve van klimaateffect bij korte duren
    
    dispcoef = GEVDispCoefBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner)
    locpar = GEVLocParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner)
    scalepar = dispcoef * locpar
    shapepar = GEVShapeParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario)
    
    return GEVINVERSE(locpar, scalepar, shapepar, P)


def GEVDispCoefBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de dispersiecoefficient voor de GEV-kansverdeling voor lange duur (>= 2 uur) conform STOWA 2015
    """
    if Zichtjaar == 2014:
        return 0.234
    elif Zichtjaar == 2030:
        if Corner == "lower":
            return 0.23
        elif Corner == "center":
            return 0.233
        elif Corner == "upper":
            return 0.236
    elif Zichtjaar == 2050:
        if Scenario == "GL":
            if Corner == "lower":
                return 0.234
            elif Corner == "center":
                return 0.236
            elif Corner == "upper":
                return 0.239
        elif Scenario == "GH":
            if Corner == "lower":
                return 0.232
            elif Corner == "center":
                return 0.235
            elif Corner == "upper":
                return 0.237
        elif Scenario == "WL":
            if Corner == "lower":
                return 0.235
            elif Corner == "center":
                return 0.241
            elif Corner == "upper":
                return 0.247
        elif Scenario == "WH":
            if Corner == "lower":
                return 0.227
            elif Corner == "center":
                return 0.233
            elif Corner == "upper":
                return 0.239
    elif Zichtjaar == 2085:
        if Scenario == "GL":
            if Corner == "lower":
                return 0.229
            elif Corner == "center":
                return 0.233
            elif Corner == "upper":
                return 0.237
        elif Scenario == "GH":
            if Corner == "lower":
                return 0.228
            elif Corner == "center":
                return 0.232
            elif Corner == "upper":
                return 0.236
        elif Scenario == "WL":
            if Corner == "lower":
                return 0.236
            elif Corner == "center":
                return 0.246
            elif Corner == "upper":
                return 0.255
        elif Scenario == "WH":
            if Corner == "lower":
                return 0.226
            elif Corner == "center":
                return 0.236
            elif Corner == "upper":
                return 0.245
    return None


def GEVLocParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de locatieparameter voor de GEV-kansverdeling voor lange duur, winterseizoen conform STOWA 2015
    """
    if Zichtjaar == 2014:
        # Op aanwizjen van Rudolf de extra decimaal toegevoegd in -0.193
        return (0.67 - 0.0426 * math.log(DuurMinuten / 60)) ** (1 / -0.193)
    elif Zichtjaar == 2030:
        if Corner == "lower":
            return (0.667 - 0.0435 * math.log(DuurMinuten / 60)) ** (1 / -0.197)
        elif Corner == "center":
            return (0.665 - 0.043 * math.log(DuurMinuten / 60)) ** (1 / -0.196)
        elif Corner == "upper":
            return (0.666 - 0.0425 * math.log(DuurMinuten / 60)) ** (1 / -0.194)
    elif Zichtjaar == 2050:
        if Scenario == "GL":
            if Corner == "lower":
                return (0.668 - 0.0431 * math.log(DuurMinuten / 60)) ** (1 / -0.196)
            elif Corner == "center":
                return (0.668 - 0.0426 * math.log(DuurMinuten / 60)) ** (1 / -0.194)
            elif Corner == "upper":
                return (0.667 - 0.0422 * math.log(DuurMinuten / 60)) ** (1 / -0.193)
        elif Scenario == "GH":
            if Corner == "lower":
                return (0.661 - 0.0437 * math.log(DuurMinuten / 60)) ** (1 / -0.2)
            elif Corner == "center":
                return (0.661 - 0.0432 * math.log(DuurMinuten / 60)) ** (1 / -0.198)
            elif Corner == "upper":
                return (0.66 - 0.0426 * math.log(DuurMinuten / 60)) ** (1 / -0.196)
        elif Scenario == "WL":
            if Corner == "lower":
                return (0.671 - 0.0421 * math.log(DuurMinuten / 60)) ** (1 / -0.19)
            elif Corner == "center":
                return (0.672 - 0.0411 * math.log(DuurMinuten / 60)) ** (1 / -0.186)
            elif Corner == "upper":
                return (0.671 - 0.0402 * math.log(DuurMinuten / 60)) ** (1 / -0.183)
        elif Scenario == "WH":
            if Corner == "lower":
                return (0.662 - 0.0431 * math.log(DuurMinuten / 60)) ** (1 / -0.196)
            elif Corner == "center":
                return (0.66 - 0.0422 * math.log(DuurMinuten / 60)) ** (1 / -0.193)
            elif Corner == "upper":
                return (0.661 - 0.0412 * math.log(DuurMinuten / 60)) ** (1 / -0.189)
    elif Zichtjaar == 2085:
        if Scenario == "GL":
            if Corner == "lower":
                return (0.667 - 0.0429 * math.log(DuurMinuten / 60)) ** (1 / -0.195)
            elif Corner == "center":
                return (0.666 - 0.0423 * math.log(DuurMinuten / 60)) ** (1 / -0.193)
            elif Corner == "upper":
                return (0.667 - 0.0416 * math.log(DuurMinuten / 60)) ** (1 / -0.19)
        elif Scenario == "GH":
            if Corner == "lower":
                return (0.651 - 0.0437 * math.log(DuurMinuten / 60)) ** (1 / -0.205)
            elif Corner == "center":
                return (0.651 - 0.043 * math.log(DuurMinuten / 60)) ** (1 / -0.202)
            elif Corner == "upper":
                return (0.65 - 0.0423 * math.log(DuurMinuten / 60)) ** (1 / -0.2)
        elif Scenario == "WL":
            if Corner == "lower":
                return (0.675 - 0.0417 * math.log(DuurMinuten / 60)) ** (1 / -0.185)
            elif Corner == "center":
                return (0.674 - 0.0403 * math.log(DuurMinuten / 60)) ** (1 / -0.18)
            elif Corner == "upper":
                return (0.675 - 0.0389 * math.log(DuurMinuten / 60)) ** (1 / -0.175)
        elif Scenario == "WH":
            if Corner == "lower":
                return (0.653 - 0.043 * math.log(DuurMinuten / 60)) ** (1 / -0.198)
            elif Corner == "center":
                return (0.651 - 0.0415 * math.log(DuurMinuten / 60)) ** (1 / -0.193)
            elif Corner == "upper":
                return (0.647 - 0.0402 * math.log(DuurMinuten / 60)) ** (1 / -0.19)
    return None

def GEVShapeParBasisstatistiek2015WinterLang(DuurMinuten, Zichtjaar, Scenario):
    """
    Deze functie berekent de vormparameter voor de GEV-kansverdeling voor lange duur conform STOWA 2015
    """
    if DuurMinuten / 60 <= 240:
        return -0.09 + 0.017 * (DuurMinuten / 60 / 24)
    else:
        return -0.09 + 0.683 * math.log(DuurMinuten / 60 / 24)



def GEVDispCoefBasisstatistiek2018WinterKort(DuurMinuten):
    """
    Deze functie berekent de dispersiecoefficient voor de GEV-kansverdeling voor korte duur conform STOWA 2018
    """
    if DuurMinuten <= 91:
        return 0.41692 - 0.07583 * math.log10(float(DuurMinuten))
    else:
        return 0.2684


def GEVLocParBasisstatistiek2018WinterKort(DuurMinuten):
    """
    Deze functie berekent de locatieparameter voor de GEV-kansverdeling voor korte duur, winterseizoen conform STOWA 2018
    """
    return 4.883 - 5.587 * math.log10(float(DuurMinuten)) + 3.526 * math.log10(float(DuurMinuten)) ** 2


def GEVShapeParBasisStatistiek2018WinterKort(DuurMinuten):
    """
    Deze functie berekent de vormparameter voor de GEV-kansverdeling voor korte duur, winterseizoen conform STOWA 2018
    """
    return -0.294 + 0.1474 * math.log10(float(DuurMinuten)) - 0.0192 * math.log10(float(DuurMinuten)) ** 2


def GEVShapeParBasisStatistiek2018ZomerKort(DuurMinuten):
    """
    Deze functie berekent de vormparameter voor de GEV-kansverdeling voor korte duur, zomerseizoen conform STOWA 2018
    """
    return -0.0336 - 0.264 * math.log10(float(DuurMinuten)) + 0.0636 * math.log10(float(DuurMinuten)) ** 2

def STOWA2019_NDJF_T(DuurMinuten, Volume, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent het winterneerslagvolume conform STOWA, 2019 met gegeven duur in minuten en volume in mm.
    We berekenen hem iteratief door gebruik te maken van de functie STOWA_JAARROND_V.
    """
    #print(f"Estimating return period for duration {DuurMinuten}, zichtjaar {Zichtjaar}, scenario {Scenario}, corner {Corner} en volume {Volume}")
    
    # Initialiseer de herhalingstijd op basis van de oude statistiek. We weten dat deze herhalingstijd een overschatting geeft
    T_estimate = STOWA2015_2018_WINTER_T(DuurMinuten, 2014, "", "", Volume)
    
    #print(f"T_estimate is {T_estimate}")

    Done = False
    iIter = 0
    
    while not Done:
        # Nu gaan we de geschatte herhalingstijd invoeren in de functie met actuele statistiek
        V_estimate = STOWA2019_NDJF_V(DuurMinuten, T_estimate, Zichtjaar, Scenario, Corner)

        #print(f"V_Estimate in iteration {iIter} is {V_estimate}. Volume is {Volume}")

        iIter += 1
        if iIter == 1000:
            Done = True
        elif V_estimate ==0:
            return T_estimate
        if abs(V_estimate - Volume) < 0.001:
            Done = True
        T_estimate = T_estimate * Volume / V_estimate  # Pas de geschatte herhalingstijd aan naar rato van de afwijking tussen geschat en opgegeven volume
    
    return T_estimate

def STOWA2024_NDJF_V(DuurMinuten, T, Zichtjaar, Scenario):
    """
    Deze functie berekent de herhalingstijd voor winter-neerslagstatistiek conform STOWA, 2024 met gegeven Herhalingstijd en duur in minuten.
    In de STOWA2024-scenario's zijn géén aanpassingen gedaan aan de kansverdelingsparameters.
    Ook is het scenario 'Huidig' ongemoeid gelaten. De volumes voor de diverse zichtjaren komen tot stand via een multiplier die een functie is van de verwachte temperatuursstijging.
    Voor huidig klimaat zijn de statistieken voor 2024 identiek aan die uit 2019.
    Let op: voor 'Huidig' hanteren we intern nog altijd het jaartal 2014.
    """
    result = STOWA2019_NDJF_V(DuurMinuten, T, 2014, "", "")
    
    VeranderGetal = getVeranderGetal(Zichtjaar, Scenario, "winter", DuurMinuten / 60)
    
    # Het volume is nu eenvoudigweg het verandergetal als multiplier op het volume van huidig 2019.
    return VeranderGetal * result


def STOWA2024_NDJF_T(DuurMinuten, Volume, Zichtjaar, Scenario):
    """
    In de 2024-scenario's zijn de zichtjaren eenvoudigweg multipliers op de volumes van 2019_HUIDIG.
    Scenario Huidig is in de 2024-scenario's identiek aan die van 2019.
    Daarom kunnen we vrij eenvoudig terugrekenen als we eerst ons volume terugschalen naar scenario Huidig.
    """
    VeranderGetal = getVeranderGetal(Zichtjaar, Scenario, "winter", DuurMinuten / 60)
    
    #print(f"Verandergetal is {VeranderGetal}")
    #print(f"Volume is {Volume}")

    # Corrigeer eerst het volume door te delen door het verandergetal    
    if VeranderGetal != 0:        
        Volume /= VeranderGetal

    #print(f"Gecorrigeerd volume is {Volume}")

    # Nu we het volume hebben teruggeschaald naar de equivalent voor scenario 'Huidig'
    # Kunnen we eenvoudigweg de functie STOWA2019_NDJF_T aanroepen, met scenario 'Huidig' als referentie
    return STOWA2019_NDJF_T(DuurMinuten, Volume, 2014, "", "")

def STOWA2024_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario):
    """
    Deze functie berekent de herhalingstijd voor winter-neerslagstatistiek conform STOWA, 2024 met gegeven Herhalingstijd en duur in minuten.
    In de STOWA2024-scenario's zijn géén aanpassingen gedaan aan de kansverdelingsparameters.
    Ook is het scenario 'Huidig' ongemoeid gelaten. De volumes voor de diverse zichtjaren komen tot stand via een multiplier die een functie is van de verwachte temperatuursstijging.
    Voor huidig klimaat zijn de statistieken voor 2024 identiek aan die uit 2019.
    Let op: voor 'Huidig' hanteren we intern nog altijd het jaartal 2014.
    """
    result = STOWA2019_JAARROND_V(DuurMinuten, T, 2014, "", "")
    
    VeranderGetal = getVeranderGetal(Zichtjaar, Scenario, "jaarrond", DuurMinuten / 60)
    
    # Het volume is nu eenvoudigweg het verandergetal als multiplier op het volume van huidig 2019.
    return VeranderGetal * result

def STOWA2024_JAARROND_T(DuurMinuten, Volume, Zichtjaar, Scenario, Debugging=False):
    """
    In de 2024-scenario's zijn de zichtjaren eenvoudigweg multipliers op de volumes van 2019_HUIDIG.
    Scenario Huidig is in de 2024-scenario's identiek aan die van 2019.
    Daarom kunnen we vrij eenvoudig terugrekenen als we eerst ons volume terugschalen naar scenario Huidig.
    """
    VeranderGetal = getVeranderGetal(Zichtjaar, Scenario, "jaarrond", DuurMinuten / 60)
    
    # Corrigeer eerst het volume door te delen door het verandergetal    
    if VeranderGetal != 0:        
        Volume /= VeranderGetal
    
    # Nu we het volume hebben teruggeschaald naar de equivalent voor scenario 'Huidig'
    # Kunnen we eenvoudigweg de functie STOWA2019_JAARROND_T aanroepen, met scenario 'Huidig' als referentie
    return STOWA2019_JAARROND_T(DuurMinuten, Volume, 2014, "", "", Debugging)


def STOWA2019_JAARROND_T(DuurMinuten, Volume, Zichtjaar, Scenario, Corner, Debugging=False):
    """
    Deze functie berekent het jaarrond neerslagvolume conform STOWA, 2019 met gegeven duur in minuten en volume in mm.
    We berekenen hem iteratief door gebruik te maken van de functie STOWA_JAARROND_V.
    """
    # Initialiseer de herhalingstijd
    T_estimate = 1

    #print(f"Iterating towards return period for duration {DuurMinuten}, volume {Volume} and zichtjaar {Zichtjaar}")
    
    Done = False
    iIter = 0
    
    while not Done:
        #print(f"Iteratie met duur {DuurMinuten}, geschatte Herhalingstijd {T_estimate}, Zichtjaar {Zichtjaar}, Scenario {Scenario} en corner {Corner}")

        # Nu gaan we de geschatte herhalingstijd invoeren in de functie met actuele statistiek
        V_estimate = STOWA2019_JAARROND_V(DuurMinuten, T_estimate, Zichtjaar, Scenario, Corner, Debugging)
        iIter += 1
        if iIter == 1000:
            Done = True
        elif V_estimate == 0:
            return T_estimate
        if abs(V_estimate - Volume) < 0.001:
            Done = True
        T_estimate = T_estimate * Volume / V_estimate
    
    return T_estimate

def getVeranderGetal(Zichtjaar, Scenario, Seizoen, DuurUren):
    """
    Deze functie berekent het verandergetal voor de klimaatscenario's 2024 als functie van zichtjaar, scenario en duur.
    Op zijn beurt roept deze functie weer de functie VeranderGetalFunctie aan, waarin hij de verwachtte temperatuursstijging meegeeft, die afhangt van het zichtjaar en scenario.
    """
    if Zichtjaar == 2014:
        # geen verandering
        return 1
    elif Zichtjaar == 2033:
        if Scenario == "L":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(0.6, DuurUren)
            else:
                return verandergetalfunctieJaarrond(0.6, DuurUren)
        else:
            # is een niet-bestaand scenario
            return 1
    elif Zichtjaar == 2050:
        if Scenario == "L":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(0.8, DuurUren)
            else:
                return verandergetalfunctieJaarrond(0.8, DuurUren)
        elif Scenario == "M":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(1.1, DuurUren)
            else:
                return verandergetalfunctieJaarrond(1.1, DuurUren)
        elif Scenario == "H":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(1.5, DuurUren)
            else:
                return verandergetalfunctieJaarrond(1.5, DuurUren)
        else:
            # is een niet-bestaand scenario
            return 1
    elif Zichtjaar == 2100:
        if Scenario == "L":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(0.8, DuurUren)
            else:
                return verandergetalfunctieJaarrond(0.8, DuurUren)
        elif Scenario == "M":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(1.9, DuurUren)
            else:
                return verandergetalfunctieJaarrond(1.9, DuurUren)
        elif Scenario == "H":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(4, DuurUren)
            else:
                return verandergetalfunctieJaarrond(4, DuurUren)
        else:
            # is een niet-bestaand scenario
            return 1
    elif Zichtjaar == 2150:
        if Scenario == "L":
            # is identiek aan 2050L en 2100L
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(0.8, DuurUren)
            else:
                return verandergetalfunctieJaarrond(0.8, DuurUren)
        elif Scenario == "M":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(2.1, DuurUren)
            else:
                return verandergetalfunctieJaarrond(2.1, DuurUren)
        elif Scenario == "H":
            if Seizoen == "winter":
                return VeranderGetalFunctieWinter(5.5, DuurUren)
            else:
                return verandergetalfunctieJaarrond(5.5, DuurUren)
        else:
            # is een niet-bestaand scenario
            return 1
    else:
        # is een niet-bestaand zichtjaar
        return 1

def VeranderGetalFunctieWinter(Ts, D, T=1):
    """
    Deze functie berekent het verandergetal wat nodig is voor de klimaatscenario's van 2024.
    Ts: temperatuurstijging in graden Celsius
    D: duur in uren
    T: terugkeertijd (irrelevante parameter)
    """
    if D < 1 / 6:
        raise ValueError(f"Gekozen duur {D} valt buiten domein (10 minuten t/m 240 uur)")
    elif D <= 24:
        v = 1.244
    elif D < 120:
        v = Poly(D)
    elif D <= 240:
        v = 1.181
    elif D > 240:
        raise ValueError(f"Gekozen duur {D} valt buiten domein: 10 minuten t/m 10 dagen (240 uur)")

    # De factor v is afgeleid voor 4 graden temperatuurstijging t.o.v. 2005,
    # maar in 2023 hebben we al 0.4 graden gehad (0.6 graden in 2033, ~0.4 in 2023)
    return 1 + (v - 1) * (Ts - 0.4) / (4 - 0.4)


def verandergetalfunctieJaarrond(Ts, D, T=1):
    """
    Ts: temperatuurstijging in graden Celsius
    D: duur in uren
    T: terugkeertijd (irrelevante parameter)
    """
    if D < 1 / 6:
        raise ValueError(f"Gekozen duur {D} valt buiten domein (10 minuten t/m 240 uur)")
    elif D <= 24:
        v = 1.234
    elif D < 120:
        v = LogPoly(D)  # Assuming LogPoly is a function you have elsewhere
    elif D <= 240:
        v = 1.109
    elif D > 240:
        raise ValueError(f"Gekozen duur {D} valt buiten domein: 10 minuten t/m 10 dagen (240 uur)")

    # De factor v is afgeleid voor 4 graden temperatuurstijging t.o.v. 2005,
    # maar in 2023 hebben we al 0.4 graden gehad (0.6 graden in 2033, ~0.4 in 2023)
    return 1 + (v - 1) * (Ts - 0.4) / (4 - 0.4)


def Poly(D):
    """
    Dit is een door HKV gefitte polynoom aan de multipliers voor verschillende duren, voor het winterseizoen; publicaties 2024.
    Calculate the polynomial value based on D.
    """
    return 0.000005952 * D ** 2 - 0.001515 * D + 1.277


def LogPoly(D):
    """
    Dit is een door HKV gefitter polynoom aan de multipliers voor verschillende duren, voor jaarrond-neerslagstatistiek; publicaties 2024.
    Logarithmic polynomial calculation as specified.
    """
    logD = math.log(D)
    return 0.009143 * logD ** 2 - 0.1508 * logD + 1.621

import math

def STOWA2019_NDJF_V(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de herhalingstijd voor winter-neerslagstatistiek conform STOWA, 2019 met gegeven Herhalingstijd en duur in minuten.
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input.
    """
    P = math.exp(-1 / T)
    
    if DuurMinuten > 720:
        dispcoef = GEVDispCoefBasisstatistiek2019LangeDuurWinter(DuurMinuten)
        locpar = GEVLocparBasisstatistiek2019LangeDuurWinter(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2019LangeDuurWinter(DuurMinuten)
        Volume = GEVINVERSE(locpar, scalepar, shapepar, P)
    else:
        dispcoef = GEVDispCoefBasisstatistiek2019KorteDuurWinter(DuurMinuten)
        locpar = GEVLocparBasisstatistiek2019KorteDuurWinter(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2019KorteDuurWinter(DuurMinuten)
        Volume = GEVINVERSE(locpar, scalepar, shapepar, P)
    
    # bepaal nu de aanpassingen als gevolg van het onderhavige klimaat
    # let op: voor de winterstatistiek maken we géén onderscheid tussen korte en lange duur bij het berekenen van de klimaatscenario's.
    if Zichtjaar != 2014:
        Multiplier = STOWA2019_MULTIPLIER_WINTER(DuurMinuten, T, Zichtjaar, Scenario, Corner)
    else:
        Multiplier = 1
    
    Volume *= Multiplier
    
    return Volume

def GEVDispCoefBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return 0.234

def GEVLocparBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return (0.67 - 0.0426 * math.log(DuurMinuten / 60)) ** (-1 / 0.193)

def GEVShapeParBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return -0.09 + 0.017 * DuurMinuten / 60 / 24

def GEVDispCoefBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    if DuurMinuten <= 91:
        return 0.41692 - 0.07583 * math.log10(float(DuurMinuten))
    else:
        return 0.2684

def GEVLocparBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return 1.07 * 1.02 * (4.883 - 5.587 * math.log10(float(DuurMinuten)) + 3.526 * (math.log10(float(DuurMinuten))) ** 2)

def GEVShapeParBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return -0.294 + 0.1474 * math.log10(float(DuurMinuten)) - 0.0192 * (math.log10(float(DuurMinuten))) ** 2

def GLOScaleParBasisstatistiek2019KorteDuur(DuurMinuten):
    return GLODispCoefBasisstatistiek2019KorteDuur(DuurMinuten) * GLOLocparBasisstatistiek2019KorteDuur(DuurMinuten)

def GEVScaleParBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return GEVDispCoefBasisstatistiek2019KorteDuurWinter(DuurMinuten) * GEVLocparBasisstatistiek2019KorteDuurWinter(DuurMinuten)

def GLOShapeParBasisstatistiek2019KorteDuur(DuurMinuten, T_estimate):
    if DuurMinuten <= 90 or (DuurMinuten <= 720 and T_estimate <= 120):
        return -0.0336 - 0.264 * math.log10(float(DuurMinuten)) + 0.0636 * (math.log10(float(DuurMinuten))) ** 2
    else:
        return -0.31 - 0.0544 * math.log10(float(DuurMinuten)) + 0.0288 * (math.log10(float(DuurMinuten))) ** 2

import math

def GEVDispCoefBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return 0.234

def GEVLocparBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return (0.67 - 0.0426 * math.log(DuurMinuten / 60)) ** (-1 / 0.193)

def GEVShapeParBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    return -0.09 + 0.017 * DuurMinuten / 60 / 24

def GEVDispCoefBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    if DuurMinuten <= 91:
        return 0.41692 - 0.07583 * math.log10(DuurMinuten)
    else:
        return 0.2684

def GEVLocparBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return 1.07 * 1.02 * (4.883 - 5.587 * math.log10(DuurMinuten) + 3.526 * (math.log10(DuurMinuten)) ** 2)

def GEVShapeParBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return -0.294 + 0.1474 * math.log10(DuurMinuten) - 0.0192 * (math.log10(DuurMinuten)) ** 2

def GLOScaleParBasisstatistiek2019KorteDuur(DuurMinuten):
    return GLODispCoefBasisstatistiek2019KorteDuur(DuurMinuten) * GLOLocparBasisstatistiek2019KorteDuur(DuurMinuten)

def GEVScaleParBasisstatistiek2019KorteDuurWinter(DuurMinuten):
    return GEVDispCoefBasisstatistiek2019KorteDuurWinter(DuurMinuten) * GEVLocparBasisstatistiek2019KorteDuurWinter(DuurMinuten)

def GLOShapeParBasisstatistiek2019KorteDuur(DuurMinuten, T_estimate):
    if DuurMinuten <= 90 or (DuurMinuten <= 720 and T_estimate <= 120):
        return -0.0336 - 0.264 * math.log10(DuurMinuten) + 0.0636 * (math.log10(DuurMinuten)) ** 2
    else:
        return -0.31 - 0.0544 * math.log10(DuurMinuten) + 0.0288 * (math.log10(DuurMinuten)) ** 2

def GLODispCoefBasisstatistiek2019KorteDuur(DuurMinuten):
    # Placeholder function, needs implementation
    pass

def GLOLocparBasisstatistiek2019KorteDuur(DuurMinuten):
    # Placeholder function, needs implementation
    pass

def GEVLocparBasisstatistiek2019LangeDuur(DuurMinuten):
    return 1.02 * (0.239 - 0.025 * math.log(DuurMinuten / 60)) ** (-1 / 0.512)

def GEVLocParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner):
    if Zichtjaar == 2014:
        return (0.239 - 0.025 * math.log(DuurMinuten / 60)) ** (1 / -0.512)
    elif Zichtjaar == 2030:
        if Corner == "lower":
            return (0.246 - 0.0257 * math.log(DuurMinuten / 60)) ** (1 / -0.503)
        elif Corner == "center":
            return (0.24 - 0.025 * math.log(DuurMinuten / 60)) ** (1 / -0.506)
        elif Corner == "upper":
            return (0.235 - 0.0243 * math.log(DuurMinuten / 60)) ** (1 / -0.509)
        else:
            return -999
    elif Zichtjaar == 2050:
        if Scenario == "GL":
            if Corner == "lower":
                return (0.247 - 0.0258 * math.log(DuurMinuten / 60)) ** (1 / -0.501)
            elif Corner == "center":
                return (0.241 - 0.025 * math.log(DuurMinuten / 60)) ** (1 / -0.504)
            elif Corner == "upper":
                return (0.236 - 0.0243 * math.log(DuurMinuten / 60)) ** (1 / -0.506)
            else:
                return -999
        elif Scenario == "GH":
            if Corner == "lower":
                return (0.269 - 0.0272 * math.log(DuurMinuten / 60)) ** (1 / -0.474)
            elif Corner == "center":
                return (0.26 - 0.0263 * math.log(DuurMinuten / 60)) ** (1 / -0.479)
            elif Corner == "upper":
                return (0.252 - 0.0254 * math.log(DuurMinuten / 60)) ** (1 / -0.483)
            else:
                return -999
        elif Scenario == "WL":
            if Corner == "lower":
                return (0.262 - 0.0266 * math.log(DuurMinuten / 60)) ** (1 / -0.48)
            elif Corner == "center":
                return (0.249 - 0.0252 * math.log(DuurMinuten / 60)) ** (1 / -0.485)
            elif Corner == "upper":
                return (0.241 - 0.024 * math.log(DuurMinuten / 60)) ** (1 / -0.486)
            else:
                return -999
        elif Scenario == "WH":
            if Corner == "lower":
                return (0.289 - 0.0287 * math.log(DuurMinuten / 60)) ** (1 / -0.451)
            elif Corner == "center":
                return (0.276 - 0.0271 * math.log(DuurMinuten / 60)) ** (1 / -0.456)
            elif Corner == "upper":
                return (0.265 - 0.0257 * math.log(DuurMinuten / 60)) ** (1 / -0.459)
            else:
                return -999
        else:
            return -999
    elif Zichtjaar == 2085:
        if Scenario == "GL":
            if Corner == "lower":
                return (0.252 - 0.0261 * math.log(DuurMinuten / 60)) ** (1 / -0.494)
            elif Corner == "center":
                return (0.243 - 0.025 * math.log(DuurMinuten / 60)) ** (1 / -0.498)
            elif Corner == "upper":
                return (0.235 - 0.0241 * math.log(DuurMinuten / 60)) ** (1 / -0.501)
            else:
                return -999
        elif Scenario == "GH":
            if Corner == "lower":
                return (0.271 - 0.0274 * math.log(DuurMinuten / 60)) ** (1 / -0.471)
            elif Corner == "center":
                return (0.26 - 0.0262 * math.log(DuurMinuten / 60)) ** (1 / -0.476)
            elif Corner == "upper":
                return (0.25 - 0.0251 * math.log(DuurMinuten / 60)) ** (1 / -0.481)
            else:
                return -999
        elif Scenario == "WL":
            if Corner == "lower":
                return (0.272 - 0.0272 * math.log(DuurMinuten / 60)) ** (1 / -0.464)
            elif Corner == "center":
                return (0.248 - 0.0244 * math.log(DuurMinuten / 60)) ** (1 / -0.475)
            elif Corner == "upper":
                return (0.23 - 0.0223 * math.log(DuurMinuten / 60)) ** (1 / -0.482)
            else:
                return -999
        elif Scenario == "WH":
            if Corner == "lower":
                return (0.286 - 0.0284 * math.log(DuurMinuten / 60)) ** (1 / -0.448)
            elif Corner == "center":
                return (0.262 - 0.0256 * math.log(DuurMinuten / 60)) ** (1 / -0.458)
            elif Corner == "upper":
                return (0.247 - 0.0236 * math.log(DuurMinuten / 60)) ** (1 / -0.461)
            else:
                return -999
        else:
            return -999
    else:
        return -999

def GLODispCoefBasisstatistiek2018(DuurMinuten):
    if DuurMinuten <= 104:
        return 0.04704 + 0.1978 * math.log10(DuurMinuten) - 0.05729 * math.log10(DuurMinuten) ** 2
    else:
        return 0.2801 - 0.0333 * math.log10(DuurMinuten)

def GLOLocParBasisstatistiek2018(DuurMinuten):
    return 7.339 + 0.848 * math.log10(DuurMinuten) + 2.844 * math.log10(DuurMinuten) ** 2

def GLOShapeParBasisStatistiek2018(DuurMinuten):
    return -0.0336 - 0.264 * math.log10(DuurMinuten) + 0.0636 * math.log10(DuurMinuten) ** 2

def GEVDispCoefBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner):
    if Zichtjaar == 2014:
        return 0.378 - 0.0578 * math.log(DuurMinuten / 60) + 0.0054 * math.log(DuurMinuten / 60) ** 2
    elif Zichtjaar == 2030:
        if Corner == "lower":
            return 0.377 - 0.0565 * math.log(DuurMinuten / 60) + 0.005 * math.log(DuurMinuten / 60) ** 2
        elif Corner == "center":
            return 0.384 - 0.0576 * math.log(DuurMinuten / 60) + 0.0051 * math.log(DuurMinuten / 60) ** 2
        elif Corner == "upper":
            return 0.39 - 0.0587 * math.log(DuurMinuten / 60) + 0.0052 * math.log(DuurMinuten / 60) ** 2
        else:
            return -999
    elif Zichtjaar == 2050:
        if Scenario == "GL":
            if Corner == "lower":
                return 0.377 - 0.0577 * math.log(DuurMinuten / 60) + 0.0053 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.384 - 0.0589 * math.log(DuurMinuten / 60) + 0.0054 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.391 - 0.06 * math.log(DuurMinuten / 60) + 0.0055 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "GH":
            if Corner == "lower":
                return 0.374 - 0.0563 * math.log(DuurMinuten / 60) + 0.0051 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.382 - 0.0574 * math.log(DuurMinuten / 60) + 0.0051 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.39 - 0.0586 * math.log(DuurMinuten / 60) + 0.0052 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "WL":
            if Corner == "lower":
                return 0.375 - 0.0557 * math.log(DuurMinuten / 60) + 0.0049 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.386 - 0.0572 * math.log(DuurMinuten / 60) + 0.005 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.398 - 0.0591 * math.log(DuurMinuten / 60) + 0.0052 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "WH":
            if Corner == "lower":
                return 0.4 - 0.0698 * math.log(DuurMinuten / 60) + 0.0064 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.416 - 0.0728 * math.log(DuurMinuten / 60) + 0.0066 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.432 - 0.0755 * math.log(DuurMinuten / 60) + 0.0069 * math.log(DuurMinuten / 60) ** 2
    elif Zichtjaar == 2085:
        if Scenario == "GL":
            if Corner == "lower":
                return 0.377 - 0.0553 * math.log(DuurMinuten / 60) + 0.005 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.386 - 0.0563 * math.log(DuurMinuten / 60) + 0.0051 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.394 - 0.0572 * math.log(DuurMinuten / 60) + 0.0052 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "GH":
            if Corner == "lower":
                return 0.384 - 0.0559 * math.log(DuurMinuten / 60) + 0.0046 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.395 - 0.0572 * math.log(DuurMinuten / 60) + 0.0047 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.405 - 0.0584 * math.log(DuurMinuten / 60) + 0.0047 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "WL":
            if Corner == "lower":
                return 0.374 - 0.0581 * math.log(DuurMinuten / 60) + 0.0053 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.398 - 0.0612 * math.log(DuurMinuten / 60) + 0.0055 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.423 - 0.0657 * math.log(DuurMinuten / 60) + 0.0059 * math.log(DuurMinuten / 60) ** 2
        elif Scenario == "WH":
            if Corner == "lower":
                return 0.391 - 0.0654 * math.log(DuurMinuten / 60) + 0.0055 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "center":
                return 0.415 - 0.0681 * math.log(DuurMinuten / 60) + 0.0056 * math.log(DuurMinuten / 60) ** 2
            elif Corner == "upper":
                return 0.435 - 0.0702 * math.log(DuurMinuten / 60) + 0.0056 * math.log(DuurMinuten / 60) ** 2
    return -999

def GEVShapeParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario):
    """
    Deze functie berekent de vormparameter voor de GEV-kansverdeling voor lange duur (>= 2 uur) conform STOWA 2015.
    """
    if DuurMinuten / 60 <= 240:
        return -0.09 + 0.017 * (DuurMinuten / 60) / 24
    else:
        return 0

def GEVDispCoefBasisstatistiek2019LangeDuur(DuurMinuten):
    """
    Deze functie berekent de dispersiecoefficient epsylon voor de GLO-kansverdeling voor lange duur (> 720 uur en <= 14400 min) volgens STOWA 2019, deelrapport 1 p12.
    Let op: dit is NIET de schaalparameter uit de GLO-verdeling. Daarvoor moet eerst nog met de locatiepar (zeta) worden vermenigvuldigd.
    """
    return 0.478 - 0.0681 * math.log10(DuurMinuten)

def GEVScaleParBasisstatistiek2019LangeDuur(DuurMinuten):
    """
    Bereken de schaalparameter voor lange duren, jaarrond.
    """
    locpar = GEVLocparBasisstatistiek2019LangeDuur(DuurMinuten)
    dispcoef = GEVDispCoefBasisstatistiek2019LangeDuur(DuurMinuten)
    return locpar * dispcoef

def GEVScaleParBasisstatistiek2019LangeDuurWinter(DuurMinuten):
    """
    Bereken de schaalparameter voor lange duren, winterseizoen (NDJF).
    """
    locpar = GEVLocparBasisstatistiek2019LangeDuurWinter(DuurMinuten)
    dispcoef = GEVDispCoefBasisstatistiek2019LangeDuurWinter(DuurMinuten)
    return locpar * dispcoef

def GEVShapeParBasisstatistiek2019LangeDuur(DuurMinuten):
    """
    Deze functie berekent de dispersiecoefficient epsylon voor de GLO-kansverdeling voor lange duur (> 720 uur en <= 14400 min) volgens STOWA 2019, deelrapport 1 p12.
    Let op: dit is NIET de schaalparameter uit de GLO-verdeling. Daarvoor moet eerst nog met de locatiepar (zeta) worden vermenigvuldigd.
    """
    return 0.118 - 0.266 * math.log10(DuurMinuten) + 0.0586 * (math.log10(DuurMinuten)) ** 2


import math

def STOWA2015_2018_JAARROND_T(DuurMinuten, Zichtjaar, Scenario, Corner, Volume):
    """
    Deze functie berekent het jaarrond neerslagvolume conform STOWA, 2015/2018 met gegeven duur in minuten en volume in mm.
    We berekenen hem in twee iteraties. In de eerste werken we met een geschatte herhalingstijd < 120 jaar.
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input.
    """
    if DuurMinuten > 720:
        dispcoef = GEVDispCoefBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
        locpar = GEVLocParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario)
        P = GEVCDF(locpar, scalepar, shapepar, Volume)
    else:
        Volume /= 1.02
        dispcoef = GLODispCoefBasisstatistiek2018(DuurMinuten)
        locpar = GLOLocParBasisstatistiek2018(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GLOShapeParBasisStatistiek2018(DuurMinuten)
        P = GLOCDF(locpar, scalepar, shapepar, Volume)
    
    return 1 / -math.log(P)

def STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de herhalingstijd voor jaarrond-neerslagstatistiek conform STOWA, 2015 met gegeven Herhalingstijd en duur in minuten.
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input.
    """
    P = math.exp(-1 / T)
    dispcoef = GEVDispCoefBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
    locpar = GEVLocParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
    scalepar = dispcoef * locpar
    shapepar = GEVShapeParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario)
    return GEVINVERSE(locpar, scalepar, shapepar, P)

def STOWA2015_2018_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de herhalingstijd voor jaarrond-neerslagstatistiek conform STOWA, 2015/2018 met gegeven Herhalingstijd en duur in minuten.
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input.
    """
    P = math.exp(-1 / T)
    if DuurMinuten > 720:
        dispcoef = GEVDispCoefBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
        locpar = GEVLocParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario, Corner)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2015(DuurMinuten, Zichtjaar, Scenario)
        return GEVINVERSE(locpar, scalepar, shapepar, P)
    else:
        dispcoef = GLODispCoefBasisstatistiek2018(DuurMinuten)
        locpar = GLOLocParBasisstatistiek2018(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GLOShapeParBasisStatistiek2018(DuurMinuten)
        return GLOINVERSE(locpar, scalepar, shapepar, P) * 1.02

def STOWA2019_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario, Corner, Debugging=False):
    """
    Deze functie berekent de herhalingstijd voor jaarrond-neerslagstatistiek conform STOWA, 2019 met gegeven Herhalingstijd en duur in minuten.
    In de tweede iteratie gebruiken we de herhalingstijd die werd berekend weer als input.
    Voor de multipliers van klimaatscenario's onder korte duren (<= 2 uur) zie STOWA 2019-19 deelrapport 2 tabel 5.
    """
    P = math.exp(-1 / T)
    #print(f"P is {P} en T is {T}")
    if DuurMinuten > 720:
        dispcoef = GEVDispCoefBasisstatistiek2019LangeDuur(DuurMinuten)
        locpar = GEVLocparBasisstatistiek2019LangeDuur(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GEVShapeParBasisstatistiek2019LangeDuur(DuurMinuten)
        Volume = GEVINVERSE(locpar, scalepar, shapepar, P)
    else:
        dispcoef = GLODispCoefBasisstatistiek2019KorteDuur(DuurMinuten)
        locpar = GLOLocparBasisstatistiek2019KorteDuur(DuurMinuten)
        scalepar = dispcoef * locpar
        shapepar = GLOShapeParBasisstatistiek2019KorteDuur(DuurMinuten, T)
        Volume = GLOINVERSE(locpar, scalepar, shapepar, P)

    if Debugging:
        print(f"DuurMinuten: {DuurMinuten}")
        print(f"dispcoef: {dispcoef}")
        print(f"locpar: {locpar}")
        print(f"scalepar: {scalepar}")
        print(f"shapepar: {shapepar}")
        print(f"Volume: {Volume}")

    if Zichtjaar != 2014:
        KorteDuurMultiplier = STOWA2019_KORTEDUUR_MULTIPLIER1(Zichtjaar, Scenario, Corner)
        LangeDuurMultiplier = STOWA2019_LANGEDUUR_MULTIPLIER(DuurMinuten, T, Zichtjaar, Scenario, Corner)
    else:
        KorteDuurMultiplier = 1
        LangeDuurMultiplier = 1

    if DuurMinuten <= 120:
        Multiplier = KorteDuurMultiplier
        Volume *= Multiplier
    elif DuurMinuten < 1440:
        LangeDuurMultiplier = STOWA2019_LANGEDUUR_MULTIPLIER(1440, T, Zichtjaar, Scenario, Corner)
        Multiplier = Interpolate(120, KorteDuurMultiplier, 1440, LangeDuurMultiplier, float(DuurMinuten))
        Volume *= Multiplier
    else:
        Multiplier = LangeDuurMultiplier
        Volume *= Multiplier

    return Volume


import math

def STOWA2019_LANGEDUUR_MULTIPLIER(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de klimaatmultiplier voor een gegeven klimaatscenario, jaarrond.
    De multiplier is gebaseerd op de verhouding klimaat/huidig uit de statistiek van 2015. Dit mag omdat sindsdien de verhoudingen onveranderd zijn gebleven.
    """
    try:
        multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario, Corner) / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
        if multiplier == float('inf'):  # Handle the case where the result is infinity due to a very small denominator
            return 1
        return multiplier
    except ZeroDivisionError:
        return 1

def STOWA2019_MULTIPLIER_WINTER(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de klimaatmultiplier voor een gegeven klimaatscenario, winterseizoen.
    De multiplier is gebaseerd op de verhouding klimaat/huidig uit de statistiek van 2015. Dit mag omdat sindsdien de verhoudingen onveranderd zijn gebleven.
    """
    try:
        multiplier = STOWA2015_WINTER_V(DuurMinuten, T, Zichtjaar, Scenario, Corner) / STOWA2015_WINTER_V(DuurMinuten, T, 2014, "", "")
        if multiplier == float('inf'):  # Handle the case where the result is infinity due to a very small denominator
            return 1
        return multiplier
    except ZeroDivisionError:
        return 1
    
def STOWA2019_KORTEDUUR_MULTIPLIER2(DuurMinuten, T, Zichtjaar, Scenario, Corner):
    """
    Deze functie berekent de klimaatmultiplier voor een gegeven klimaatscenario, korte duur.
    """
    try:
        if Zichtjaar == 2030:
            if Corner in ["lower", "upper"]:
                multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, Scenario, Corner) / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
                if multiplier == float('inf'):
                    return 1
                return multiplier
            else:
                return 1
        elif Zichtjaar == 2050:
            if Scenario == "GH" and Corner == "lower":
                multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, "GL", "lower") / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
                if multiplier == float('inf'):
                    return 1
                return multiplier
            elif Scenario == "WL" and Corner == "upper":
                multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, "WH", "upper") / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
                if multiplier == float('inf'):
                    return 1
                return multiplier
            else:
                return 1
        elif Zichtjaar == 2085:
            if Scenario == "GH" and Corner == "lower":
                multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, "GL", "lower") / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
                if multiplier == float('inf'):
                    return 1
                return multiplier
            elif Scenario == "WL" and Corner == "upper":
                multiplier = STOWA2015_JAARROND_V(DuurMinuten, T, Zichtjaar, "WL", "upper") / STOWA2015_JAARROND_V(DuurMinuten, T, 2014, "", "")
                if multiplier == float('inf'):
                    return 1
                return multiplier
            else:
                return 1
        else:
            return 1
    except ZeroDivisionError:
        return 1
    
def STOWA2019_KORTEDUUR_MULTIPLIER1(Zichtjaar, Scenario, Corner):
    """
    De multipliers voor korte duur zijn ontleend aan de brochure KNMI '14 klimaatscenarios voor Nederland (KNMI).
    De oude multipliers (tov zichtjaar 1995) zijn te vinden in tabel op pag 5. Deze moesten worden gecorrigeerd zodat ze uitgedrukt worden tov zichtjaar 2014.
    Voor zes scenario's is dit al gedaan door KNMI en zijn de waarden gepubliceerd in STOWA 2019-19.
    """
    try:
        if Zichtjaar == 2030:
            if Corner == "lower":
                return 1.0385
            elif Corner == "upper":
                return 1.077
            else:
                return 1
        elif Zichtjaar == 2050:
            if Scenario == "GH" and Corner == "lower":
                return 1.0385
            elif Scenario == "WL" and Corner == "upper":
                return 1.2125
            else:
                return 1
        elif Zichtjaar == 2085:
            if Scenario == "GH" and Corner == "lower":
                return 1.064
            elif Scenario == "WL" and Corner == "upper":
                multiplier = 1 + ((3.5 - 0.3) / 3.5 * 45) / 100
                if multiplier == float('inf'):
                    return 1
                return multiplier
            else:
                return 1
        else:
            return 1
    except ZeroDivisionError:
        return 1

def GLOLocparBasisstatistiek2019KorteDuur(DuurMinuten):
    """
    Deze functie berekent de locatieparameter zeta voor de GLO-kansverdeling voor korte duur (10 minuten t/m 12 uur) volgens STOWA 2019, deelrapport 1 p12.
    """
    return 1.02 * (7.339 + 0.848 * math.log10(DuurMinuten) + 2.844 * (math.log10(DuurMinuten)) ** 2)

def GLODispCoefBasisstatistiek2019KorteDuur(DuurMinuten):
    """
    Deze functie berekent de dispersiecoefficient epsylon voor de GLO-kansverdeling voor korte duur (10 minuten t/m 12 uur) volgens STOWA 2019, deelrapport 1 p12.
    Let op: dit is NIET de schaalparameter uit de GLO-verdeling. Daarvoor moet eerst nog met de locatiepar (zeta) worden vermenigvuldigd.
    """
    if DuurMinuten <= 104:
        return 0.04704 + 0.1978 * math.log10(DuurMinuten) - 0.05729 * (math.log10(DuurMinuten)) ** 2
    else:
        return 0.2801 - 0.0333 * math.log10(DuurMinuten)


