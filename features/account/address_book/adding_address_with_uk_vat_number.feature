@address_book_uk_vat_number
Feature: Adding a new address with a UK VAT number to the book
    In order to have my United Kingdom company address saved on my account
    As a Customer
    I want my UK VAT number to be verified with the HMRC service

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "United Kingdom" and "Germany"
        And I am a logged in customer
        And I want to add a new address to my address book
        And I specify the address as "Emma Watson", "Baker Street", "NW1 6XE", "London", "United Kingdom", "Greater London"

    @ui
    Scenario: Adding address with a registered UK VAT number to address book
        When I specify my VAT number as "GB123456789"
        And I add it
        Then I should be notified that the address has been successfully added

    @ui
    Scenario: Adding address with a registered UK VAT number without country prefix
        When I specify my VAT number as "123456789"
        And I add it
        Then I should be notified that the address has been successfully added

    @ui
    Scenario: Adding address with an unregistered UK VAT number to address book
        When I specify my VAT number as "GB666666666"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding address with a malformed UK VAT number to address book
        When I specify my VAT number as "GB666XY"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding address with a VAT number of another country to address book
        When I specify my VAT number as "DE123456789"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding and updating address with a UK VAT number
        When I specify my VAT number as "GB123456789"
        And I add it
        Then I should be notified that the address has been successfully added
        And I see in the database that vat number for "Emma Watson" is valid
        When I try to edit the address of "Emma Watson"
        And I specify my VAT number as "123456789"
        And I save my changed address
        Then I should be notified that the address has been successfully updated
        And I see in the database that vat number for "Emma Watson" is valid
        When I try to edit the address of "Emma Watson"
        And I specify my VAT number as "GB999999999"
        And I save my changed address
        Then I should be notified that the address has been successfully updated
        And I see in the database that vat number for "Emma Watson" is not valid
