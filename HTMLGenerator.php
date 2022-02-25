<?php

namespace DMo\Captcha;

use DMo\Captcha\Code;

/**
 * Generates an array with seven keys for seven input fields in a form.
 */
class HTMLGenerator {

    /**
     * @var array
     */
    protected $inputNames = [
        'road', 'village', 'familyaddress', 'houseblock', 'rearname', 'backname', 'frontname'
    ];

    /**
     * @var string
     */
    private $type;
    /**
     * @var Code
     */
    private $code;

    /**
     * @param Code $code
     * @param string $type
     */
    public function __construct(Code $code, $type = 'hidden') {
        $this->code = $code;
        $this->setInputType($type);
    }

    /**
     * @return string
     */
    public function get() {
        $code = $this->code->get();
        $codeElem = reset($code);
        $output = array_map(function ($name) use (&$codeElem, &$code) {
            $out = sprintf(
                '<input type="%s" name="%s" value="%s" />',
                $this->type, $name, $codeElem
            );
            $codeElem = next($code);
            return $out;
        }, $this->inputNames);

        return "\n    ".implode("\n    ", $output)."\n";
    }

    /**
     * @return array
     */
    public function getInputNames() {
        return $this->inputNames;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setInputType($type = 'text') {
        $this->type = $type;
        return $this;
    }
}
