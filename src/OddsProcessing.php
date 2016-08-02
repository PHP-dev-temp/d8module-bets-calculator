<?php

namespace Drupal\bets_calculator;

class OddsProcessing {

  private $minCorrectTips;
  private $totalRows;
  private $sumOdds;
  private $marker = array();
  private $results = array();

  /**
   * OddsProcessing constructor.
   * @param array $results
   * @param $minCorrectTips
     */
  function __construct(array $results, $minCorrectTips){
    $this->totalRows = count($results);
    $this->minCorrectTips = $minCorrectTips;
    $this->sumOdds = 0;
    for ($i=0; $i<$this->totalRows; $i++){
      $this->marker[] = $i;
    }
    for ($i=0; $i<$this->totalRows; $i++){
      $this->results[] = $results[$i][1];
    }
  }

  /**
   * @param $current
   */
  private function iteration($current){
    $last = $this->totalRows - $this->minCorrectTips + $current;
    $start = 0;
    $delta = $this->minCorrectTips - $current - 1;
    if($current>0) $start = $this->marker[$current - 1] + 1;
      for ($i=$start; $i<=$last; $i++){
      $this->marker[$current] = $i;
            if($delta>0) {
              $this->iteration($current+1);
            } else {
              $odds = 1;
              for ($j=0; $j<$this->minCorrectTips; $j++){
                $odds *= $this->results[$this->marker[$j]];
                }
                $this->sumOdds += $odds;
            }
        }
    }

  /**
   * @return int
   */
  public function getOdds(){
      $this->iteration(0);
      return $this->sumOdds;
    }

}