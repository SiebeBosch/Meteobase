# -*- coding: utf-8 -*-
"""
Python app to convert WiWB meteo-data to Hydromedah-data and FEWS-CSV
"""

__author__ = "Daniel Tollenaar"
__credits__ = ["Daniel Tollenaar", "Siebe Bosch"]
__maintainer__ = "Daniel Tollenaar"
__email__ = "daniel@d2hydro.nl"
__status__ = "Development"

import base64
import datetime
from flask import (Blueprint,  request, send_file)
from operator import itemgetter
import glob
import numpy as np
import json
import os
#import rasterio
import requests
import shutil
import time
import zipfile
#from rasterio import features
import pygeoj

# create directories if not existing
def read_status():
    f = open(status_file ,'r')
    line = f.readlines()
    return json.loads(line[-1])

def make_dir(directory):
    dirpath = os.path.abspath(directory)
    if os.path.exists(dirpath): shutil.rmtree(dirpath)
    os.makedirs(os.path.abspath(directory))

def print_status(message):
    print(json.dumps({"time":time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()),"status":message}),file=open(status_file, "a"))
    
def from_wiwb(dsc,variable_code,bounds,start_date,end_date,zip_dir='wiwb_raw'):
    url = 'https://wiwb.hydronet.com/api/grids/get'
    headers = {'Authorization':"Basic "+bytes.decode(base64.standard_b64encode(str.encode("siebe.bosch:" + "iY0Hofot3zaZWxyCOxPX"))),"Content-Type":"application/json","Accept":"application/json"}
    payload = {"Readers":[{"DataSourceCode":dsc,"Settings":{"StructureType":"Grid","ReadQuality":False,"ReadAvailability":True,"StartDate":start_date.strftime('%Y%m%d%H%M%S'),"EndDate":end_date.strftime('%Y%m%d%H%M%S'),"VariableCodes":[variable_code],"Extent":{"XLL":bounds[0],"YLL":bounds[1],"XUR":bounds[2],"YUR":bounds[3],"SpatialReference":{"Epsg":"28992"}},"Interval":{"Type":"Hours","Value":1},"ReadAccumulated":False}}],"Exporter":{"DataFormatCode":"geotiff","Settings":{"DigitsToRound":2}}} 
    
    return requests.post(url, headers=headers, data=json.dumps(payload),timeout=10 * 60 * 1000)

def to_hydromedah(variable,zip,inp_array):
    try:
        zip_ref = zipfile.ZipFile(os.path.join(wiwb_dir,'{}.zip'.format(variable)), 'r')
        zip_ref.extractall(work_dir)
        zip_ref.close()   
        
        tiff_files = glob.glob(os.path.abspath(os.path.join(work_dir,'*.tif')))
    except:
       return ['ERROR','server failed to unzip {}.zip retrieved from WiWb'.format(variable)]
       print_status('ready')
      
    
    try:
        for idx, tiff in enumerate(tiff_files):
              # reading raster meta data and band
              tiff_name = os.path.splitext(os.path.basename(tiff))[0]
              raster= rasterio.open(tiff)
              no_data = raster.nodata
              data = raster.read(1)
              profile = raster.profile
              # fill no_data with 0
              data[data== no_data] = 0.
              
              # multiply rainfall grids with 24 and fill inp-file
              date_obj = datetime.strptime(tiff_name[len(tiff_name)-41:len(tiff_name)-21],'%Y-%m-%dT%Hh%Mm%Ss')
              if variable == 'P':
                  tstep = float(idx)/24.
                  asc_file = date_obj.strftime('NSL_%Y%m%d_%H.ASC')
                  zip_path = os.path.join('NSL',os.path.basename(asc_file))
                  inp_array[idx,:] = ['%.10f' % tstep,date_obj.year,'"{}"'.format(asc_file),'"{}"'.format('...................'),'"NoValue"','"NoValue"','"NoValue"']
                  data = data * 24
              else: 
                  asc_file = date_obj.strftime('MAK_%Y%m%d.ASC')
                  zip_path = os.path.join('MAK',os.path.basename(asc_file))
                  inp_array[idx*24:idx*24+24,3] = '"{}"'.format(asc_file)
              
              #write to ascii and store in zip-file
              profile['driver'] = 'AAIGrid'
              asc_path = os.path.join(work_dir,asc_file)
              with rasterio.open(asc_path, 'w', **profile) as dst:
                  dst.write(data, 1)
              zip.write(asc_path, zip_path)
              raster.close()
        make_dir(work_dir)
    except:
        return ['ERROR','server failed to process {} retrieved from WiWb'.format(os.path.basename(tiff))]
    return ['SUCCES',inp_array]

