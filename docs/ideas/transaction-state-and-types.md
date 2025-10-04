# Transaction State and Types

## Types
Transaction types are:
- Income
- Expense
- Transfer

## State
Transaction state is:
- Planned 
    -- default state when adding a transaction to calendar any date after today.
    -- all transactions added by import csv file are planned.
- Entered
    -- Entering a transaction manually today or a previous day will be set as entered as it has happened and you should be able to confirm this.
    -- These should be displayed on the calendar as with the background color alpha reduced to 70%.
