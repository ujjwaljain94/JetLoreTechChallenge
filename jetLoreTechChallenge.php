<?php

  /*Module 1 which crawls twitter/facebook to receive feeds*/
  class Crawler {
    public function getInputFeed() {
      return 'Obama visited Facebook headquarters: http://bit.ly/xyz @elversatile';
    }
  }

  /*Module 2 which processes the input string and gives the positions through which different elements are identified*/

  class StringProcessor {
    public function __construct($inputString) {
      $this->inputString = $inputString;
    }

    public function getProcessedOutput() {
      /*This is the structure of the output expected from this module, any new type
      must just be appended to the array with a key type, */
      return [
          'entity' => [
              [
                  'startIndex' => 14,
                  'endIndex' => 22
              ],
              [
                  'startIndex' => 0,
                  'endIndex' => 5
              ],
          ],
          'userName' => [
              [
                  'startIndex' => 55,
                  'endIndex' => 67
              ]
          ],
          'link' => [
              [
                  'startIndex' => 37,
                  'endIndex' => 54
              ]
          ],
      ];
    }
  }

  /* Interface to be implemented by every type of element which will format the input*/
  interface BaseFormat {
      public function formatTypeString($formatWord);
  }

  /*To append strong tag for type = entity*/
  class FormatEntity implements BaseFormat{
    private static $obj;

    private function __constructor() {}

    public static function getInstance() {
        if ($obj == null)
            $obj = new FormatEntity();
        return $obj;
    }

    public function formatTypeString($formatWord) {
      return '<strong>' . $formatWord . '</strong>';
    }
  }

  /*To append a tag for type = username*/
  class FormatUserName implements BaseFormat{
    private static $obj;

    private function __constructor() {}

    public static function getInstance() {
        if ($obj == null)
            $obj = new FormatUserName();
        return $obj;
    }

    public function formatTypeString($formatWord) {
      return '<a href = "http://twitter.com/' . $formatWord . '" >' . $formatWord . '</a>';
    }
  }

  /*To append a tag for type = link*/
  class FormatLink implements BaseFormat{
    private static $obj;

    private function __constructor() {}

    public static function getInstance() {
        if ($obj == null)
            $obj = new FormatLink();
        return $obj;
    }

    public function formatTypeString($formatWord) {
      return '<a href = "' . $formatWord . '" >' . $formatWord . '</a>';
    }
  }

  /*This class is used to create an instance of the formatting type class based on element type*/

  class FormatTypeFactory {
    public static function getInstanceType($type) {
      switch ($type) {
        case 'entity':
          return FormatEntity::getInstance();
        case 'userName':
          return FormatUserName::getInstance();
        case 'link':
          return FormatLink::getInstance();
        default:
          // code...
          break;
      }
    }
  }

  /*This class is Module 3 that is used to format the string */
  class BeautifyString {
    private $formattedString;
    private $beautifyString;
    public $processedStrFlatStructure;

    public function __construct($inputString, $processedOutput) {
      $this->inputStr = $inputString;
      $this->processedOutputArray = $processedOutput;
      $this->processedStrFlatStructure = array();
      $this->formattedString = '';
    }

    public function sortByStartIndex($a, $b) {
      $a = $a['startIndex'];
      $b = $b['startIndex'];
      if ($a == $b) return 0;
      return ($a < $b) ? -1 : 1;
    }

    private function flatStructureArray($output, $keyName) {
        $dummyArray = $output;
        $dummyArray['type'] = $keyName;
        $dummyArray['word'] = substr($this->inputStr, $output['startIndex'], ($output['endIndex'] - $output['startIndex']));
        array_push($this->processedStrFlatStructure, $dummyArray);
    }

    /*Function to reduce the module 2 multi dimensional array and sort it based on startIndex into a flat struture*/
    public function getSortedFlatStructureArray() {
      foreach ($this->processedOutputArray as $typeKey => $processedOutput) {
        foreach ($processedOutput as $key => $value) {
          $this->flatStructureArray($value, $typeKey);
        }
      }
      usort($this->processedStrFlatStructure, array($this, 'sortByStartIndex'));
    }

    /*Function to append html tags to the original string*/
    public function formatOriginalString() {
      $i = 0;
      $count = 0;

      while($i < strlen($this->inputStr) && $count < sizeof($this->processedStrFlatStructure)) {
        if($i == $this->processedStrFlatStructure[$count]['startIndex']) {
          $this->formattedString .= FormatTypeFactory::getInstanceType($this->processedStrFlatStructure[$count]['type'])->formatTypeString($this->processedStrFlatStructure[$count]['word']);
          $i += ($this->processedStrFlatStructure[$count]['endIndex'] - $this->processedStrFlatStructure[$count]['startIndex']);
          $count += 1;
        } else {
          $this->formattedString .= $this->inputStr[$i];
          $i++;
        }
      }
    }

    public function beautifyOutputString() {

      if($this->inputStr == '') {
        return 'No input feed to process, empty string received! Please retry.';
      }

      $this->getSortedFlatStructureArray();

      if(sizeof($this->processedStrFlatStructure) == 0) {
          return $this->inputStr;
      }

      $this->formatOriginalString();
      // echo htmlentities($this->formattedString);
      return $this->formattedString;
    }

  }

  $crawler = new Crawler();
  $inputStr = $crawler->getInputFeed();

  $processor = new StringProcessor($inputStr);
  $processedStr = $processor->getProcessedOutput();

  $beautifyClass = new BeautifyString($inputStr, $processedStr);
  $beautifiedString = $beautifyClass->beautifyOutputString();

  print_r($beautifiedString);

?>
