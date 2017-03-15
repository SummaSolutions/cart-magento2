@MercadoPago
Feature: Validation of custom checkout form

  Background:
    Given User "test_user_58666377@testuser.com" "magento" exists
    And I am logged in as "test_user_58666377@testuser.com" "magento"
    And Setting merchant "mla"
    And I empty cart
    And I am on page "push-it-messenger-bag.html"
    And I press "#product-addtocart-button" element
    And I am on page "checkout/cart/"
    And I press "[data-role='proceed-to-checkout']" element
    And I wait for "6" seconds
    And I fill the shipping address
    And I select shipping method "flatrate_flatrate"
    And I press "#shipping-method-buttons-container .button" element
    And I wait for "8" seconds
    And I select payment method "mercadopago_custom"

  @CheckoutCustomForm @CardED
  Scenario: Validate card expiration date
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "1"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2016"
    And I select installment "1"

    Then I should see "Month is invalid."
    And I should see "Year is invalid."

  @CheckoutCustomForm @CardHN
  Scenario: Validate cardholder name
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "cardholderName" with "!@#APRO123"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#mp-custom-save-payment" element
    Then I should see "Card Holder Name is invalid."

  @CheckoutCustomForm @CardSC
  Scenario: Validate card security code
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "2"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "12345"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    Then I should see "CVV is invalid"

  @CheckoutCustomForm @CardDN
  Scenario: Validate card Document number
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "2"
    And I fill text field "cardholderName" with "APRO"
    And I fill text field "docNumber" with "1234"
    And I fill text field "securityCode" with "12345"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#mp-custom-save-payment" element
    Then I should see "Document Number is invalid."

  @CheckoutCustomForm @CardEmptyHN
  Scenario: Validate empty card holder name
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#mp-custom-save-payment" element
    Then I should see "This is a required field."

  @CheckoutCustomForm @CardEmptySC
  Scenario: validate empty security code
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "docNumber" with "12345678"
    And I fill text field "cardholderName" with "test"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#mp-custom-save-payment" element
    Then I should see "Please enter a valid number in this field."

  @CheckoutCustomForm @CardEmptyDN
  Scenario: validate empty document number
    Given I fill text field "cardNumber" with "4509 9535 6623 3704"
    And I select option field "cardExpirationMonth" with "10"
    And I fill text field "cardholderName" with "test"
    And I fill text field "securityCode" with "123"
    And I select option field "cardExpirationYear" with "2017"
    And I select installment "1"

    And I press "#mp-custom-save-payment" element
    Then I should see "Please enter a valid number in this field."



