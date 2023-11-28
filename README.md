# Discord PHP Bot

A Proof of Concept Discord Bot written in PHP.

This PoC uses [ReactPHP](reactphp.org) components to open a WebSocket connection to the Discord Gateway API and responds
to any messages containing "hello" with a 👋 emoji.

## Installation

Requirements: PHP 8.3 or up.

- Clone this repository
- run `composer install`
- copy the .env.example file and provide your own token

## Running the bot

Start the bot with `bin/bot.php`;