def to_timeSeries(variable,feats,attribute):
    result = {'timeZone':0,'timeSeries':[]}
    
    try:
        zip_ref = zipfile.ZipFile(os.path.join(wiwb_dir,'{}.zip'.format(variable)), 'r')
        zip_ref.extractall(work_dir)
        zip_ref.close()   
        
        tiff_files = glob.glob(os.path.abspath(os.path.join(work_dir,'*.tif')))
    except:
       return ['ERROR','server failed to unzip {}.zip retrieved from WiWb'.format(variable)]
       print_status('ready')
    
    #try:
    for idx, tiff in enumerate(tiff_files):
        # reading raster meta data and band
        tiff_name = os.path.splitext(os.path.basename(tiff))[0]
        raster= rasterio.open(tiff)
        no_data = raster.nodata
        data = raster.read(1)
        # fill no_data with 0
        data[data== no_data] = 0.
        
        date_obj = datetime.datetime.strptime(tiff_name[len(tiff_name)-99:len(tiff_name)],'%Y-%m-%dT%Hh%Mm%Ss')
        if not variable == 'P':
            print('other data than "P" not yet supported!')
        
        affine = raster.transform
        
        for feature in feats['features']:
            ident = feature['properties'][attribute] 
            if idx == 0: result['timeSeries'].append({'header':{'locationId':ident,'parameterId':variable,'missVal':float('nan'),'units':'mm'},'events':[]})
            image = rasterio.features.rasterize([feature['geometry']],out_shape=raster.shape,all_touched=True,transform=affine).astype('float32')
            image[image == 0] = np.nan
            value = round(float(np.nanmean(np.multiply(image,data))),2)
            record = next(idx for idx, item in enumerate(result['timeSeries']) if item["header"]['locationId'] == ident)
            result['timeSeries'][record]['events'].append({'date':date_obj.strftime('%Y-%m-%d'),'time':date_obj.strftime('%H:%M:%S'),'value':value})
        raster.close()
    #except:
    #    return ['ERROR','server failed to process {} retrieved from WiWb'.format(os.path.basename(tiff))]

    make_dir(work_dir)
    return result

bp = Blueprint('wiwb', __name__, url_prefix='/api/wiwb')

wiwb_dir = 'wiwb_raw'
work_dir = 'work_dir'
client_dir = 'to_client'

abspath = os.path.abspath(__file__)
dname = os.path.dirname(abspath)
os.chdir(dname)

