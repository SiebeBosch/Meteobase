# -*- coding: utf-8 -*-
"""
Created on Thu Jun  6 09:08:40 2019

@author: danie
"""
import base64
import datetime
import requests
import pygeoj
import json
import os

def from_wiwb(dsc,variable_code,bounds,start_date,end_date,zip_dir='wiwb_raw'):
    #url = 'https://wiwb.hydronet.com/api/grids/get'
    url = 'https://wiwb.hydronet.com/api/grids/get'
    headers = {'Authorization':"Basic "+bytes.decode(base64.standard_b64encode(str.encode("siebe.bosch:" + "iY0Hofot3zaZWxyCOxPX"))),"Content-Type":"application/json","Accept":"application/json"}
    payload = {"Readers":[{"DataSourceCode":dsc,"Settings":{"StructureType":"Grid","ReadQuality":False,"ReadAvailability":True,"StartDate":start_date.strftime('%Y%m%d%H%M%S'),"EndDate":end_date.strftime('%Y%m%d%H%M%S'),"VariableCodes":[variable_code],"Extent":{"XLL":bounds[0],"YLL":bounds[1],"XUR":bounds[2],"YUR":bounds[3],"SpatialReference":{"Epsg":"28992"}},"Interval":{"Type":"Hours","Value":1},"ReadAccumulated":False}}],"Exporter":{"DataFormatCode":"geotiff","Settings":{"DigitsToRound":2}}} 
    #payload = {"Readers":[{"DataSourceCode":dsc,"Settings":{"StructureType":"Grid","ReadQuality":False,"ReadAvailability":True,"StartDate":start_date.strftime('%Y%m%d%H%M%S'),"EndDate":end_date.strftime('%Y%m%d%H%M%S'),"Extent":{"XLL":bounds[0],"YLL":bounds[1],"XUR":bounds[2],"YUR":bounds[3],"SpatialReference":{"Epsg":"28992"}},"Interval":{"Type":"Hours","Value":1},"ReadAccumulated":False}}],"Exporter":{"DataFormatCode":"geotiff","Settings":{"DigitsToRound":2}}} 
    
    return requests.post(url, headers=headers, data=json.dumps(payload),timeout=10 * 60 * 1000)

featureFile = 'cabauw.geojson'
wiwb_dir = r'./'
geojson = json.load(open(featureFile, 'r'))
data = {'sd':'2019-06-06T09:00:00Z','ed':'2019-06-11T09:00:00Z'}
start_date = datetime.datetime.strptime(data['sd'],'%Y-%m-%dT%H:%M:%SZ') 
end_date = datetime.datetime.strptime(data['ed'],'%Y-%m-%dT%H:%M:%SZ') 

bounds = pygeoj.load(data=geojson).bbox

r = from_wiwb('Knmi.International.Radar.Composite','P',bounds,start_date,end_date)

if r.status_code == 200:
    with open(os.path.join(wiwb_dir,'P.zip'),'wb') as f:
        for chunk in r:
            f.write(chunk)
else: print(r.text)