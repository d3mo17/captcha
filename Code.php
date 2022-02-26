<?php

namespace DMo\Captcha;

/**
 * Generates an array with seven keys for seven input fields in a form.
 */
class Code {
    /**
     * Defines the length of output values in code array.
     * Reasonable range is 5 to 13
     * 
     * @var int
     */
    protected $CHUNK_LENGTH = 13;
    /**
     * Code is valid for this duration and expires after this time
     * 
     * @var int
     */
    protected $validForMinutes = 12;
    /**
     * Code is invalid before these seconds have past
     * 
     * @var int
     */
    protected $validNotTillSeconds = 3;

    /**
     * @var string
     */
    private $secretPepper;
    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var \Error|null
     */
    private $error;


    /**
     * Array of randomly ordered decimal numbers which have at least two same bits in
     * binary signature (means at least two zeros and two ones).
     * This is to determine which keys in code array have to be set an which last empty.
     * The code array will have seven keys, so this is the reason why only numbers with
     * at most seven bits are used.
     * 
     * Up to you to rearrange this list in a subclass!
     *
     * @var array
     */
    protected $numberSequence = [
        113, 115, 116, 118, 124, 121, 114, 117, 127, 122, 120,   6,  14,  12,   9,   5,
         81,  83,  93,  84,  91,  86,  94,  92,  89,  82,  85,  90,  87,  88,  96,   3,
        104, 105, 107, 108, 102, 110, 106, 109,  98, 101, 100,  99, 112,  97, 103,  15,
         24,  25,  27,  28,  22,  30,  26,  29,  18,  21,  20,  19,  31,  17,  23,  10,
         77,  68,  66,  69,  74,  71,  65,  70,  79,  80,  78,  76,  72,  73,  75,  67,
         43,  44,  38,  46,  47,  48,  40,  34,  42,  39,  37,  36,  33,  35,  45,  41,
         59,  60,  54,  62,  56,  49,  51,  61,  57,  50,  58,  55,  53,  52,   7,  11,
    ];


    /**
     * @param string $secretPepper
     * @param int $time
     */
    public function __construct(
        $secretPepper = '<SecretString>',
        $time = null
    ) {
        $this->secretPepper = $secretPepper;
        $this->timestamp = $time ?: time();
    }

    /**
     * Returns the needed code sequence
     * 
     * @return array
     */
    public function get() : array {
        $code = array_pad([], 7, '');
        $indices = $this->getIndicesToBeFilled();
        $hashChunks = $this->getHashChunks();

        $index = reset($indices);
        $code[$index] = sprintf(
            '%s:%s:%s',
            $this->currentSecondInPeriod(),
            $this->getTimeHash($this->currentSecondInPeriod(), $this->getMinuteOfDay()), 
            $this->getMinuteOfDay()
        );
        $chunk = reset($hashChunks);
        while ($index = next($indices)) {
            $code[$index] = $chunk;
            $chunk = next($hashChunks);
        }

        return $code;
    }

    /**
     * @param array $codeArray
     * 
     * @return bool
     */
    public function validate($codeArray) : bool {
        $removedEmpty = array_diff($codeArray, ['']);
        list($secondToCompare, $checkHash, $minuteToCompare) = explode(':', reset($removedEmpty));

        $minuteOfDay = $this->getMinuteOfDay() < $minuteToCompare
            ? (24*60) + $this->getMinuteOfDay()
            : $this->getMinuteOfDay();
        $duration = $minuteOfDay - $minuteToCompare;
        if ($duration > $this->validForMinutes) {
            $this->error = new \Error('Expired (lasts ' . $duration . ' min.)!', 1645535407);
            return false;
        }

        $currentSecondInPeriod = $this->currentSecondInPeriod() < $secondToCompare
            ? ($this->validForMinutes * 60) + $this->currentSecondInPeriod()
            : $this->currentSecondInPeriod();
        $duration = $currentSecondInPeriod - $secondToCompare;
        if ($duration <= $this->validNotTillSeconds) {
            $this->error = new \Error('Too fast input for human beings (' . $duration . ' sec.)!', 1645532843);
            return false;
        }

        if (!$this->validateSignature($codeArray)) {
            $this->error = new \Error('Signature validation of code array fails!', 1645535534);
            return false;
        }

        if (!$this->validateTimeValues($checkHash, $secondToCompare, $minuteToCompare)) {
            $this->error = new \Error('Validation hash doesn\'t correspond to time values!', 1645535755);
            return false;
        }

        return true;
    }

