# WombatGIS

![screenshot](https://raw.githubusercontent.com/Champs-Libres/WombatGIS/master/img-dist/logo_black.png)

## Presentation

WombatGIS is php / javascript application to draw / display graphical information on maps.

![screenshot](https://raw.githubusercontent.com/Champs-Libres/WombatGIS/master/overview.png)

Demo : (http://wombatgis.champs-libres.coop/)

## Prerequisites

* PHP 5 >= 5.2

## Installation

`cp -R data-dist data`
`cp -R css-dist css`
`cp -R img-dist img`

If the directory `data` is not owned by `www-data` user :

`sudo chown -R www-data data`

## How does it works ?

The configuration files are in the directory `data`.

The file `config.json` is used to configure the global map and the files `__name_of_the_layer__.json` to configure the relative layer.

There exists a web interface in the directory `admin` for editing the configuration files.