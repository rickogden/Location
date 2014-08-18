<?php


namespace Ricklab\Location;


abstract class Geometry implements \JsonSerializable
{
    abstract public function toSql();

    abstract public function jsonSerialize();

} 