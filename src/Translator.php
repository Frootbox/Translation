<?php
/**
 *
 */

namespace Frootbox\Translation;

class Translator {

    protected $data;

    /**
     *
     */
    public function get ( $segment ) {

        return $this->data['global'][$segment];
    }


    /**
     *
     */
    public function addResource ( $path, $scope = 'global' ): Translator {

        $this->data[$scope] = require $path;

        return $this;
    }
}