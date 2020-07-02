<?php

namespace Drupal\Tests\commerce_vantiv\FunctionalJavascript;

use Drupal\commerce_payment\Entity\PaymentGateway;
use Drupal\Tests\commerce\FunctionalJavascript\CommerceWebDriverTestBase;

/**
 * Tests the creation of the Vantiv Onsite gateway
 *
 * @group commerce
 */
class ConfigurationFormTest extends CommerceWebDriverTestBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * The product.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $product;

  /**
   * A non-reusable order payment method.
   *
   * @var \Drupal\commerce_payment\Entity\PaymentMethodInterface
   */
  protected $orderPaymentMethod;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'commerce_vantiv',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return array_merge([
      'administer commerce_payment_gateway',
    ], parent::getAdministratorPermissions());
  }

  /**
   * Tests creating a Vantiv onsite payment gateway via the config UI.
   */
  public function testCreateGateway() {
    // Navigate to the "payment gateways" list page
    $this->drupalGet('admin/commerce/config/payment-gateways');

    // Click the button to add a new payment gateway
    $this->getSession()->getPage()->clickLink('Add payment gateway');

    // Get the page so we can set form values
    $page = $this->getSession()->getPage();

    // Choose the Vantiv (onsite) radio option
    $page->findField('Vantiv (Onsite)')->selectOption('vantiv_onsite');
    $this->waitForAjaxToFinish();

    // Choose the pre-live option
    $page->findField('pre-live')->selectOption(0);

    // Fill out the form
    $page->findField('label')->setValue('Vantiv Onsite US');
    $page->findField('id')->setValue('vantiv_onsite_us');
    $page->findField('configuration[vantiv_onsite][user]')->setValue('UserName');
    $page->findField('configuration[vantiv_onsite][password]')->setValue('PassWord');
    $page->findField('configuration[vantiv_onsite][currency_merchant_map][default]')->setValue('0137147');
    $page->findField('configuration[vantiv_onsite][proxy]')->setValue('prox://y');
    $page->findField('configuration[vantiv_onsite][paypage_id]')->setValue('PayPageID');
    $page->findField('configuration[vantiv_onsite][batch_requests_path]')->setValue('/batch-requests-path');
    $page->findField('configuration[vantiv_onsite][litle_requests_path]')->setValue('/litle-requests-path');
    $page->findField('configuration[vantiv_onsite][sftp_username]')->setValue('sFTP Username');
    $page->findField('configuration[vantiv_onsite][sftp_password]')->setValue('sFTP Password');

    $page->findField('configuration[vantiv_onsite][batch_url]')->setValue('batch://url');
    $page->findField('configuration[vantiv_onsite][tcp_port]')->setValue('3000');
    $page->findField('configuration[vantiv_onsite][tcp_timeout]')->setValue('20');
    $page->findField('configuration[vantiv_onsite][tcp_ssl]')->check();
    $page->findField('configuration[vantiv_onsite][print_xml]')->check();
    $page->findField('configuration[vantiv_onsite][timeout]')->setValue('10');
    $page->findField('configuration[vantiv_onsite][report_group]')->setValue('eCommerce');
    $page->findField('status')->selectOption(1);

    $this->submitForm([], t('Save'));
    $this->assertSession()->waitForElementVisible('css', '.non-exist-element', 9999);
    $this->assertSession()->pageTextContains('Saved the Vantiv Onsite US payment gateway.');

    $payment_gateway = PaymentGateway::load('vantiv_onsite_us');
    $payment_gateway_plugin = $payment_gateway->getPlugin();
    $config = $payment_gateway_plugin->getConfiguration();
    $this->assertEquals('vantiv_onsite_us', $payment_gateway->id());
    $this->assertEquals('Vantiv Onsite US', $payment_gateway->label());
    $this->assertEquals('vantiv_onsite', $payment_gateway->getPluginId());
    $this->assertEquals(TRUE, $payment_gateway->status());
    $this->assertEquals('0', $payment_gateway_plugin->getMode());
    $this->assertEquals('UserName', $config['user']);
    $this->assertEquals('PassWord', $config['password']);
    $this->assertEquals('0137147', $config['currency_merchant_map']['default']);
    $this->assertEquals('prox://y', $config['proxy']);
    $this->assertEquals('PayPageID', $config['paypage_id']);
    $this->assertEquals('/batch-requests-path', $config['batch_requests_path']);
    $this->assertEquals('/litle-requests-path', $config['litle_requests_path']);
    $this->assertEquals('sFTP Username', $config['sftp_username']);
    $this->assertEquals('sFTP Password', $config['sftp_password']);
    $this->assertEquals('batch://url', $config['batch_url']);
    $this->assertEquals('3000', $config['tcp_port']);
    $this->assertEquals('20', $config['tcp_timeout']);
    $this->assertEquals('1', $config['tcp_ssl']);
    $this->assertEquals('1', $config['print_xml']);
    $this->assertEquals('10', $config['timeout']);
    $this->assertEquals('eCommerce', $config['report_group']);
  }

}
