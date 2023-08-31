# Simple PHP Blockchain

## Requirements

- PHP >= 7.4

## Running the Code

1. Ensure you have PHP 7.4 or newer installed.
2. Run the PHP script from your terminal or command prompt: `php Blockchain.php`.

## Overview

This code provides a simple demonstration of how a basic blockchain works, written in PHP. It includes:

- **Users** with a name and balance.
- **Transactions** that move amounts between users.
- **Blocks** containing a list of transactions.
- **Blockchain** which is a list of mined blocks.

When running, the program will:

1. Set up three users with initial balances.
2. Display the initial balances.
3. Create a block.
4. Add a Coinbase transaction (block reward) to the block.
5. Add two more sample transactions.
6. Mine the block (find a nonce so that the block's hash begins with a number of zeroes specified by the 'difficulty').
7. Add the block to the blockchain.
8. Display the contents of the blockchain.
9. Display the final balances of the users.

This is a basic and educational example to demonstrate core blockchain concepts. In a real-world scenario, many other factors and verifications would be involved.
