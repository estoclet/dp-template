<?php
/**
 * Copyright © Lyra Network.
 * This file is part of Systempay for Drupal Commerce. See COPYING.md for license details.
 *
 * @package   Systempay
 * @author    Lyra Network <contact@lyra-network.com>
 * @copyright Lyra Network
 * @license   http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL v2)
 */
namespace Drupal\commerce_systempay\Plugin\Commerce\PaymentGateway;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides PayPal payment through the Systempay payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "systempay_paypal",
 *   label = @Translation("Systempay - PayPal Payment"),
 *   display_label = @Translation("Payment with PayPal"),
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_systempay\PluginForm\PaypalForm"
 *   },
 *   modes = {
 *     "TEST" = @Translation("TEST"),
 *     "PRODUCTION" = @Translation("PRODUCTION")
 *   }
 * )
 */
class Paypal extends Systempay
{

    /**
     * {@inheritdoc}
     */
    public function buildConfigurationForm(array $form, FormStateInterface $form_state)
    {
        $form = parent::buildConfigurationForm($form, $form_state);

        // cannot configure payment cards for PayPal payment
        unset($form['payment_page']['payment_cards']);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPaymentMeans()
    {
        return [
            'PAYPAL' => 'PayPal',
            'PAYPAL_SB' => 'PayPal - Sandbox'
        ];
    }
}