    /**
     * Returns theLast Error, if there is one (after validation fails)
     * @return \Error|null
     */
    public function getLastError() {
        return $this->error;
    }


    /**
     * Get minute of current day from timestamp.
     * 
     * @return int
     */
    final public function getMinuteOfDay() : int {
        return intval(date('H', $this->timestamp)) * 60 + intval(date('i', $this->timestamp)) + 1;
    }

    /**
     * Has the purpose to obscure the order of number sequence depending on minute of day.
     * Up to you to override it in a subclass for more individuality. But consider this has to be consistent
     * in each moment
     * 
     * @return int
     */
    protected function getShiftingNumber() : int {
        return intval(date('Y', $this->timestamp))
                - intval(date('d', $this->timestamp))
                - intval(date('m', $this->timestamp)) * 15;
    }

    /**
     * @return int
     */
    private function getIndexOfPeriod() : int {
        return (ceil($this->getMinuteOfDay() / $this->validForMinutes) + $this->getShiftingNumber()) % 112;
    }

    /**
     * Returns which indices of code array have to be set.
     * 
     * @return array
     */
    private function getIndicesToBeFilled() : array {
        $decNum = $this->numberSequence[$this->getIndexOfPeriod()];
        $binCode = str_pad(decbin($decNum), 7, '0', STR_PAD_LEFT);
        return array_keys(array_diff(str_split($binCode), [0]));
    }

    /**
     * Returns an array containing three hash strings of configured length
     * 
     * @return array
     */
    private function getHashChunks() : array {
        $chunks = str_split(sha1($this->secretPepper.$this->getIndexOfPeriod()), $this->CHUNK_LENGTH);
        array_pop($chunks);
        return $chunks;
    }

    /**
     * Returns the significant second in current period
     * 
     * @return integer
     */
    final protected function currentSecondInPeriod() : int {
        return ($this->timestamp % ($this->validForMinutes * 60)) + 1;
    }

    /**
     * Compares two values under the aspect they can be compared
     * 
     * @param string $expectation
     * @param string $givenValue
     * 
     * @return bool
     */
    private function compareValues(string $expectation, string $givenValue) : bool {
        if (empty($expectation) && !empty($givenValue)) {
            return false;
        }

        if (strlen($expectation) === $this->CHUNK_LENGTH && $expectation !== $givenValue) {
            return false;
        }

        // ignore expectations containing time values (the first two values of code array)

        return true;
    }

    /**
     * Validates the passed array
     * 
     * @param array $codeArray
     * 
     * @return bool
     */
    private function validateSignature(array $codeArray) : bool {
        $expectations = $this->get();

        $valid = true;
        foreach ($expectations as $key => $expected) {
            $valid = $valid && $this->compareValues($expected, $codeArray[$key]);
        }

        if ($valid) {
            return true;
        }

        // As a function of tolarance, we have to check values from the period before
        $lastCaptcha = new static();
        $lastCaptcha->secretPepper = $this->secretPepper;
        $lastCaptcha->timestamp = $this->timestamp - ($this->validForMinutes * 60);

        $expectations = $lastCaptcha->get();

        $valid = true;
        foreach ($expectations as $key => $expected) {
            $valid = $valid && $this->compareValues($expected, $codeArray[$key]);
        }

        if ($valid) {
            return true;
        }

        return false;
    }

    /**
     * @param int $seconds
     * @param int $minutes
     * @return string
     */
    private function getTimeHash($seconds, $minutes) {
        return substr(
            sha1($this->secretPepper . $seconds . $minutes),
            $minutes % 27, $this->CHUNK_LENGTH
        );
    }

    /**
     * @param string $checkHash
     * @param int $seconds
     * @param int $minutes
     * @return bool
     */
    private function validateTimeValues($checkHash, $seconds, $minutes) {
        return $checkHash === $this->getTimeHash($seconds, $minutes);
    }
}
