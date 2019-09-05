<?php
/**
 *
 */

namespace Frootbox\Translation;

class Translator {

    protected $data;
    protected $scope;


    /**
     *
     */
    public function getData ( ): array
    {
        return $this->data;
    }


    /**
     *
     */
    public function addResource ( $path, $scope = 'global' ): Translator {

        $data = require $path;

        foreach ($data as $key => $value) {
            $this->data[$scope . '.' . $key] = $value;
        }

        return $this;
    }


    /**
     *
     */
    public function setData ( array $data ): Translator
    {
        $this->data = $data;

        return $this;
    }


    /**
     *
     */
    public function setLanguage ( $language ): Translator
    {
        return $this;
    }


    /**
     * Set translators current scope
     */
    public function setScope ( $scope ): Translator
    {
        $this->scope = $scope;

        return $this;
    }


    /**
     *
     */
    public function translate ( $key ): ?string
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }

        $segments = explode('.', $this->scope);
        $loops = count($segments);

        for ($i = 0; $i < $loops; ++$i) {

            $nkey = implode('.', $segments) . '.' . $key;

            if (array_key_exists($nkey, $this->data)) {
                return $this->data[$nkey];
            }

            array_pop($segments);
        }

        return null;
    }
}