import math

def GEVCDF(mu, sigma, zeta, x):
    """
    Deze routine berekent de ONDERschrijdingskans van een bepaalde parameterwaarde volgens de GEV-verdeling (Gegeneraliseerde Extreme Waarden).
    Dit betekent gewoon dat we de verdelingsfunctie gaan berekenen (= de integraal van de kansdichtheidsfunctie).
    Kansverdelingsfunctie:    F(x; mu, sigma, zeta) = exp{-(1 + zeta * ((x - mu) / sigma))^(-1 / zeta)}
    """
    e = math.exp(1)
    Z = (x - mu) / sigma
    
    if zeta == 0:
        T = e ** -Z
    else:
        T = (1 - zeta * Z) ** (1 / zeta)
    
    return e ** -T

def GEVINVERSE(mu, sigma, zeta, value):
    """
    Deze routine berekent de ONDERschrijdingskans p van een bepaalde parameterwaarde volgens GEV-verdeling.
    Dit betekent gewoon dat we de verdelingsfunctie gaan berekenen (= de integraal van de kansdichtheidsfunctie).
    """

    if value == 0:
        #prevent division by zero. Since we're dealing with rainfall, zero is the absolute minimum volume
        return 0
    elif value >=1:
        #prevent zero to the power zeta from occurring so  return a very high quantile
        return mu + 5 * sigma
    elif zeta == 0:
        # When zeta is zero, the distribution simplifies to the Gumbel distribution
        return mu - sigma * math.log(-math.log(value))
    else:
        # print(f"mu is {mu}")
        # print(f"sigma is {sigma}")
        # print(f"value is {value}")
        # print(f"zeta is {zeta}")
        return mu + sigma * ((-math.log(value)) ** zeta - 1) / -zeta

def GLOCDF(mu, sigma, teta, x):
    """
    Deze routine berekent de ONDERschrijdingskans van een bepaalde parameterwaarde volgens de GLO-verdeling (Generalized Logistic).
    Dit betekent gewoon dat we de verdelingsfunctie gaan berekenen (= de integraal van de kansdichtheidsfunctie).
    """
    Z = (x - mu) / sigma
    
    if teta == 0:
        return (1 + math.exp(-Z)) ** -1
    else:
        return (1 + (1 - teta * Z) ** (1 / teta)) ** -1

def GLOINVERSE(mu, sigma, teta, value):
    """
    Deze routine berekent de waarde X gegeven een ONDERschrijdingskans en een GLO-kansverdeling (Generalized Logistic).
    Dit betekent gewoon dat we de verdelingsfunctie gaan berekenen (= de integraal van de kansdichtheidsfunctie).
    """

    if teta == 0 or value == 0:
        if value == 0:
            #prevent division by zero. Since we're dealing with rainfall, zero is the absolute minimum volume
            return 0
        else:            
            return mu - sigma * math.log(1 / value - 1) 
    else:
        #if value ==1 : print("value is 1")
        #if value == 0 : print("value is 0")
        return mu + sigma * ((1 - (1 / value - 1) ** teta) / teta)
    

def Interpolate(X1, Y1, X2, Y2, X3, block_interpolate=False):
    """
    Deze functie voert lineaire interpolatie uit.
    """
    if X3 < min(X1, X2) or X3 > max(X1, X2):
        return -999
    elif block_interpolate:
        return Y1
    else:
        return Y1 + (Y2 - Y1) / (X2 - X1) * (X3 - X1)
