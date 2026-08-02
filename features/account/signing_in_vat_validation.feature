@customer_login_vat_number
Feature: Signing in to the store with expired VAT number validation
    In order to have always a validated VAT number
    As a Customer
    I want to shop with a valid VAT number

    Background:
        Given the store operates on a channel named "Web"
        And the store operates in "Belgium"
        And there is a customer account "ted@example.com" identified by "bear"
        And their default address is "Ankh Morpork", "Merelstraat", "9000", "Gent", "Belgium", "Gent"
        And I want to log in
        And I specify the username as "ted@example.com"
        And I specify the password as "bear"

    @ui
    Scenario: Trying to sign in with VAT number validated yesterday
        Given their default address VAT number is "BE0402231383" validated since "1 day ago"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was validated "1 day ago"

    @ui
    Scenario: Trying to sign in with expired valid VAT number
        Given their default address VAT number is "BE0402231383" validated since "2020-10-10 11:00"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was validated "today"

    @ui
    Scenario: Trying to sign in with expired invalid VAT number
        Given their default address VAT number is "BE1231231231" validated since "2020-10-10 11:00"
        When I try to log in
        Then I should be logged in
        And my VAT number for the default address was invalidated "today"
