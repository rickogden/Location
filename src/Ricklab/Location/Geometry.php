<?php


namespace Ricklab\Location;


interface Geometry extends \JsonSerializable
{
    public function toSql();

} 