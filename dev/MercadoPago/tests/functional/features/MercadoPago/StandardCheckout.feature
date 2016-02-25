@MercadoPago
Feature: A customer should be able to do a checkout with MercadoPago

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And I am logged in as "test_user_58666377@testuser.com" "magento"
    And I empty cart

  @frontend @viewStandard
  Scenario: See MercadoPago standard option as a payment method
    When I am on page "push-it-messenger-bag.html"
    And I press "#product-addtocart-button" element
    And I am on page "checkout/cart/"
    And I press "[data-role='proceed-to-checkout']" element
    And I fill the shipping address
    And I select shipping method "flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should see MercadoPago Standard available

  @frontend @Availability @ClientId
  Scenario: Not See MercadoPago option as a payment method when is not client id
    When Setting Config "payment/mercadopago_standard/client_id" is "0"
    And I am on page "push-it-messenger-bag.html"
    And I press "#product-addtocart-button" element
    And I am on page "checkout/cart/"
    And I press "[data-role='proceed-to-checkout']" element
    And I fill the shipping address
    And I select shipping method "flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Standard available

  @frontend @Availability @ClientSecret
  Scenario: Not See MercadoPago option as a payment method when is not available client secret
    When Setting Config "payment/mercadopago_standard/client_secret" is "0"
    And I am on page "push-it-messenger-bag.html"
    And I press "#product-addtocart-button" element
    And I am on page "checkout/cart/"
    And I press "[data-role='proceed-to-checkout']" element
    And I fill the shipping address
    And I select shipping method "flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should not see MercadoPago Standard available
