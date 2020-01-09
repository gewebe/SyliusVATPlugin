@address_vat_number
Feature: See VAT number at billing address after an order has been placed
    In order to write an order's bill with VAT number
    As an Administrator
    I want to be able to validate a customer's VAT number after an order has been placed

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Germany"
        And the store has a zone "Germany" with code "DE"
        And the store ships everywhere for free
        And the store allows paying with "Cash on Delivery"
        And the store has a product "PHP T-Shirt" priced at "$10.00"
        And there is a customer "mike@ross.com" that placed an order "#00000001"
        And the customer bought a single "PHP T-Shirt"
        And the customer "Mike Ross" addressed it to "5th Ave", "10118" "Berlin" in the "Germany" with identical billing address

    @ui
    Scenario: Modifying a customer's billing address VAT number
        Given the customer set the billing address VAT number to "DE123123123" which is valid
        And the customer chose "Free" shipping method with "Cash on Delivery" payment
        When I am logged in as an administrator
        And I view the summary of the order "#00000001"
        Then I should see valid VAT number "DE123123123" in the billing address
        When I want to modify a customer's billing address of this order
        And I do specify billing address VAT number to "DE321321321"
        And I try to save my changes
        Then I should see valid VAT number "DE321321321" in the billing address

    @ui
    Scenario Outline: See customer's VAT number validation status
        Given the customer set the <addressType> address VAT number to "<vatNumber>" which is <vatValid>
        And the customer chose "Free" shipping method with "Cash on Delivery" payment
        When I am logged in as an administrator
        And I view the summary of the order "#00000001"
        Then I should see <vatValid> VAT number "<vatNumber>" in the <addressType> address

        Examples:
            | vatNumber | vatValid | addressType |
            |  DE118716043   |  valid  |  billing  |
            |  DE123123123   |  invalid  |  billing  |
            |  DE123123123   |  unverified  |  billing  |
            |  DE118716043   |  valid  |  shipping  |
            |  DE123123123   |  invalid  |  shipping  |
            |  DE123123123   |  unverified  |  shipping  |
