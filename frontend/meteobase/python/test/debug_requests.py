#%%
import requests

# %% parameters
payload = {
    "climate": 2024,
    "season": "jaarrond"
           }

headers = {'Content-Type': 'application/x-www-form-urlencoded'}

# %% get response from meteobase
url = "https://www.meteobase.nl/regenduurlijnen/api/stats/volume/STOWA2019"


response = requests.post(url, data=payload, verify=False)
reference = response.json()

# %% get response from meteobase
url = "http://127.0.0.1:5000/api/stats/volume/STOWA2019"

response = requests.post(url, data=payload, verify=False)
local_result = response.json()

# %%
