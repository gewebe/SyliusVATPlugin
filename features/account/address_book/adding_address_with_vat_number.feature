@address_book_vat_number
Feature: Adding a new address with vat number to the book
    In order to have saved addresses with vat number on my account
    As a Customer
    I want to be able to add a new address with vat number to address book

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Germany"
        And I am a logged in customer
        And I want to add a new address to my address book
        And I specify the address as "Ankh Morpork", "Frost Alley", "20355", "Hamburg", "Germany", "Hamburg"

    @ui
    Scenario: Adding address with correct vat number to address book
        When I specify my vat number as "DE118716043"
        And I add it
        Then I should be notified that the address has been successfully added

    @ui
    Scenario: Adding address with wrong vat number to address book
        When I specify my vat number as "XY118716043"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors
