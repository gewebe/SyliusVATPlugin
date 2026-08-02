@address_book_vat_number
Feature: Adding a new address with VAT number to the book
    In order to have saved addresses with VAT number on my account
    As a Customer
    I want to be able to add a new address with VAT number to address book

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Portugal" and "Germany"
        And I am a logged in customer
        And I want to add a new address to my address book
        And I specify the address as "Ankh Morpork", "Frost Alley", "20355", "Hamburg", "Germany", "Hamburg"

    @ui
    Scenario: Adding address for a company where the VAT number is required
        When I specify my company as "Jodeliti"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding address for a country where the VAT number is required
        When I specify the address as "Maria da Graça", "Praça do Comércio", "1100-016", "Lisboa", "Portugal", "Lisboa"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding address with wrong VAT number to address book
        When I specify my VAT number as "XY118716043"
        And I add it
        Then I should still be on the address addition page
        And I should be notified about 1 errors

    @ui
    Scenario: Adding and updating address with a valid VAT number to address book
        When I specify my VAT number as "DE118716043"
        And I add it
        Then I should be notified that the address has been successfully added
        And I see in the database that vat number for "Ankh Morpork" is valid
        When I try to edit the address of "Ankh Morpork"
        And I specify my VAT number as "DE999999999"
        And I save my changed address
        Then I should be notified that the address has been successfully updated
        And I see in the database that vat number for "Ankh Morpork" is not valid

