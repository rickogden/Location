# PHP Location Library [![CI](https://github.com/rickogden/Location/actions/workflows/ci.yaml/badge.svg)](https://github.com/rickogden/Location/actions/workflows/ci.yaml)

A library for geospatial calculations in PHP.

## Installation

Using composer, run `composer require ricklab/location`

## Usage

A brief example of how this library can be used:

    $point = new Ricklab\Location\Geometry\Point($longitude, $latitude);
    $point2 = new Ricklab\Location\Geometry\Point($lon2, $lat2);
    $distance = $point->distanceTo($point2, 'miles');
    $line = new Ricklab\Location\Geometry\LineString([$point, $point2]);
 
