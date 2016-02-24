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
    And I press "#top-cart-btn-checkout" element
    And I select shipping method "s_method_flatrate"
    And I press "#shipping-method-buttons-container .button" element

    Then I should see MercadoPago Standard available