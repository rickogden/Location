<?php

declare(strict_types=1);
/**
 * Author: rick
 * Date: 17/07/15
 * Time: 11:14.
 */

namespace Ricklab\Location\Feature;

use ArrayAccess;
use Ricklab\Location\Geometry\BoundingBox;
use Ricklab\Location\Geometry\GeometryInterface;

class Feature extends FeatureAbstract implements ArrayAccess
{
    protected $id;
    protected ?GeometryInterface $geometry;
    protected array $properties = [];

    public function __construct(array $properties = [], ?GeometryInterface $geometry = null, bool $bbox = false)
    {
        $this->properties = $properties;
        $this->geometry   = $geometry;
        $this->bbox       = $bbox;
    }

    public function enableBBox(): void
    {
        $this->bbox = true;
    }

    public function disableBBox(): void
    {
        $this->bbox = false;
    }

    public function getGeometry(): ?GeometryInterface
    {
        return $this->geometry;
    }

    /**
     * @return $this
     */
    public function setGeometry(GeometryInterface $geometry): self
    {
        $this->geometry = $geometry;

        return $this;
    }

    /**
     * @return array all the properties
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * Overwrites all properties.
     *
     * @return $this
     */
    public function setProperties(array $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $array = [];

        if (null !== $this->id) {
            $array['id'] = $this->id;
        }

        $array['type'] = 'Feature';

        if ($this->geometry instanceof GeometryInterface) {
            if ($this->bbox) {
                $array['bbox'] = BoundingBox::fromGeometry($this->geometry)->getBounds();
            }
            $array['geometry'] = $this->geometry->jsonSerialize();
        } else {
            $array['geometry'] = null;
        }

        $array['properties'] = $this->properties;

        return $array;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->properties[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->getProperty($offset);
    }

    /**
     * @param $key string the key of the property
     *
     * @return mixed the value of the property
     */
    public function getProperty(string $key)
    {
        return $this->properties[$key];
    }

    public function offsetSet($offset, $value): void
    {
        $this->setProperty($offset, $value);
    }

    /**
     * @param $key string Property key
     * @param $value string Property value
     *
     * @return $this
     */
    public function setProperty(string $key, $value): self
    {
        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     */
    public function offsetUnset($offset): void
    {
        $this->removeProperty($offset);
    }

    public function removeProperty($key): void
    {
        unset($this->properties[$key]);
    }
}
