# -*- coding: utf-8 -*-
"""
Created on Mon Sep  2 15:02:43 2019

@author: danie

#script om te runnen op de meteobase server

"""

import base64
import json
import requests
import datetime

#%% parameters + functies
bounds = (101707.1769999999960419,493804.8859999999986030,134515.6499999999941792,553284.1199999999953434)

dsc = 'Meteobase.Precipitation'
parameter = 'P'

dsc = 'Meteobase.Evaporation.Makkink'
parameter = 'Evaporation'

for month in range(1,12,1):
	start_date = datetime.datetime(year=2017, month=month,day=1) 
	end_date = datetime.datetime(year=2017, month=month+1,day=1)
	def from_wiwb(dsc,variable_code,bounds,start_date,end_date,zip_dir='wiwb_raw'):
		url = 'https://wiwb.hydronet.com/api/grids/get'
		headers = {'Authorization':"Basic "+bytes.decode(base64.standard_b64encode(str.encode("siebe.bosch:" + "iY0Hofot3zaZWxyCOxPX"))),"Content-Type":"application/json","Accept":"application/json"}
		payload = {"Readers":[{"DataSourceCode":dsc,"Settings":{"StructureType":"Grid","ReadQuality":False,"ReadAvailability":True,"StartDate":start_date.strftime('%Y%m%d%H%M%S'),"EndDate":end_date.strftime('%Y%m%d%H%M%S'),"VariableCodes":[variable_code],"Extent":{"XLL":bounds[0],"YLL":bounds[1],"XUR":bounds[2],"YUR":bounds[3],"SpatialReference":{"Epsg":"28992"}},"Interval":{"Type":"Hours","Value":1},"ReadAccumulated":False}}],"Exporter":{"DataFormatCode":"geotiff","Settings":{"DigitsToRound":2}}} 

		return requests.post(url, headers=headers, data=json.dumps(payload),timeout=10 * 60 * 1000)

	#%% script
	r = from_wiwb(dsc,parameter,bounds,start_date,end_date)

	if r.status_code == 200:
		with open('{}.{}.{}.zip'.format(dsc,parameter,month),'wb') as f:
					for chunk in r:
						f.write(chunk)
	else:
		print('request failed with response:', r.text)