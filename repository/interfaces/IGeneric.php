<?php

namespace Repository\Interfaces;

interface IGeneric
{
    public function __construct(\Phalcon\Config $config);

    public function setModelMap($dir, $np = null);

    public function setModel($modelName);

    public function boot($modelName);
}