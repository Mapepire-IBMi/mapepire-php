<?php

/*
 * Copyright 2024 Jack J. Woehr
 * jwoehr@softwoehr.com
 * PO Box 82 Beulah, Colorado 81023-8282
 * All Rights Reserved
 */

namespace Mapepire;

class Client implements \Stringable
{

    protected ?string $user = null;

    public function __tostring(): string
    {
        return "\Mapepire\Client";
    }
}
