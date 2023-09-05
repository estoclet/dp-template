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

class MultiForm extends SystempayForm
{

    protected function buildSystempayRequest(array $form, FormStateInterface $form_state)
    {
        $request = parent::buildSystempayRequest($form, $form_state);

        $configuration = $this->getPluginConfiguration();

        // get mutiple payment options
        $options = $configuration['payment_options'];

        $amount = $request->get('amount');
        $first = $options['first'] ? round(($options['first'] / 100) * $amount) : null;
        $request->setMultiPayment($amount, $first, $options['count'], $options['period']);

        return $request;
    }
}
