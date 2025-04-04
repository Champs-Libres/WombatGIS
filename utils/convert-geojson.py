import os
import subprocess


directory = os.fsencode('../../../data/shp/')

for subdir, _, files in os.walk(directory):
    for file in files:
        filename_ext = os.fsdecode(file)

        if filename_ext.endswith(".shp"):
            last_dir = str(subdir).split('/')[-1].replace("'", "")
            filename = filename_ext[:-4]
            print(filename)

            shapefile_path = f'../../../data/shp/{last_dir}/{filename}.shp'
            geojson_path = f'../../../data/geojson/{filename}.geojson'
            command = [
                    'ogr2ogr',
                    '-f', 'GeoJSON',
                    '-t_srs' , 'EPSG:4326', # Good geojson is WGS84
                    '-lco', 'ID_GENERATE=yes', # Good geojson have id
                    geojson_path,
                    shapefile_path
                ]

            subprocess.run(command, check=True)





