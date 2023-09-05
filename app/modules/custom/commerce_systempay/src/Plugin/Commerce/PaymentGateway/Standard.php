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

/**
 * Provides the Systempay payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "systempay_standard",
 *   label = @Translation("Systempay - Standard payment"),
 *   display_label = @Translation("Payment by credit card"),
 *   forms = {
 *     "offsite-payment" = "Drupal\commerce_systempay\PluginForm\StandardForm"
 *   },
 *   modes = {
 *     "TEST" = @Translation("TEST"),
 *     "PRODUCTION" = @Translation("PRODUCTION")
 *   }
 * )
 */
class Standard extends Systempay
{

    /**
     * {@inheritdoc}
     */
    protected function getSupportedPaymentMeans()
    {
        return \SystempayApi::getSupportedCardTypes();
    }
}
