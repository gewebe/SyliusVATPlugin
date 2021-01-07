@customer_login_vat_number
Feature: Signing in to the store with expired vat number validation
    In order to have always a validated vat number
    As a Customer
    I want to shop with a valid vat number

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Germany"
        And there is a customer account "ted@example.com" identified by "bear"
        And their default address is "Ankh Morpork", "Frost Alley", "20355", "Hamburg", "Germany", "Hamburg"
        And I want to log in
        And I specify the username as "ted@example.com"
        And I specify the password as "bear"

    @ui
    Scenario: Trying to sign in with vat number validated yesterday
        Given their default address VAT number is "DE118716043" validated since "1 day ago"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was validated "1 day ago"

    @ui
    Scenario: Trying to sign in with expired valid vat number
        Given their default address VAT number is "DE118716043" validated since "2020-10-10 11:00"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was validated "today"

    @ui
    Scenario: Trying to sign in with expired invalid vat number
        Given their default address VAT number is "DE123123123" validated since "2020-10-10 11:00"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was invalidated "today"
