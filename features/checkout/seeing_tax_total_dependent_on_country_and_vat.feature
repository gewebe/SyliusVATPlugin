@checkout_vat_number
Feature: Seeing tax total dependent on country and vat
    In order to be certain about total tax for vat number
    As an Customer
    I want to see tax total dependent on country and vat number

    Background:
        # eu countries
        Given the store operates in "Belgium"
        And the store has a zone "Belgium" with code "BE"
        And it also has the "Belgium" country member
        And the store operates in "Croatia"
        And the store has a zone "Croatia" with code "HR"
        And it also has the "Croatia" country member
        And the store has a "tax" zone "Croatia tax" with code "HR-tax"
        And it also has the "Croatia" country member
        And the store operates in "France"
        And the store has a zone "France" with code "FR"
        And it also has the "France" country member
        And the store has a "tax" zone "France tax" with code "FR-tax"
        And it also has the "France" country member
        And the store has a zone "European Union" with code "EU"
        And it has the zone named "Belgium"
        And it has the zone named "Croatia"
        And it has the zone named "France"

        # channel
        And the store operates on a channel named "Web"
        And channel "Web" billing data is "YourShirtShop", "Rue Belliard", "1000" "Brussels", "Belgium" with "1234567890" tax ID
        And default tax zone is "BE"

        # taxes
        And the store has included in price "BTW" tax rate of 21% for "VAT" within the "BE" zone
        And the store has included in price "TVA" tax rate of 20% for "VAT" within the "FR-tax" zone
        And the store has "PDV" tax rate of 25% for "VAT" within the "HR-tax" zone

        # product
        And the store has a product "PHP T-Shirt" priced at "$10.00"
        And it belongs to "VAT" tax category

        # shipping
        And the store has "Free" shipping method with "$0.00" fee within the "BE" zone
        And the store has "Post-HR" shipping method with "$4.00" fee within the "HR" zone
        And the store has "Post-FR" shipping method with "$2.50" fee within the "FR" zone
        And shipping method "Post-FR" belongs to "VAT" tax category
        And the store allows paying offline

        # customer
        And I am a logged in customer
        And I have 2 products "PHP T-Shirt" in the cart
        And I am at the checkout addressing step

    @ui
    Scenario: Seeing included tax of 21% within business country
        When I specify the billing address as "Gent", "Merelstraat", "9000", "Belgium" for "Ankh Morpork"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "(Included in price) $3.47"
        And my order total should be "$20.00"

    @ui
    Scenario: Seeing included tax of 21% with valid VAT number within business country
        When I specify the billing address as "Gent", "Merelstraat", "9000", "Belgium" for "Ankh Morpork"
        And I specify the billing vat number as "BE0402231383"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "(Included in price) $3.47"
        And my order total should be "$20.00"

    @ui
    Scenario: Seeing excluded tax of 25% from other than business country
        When I specify the billing address as "Zagreb", "Crni put", "10000", "Croatia" for "Sabina Babic"
        And I try to complete the addressing step
        And I proceed with "Post-HR" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$5.00"
        And my order shipping should be "$4.00"
        And my order total should be "$29.00"

    @ui
    Scenario: Seeing excluded tax of 0% with valid VAT number from other than business country
        When I specify the billing address as "Zagreb", "Crni put", "10000", "Croatia" for "Sabina Babic"
        And I specify the billing vat number as "HR00183562417"
        And I try to complete the addressing step
        And I proceed with "Post-HR" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$0.00"
        And my order shipping should be "$4.00"
        And my order total should be "$24.00"

    @ui
    Scenario: Seeing included tax of 20% from other than business country
        When I specify the billing address as "Marseille", "Chaude Ruelle", "13003", "France" for "Pierre Simon"
        And I try to complete the addressing step
        And I proceed with "Post-FR" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "(Included in price) $3.75"
        And my order shipping should be "$2.50"
        And my order total should be "$22.50"

    @ui
    Scenario: Seeing included tax of 0% with valid VAT number from other than business country
        When I specify the billing address as "Marseille", "Chaude Ruelle", "13003", "France" for "Pierre Simon"
        And I specify the billing vat number as "FR91552118465"
        And I try to complete the addressing step
        And I proceed with "Post-FR" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$0.00"
        And my order shipping should be "$2.08"
        And my order total should be "$18.76"
