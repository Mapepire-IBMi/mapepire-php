<?php

/*
 * Copyright 2024 Jack J. Woehr
 * jwoehr@softwoehr.com
 * PO Box 82 Beulah, Colorado 81023-8282
 * All Rights Reserved
 */

namespace Mapepire;

/**
 * Simple encapsulation of the mapepire server functionality
 */
class Server {

    protected $process = null;
    protected $pipes = null;

    public function simple(string $jar = 'codeforibmiserver.jar',
            string $java = 'QOpenSys/QIBM/ProdData/JavaVM/jdk80/64bit/bin/java',
            string $cwd = '.',
            ?array $env = null): mixed {

        $descriptorspec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => array("pipe", "w"), // stdout is a pipe that the child will write to
            2 => array("pipe", "w") // stderr is a pipe that the child will write to
        );

        $this->process = proc_open("$java $jar", $descriptorspec, $this->pipes, $cwd, $env);
    }

    /**
     * 
     * @return mixed
     */
    public function stdin(): mixed {
        return $this->pipes[0];
    }

    /**
     * 
     * @return mixed
     */
    public function stdout(): mixed {
        return $this->pipes[1];
    }

    /**
     * 
     * @return mixed
     */
    public function stderr(): mixed {
        return $this->pipes[2];
    }

    /**
     * 
     * @param string $data
     * @param int $length
     * @return int|false
     */
    public function write(string $data, int $length = null): int|false {
        return fwrite($this->stdout(), $data, $length);
    }

    /**
     * 
     * @param int $length
     * @return string|false
     */
    public function readOut(int $length): string|false {
        return fread($this->stdout(), $length);
    }

    /**
     * 
     * @param int $length
     * @return string|false
     */
    public function readErr(int $length): string|false {
        return fread($this->stderr(), $length);
    }
}
