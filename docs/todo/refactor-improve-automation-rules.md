## Issue
- When I go to the automation page and click the "Apply all Rules" button the rules are not applied. I see now output and no errors or actions in logs. I think there should be a report of the results of the rules.
- When I click the "Test Rule" button for the current rule I see no output or errors in the logs. I think there should be a report that shows the rule found several transactions and show what the transactions are so the user can confirm this rule is configured correctly.

## Changes
- In the add or edit rule window there should be a test button that will show a modal with latest 5 results found in the transactions that match the rule.
- In the current section that shows the rules the "Test Rule" button should be used to run the rule on the transactions.
- The button at the top of the page, "Apply all Rules" should be used to apply all rules to the transactions.
- When the rules are run on a single rule or all the rules a modal should show the results for each rule.

## Testing
Remember the application is using PEST 4 for testing witch has browser testing built in. You can review the docs `docs/packages/repomix-pestphp-docs.md` or using context7 to get more info where needed.
