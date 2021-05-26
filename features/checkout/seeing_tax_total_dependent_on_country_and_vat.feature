@checkout_vat_number
Feature: Seeing tax total dependent on country and vat
    In order to be certain about total tax for vat number
    As an Customer
    I want to see tax total dependent on country and vat number

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Germany"
        And the store operates in "France"
        And channel "Web" billing data is "Ragnarok", "Frost Alley", "20355" "Hamburg", "Germany" with "1100110011" tax ID
        And the store has a zone "Germany" with code "DE"
        And it also has the "Germany" country member
        And the store has a zone "France" with code "FR"
        And it also has the "France" country member
        And the store has a "tax" zone "France tax" with code "FR-tax"
        And it also has the "France" country member
        And the store has a zone "European Union" with code "EU"
        And it has the zone named "Germany"
        And it has the zone named "France"
        And the store has "MwsT" tax rate of 19% for "Clothes" within the "DE" zone
        And the store has "VAT" tax rate of 20% for "Clothes" within the "FR-tax" zone
        And the store has "VAT" tax rate of 19% for "Clothes" within the "EU" zone
        And default tax zone is "EU"
        And the store has a product "PHP T-Shirt" priced at "$10.00"
        And it belongs to "Clothes" tax category
        And the store ships everywhere for free
        And the store allows paying offline
        And I am a logged in customer
        And I have product "PHP T-Shirt" in the cart
        And I am at the checkout addressing step

    @ui
    Scenario: Seeing the total tax of 19% with valid VAT number from our business country
        When I specify the billing address as "Hamburg", "Frost Alley", "20355", "Germany" for "Ankh Morpork"
        And I specify the billing vat number as "DE118716043"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$1.90"
        And my order total should be "$11.90"

    @ui
    Scenario: Seeing the total tax of 20% in another country
        When I specify the billing address as "Marseille", "Chaude Ruelle", "13003", "France" for "Pierre Simon"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$2.00"
        And my order total should be "$12.00"

    @ui
    Scenario: Seeing tax total tax of 0% with valid VAT number from another country than our business country
        When I specify the billing address as "Marseille", "Chaude Ruelle", "13003", "France" for "Pierre Simon"
        And I specify the billing vat number as "FR91552118465"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$0.00"
        And my order total should be "$10.00"
