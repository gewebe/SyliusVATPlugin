@checkout_vat_number
Feature: Seeing tax total dependent on country and vat
    In order to be certain about total tax for VAT number
    As an Customer
    I want to see tax total dependent on country and VAT number

    Background:
        # "BE0123456789", "HR00123456789" and "FR00123456789" are registered with the fake VIES service
        # be
        Given the store operates in "Belgium"
        And the store has a zone "Belgium VAT" with code "BE-vat"
        And it also has the "Belgium" country member
        # hr
        And the store operates in "Croatia"
        And the store has a zone "Croatia VAT" with code "HR-vat"
        And it also has the "Croatia" country member
        # fr
        And the store operates in "France"
        And the store has a "tax" zone "France VAT" with code "FR-vat"
        And it also has the "France" country member
        And the store has a "shipping" zone "France Shipping" with code "FR-shipping"
        And it also has the "France" country member
        # eu
        And the store has a zone "European Union VAT" with code "EU"
        And it has the zone named "Belgium VAT"
        And it has the zone named "Croatia VAT"
        And it has the zone named "France VAT"

        # channel
        And the store operates on a channel named "Web"
        And channel "Web" billing data is "ShirtShop", "Rue Belliard", "1000" "Brussels", "Belgium" with "123" tax ID, "BE199" VAT No.
        And default tax zone is "BE-vat"

        # taxes
        And the store has included in price "BTW" tax rate of 21% for "VAT" within the "BE-vat" zone
        And the store has included in price "TVA" tax rate of 20% for "VAT" within the "FR-vat" zone
        And the store has included in price "TVA" tax rate of 20% for "VAT" within the "FR-shipping" zone
        And the store has "PDV" tax rate of 25% for "VAT" within the "HR-vat" zone

        # product
        And the store has a product "PHP T-Shirt" priced at "$10.00"
        And it belongs to "VAT" tax category

        # shipping
        And the store has "Free" shipping method with "$0.00" fee within the "BE-vat" zone
        And the store has "Post-HR" shipping method with "$4.00" fee within the "HR-vat" zone
        And the store has "Post-FR" shipping method with "$2.50" fee within the "FR-shipping" zone
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
        And my tax total should be "$3.47"
        And my order total should be "$20.00"

    @ui
    Scenario: Seeing included tax of 21% with valid VAT number within business country
        When I specify the billing address as "Gent", "Merelstraat", "9000", "Belgium" for "Ankh Morpork"
        And I specify the billing VAT number as "BE0123456789"
        And I try to complete the addressing step
        And I proceed with "Free" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$3.47"
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
        And I specify the billing VAT number as "HR00123456789"
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
        And my tax total should be "$3.75"
        And my order shipping should be "$2.50"
        And my order total should be "$22.50"

    @ui
    Scenario: Seeing included tax of 0% with valid VAT number from other than business country
        When I specify the billing address as "Marseille", "Chaude Ruelle", "13003", "France" for "Pierre Simon"
        And I specify the billing VAT number as "FR00123456789"
        And I try to complete the addressing step
        And I proceed with "Post-FR" shipping method and "Offline" payment
        Then I should be on the checkout summary step
        And my tax total should be "$0.00"
        And my order shipping should be "$2.08"
        And my order total should be "$18.76"
