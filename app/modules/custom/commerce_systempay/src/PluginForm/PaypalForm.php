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
namespace Drupal\commerce_systempay\PluginForm;

use Drupal\Core\Form\FormStateInterface;

class PaypalForm extends SystempayForm
{

    protected function buildSystempayRequest(array $form, FormStateInterface $form_state)
    {
        $request = parent::buildSystempayRequest($form, $form_state);

        $test_mode = $request->get('ctx_mode') == 'TEST';
        $request->set('payment_cards', $test_mode ? 'PAYPAL_SB' : 'PAYPAL');

        return $request;
    }
}
