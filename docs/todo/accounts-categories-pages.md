Here is a brief description of the accounts, categories and pages.

## Accounts

This can be a link in the left sidebar under Dashboard "Accounts".
The page will show each account int the system with name and current balance. These could be in cards in a row.
The top section could be used for a simple for to add a new account. 

### Details of Add Account

This section should have the same fields as the example `docs/todo/add-account-modal.png`
- balance will be required negative for accounts that have "Credit Limit" selected.
- Selecting the "Credit Limit" will show a hidden input field for the credit limit.
- Category is for adding or hiding balance from the total balance.

### Details of Account Category

Example of this modal is `docs/todo/add-category-account.png`
- "Display Category in Accounts List" will show a accounts in a separate section on the Accounts page.
- Each category will be a row on the Accounts page
- each row should have a total for the Account Category

## Transaction Category

This will be the type of transaction entered in the transaction modal. It will facilitate the breakdown of income and expense. Categories can be nested using `/`.

### Example

- `Food/Grocerries`
- `Food/Restaurant`
- `Food/Fast Food`

They will display as you see but saved using lowercase tags.

### Category Requirements

- The Category page will show the list of categories with a search function. 
- Each category will show the the total number of transactions associated with it.
- Category can not be deleted if it has transactions associated with it.
- Section at the top will allow adding a new category in a single input allowing the `Bills/Electricity` format.
- As you type it will search the current list to show what is in place already so when I type Bills, not case sensitive, it will show the existing categories like `Bills/Water`, `Bills/Electricity`, etc.
