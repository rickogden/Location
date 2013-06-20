# PHP Location Library

A library for geospatial calculations in PHP.

## Installation

Using composer, add to your composer.json: "ricklab/location": "dev-master"

## Usage

A brief example of how this library can be used:

 $point = new Ricklab\Location\Point($latitude, $longitude);
 $point2 = new Ricklab\Location\Point($lat2, $lon2);
 $distance = $point->distanceTo($point2)->to('miles');
 $line = new Ricklab\Location\Line($point, $point2);
 
