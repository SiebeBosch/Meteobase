# %%
# duur in uren
#from wiwb.stats_app import get_2019_params
#from wiwb.stats_app import rp_2019_huidig
#from wiwb.stats_app import rp_2019
#from wiwb.stats_app import vols_2019_huidig
from wiwb.stats_app import rp_2024
from wiwb.stats_app import rp_2024_old
from wiwb.stats_app import vols_2024_old
from wiwb.stats_app import vols_2024
from numpy  import exp
durations = [1/6,1/2,1,2,4,8,12,24,48,96,192,216,240]
rp = [0.5,1,2,5,10,20,25,50,100,250,500,1000]
vols = [10,20,30,40,50,75,100,150]
season = 'winter'
climate = '2050'
scenario = 'H'
print(vols_2024(rp,durations,season,climate,scenario))
#print(vols_2024_old(rp,durations,season,climate,scenario))
#print(rp_2024(vols,durations, season, climate, scenario))
#print(rp_2024_old(vols,durations, season, climate, scenario))

# %%
