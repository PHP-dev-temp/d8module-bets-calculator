<?php
/**
 * @file
 * Contains Drupal\bets_calculator\Form\BetsCalculatorForm.
 */

namespace Drupal\bets_calculator\Form;


use Drupal\bets_calculator\BetProcessing;
use Drupal\bets_calculator\OddsProcessing;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

class BetsCalculatorFormOdds extends FormBase
{

    /**
     * {@inheritdoc}.
     */
    public function getFormId() {
        return 'bets_calculator_odds.form';
    }

    /**
     * {@inheritdoc}.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $request = Request::createFromGlobals();
        $variables = $request->query->all();
        $combinations = $variables['combinations'];
        $total = $variables['total'];
        if ($total < $combinations || $combinations < 2 || $total > 20){
            $form['reset_text'] = array(
                '#type' => 'html_tag',
                '#tag' => 'p',
                '#value' => $this->t('Wrong number of combinations or total matches. Please reset form!'),
            );
            $form['reset'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Reset form'),
                '#submit' => array('::bets_calculator_reset_form'),
            );
        } else {
            $form['combinations'] = array(
                '#type' => 'hidden',
                '#value' => $this->t($combinations),
            );
            $form['total'] = array(
                '#type' => 'hidden',
                '#value' => $this->t($total),
            );

            for ($i = 1; $i <= $total; $i++) {
                $form['match_text' . $i] = array(
                    '#type' => 'html_tag',
                    '#tag' => 'p',
                    '#value' => $this->t('Match @m', array('@m' => $i)),
                );
                $form['odd' . $i] = array(
                    '#type' => 'textfield',
                    '#title' => $this->t('Odd:'),
                );
                $form['win' . $i] = array(
                    '#type' => 'checkbox',
                    '#title' => $this->t('Win!'),
                );
            }
			$form['actions'] = array(
				'#type' => 'html_tag',
				'#tag' => 'p',
				'#value' => $this->t(''),
			);
            $form['reset'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Reset form'),
                '#submit' => array('::bets_calculator_reset_form'),
            );
            $form['ajax_submit'] = array(
                '#type' => 'submit',
                '#value' => $this->t('Calculate'),
                '#ajax' => [
                    'callback' => array($this, 'bets_calculator_ajax_submit'),
                    'event' => 'click',
                    'progress' => array(
                        'type' => 'throbber',
                        'message' => t('Calculating...'),
                    ),
                ],
                '#suffix' => '<div class="bets_calculator_result"></div>'
            );
        }
        return $form;
    }

    /**
     * {@inheritdoc}.
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        parent::validateForm($form, $form_state);
    }

    /**
     * {@inheritdoc}.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        //drupal_set_message($this->generateOutput($form_state, ' -> '));
    }

    /**
     * @param array $form
     * @param FormStateInterface $form_state
     */
    public function bets_calculator_reset_form(array &$form, FormStateInterface $form_state) {
        $form_state->setRedirect('bets_calculator.form');
    }


    /**
     * @param array $form
     * @param FormStateInterface $form_state
     * @return AjaxResponse
     */
    public function bets_calculator_ajax_submit(array &$form, FormStateInterface $form_state) {
        $message = $this->generateOutput($form_state, '<br>');
        $response = new AjaxResponse();
        $response->addCommand(new HtmlCommand('.bets_calculator_result', $message));
        return $response;
    }

    public function generateOutput(FormStateInterface $form_state, $nl) {
        $results = array();
        $combinations = $form_state->getValue('combinations')->getUntranslatedString();
        $total = (int) $form_state->getValue('total')->getUntranslatedString();
        for ($i = 1; $i <= $total; $i++) {
            $results[$i-1][0] = $form_state->getValue('win' . $i);
            $results[$i-1][1] = (string) (double) $form_state->getValue('odd' . $i);
        }
        $bp = new BetProcessing($results, $combinations);
        $combinationsTotal = $bp->getBetCombinations();
        $res = $bp->getCompressedResults();
        $qp = new OddsProcessing($res, $combinations);
        $sumOdds = $qp->getOdds();
        $tq = $sumOdds / $combinationsTotal;

        $output = $this->t('Ticket system: @combinations/@total',
            array('@combinations' => $combinations, '@total' => $total));
        $output .= $nl;
        $output .= $this->t('Total correct tips: @combinations',
            array('@combinations' => (string)$bp->getTotalCorrectTips()));
        $output .= $nl;
        $output .= $this->t('Total combinations: @combinations',
            array('@combinations' => (string)$combinationsTotal));
        $output .= $nl;
        $output .= $this->t('Odds: @odds',
            array('@odds' => (string)$sumOdds));
        $output .= $nl;
        $output .= $this->t('Total odds: @odds',
            array('@odds' => (string)$tq));
        return $output;
    }
}