status_file = os.path.join('status.txt')

          
@bp.route('/hydromedah', methods=('GET', ))
def get_hydromedah():
    # try if no previous instance is active
    try:
        if os.path.exists(status_file):
            status = read_status()['status']
            if not status == 'ready': 
                time.sleep(2)
                response = 'wiwb api status = "{}". Please try again later'.format(status)
                return  response ,200
    except: 
        pass
    
    file = open(status_file,'w')
    file.close()
    
    # try to parse parameters   
    try:
        bounds = json.loads(request.args.get('bounds'))
        start_date = datetime.datetime.strptime(request.args.get('sd'),'%Y-%m-%dT%H:%M:%SZ') 
        end_date = datetime.datetime.strptime(request.args.get('ed'),'%Y-%m-%dT%H:%M:%SZ') 
    except:
        error_message = 'Missing or mall-formed input parameters: bounds: {}, start_date: {}, end_date: {}'.format(bounds,start_date,end_date)
        print_status('ready')
        return error_message , 400
    
    # some checks
    if (end_date - start_date).days <= 1: 
        print_status('ready')
        return 'your timespan should be > 1 day', 400
    
    # get rainfall from wiwb server
    print_status('getting rainfall from wiwb')
    make_dir(wiwb_dir)
    
    try:
        r = from_wiwb('Meteobase.Precipitation','P',bounds,start_date,end_date)
    except:
        error_message = 'failed to send request to WiWb'
        print_status('ready')
        return error_message , 500
    
    if r.status_code == 200:
        with open(os.path.join(wiwb_dir,'P.zip'),'wb') as f:
            for chunk in r:
                f.write(chunk)
    else:
        error_message = 'WiWb-server failed with message: {}'.format(r.text)
        print_status('ready')
        return error_message , r.status_code 
    
    # get evaporation from wiwb server
    print_status('getting evaporation from wiwb')
    
    try:
        r = from_wiwb('Meteobase.Evaporation.Makkink','Evaporation',bounds,start_date,end_date)
    except:
        error_message = 'failed to send request to WiWb'
        print_status('ready')
        return error_message , 500
    
    if r.status_code == 200:
        with open(os.path.join(wiwb_dir,'Evaporation.zip'),'wb') as f:
            for chunk in r:
                f.write(chunk)
    else:
        error_message = 'WiWb-server failed with message: {}'.format(r.text)
        print_status('ready')
        return error_message , r.status_code 
    
    # convert to hydromedah-format
    print_status('converting data to hydromedah-format')
    make_dir(client_dir)

    zip = zipfile.ZipFile(os.path.join(client_dir,'forcing.zip'), "w", zipfile.ZIP_DEFLATED)
    
    inp_array = np.chararray([int((end_date - start_date).days*24),7],itemsize=21)
    result = to_hydromedah('P',zip,inp_array)
    if result[0] == 'ERROR': 
        return result[1], 500
        print_status('ready')
    else: inp_array = result[1]
    result = to_hydromedah('Evaporation',zip,inp_array)
    if result[0] == 'ERROR': 
        return result[1], 500
        print_status('ready')
    else: inp_array = result[1]
    np.savetxt(os.path.join(work_dir,'Mete_grid.inp'),inp_array.decode('utf-8'),delimiter=',',fmt='%s')
    zip.write(os.path.join(work_dir,'Mete_grid.inp'),'Mete_grid.inp')
    zip.close()         
    
    print_status('ready')
    return send_file(os.path.join(client_dir,'forcing.zip')), 200

@bp.route('/timeseries', methods=('GET', ))
def get_timeSeries():
    # try if no previous instance is active
    data = json.loads(request.data)
    try:
        if os.path.exists(status_file):
            status = read_status()['status']
            if not status == 'ready': 
                time.sleep(2)
                response = 'wiwb api status = "{}". Please try again later'.format(status)
                return  response ,200
    except: 
        pass
    
    file = open(status_file,'w')
    file.close()
    # try to parse parameters   
    try:
        geojson, attribute,start_date,end_date = None,None,None,None
        geojson = data['geojson']
        attribute = data['id']
        start_date = datetime.datetime.strptime(data['sd'],'%Y-%m-%dT%H:%M:%SZ') 
        end_date = datetime.datetime.strptime(data['ed'],'%Y-%m-%dT%H:%M:%SZ') 
    except:
        error_message = 'Missing or mall-formed input parameters: geojson: {}, attribute: {}, start_date: {}, end_date: {}'.format(data['geojson'],data['id'],data['sd'],data['ed'])
        print_status('ready')
        return error_message , 400
    
    # some checks
    if (end_date - start_date).days <= 1: 
        print_status('ready')
        return 'your timespan should be > 1 day', 400
    

    if not attribute in pygeoj.load(data=geojson).common_attributes:
        error_message = '"{}" not an attribute in geojson specified in areas parameter'.format(attribute)
        print(error_message)
        return error_message , 400
    
    bounds = pygeoj.load(data=geojson).bbox
    

    # get rainfall from wiwb server
    print_status('getting rainfall from wiwb')
    make_dir(wiwb_dir)
    
    try:
        r = from_wiwb('Meteobase.Precipitation','P',bounds,start_date,end_date)
    except:
        error_message = 'failed to send request to WiWb'
        print_status('ready')
        return error_message , 500
    
    if r.status_code == 200:
        with open(os.path.join(wiwb_dir,'P.zip'),'wb') as f:
            for chunk in r:
                f.write(chunk)
    else:
        error_message = 'WiWb-server failed with message: {}'.format(r.text)
        print_status('ready')
        return error_message , r.status_code 
     
    # convert to json

    timeSeries = to_timeSeries('P',geojson,attribute)      
    
    print_status('ready')
    
    return json.dumps(timeSeries), 200

@bp.route('/status', methods=('GET', ))
def status():
    try:
        response = read_status()
    except:
        response = {"status":'api status not available'}  
    time.sleep(2)
    return json.dumps(response),200
