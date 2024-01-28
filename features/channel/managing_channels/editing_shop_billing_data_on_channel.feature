@channel_vat_number
Feature: Editing shop billing data with vat number on channel
    In order to have proper shop billing data with vat number on shop-related documents
    As an Administrator
    I want to be able to edit shop billing data with vat number on a channel

    Background:
        Given the store operates on a channel named "Web"
        And the store ships to "Austria"
        And channel "Web" billing data is "Joda", "Burgring 1", "1010" "Vienna", "Austria" with "123" tax ID, "ATU" VAT No.
        And I am logged in as an administrator

    @ui
    Scenario: Editing shop billing data with vat number on channel
        When I want to modify a channel "Web"
        And I specify company as "Jodeliti"
        And I specify tax ID as "99-999/9999"
        And I specify VAT number as "ATU12345678"
        And I specify shop billing address as "Opernring 1", "8010" "Graz", "Austria"
        And I save my changes
        Then I should be notified that it has been successfully edited
        And this channel company should be "Jodeliti"
        And this channel tax ID should be "99-999/9999"
        And this channel VAT number should be "ATU12345678"
        And this channel shop billing address should be "Opernring 1", "8010" "Graz", "Austria"
