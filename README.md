# Concerto Project

Concerto is a project management tool designed to help you organize and manage your ideas efficiently. It supports tagging, categorization, and provides a user-friendly interface for tracking the progress of your ideas.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [Future Additions](#future-additions)
- [License](#license)

## Overview

Concerto is designed to be a lightweight, intuitive tool for managing your ideas. Whether you're working on a personal project or collaborating with a team, Concerto helps you keep track of your ideas and their statuses with ease.

## Features

- **Tagging and Categorization**: Organize your ideas with tags and categories.
- **User Management**: Securely store user information such as email, Google ID, Discord ID, GitHub ID, and username.
- **Status Tracking**: Track the progress of ideas with statuses like To Do, In Progress, and Done.
- **Responsive Design**: Optimized for both desktop and mobile views.
  
## Demo

A live demo is available at: [concerto.virgilebaisnee.fr](https://concerto.virgilebaisnee.fr)

## Installation

### Prerequisites

- [PHP](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/download/)
- [Node.js](https://nodejs.org/) and [npm](https://www.npmjs.com/)
- [Symfony CLI](https://symfony.com/download)

### Steps

1. **Clone the repository**:
    ```bash
    git clone https://github.com/KcivazB/concerto.git
    cd concerto
    ```

2. **Install PHP dependencies**:
    ```bash
    composer install
    ```

3. **Install JavaScript dependencies**:
    ```bash
    npm install
    ```

4. **Setup environment variables**:
    Copy `.env.example` to `.env` and configure your database and other environment variables.
    ```bash
    cp .env.example .env
    ```

5. **Run migrations**:
    ```bash
    php bin/console doctrine:migrations:migrate
    ```

6. **Build assets**:
    ```bash
    npm run build
    ```

7. **Start the server**:
    ```bash
    symfony server:start
    ```

## Usage

Once the server is running, open your browser and navigate to `http://localhost:8000`. You should see the Concerto landing page. From here, you can register a new user, log in, and start managing your ideas.

Please make sure to update tests as appropriate.

## Future Additions
  - Docker support
  - Grafana integration
  - Tasks prioritizing
  - i18n

### Grafana Integration

I plan to integrate Grafana for advanced analytics and monitoring. This will allow users to visualize data and track metrics more effectively.

### Docker Support

Docker support is coming soon! This will allow you to run the project in a containerized environment, making it easier to deploy and manage.

---

*Note: This project is in active development. Features and functionalities are subject to change.*
