<?php
/**
 *
 */

namespace Frootbox\Translation;

class Translator
{
    private $data;
    private $scope;
    private $notOverwritables = [ ];

    /**
     *
     */
    private function getPhrase($key): string
    {
        // Force key to be PascalCase
        $key = ucfirst($key);

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

        return $key;
    }

    /**
     *
     */
    private function parseInset($inset): ?string
    {
        if (substr($inset, 0, 2) != 'T:') {
            return $inset;
        }

        return $this->getPhrase(substr($inset, 2));
    }

    /**
     *
     */
    public function addResource($path, $scope = null, $allowOverwrite = true): Translator {

        if ($scope === null) {
            $scope = $this->scope;
        }

        if (!file_exists($path)) {
            return $this;
        }

        $data = require $path;
        $notOverwrite = false;

        foreach ($data as $key => $value) {

            if ($key[0] == '!') {
                $key = substr($key, 1);
                $notOverwrite = true;
            }

            if ($key[0] == '\\' OR $key[0] == '.') {
                $key = substr($key, 1);
            }
            else if (!empty($scope)) {
                $key = $scope . '.' . $key;
            }

            if (!$allowOverwrite and !empty($this->data[$key])) {
                continue;
            }

            if ($notOverwrite or !in_array($key, $this->notOverwritables)) {
                $this->data[$key] = $value;

                if ($notOverwrite) {
                    $this->notOverwritables[] = $key;
                    $notOverwrite = false;
                }
            }
        }

        ksort($this->data);

        return $this;
    }

    /**
     *
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     *
     */
    public function hasKey(string $key): bool
    {
        // Force key to be PascalCase
        $key = ucfirst($key);

        if (array_key_exists($key, $this->data)) {
            return true;
        }

        $segments = explode('.', $this->scope);
        $loops = count($segments);

        for ($i = 0; $i < $loops; ++$i) {

            $nkey = implode('.', $segments) . '.' . $key;

            if (array_key_exists($nkey, $this->data)) {
                return true;
            }

            array_pop($segments);
        }

        return false;

    }

    /**
     *
     */
    public function setData(array $data): Translator
    {
        $this->data = $data;

        return $this;
    }

    /**
     *
     */
    public function setLanguage($language): Translator
    {
        return $this;
    }

    /**
     * Set translators current scope
     */
    public function setScope($scope): Translator
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Request translation of a given translation key
     *
     */
    public function translate($key, array $insets = null, $default = null): string
    {
        // Obtain phrase from translations
        $phrase = $this->getPhrase($key);

        if (!empty($insets)) {

            // Replace [link] pattern
            if (preg_match('#\[(?<linkTitle>.*?)\]#', $phrase, $match)) {

                $attributes = (string) null;

                if ($match['linkTitle'][0] == '^') {
                    $match['linkTitle'] = substr($match['linkTitle'], 1);
                    $attributes .= ' target="_blank" ';
                }

                $phrase = str_replace($match[0], '<a ' . $attributes . ' href="' . array_shift($insets) . '">' . $match['linkTitle'] . '</a>', $phrase);
            }

            switch (count($insets)) {
                case 1:
                    $phrase = sprintf($phrase, $this->parseInset($insets[0]));
                    break;

                case 2:
                    $phrase = sprintf($phrase, $this->parseInset($insets[0]), $this->parseInset($insets[1]));
                    break;

                case 3:
                    $phrase = sprintf($phrase, $this->parseInset($insets[0]), $this->parseInset($insets[1]), $this->parseInset($insets[2]));
                    break;
            }
        }

        // Remove unused [link] tags
        $phrase = preg_replace('#\[(.*?)\]#', '\\1', $phrase);

        if ($phrase == $key and !empty($default)) {
            $phrase = $default;
        }

        return $phrase;
    }

    /**
     *
     */
    public function translateDate(
        string $dateString,
        string $formatString
    ): string
    {
        $date = new \Frootbox\Dates\Date($dateString);
        $key = $date->format($formatString);

        return $this->translate($key);
    }
}
