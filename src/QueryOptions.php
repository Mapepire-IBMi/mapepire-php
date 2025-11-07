<?php declare(strict_types=1);

/*
 * Copyright 2024 IBM
 * All Rights Reserved
 * Apache License 2.0 ... see LICENSE file
 * 
 * Original Author: Matthew Wiltzius <matthew.wiltzius@ibm.com>
 */

namespace Mapepire;

/**
 * The QueryOptions class configures query execution behavior:
 */
final class QueryOptions
{
    public ?bool  $isTerseResults = null;
    public ?bool  $isClCommand    = null;
    public ?array $parameters     = null;
    public ?bool  $autoClose      = null;

    public function __construct(?array $data = null) {
        $this->isTerseResults = isset($data['isTerseResults']) ? (bool)$data['isTerseResults'] : null;
        $this->isClCommand    = isset($data['isClCommand'])    ? (bool)$data['isClCommand']    : null;
        $this->parameters     = isset($data['parameters'])     ? array_values((array)$data['parameters']) : null;
        $this->autoClose      = isset($data['autoClose'])      ? (bool)$data['autoClose']      : null;
    }
}