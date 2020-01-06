@address_vat_number
Feature: Order addressing vat validation
    In order to avoid making mistakes when addressing an order
    As an Customer
    I want to be prevented from adding it with invalid vat number

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Germany"
        And the store has a product "PHP T-Shirt" priced at "$19.99"
        And I am a logged in customer
        And I have product "PHP T-Shirt" in the cart
        And I am at the checkout addressing step

    @ui
    Scenario: Address an order with invalid vat number
        When I specify the shipping address as "Hamburg", "Frost Alley", "20355", "Germany" for "Ankh Morpork"
        And I specify the shipping vat number as "XY118716043"
        And I try to complete the addressing step
        Then I should be notified that the vat number in shipping is not valid
