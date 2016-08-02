<?php
/**
 * @file
 * Contains Drupal\bets_calculator\Form\BetsCalculatorForm.
 */

namespace Drupal\bets_calculator\Form;


use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class BetsCalculatorForm extends FormBase {

    /**
     * {@inheritdoc}.
     */
    public function getFormId() {
        return 'bets_calculator_form';
    }

    /**
     * {@inheritdoc}.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['combinations'] = array(
            '#type' => 'number',
            '#title' => $this->t('Min correct tips:'),
        );
        $form['total'] = array(
            '#type' => 'number',
            '#title' => $this->t('Total matches:'),
        );
        $form['next'] = array(
            '#type' => 'submit',
            '#value' => $this->t('Next'),
        );
        return $form;
    }

    /**
     * {@inheritdoc}.
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::validateForm($form, $form_state);
        $combinations = (int) $form_state->getValue('combinations');
        $total = (int) $form_state->getValue('total');
        if ($total < $combinations || $combinations < 2 || $total > 20) {
            $form_state->setErrorByName(
            'combinations',
            $this->t("Minimum correct tips must be at least 2 and not higher then total matches!
                      Total matches must be smaller then 21!")
            );
        }
    }

    /**
     * {@inheritdoc}.
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        //drupal_set_message($this->t('Minimum correct tips @comb!', array('@comb' => $form_state->getValue('combinations'))));
        //drupal_set_message($this->t('Total tips @total!', array('@total' => $form_state->getValue('total'))));
        $form_state->setRedirect('bets_calculator_odds.form', array(),
            array( 'query' => array(
                'combinations' => $form_state->getValue('combinations'),
                'total' => $form_state->getValue('total'),
                ))
        );
    }
}