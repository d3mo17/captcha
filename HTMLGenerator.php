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
     * @param array $requestParams
     * @return array
     */
    public function restoreCode(array $requestParams = []) : array {
        if (empty($requestParams)) {
            $requestParams = $_REQUEST ?: ($_POST ?: $_GET);
        }

        return array_map(function ($name) use ($requestParams) {
            return $requestParams[$name];
        }, $this->getInputNames());
    }

    /**
     * @return string
     */
    public function get() : string {
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
    public function getInputNames() : array {
        return $this->inputNames;
    }

    /**
     * @param array $inputNames
     * @return $this
     * 
     * @throws LengthException
     */
    public function setInputNames(array $inputNames) {
        count($inputNames) !== 7 && self::spit(
            \LengthException::class,
            'There have to be seven names for input elements!',
            1646092907
        );
        $this->inputNames = $inputNames;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     * 
     * @throws DomainException
     */
    public function setInputType(string $type = 'text') {
        !in_array($type, ['hidden', 'text']) && self::spit(
            \DomainException::class,
            'Use value "hidden" or "text" for type!',
            1646092504
        );
        $this->type = $type;
        return $this;
    }

    /**
     * @param string $exceptionType
     * @param string $message
     * @param int $code
     * @return void
     */
    static private function spit(string $exceptionType, string $message, int $code) : void {
        throw new $exceptionType($message, $code);
    }
}
