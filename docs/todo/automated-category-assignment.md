# Automated Category Assignment

This feature will allow the user to set a specific category for a transaction type based on a rule created by the user. The rule will be evaluated against the transaction description and the category will be assigned accordingly.

## Rules
- Account
- Description content. This will need to be configure as a subset of the description. Allowing "Starts with", "Contains", "Ends with", etc.
- Transaction Amount

## Implementation
- Need a new section Automation witch will have sub sections for creating the rules.
- Rules can be run against the current transactions in the system and the results will be displayed so the user knows the rule is working.
- When the user setting the rule for description, this will be a predictive text box searching all the descriptions of the transactions, filtered by the account selected.
