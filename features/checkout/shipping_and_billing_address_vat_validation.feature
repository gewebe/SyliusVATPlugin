@checkout_vat_number
Feature: Order addressing vat validation
    In order to avoid making mistakes when addressing an order
    As an Customer
    I want to be prevented from adding it with invalid VAT number

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Portugal" and "Spain"
        And the store has a product "PHP T-Shirt" priced at "$19.99"
        And I am a logged in customer
        And I have product "PHP T-Shirt" in the cart
        And I am at the checkout addressing step

    @ui
    Scenario: Address an order for a company where the VAT number is required
        When I specify the billing company as "LaLuna"
        And I try to complete the addressing step
        Then I should be notified that the company VAT number in billing is required

    @ui
    Scenario: Address an order for a country where the VAT number is required
        When I specify the billing address as "Lisboa", "Rua Augusta", "1100-016", "Portugal" for "Maria Lopes"
        And I try to complete the addressing step
        Then I should be notified that the VAT number in billing is required

    @ui
    Scenario: Address an order with invalid VAT number
        When I specify the billing address as "Barcelona", "Carrer de Mercedes", "C573+5G", "Spain" for "Pau Güell"
        And I specify the billing VAT number as "ES666666666"
        And I try to complete the addressing step
        Then I should be notified that the VAT number in billing is not valid
