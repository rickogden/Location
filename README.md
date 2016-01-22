# PHP Location Library [![Build Status](https://travis-ci.org/rickogden/Location.svg?branch=master)](https://travis-ci.org/rickogden/Location)

A library for geospatial calculations in PHP.

## Installation

Using composer, run `composer require ricklab\location`

## Usage

A brief example of how this library can be used:

    $point = new Ricklab\Location\Geometry\Point($latitude, $longitude);
    $point2 = new Ricklab\Location\Geometry\Point($lat2, $lon2);
    $distance = $point->distanceTo($point2, 'miles');
    $line = new Ricklab\Location\Geometry\LineString($point, $point2);
 
